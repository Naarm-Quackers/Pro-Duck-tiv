<template>
    <div class="d-flex overflow-hidden" style="height:calc(100vh - 65px);">

        <!-- Sidebar -->
        <div class="d-flex flex-column border-end bg-light" style="width:240px;min-width:240px;">

            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <span class="fw-bold">Journals</span>
                <button class="btn btn-sm btn-outline-secondary" @click="openNewJournal">+ New</button>
            </div>

            <div class="overflow-y-auto flex-grow-1">
                <div v-if="loadingJournals" class="p-3 text-muted small">Loading...</div>
                <div v-else-if="journals.length === 0" class="p-3 text-muted small">No journals yet.</div>

                <div v-for="j in journals" :key="j.journal_id" class="p-3 border-bottom"
                    :class="activeJournal?.journal_id === j.journal_id ? 'bg-primary text-white' : ''"
                    style="cursor:pointer;" @click="loadJournal(j)">
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
                <button class="btn btn-primary btn-sm" @click="openNewJournal">New Journal</button>
            </div>

            <!-- Editor -->
            <div v-else class="d-flex flex-column flex-grow-1 overflow-hidden">

                <!-- Toolbar -->
                <div class="d-flex flex-column border-bottom bg-white flex-shrink-0">
                    <div class="d-flex align-items-center gap-2 p-2">
                        <input v-model="form.journal_name" class="form-control form-control-sm"
                            placeholder="Journal name" maxlength="120" @input="markDirty" />
                        <input v-model="form.journal_date" type="date" class="form-control form-control-sm"
                            style="width:160px;" @change="markDirty" />
                        <span class="badge text-bg-secondary text-nowrap">
                            {{ pages.length }} page{{ pages.length !== 1 ? 's' : '' }}
                        </span>
                        <button class="btn btn-sm text-nowrap"
                            :class="isDirty ? 'btn-primary' : 'btn-outline-secondary'" :disabled="saving"
                            @click="saveJournal">
                            {{ saving ? 'Saving…' : isDirty ? 'Save' : 'Saved ✓' }}
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
                    <textarea
                        ref="textareaRef"
                        v-model="pages[currentPage]"
                        class="flex-grow-1 w-100 border-0"
                        style="resize:none;outline:none;font-size:15px;line-height:1.8;overflow:hidden;"
                        placeholder="Begin writing…"
                        @keydown="onKeydown"
                        @input="onInput"
                    ></textarea>

                    <!-- Bottom bar -->
                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2 flex-shrink-0">
                        <Paginate
                            v-if="pages.length > 1"
                            :page-count="pages.length"
                            :click-handler="goToPage"
                            prev-text="← Prev"
                            next-text="Next →"
                            container-class="pagination pagination-sm mb-0"
                            page-class="page-item"
                            page-link-class="page-link"
                            prev-class="page-item"
                            prev-link-class="page-link"
                            next-class="page-item"
                            next-link-class="page-link"
                            :force-page="currentPage + 1"
                        />
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

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import Paginate from 'vuejs-paginate-next'

const JOURNALS_API = '/cos30043/s105337347/proDucktiv/resources/api_journals.php'
const USER_ID = 2 // replace with the logged-in user's real ID
const PAGE_CHAR_LIMIT = 1800

const STATUS_OPTIONS = [
    { value: 'great', label: 'Great', activeClass: 'btn-success', inactiveClass: 'btn-outline-success' },
    { value: 'average', label: 'Average', activeClass: 'btn-warning text-dark', inactiveClass: 'btn-outline-warning' },
    { value: 'bad', label: 'Bad', activeClass: 'btn-danger', inactiveClass: 'btn-outline-danger' },
]

const journals = ref([])
const activeJournal = ref(null)
const creatingNew = ref(false)
const loadingJournals = ref(false)
const saving = ref(false)
const isDirty = ref(false)
const showDeleteModal = ref(false)

const form = reactive({
    journal_name: '',
    journal_date: todayISO(),
    journal_status: '',
})

const pages = ref([''])
const currentPage = ref(0)
const textareaRef = ref(null)

const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimer = null

onMounted(fetchJournals)

// Fetch Journals
async function fetchJournals() {
    loadingJournals.value = true
    try {
        const res = await fetch(`${JOURNALS_API}?fld=user_id&key=${USER_ID}`)
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        journals.value = await res.json()
    } catch (err) {
        showToast(`Failed to load journals: ${err.message}`, 'danger')
    } finally {
        loadingJournals.value = false
    }
}

