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

        <div
          v-for="j in journals"
          :key="j.journal_id"
          class="p-3 border-bottom"
          :class="activeJournal?.journal_id === j.journal_id ? 'bg-primary text-white' : ''"
          style="cursor:pointer;"
          @click="loadJournal(j)"
        >
          <div class="small fw-medium text-truncate">{{ j.journal_name }}</div>
          <div class="small" :class="activeJournal?.journal_id === j.journal_id ? 'text-white-50' : 'text-muted'">
            {{ formatSidebarDate(j.journal_date) }}
          </div>
        </div>
      </div>

    </div>

    <!-- Main area -->
    <div class="d-flex flex-column flex-grow-1 overflow-hidden">

      <!-- Empty state -->
      <div v-if="!activeJournal && !creatingNew" class="d-flex flex-column align-items-center justify-content-center flex-grow-1 gap-2 text-muted">
        <p class="mb-0">Select a journal or create a new one.</p>
        <button class="btn btn-primary btn-sm" @click="openNewJournal">New Journal</button>
      </div>

      <!-- Editor -->
      <div v-else class="d-flex flex-column flex-grow-1 overflow-hidden">

        <!-- Toolbar -->
        <div class="d-flex align-items-center gap-2 p-2 border-bottom bg-white flex-shrink-0">
          <input
            v-model="form.journal_name"
            class="form-control form-control-sm"
            placeholder="Journal name"
            maxlength="120"
            @input="markDirty"
          />
          <input
            v-model="form.journal_date"
            type="date"
            class="form-control form-control-sm"
            style="width:160px;"
            @change="markDirty"
          />
          <span class="badge text-bg-secondary text-nowrap">
            {{ pages.length }} page{{ pages.length !== 1 ? 's' : '' }}
          </span>
          <button
            class="btn btn-sm text-nowrap"
            :class="isDirty ? 'btn-primary' : 'btn-outline-secondary'"
            :disabled="saving"
            @click="saveJournal"
          >
            {{ saving ? 'Saving…' : isDirty ? 'Save' : 'Saved ✓' }}
          </button>
          <button
            v-if="activeJournal && !creatingNew"
            class="btn btn-sm btn-outline-danger"
            @click="showDeleteModal = true"
          >
            Delete
          </button>
        </div>

        <!-- Page + pagination -->
        <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-start p-4 bg-secondary bg-opacity-10 overflow-y-auto">

          <!-- A4 paper -->
          <div class="bg-white border p-5 d-flex flex-column" style="width:794px;height:1040px;">
            <textarea
              ref="textareaRef"
              v-model="pages[currentPage]"
              class="flex-grow-1 w-100 border-0"
              style="resize:none;outline:none;font-size:15px;line-height:1.8;overflow:hidden;"
              placeholder="Begin writing…"
              @keydown="onKeydown"
              @input="onInput"
            ></textarea>
            <div class="text-end text-muted border-top pt-1" style="font-size:11px;">
              Page {{ currentPage + 1 }}
            </div>
          </div>

          <!-- Pagination -->
          <nav class="mt-3">
            <ul class="pagination pagination-sm justify-content-center mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 0 }">
                <button class="page-link" @click="prevPage">← Prev</button>
              </li>
              <li class="page-item disabled">
                <span class="page-link">{{ currentPage + 1 }} / {{ pages.length }}</span>
              </li>
              <li class="page-item">
                <button class="page-link" @click="nextPage">Next →</button>
              </li>
            </ul>
          </nav>

        </div>

      </div>
    </div>

    <!-- Delete modal -->
    <div
      v-if="showDeleteModal"
      class="modal d-block"
      style="background:rgba(0,0,0,0.5);"
      @click.self="showDeleteModal = false"
    >
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Delete journal?</h5>
            <button class="btn-close" @click="showDeleteModal = false"></button>
          </div>
          <div class="modal-body text-muted small">
            Permanently delete <strong class="text-dark">{{ activeJournal?.journal_name }}</strong>? This cannot be undone.
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
      <div
        v-if="toast.show"
        class="position-fixed bottom-0 end-0 m-3"
        style="z-index:1080;"
      >
        <div
          class="toast show"
          :class="toast.type === 'danger' ? 'text-bg-danger' : 'text-bg-dark'"
        >
          <div class="toast-body">{{ toast.message }}</div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'

const JOURNALS_API = '/resources/api_journals.php'
const USER_ID = 2  // temporary — replace with the logged-in user's real ID

const PAGE_CHAR_LIMIT = 1800

const journals        = ref([])
const activeJournal   = ref(null)
const creatingNew     = ref(false)
const loadingJournals = ref(false)
const saving          = ref(false)
const isDirty         = ref(false)
const showDeleteModal = ref(false)

const form = reactive({
  journal_name: '',
  journal_date: todayISO(),
  journal_body: '',
})

