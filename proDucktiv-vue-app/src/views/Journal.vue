<template>
    <div class="container-fluid d-flex flex-column overflow-hidden" style="height: 100vh; box-sizing: border-box;">

        <h1 style="margin: 0; padding: 1rem; flex-shrink: 0;">Journal Page</h1>

        <div class="row flex-grow-1 overflow-hidden" style="margin: 0; padding: 1rem; box-sizing: border-box;">

            <!-- Left side: placeholder for future use -->
            <div class="col-4">
                <p>Left panel (future use)</p>
            </div>

            <!-- Right side: journal page -->
            <div class="col-8 d-flex flex-column h-100">

                <!-- The fixed-height writing area - does NOT scroll or resize -->
                <textarea ref="textareaRef" v-model="pages[currentPage]" class="flex-grow-1 w-100"
                    style="resize: none; overflow: hidden;" placeholder="Start writing..."></textarea>

                <!-- Page navigation -->
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <button @click="prevPage" :disabled="currentPage === 0">← Prev</button>
                    <span>Page {{ currentPage + 1 }} of {{ pages.length }}</span>
                    <button @click="nextPage">Next →</button>
                </div>

            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'

const pages = ref([''])
const currentPage = ref(0)
const textareaRef = ref(null)

function prevPage() {
    if (currentPage.value > 0) {
        currentPage.value--
    }
}

function nextPage() {
    currentPage.value++
    if (currentPage.value >= pages.value.length) {
        pages.value.push('')
    }
}

// Detect when the textarea is full and prevent further typing
function handleKeydown(e) {
    const ta = textareaRef.value
    if (!ta) return

    // Allow navigation/deletion keys through always
    const allowed = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown']
    if (allowed.includes(e.key)) return

    // If content already overflows the textarea height, block new input
    if (ta.scrollHeight > ta.clientHeight) {
        e.preventDefault()
    }
}

onMounted(() => {
    const ta = textareaRef.value
    if (ta) ta.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
    const ta = textareaRef.value
    if (ta) ta.removeEventListener('keydown', handleKeydown)
})
</script>