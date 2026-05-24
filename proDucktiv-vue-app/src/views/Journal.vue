<template>
    <div class="d-flex flex-column flex-md-row overflow-hidden" style="height:calc(100vh - 65px);">

        <!-- Mobile sidebar toggle -->
        <div class="d-md-none border-bottom bg-light px-3 py-2 flex-shrink-0">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="offcanvas"
                data-bs-target="#journalSidebar">
                <i class="bi bi-journals me-1"></i> Journals
            </button>
        </div>

        <!-- Sidebar (offcanvas on mobile, inline on desktop) -->
        <div id="journalSidebar"
            class="offcanvas-md offcanvas-start d-flex flex-column border-end bg-light flex-shrink-0"
            style="width:240px;">

            <div class="offcanvas-header d-md-none border-bottom">
                <span class="fw-bold offcanvas-title">Journals</span>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>

            <div class="d-none d-md-flex justify-content-between align-items-center p-3 border-bottom">
                <span class="fw-bold">Journals</span>
                <button class="btn btn-sm btn-outline-success" @click="openNewJournal">+ New</button>
            </div>

            <div class="d-md-none px-3 py-2 border-bottom">
                <button class="btn btn-sm btn-outline-success w-100"
                    data-bs-dismiss="offcanvas" @click="openNewJournal">+ New Journal</button>
            </div>

            <div class="overflow-y-auto flex-grow-1">
                <div v-if="loadingJournals" class="p-3 text-muted small">Loading...</div>
                <div v-else-if="journals.length === 0" class="p-3 text-muted small">No journals yet.</div>

                <div v-for="j in journals" :key="j.journal_id" class="p-3 border-bottom"
                    :class="activeJournal?.journal_id === j.journal_id ? 'bg-primary text-white' : ''"
                    style="cursor:pointer;" data-bs-dismiss="offcanvas" @click="loadJournal(j)">
                    <div class="small fw-medium text-truncate">{{ j.journal_name }}</div>
                    <div class="d-flex align-items-center gap-1 mt-1">
                        <span class="small"
                            :class="activeJournal?.journal_id === j.journal_id ? 'text-white-50' : 'text-muted'">
                            {{ formatSidebarDate(j.journal_date) }}
                        </span>
                        <span v-if="j.journal_status"
                            :class="statusBadgeClass(j.journal_status, activeJournal?.journal_id === j.journal_id)"
                            class="badge rounded-pill text-capitalize" style="font-size:10px;">
                            {{ j.journal_status }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Main area -->
        <div class="d-flex flex-column flex-grow-1 overflow-hidden">

            <!-- Empty state -->
            <div v-if="!activeJournal && !creatingNew"
                class="d-flex flex-column align-items-center justify-content-center flex-grow-1 gap-2 text-muted">
                <p class="mb-0">Select a journal or create a new one.</p>
                <button class="btn btn-success btn-sm" @click="openNewJournal">New Journal</button>
            </div>

            <!-- Editor -->
            <div v-else class="d-flex flex-column flex-grow-1 overflow-hidden">

                <!-- Toolbar -->
                <div class="d-flex flex-column border-bottom bg-white flex-shrink-0">
                    <div class="d-flex flex-wrap align-items-center gap-2 p-2">
                        <input v-model="form.journal_name" class="form-control form-control-sm"
                            placeholder="Journal name" maxlength="120" style="min-width:120px;flex:1;"
                            @input="markDirty" />
                        <input v-model="form.journal_date" type="date" class="form-control form-control-sm"
                            style="width:160px;" @change="markDirty" />
                        <span class="badge text-bg-secondary text-nowrap">
                            {{ pages.length }} page{{ pages.length !== 1 ? 's' : '' }}
                        </span>
                        <button class="btn btn-sm text-nowrap"
                            :class="isDirty || creatingNew ? 'btn-primary' : 'btn-outline-secondary'" :disabled="saving"
                            @click="saveJournal">
                            {{ saving ? 'Saving…' : isDirty || creatingNew ? 'Save' : 'Saved ✓' }}
                        </button>
                        <button v-if="activeJournal && !creatingNew" class="btn btn-sm btn-outline-danger"
                            @click="showDeleteModal = true">
                            Delete
                        </button>
                    </div>

                    <!-- Status row -->
                    <div class="d-flex align-items-center gap-2 px-2 pb-2">
                        <span class="small text-muted text-nowrap">How was today?</span>
                        <button v-for="s in STATUS_OPTIONS" :key="s.value" class="btn btn-sm"
                            :class="form.journal_status === s.value ? s.activeClass : s.inactiveClass"
                            @click="setStatus(s.value)">
                            {{ s.label }}
                        </button>
                    </div>
                </div>

                <!-- Writing area -->
                <div class="flex-grow-1 d-flex flex-column p-4 overflow-hidden">
                    <textarea ref="textareaRef" v-model="pages[currentPage]" class="flex-grow-1 w-100 border-0"
                        style="resize:none;outline:none;font-size:15px;line-height:1.8;overflow:hidden;"
                        placeholder="Begin writing…" @keydown="onKeydown" @input="onInput"></textarea>

                    <!-- Bottom bar -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2 flex-shrink-0">
                        <Paginate v-if="pages.length > 1" :page-count="pages.length" :click-handler="goToPage"
                            prev-text="← Prev" next-text="Next →" container-class="pagination pagination-sm mb-0"
                            page-class="page-item" page-link-class="page-link" prev-class="page-item"
                            prev-link-class="page-link" next-class="page-item" next-link-class="page-link"
                            :force-page="currentPage + 1" />
                        <div v-else></div>
                        <span class="text-muted" style="font-size:11px;">Page {{ currentPage + 1 }}</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Delete modal -->
        <div v-if="showDeleteModal" class="modal d-block" style="background:rgba(0,0,0,0.5);"
            @click.self="showDeleteModal = false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete journal?</h5>
                        <button class="btn-close" @click="showDeleteModal = false"></button>
                    </div>
                    <div class="modal-body text-muted small">
                        Permanently delete <strong class="text-dark">{{ activeJournal?.journal_name }}</strong>? This
                        cannot be undone.
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-sm btn-secondary" @click="showDeleteModal = false">Cancel</button>
                        <button class="btn btn-sm btn-danger" @click="deleteJournal">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast -->
        <transition name="fade">
            <div v-if="toast.show" class="position-fixed bottom-0 end-0 m-3" style="z-index:1080;">
                <div class="toast show" :class="toast.type === 'danger' ? 'text-bg-danger' : 'text-bg-dark'">
                    <div class="toast-body">{{ toast.message }}</div>
                </div>
            </div>
        </transition>

    </div>
</template>

<script>
import Paginate from 'vuejs-paginate-next'

const JOURNALS_API = '/resources/api_journals.php'

function getLoggedInUserId() {
    try {
        const user = localStorage.getItem('currentUser')
        if (!user) return 0 // if not logged in needs to sign in

        const parsed = JSON.parse(user)
        const id = Number(parsed.user_id)

        return Number.isFinite(id) ? id : 0
    } catch {
        return 0
    }
}

const USER_ID = getLoggedInUserId()
const PAGE_CHAR_LIMIT = 500

export default {
    components: { Paginate },

    data() {
        return {
            userId: getLoggedInUserId(),
            STATUS_OPTIONS: [
                { value: 'great', label: 'Great', activeClass: 'btn-success', inactiveClass: 'btn-outline-success' },
                { value: 'average', label: 'Average', activeClass: 'btn-warning text-dark', inactiveClass: 'btn-outline-warning' },
                { value: 'bad', label: 'Bad', activeClass: 'btn-danger', inactiveClass: 'btn-outline-danger' },
            ],
            journals: [],
            activeJournal: null,
            creatingNew: false,
            loadingJournals: false,
            saving: false,
            isDirty: false,
            showDeleteModal: false,
            form: {
                journal_name: '',
                journal_date: new Date().toISOString().split('T')[0],
                journal_status: '',
            },
            pages: [''],
            currentPage: 0,
            toast: { show: false, message: '', type: 'success' },
            toastTimer: null,
        }
    },

    mounted() {
        if (!this.userId) {
            this.showToast('No logged-in user found. Please sign in.', 'danger')
            return
        }

        this.fetchJournals()
    },

    methods: {

        // Fetch Journals
        async fetchJournals() {
            this.loadingJournals = true
            try {
                const res = await fetch(`${JOURNALS_API}?fld=user_id&key=${this.userId}`)
                if (!res.ok) throw new Error(`HTTP ${res.status}`)

                const text = await res.text()
                let json

                try {
                    json = JSON.parse(text)
                } catch {
                    throw new Error('API returned HTML instead of JSON. Check Vite /resources proxy and PHP server root.')
                }

                this.journals = Array.isArray(json) ? json : []
            } catch (err) {
                this.showToast(`Failed to load journals: ${err.message}`, 'danger')
            } finally {
                this.loadingJournals = false
            }
        },

        // Save Journal
        async saveJournal() {
            if (!this.isDirty && this.activeJournal) return

            if (!this.form.journal_name.trim()) {
                this.showToast('Please enter a journal name.', 'danger')
                return
            }
            if (!this.pages.join('').trim()) {
                this.showToast('Please write something before saving.', 'danger')
                return
            }
            if (!this.form.journal_status) {
                this.showToast('Please select a mood before saving.', 'danger')
                return
            }

            this.saving = true

            const payload = {
                user_id: this.userId,
                journal_name: this.form.journal_name,
                journal_body: this.pages.join(''),
                journal_date: this.form.journal_date,
                journal_status: this.form.journal_status,
            }

            try {
                let saved
                if (this.creatingNew || !this.activeJournal) {
                    const res = await fetch(JOURNALS_API, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    })
                    if (!res.ok) throw new Error()
                    saved = await res.json()
                    this.journals.unshift(saved)
                    this.activeJournal = saved
                    this.creatingNew = false
                } else {
                    const res = await fetch(`${JOURNALS_API}?fld=journal_id&key=${this.activeJournal.journal_id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    })
                    if (!res.ok) throw new Error()
                    saved = await res.json()
                    const idx = this.journals.findIndex(j => j.journal_id === saved.journal_id)
                    if (idx !== -1) this.journals[idx] = saved
                    this.activeJournal = saved
                }
                this.isDirty = false
                this.showToast('Journal saved', 'success')
            } catch (err) {
                this.showToast(`Save failed: ${err.message}`, 'danger')
            } finally {
                this.saving = false
            }
        },

        // Delete Journal
        async deleteJournal() {
            this.showDeleteModal = false
            try {
                const res = await fetch(`${JOURNALS_API}?fld=journal_id&key=${this.activeJournal.journal_id}`, { method: 'DELETE' })
                if (!res.ok) throw new Error(`HTTP ${res.status}`)
                this.journals = this.journals.filter(j => j.journal_id !== this.activeJournal.journal_id)
                this.activeJournal = null
                this.creatingNew = false
                this.pages = ['']
                this.currentPage = 0
                this.showToast('Journal deleted', 'danger')
            } catch (err) {
                this.showToast(`Delete failed: ${err.message}`, 'danger')
            }
        },

        // New Journal
        openNewJournal() {
            this.activeJournal = null
            this.creatingNew = true
            this.isDirty = false
            this.form.journal_name = ''
            this.form.journal_date = this.todayISO()
            this.form.journal_status = ''
            this.pages = ['']
            this.currentPage = 0
        },

        // Load Journal
        loadJournal(j) {
            this.activeJournal = j
            this.creatingNew = false
            this.isDirty = false
            this.form.journal_name = j.journal_name
            this.form.journal_date = j.journal_date
            this.form.journal_status = j.journal_status || ''
            this.splitBodyIntoPages(j.journal_body || '')
        },

        // Page Management
        splitBodyIntoPages(body) {
            if (!body) { this.pages = ['']; this.currentPage = 0; return }
            const chunks = []
            let remaining = body
            while (remaining.length > 0) {
                chunks.push(remaining.slice(0, PAGE_CHAR_LIMIT))
                remaining = remaining.slice(PAGE_CHAR_LIMIT)
            }
            this.pages = chunks.length ? chunks : ['']
            this.currentPage = 0
        },

        onKeydown(e) {
            if (e.key !== 'Backspace') return
            const ta = this.$refs.textareaRef
            if (ta && ta.selectionStart === 0 && ta.selectionEnd === 0 && this.currentPage > 0) {
                e.preventDefault()
                const prev = this.pages[this.currentPage - 1]
                const cur = this.pages[this.currentPage]
                if ((prev + cur).length <= PAGE_CHAR_LIMIT) {
                    this.pages[this.currentPage - 1] = prev + cur
                    this.pages.splice(this.currentPage, 1)
                }
                this.currentPage--
                this.$nextTick(() => {
                    if (this.$refs.textareaRef) this.$refs.textareaRef.setSelectionRange(prev.length, prev.length)
                })
                this.markDirty()
            }
        },

        onInput() {
            const text = this.pages[this.currentPage]
            if (text.length > PAGE_CHAR_LIMIT) {
                const kept = text.slice(0, PAGE_CHAR_LIMIT)
                const overflow = text.slice(PAGE_CHAR_LIMIT)
                this.pages[this.currentPage] = kept
                const next = this.currentPage + 1
                if (this.pages[next] !== undefined) {
                    this.pages[next] = overflow + this.pages[next]
                } else {
                    this.pages.push(overflow)
                }
                this.currentPage = next
            }
            this.markDirty()
        },

        goToPage(pageNum) {
            this.currentPage = pageNum - 1
        },

        // Status Selection
        setStatus(value) {
            this.form.journal_status = this.form.journal_status === value ? '' : value
            this.markDirty()
        },

        statusBadgeClass(status, isActive) {
            if (isActive) return 'text-white bg-opacity-75 ' + (status === 'great' ? 'bg-success' : status === 'average' ? 'bg-warning' : 'bg-danger')
            return status === 'great' ? 'text-bg-success' : status === 'average' ? 'text-bg-warning' : 'text-bg-danger'
        },

        // Dirty state is for tracking unsaved changes to the journal, so that the user cannot save whilst, no changes have been made
        markDirty() {
            this.isDirty = true
        },

        formatSidebarDate(iso) {
            if (!iso) return ''
            return new Date(iso + 'T00:00:00').toLocaleDateString('en-AU', {
                day: 'numeric', month: 'short', year: 'numeric',
            })
        },

        todayISO() {
            return new Date().toISOString().split('T')[0]
        },

        showToast(message, type = 'success') {
            clearTimeout(this.toastTimer)
            this.toast.show = true
            this.toast.message = message
            this.toast.type = type
            this.toastTimer = setTimeout(() => { this.toast.show = false }, 2800)
        },

    },
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