const pages       = ref([''])
const currentPage = ref(0)
const textareaRef = ref(null)

const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimer    = null
let autoSaveTimer = null

onMounted(fetchJournals)

async function fetchJournals() {
  loadingJournals.value = true
  try {
    const res = await fetch(`${JOURNALS_API}/user_id/${USER_ID}`)
    if (!res.ok) throw new Error()
    journals.value = await res.json()
  } catch {
    journals.value = sampleJournals()
  } finally {
    loadingJournals.value = false
  }
}

async function saveJournal() {
  if (!isDirty.value && activeJournal.value) return
  saving.value = true
  form.journal_body = pages.value.join('')

  const payload = {
    user_id:      USER_ID,
    journal_name: form.journal_name || 'Untitled',
    journal_body: form.journal_body,
    journal_date: form.journal_date,
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
      creatingNew.value   = false
    } else {
      const res = await fetch(`${JOURNALS_API}/journal_id/${activeJournal.value.journal_id}`, {
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
  } catch {
    isDirty.value = false
    showToast('Saved (demo mode)', 'success')
  } finally {
    saving.value = false
  }
}

async function deleteJournal() {
  showDeleteModal.value = false
  try {
    await fetch(`${JOURNALS_API}/journal_id/${activeJournal.value.journal_id}`, { method: 'DELETE' })
  } catch { /* demo */ }
  journals.value      = journals.value.filter(j => j.journal_id !== activeJournal.value.journal_id)
  activeJournal.value = null
  creatingNew.value   = false
  pages.value         = ['']
  currentPage.value   = 0
  showToast('Journal deleted', 'danger')
}

function openNewJournal() {
  activeJournal.value = null
  creatingNew.value   = true
  isDirty.value       = false
  form.journal_name   = ''
  form.journal_date   = todayISO()
  form.journal_body   = ''
  pages.value         = ['']
  currentPage.value   = 0
}

function loadJournal(j) {
  activeJournal.value = j
  creatingNew.value   = false
  isDirty.value       = false
  form.journal_name   = j.journal_name
  form.journal_date   = j.journal_date
  splitBodyIntoPages(j.journal_body || '')
}

function splitBodyIntoPages(body) {
  if (!body) { pages.value = ['']; currentPage.value = 0; return }
  const chunks = []
  let remaining = body
  while (remaining.length > 0) {
    chunks.push(remaining.slice(0, PAGE_CHAR_LIMIT))
    remaining = remaining.slice(PAGE_CHAR_LIMIT)
  }
  pages.value   = chunks.length ? chunks : ['']
  currentPage.value = 0
}

function onKeydown(e) {
  // Backspace at the very start of a page merges it back into the previous page
  if (e.key === 'Backspace') {
    const ta = textareaRef.value
    if (ta && ta.selectionStart === 0 && ta.selectionEnd === 0 && currentPage.value > 0) {
      e.preventDefault()
      const prev = pages.value[currentPage.value - 1]
      const cur  = pages.value[currentPage.value]
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

  // Let all other navigation / modifier keys through untouched
  const nav = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown', 'Tab', 'Delete', 'Escape', 'Enter']
  if (nav.includes(e.key) || e.ctrlKey || e.metaKey) return

  // Block printable chars when the page is full — auto-advance instead
  if (pages.value[currentPage.value].length >= PAGE_CHAR_LIMIT) {
    e.preventDefault()
    nextPage()
  }
}

function onInput() {
  // Safety net: if v-model somehow lets content exceed the limit, split the overflow off
  const text = pages.value[currentPage.value]
  if (text.length > PAGE_CHAR_LIMIT) {
    const kept     = text.slice(0, PAGE_CHAR_LIMIT)
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

function prevPage() {
  if (currentPage.value > 0) currentPage.value--
}

function nextPage() {
  if (currentPage.value >= pages.value.length - 1) pages.value.push('')
  currentPage.value++
}

function markDirty() {
  isDirty.value = true
  clearTimeout(autoSaveTimer)
  autoSaveTimer = setTimeout(saveJournal, 5000)
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
  toast.show    = true
  toast.message = message
  toast.type    = type
  toastTimer    = setTimeout(() => { toast.show = false }, 2800)
}

function sampleJournals() {
  return [
    {
      journal_id: 'demo-1',
      user_id: USER_ID,
      journal_name: 'First day of spring',
      journal_date: '2025-09-01',
      journal_body: 'Today was beautiful. The light came through the window differently.',
    },
    {
      journal_id: 'demo-2',
      user_id: USER_ID,
      journal_name: 'Late night thoughts',
      journal_date: '2025-09-12',
      journal_body: 'It is 1am and I cannot sleep.',
    },
  ]
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from,  .fade-leave-to      { opacity: 0; }
</style>