// Save Journal
async function saveJournal() {
    if (!isDirty.value && activeJournal.value) return
    saving.value = true

    const payload = {
        user_id: USER_ID,
        journal_name: form.journal_name || 'Untitled',
        journal_body: pages.value.join(''),
        journal_date: form.journal_date,
        journal_status: form.journal_status || null,
    }

    try {
        let saved
        if (creatingNew.value || !activeJournal.value) {
            const res = await fetch(JOURNALS_API, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            })
            if (!res.ok) throw new Error()
            saved = await res.json()
            journals.value.unshift(saved)
            activeJournal.value = saved
            creatingNew.value = false
        } else {
            const res = await fetch(`${JOURNALS_API}?fld=journal_id&key=${activeJournal.value.journal_id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            })
            if (!res.ok) throw new Error()
            saved = await res.json()
            const idx = journals.value.findIndex(j => j.journal_id === saved.journal_id)
            if (idx !== -1) journals.value[idx] = saved
            activeJournal.value = saved
        }
        isDirty.value = false
        showToast('Journal saved', 'success')
    } catch (err) {
        showToast(`Save failed: ${err.message}`, 'danger')
    } finally {
        saving.value = false
    }
}

// Delete Journal
async function deleteJournal() {
    showDeleteModal.value = false
    try {
        const res = await fetch(`${JOURNALS_API}?fld=journal_id&key=${activeJournal.value.journal_id}`, { method: 'DELETE' })
        if (!res.ok) throw new Error(`HTTP ${res.status}`)
        journals.value = journals.value.filter(j => j.journal_id !== activeJournal.value.journal_id)
        activeJournal.value = null
        creatingNew.value = false
        pages.value = ['']
        currentPage.value = 0
        showToast('Journal deleted', 'danger')
    } catch (err) {
        showToast(`Delete failed: ${err.message}`, 'danger')
    }
}

// New Journal
function openNewJournal() {
    activeJournal.value = null
    creatingNew.value = true
    isDirty.value = false
    form.journal_name = ''
    form.journal_date = todayISO()
    form.journal_status = ''
    pages.value = ['']
    currentPage.value = 0
}

// Load Journal
function loadJournal(j) {
    activeJournal.value = j
    creatingNew.value = false
    isDirty.value = false
    form.journal_name = j.journal_name
    form.journal_date = j.journal_date
    form.journal_status = j.journal_status || ''
    splitBodyIntoPages(j.journal_body || '')
}

// Page Management
function splitBodyIntoPages(body) {
    if (!body) { pages.value = ['']; currentPage.value = 0; return }
    const chunks = []
    let remaining = body
    while (remaining.length > 0) {
        chunks.push(remaining.slice(0, PAGE_CHAR_LIMIT))
        remaining = remaining.slice(PAGE_CHAR_LIMIT)
    }
    pages.value = chunks.length ? chunks : ['']
    currentPage.value = 0
}

function onKeydown(e) {
    if (e.key === 'Backspace') {
        const ta = textareaRef.value
        if (ta && ta.selectionStart === 0 && ta.selectionEnd === 0 && currentPage.value > 0) {
            e.preventDefault()
            const prev = pages.value[currentPage.value - 1]
            const cur = pages.value[currentPage.value]
            if ((prev + cur).length <= PAGE_CHAR_LIMIT) {
                pages.value[currentPage.value - 1] = prev + cur
                pages.value.splice(currentPage.value, 1)
            }
            currentPage.value--
            nextTick(() => {
                if (textareaRef.value) textareaRef.value.setSelectionRange(prev.length, prev.length)
            })
            markDirty()
        }
        return
    }

    const navigationKeys = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown', 'Tab', 'Delete', 'Escape', 'Enter']
    if (navigationKeys.includes(e.key) || e.ctrlKey || e.metaKey) return

    if (pages.value[currentPage.value].length >= PAGE_CHAR_LIMIT) {
        e.preventDefault()
        nextPage()
    }
}

function onInput() {
    const text = pages.value[currentPage.value]
    if (text.length > PAGE_CHAR_LIMIT) {
        const kept = text.slice(0, PAGE_CHAR_LIMIT)
        const overflow = text.slice(PAGE_CHAR_LIMIT)
        pages.value[currentPage.value] = kept
        const next = currentPage.value + 1
        if (pages.value[next] !== undefined) {
            pages.value[next] = overflow + pages.value[next]
        } else {
            pages.value.push(overflow)
        }
        currentPage.value = next
    }
    markDirty()
}

function goToPage(pageNum) {
    currentPage.value = pageNum - 1
}

function nextPage() {
    if (currentPage.value >= pages.value.length - 1) pages.value.push('')
    currentPage.value++
}

// Status Selection
function setStatus(value) {
    form.journal_status = form.journal_status === value ? '' : value
    markDirty()
}

function statusBadgeClass(status, isActive) {
    if (isActive) return 'text-white bg-opacity-75 ' + (status === 'great' ? 'bg-success' : status === 'average' ? 'bg-warning' : 'bg-danger')
    return status === 'great' ? 'text-bg-success' : status === 'average' ? 'text-bg-warning' : 'text-bg-danger'
}

// Utilities
function markDirty() {
    isDirty.value = true
}

function formatSidebarDate(iso) {
    if (!iso) return ''
    return new Date(iso + 'T00:00:00').toLocaleDateString('en-AU', {
        day: 'numeric', month: 'short', year: 'numeric',
    })
}

function todayISO() {
    return new Date().toISOString().split('T')[0]
}

function showToast(message, type = 'success') {
    clearTimeout(toastTimer)
    toast.show = true
    toast.message = message
    toast.type = type
    toastTimer = setTimeout(() => { toast.show = false }, 2800)
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
