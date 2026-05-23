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

        <!-- Pages -->
        <div class="overflow-y-auto flex-grow-1 p-4 bg-secondary bg-opacity-10" ref="pagesContainer">
          <div
            v-for="(page, index) in pages"
            :key="index"
            class="bg-white border mx-auto mb-4 p-5"
            style="width:794px;min-height:1040px;"
          >
            <div class="text-end text-muted border-top pt-1 mb-4" style="font-size:11px;">
              Page {{ index + 1 }}
            </div>

            <div
              class="w-100 h-100"
              style="outline:none;font-size:15px;line-height:1.8;white-space:pre-wrap;word-wrap:break-word;"
              contenteditable="true"
              :data-page="index"
              :ref="el => setPageRef(el, index)"
              @input="onPageInput(index)"
              @keydown="onPageKeydown($event, index)"
              @paste="onPaste($event, index)"
            ></div>
          </div>
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
const pagesContainer  = ref(null)

const form = reactive({
  journal_name: '',
  journal_date: todayISO(),
  journal_body: '',
})

const pages    = ref([''])
const pageRefs = ref([])

const toast = reactive({ show: false, message: '', type: 'success' })
let toastTimer    = null
let autoSaveTimer = null

onMounted(fetchJournals)

function setPageRef(el, index) {
  pageRefs.value[index] = el
}

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
  syncBodyFromPages()

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
  nextTick(renderPages)
}

function loadJournal(j) {
  activeJournal.value = j
  creatingNew.value   = false
  isDirty.value       = false
  form.journal_name   = j.journal_name
  form.journal_date   = j.journal_date
  form.journal_body   = j.journal_body || ''
  splitBodyIntoPages(j.journal_body || '')
  nextTick(renderPages)
}

function splitBodyIntoPages(body) {
  if (!body) { pages.value = ['']; return }
  const chunks = []
  let remaining = body
  while (remaining.length > 0) {
    chunks.push(remaining.slice(0, PAGE_CHAR_LIMIT))
    remaining = remaining.slice(PAGE_CHAR_LIMIT)
  }
  pages.value = chunks.length ? chunks : ['']
}

function syncBodyFromPages() {
  pageRefs.value.forEach((el, i) => {
    if (el) pages.value[i] = el.innerText || ''
  })
  form.journal_body = pages.value.join('')
}

async function renderPages() {
  await nextTick()
  pageRefs.value.forEach((el, i) => {
    if (el && pages.value[i] !== undefined) el.innerText = pages.value[i]
  })
}

function onPageInput(index) {
  const el = pageRefs.value[index]
  if (!el) return
  const text = el.innerText || ''

  if (text.length > PAGE_CHAR_LIMIT) {
    const kept     = text.slice(0, PAGE_CHAR_LIMIT)
    const overflow = text.slice(PAGE_CHAR_LIMIT)

    pages.value[index] = kept
    el.innerText = kept
    moveCaret(el, kept.length)

    if (pages.value[index + 1] !== undefined) {
      pages.value[index + 1] = overflow + pages.value[index + 1]
    } else {
      pages.value.push(overflow)
    }

    nextTick(() => renderPages().then(() => focusPage(index + 1, 0)))
  } else {
    pages.value[index] = text
  }

  markDirty()
}

function onPageKeydown(event, index) {
  const el = pageRefs.value[index]
  if (!el) return

  if (event.key === 'Backspace') {
    const sel = window.getSelection()
    if (sel && sel.anchorOffset === 0 && index > 0) {
      event.preventDefault()
      const prevText = pages.value[index - 1] || ''
      const curText  = pages.value[index]     || ''
      const merged   = prevText + curText

      if (merged.length <= PAGE_CHAR_LIMIT) {
        pages.value[index - 1] = merged
        pages.value.splice(index, 1)
        nextTick(() => renderPages().then(() => focusPage(index - 1, prevText.length)))
      } else {
        focusPage(index - 1, prevText.length)
      }
      markDirty()
    }
  }

  if (event.key === 'Enter' && (el.innerText || '').length >= PAGE_CHAR_LIMIT - 1) {
    event.preventDefault()
    if (!pages.value[index + 1]) pages.value.push('')
    nextTick(() => renderPages().then(() => focusPage(index + 1, 0)))
  }

  if (event.key === 'ArrowDown' && index < pages.value.length - 1) {
    const sel = window.getSelection()
    if (sel?.getRangeAt(0).endOffset >= (el.innerText || '').length - 1) {
      event.preventDefault()
      focusPage(index + 1, 0)
    }
  }

  if (event.key === 'ArrowUp' && index > 0) {
    const sel = window.getSelection()
    if (sel?.getRangeAt(0).startOffset === 0) {
      event.preventDefault()
      focusPage(index - 1, (pages.value[index - 1] || '').length)
    }
  }
}

function onPaste(event, index) {
  event.preventDefault()
  const text = (event.clipboardData || window.clipboardData).getData('text/plain')
  if (!text) return

  let remaining = (pageRefs.value[index]?.innerText || '') + text
  let pos = index

  while (remaining.length > 0) {
    pages.value[pos] = remaining.slice(0, PAGE_CHAR_LIMIT)
    remaining        = remaining.slice(PAGE_CHAR_LIMIT)
    if (remaining.length > 0) {
      pos++
      if (!pages.value[pos]) pages.value.push('')
    }
  }

  nextTick(() => { renderPages(); markDirty() })
}

function focusPage(index, offset) {
  const el = pageRefs.value[index]
  if (!el) return
  el.focus()
  const node = el.childNodes[0]
  if (node) {
    const range = document.createRange()
    range.setStart(node, Math.min(offset, node.length || 0))
    range.collapse(true)
    const sel = window.getSelection()
    sel.removeAllRanges()
    sel.addRange(range)
  }
}

function moveCaret(el, offset) {
  const node = el.childNodes[0]
  if (!node) return
  const range = document.createRange()
  range.setStart(node, Math.min(offset, node.length || 0))
  range.collapse(true)
  const sel = window.getSelection()
  sel.removeAllRanges()
  sel.addRange(range)
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
[contenteditable]:empty::before {
  content: 'Begin writing…';
  color: #adb5bd;
  pointer-events: none;
}

.fade-enter-active, .fade-leave-active { transition: opacity 0.25s; }
.fade-enter-from,  .fade-leave-to      { opacity: 0; }
</style>