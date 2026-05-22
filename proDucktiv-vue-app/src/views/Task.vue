<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// stores all tasks from localstorage
const tasks = ref([])

// stores the search text from the search box
const search = ref('')

// controls the current sidebar filter
const selectedView = ref('active')

// stores the green completion message
const celebrationMessage = ref('')

// controls the smooth falling confetti overlay
const showConfetti = ref(false)
const confettiPieces = ref([])

// reminder setting: tasks due within 48 hours will appear as due soon
const reminderWindowHours = 48

// confetti colours copied from the example style: dark green, bootstrap success, lime, warning yellow, and soft white
const confettiColors = ['#006b5a', '#198754', '#6fb13f', '#c9d900', '#ffc107', '#f8f9fa']

// loads saved tasks when this page opens
onMounted(() => {
  tasks.value = JSON.parse(localStorage.getItem('tasks')) || []
})

// saves all task updates back into localstorage
function saveTasks() {
  localStorage.setItem('tasks', JSON.stringify(tasks.value))
}

// checks whether a task is completed
function isTaskComplete(task) {
  return task.status === 'Complete' || task.status === 'Finished'
}

// combines the saved date and time fields into one date object
function getDueDateTime(task) {
  if (!task.date) return null

  const taskTime = task.time || '23:59'
  const due = new Date(`${task.date}T${taskTime}`)

  if (Number.isNaN(due.getTime())) return null

  return due
}

// calculates the remaining hours before the task deadline
function getHoursUntilDue(task) {
  const due = getDueDateTime(task)

  if (!due) return null

  return Math.ceil((due.getTime() - Date.now()) / (1000 * 60 * 60))
}

// checks whether a task is overdue
function isOverdue(task) {
  const hoursLeft = getHoursUntilDue(task)

  return !isTaskComplete(task) && hoursLeft !== null && hoursLeft < 0
}

// checks whether a task is due soon but not overdue
function isDueSoon(task) {
  const hoursLeft = getHoursUntilDue(task)

  return (
    !isTaskComplete(task) &&
    hoursLeft !== null &&
    hoursLeft >= 0 &&
    hoursLeft <= reminderWindowHours
  )
}

// creates the reminder text shown on task cards
function getReminderText(task) {
  const hoursLeft = getHoursUntilDue(task)

  if (hoursLeft === null || isTaskComplete(task)) return ''

  if (hoursLeft < 0) {
    const overdueHours = Math.abs(hoursLeft)
    return `overdue by ${overdueHours} hour${overdueHours === 1 ? '' : 's'}`
  }

  if (hoursLeft === 0) return 'due now'
  if (hoursLeft <= 24) return `due in ${hoursLeft} hour${hoursLeft === 1 ? '' : 's'}`

  const daysLeft = Math.ceil(hoursLeft / 24)
  return `due in ${daysLeft} days`
}

// total task count
const totalTasks = computed(() => tasks.value.length)

// active task count
const activeTasks = computed(() => tasks.value.filter((task) => task.status === 'Active'))

// completed task count
const completedTasks = computed(() => {
  return tasks.value.filter((task) => isTaskComplete(task))
})

// high priority task count
const highPriorityTasks = computed(() => {
  return tasks.value.filter((task) => task.priority === 'High')
})

// reminder task count
const reminderTasks = computed(() => {
  return tasks.value.filter((task) => isDueSoon(task) || isOverdue(task))
})

// overdue task count
const overdueTasks = computed(() => {
  return tasks.value.filter((task) => isOverdue(task))
})

// filters tasks using sidebar selection and search input
const filteredTasks = computed(() => {
  return tasks.value.filter((task) => {
    const keyword = search.value.toLowerCase()
    const searchableText = [
      task.title,
      task.type,
      task.location,
      task.description,
      task.priority,
      task.status,
    ]
      .join(' ')
      .toLowerCase()

    const matchesSearch = searchableText.includes(keyword)

    if (selectedView.value === 'active') {
      return task.status === 'Active' && matchesSearch
    }

    if (selectedView.value === 'completed') {
      return isTaskComplete(task) && matchesSearch
    }

    if (selectedView.value === 'high') {
      return task.priority === 'High' && matchesSearch
    }

    if (selectedView.value === 'reminders') {
      return (isDueSoon(task) || isOverdue(task)) && matchesSearch
    }

    return matchesSearch
  })
})

// generates smooth falling square confetti like the reference image
function createSmoothConfettiPieces() {
  return Array.from({ length: 95 }, (_, index) => {
    return {
      id: `${Date.now()}-${index}`,
      left: `${Math.random() * 100}%`,
      delay: `${Math.random() * 1.2}s`,
      duration: `${4.2 + Math.random() * 2.4}s`,
      size: `${6 + Math.random() * 6}px`,
      opacity: 0.35 + Math.random() * 0.6,
      drift: `${Math.random() * 220 - 110}px`,
      rotate: `${Math.random() * 720 - 360}deg`,
      color: confettiColors[index % confettiColors.length],
    }
  })
}

// starts the confetti animation
function triggerConfetti() {
  confettiPieces.value = createSmoothConfettiPieces()
  showConfetti.value = true

  setTimeout(() => {
    showConfetti.value = false
    confettiPieces.value = []
  }, 6200)
}

// updates task status and triggers confetti when completed
function updateStatus(task, newStatus) {
  task.status = newStatus

  if (newStatus === 'Complete') {
    task.completedDate = new Date().toLocaleString()
    celebrationMessage.value = `great work! "${task.title}" has been completed.`
    triggerConfetti()

    setTimeout(() => {
      celebrationMessage.value = ''
    }, 2800)
  }

  if (newStatus === 'Active') {
    task.completedDate = ''
  }

  saveTasks()
}

// deletes a task from the saved task list
function deleteTask(id) {
  tasks.value = tasks.value.filter((task) => task.id !== id)
  saveTasks()
}
</script>

<template>
  <div class="task-app">
    <!-- smooth confetti overlay -->
    <div v-if="showConfetti" class="confetti-layer">
      <span
        v-for="piece in confettiPieces"
        :key="piece.id"
        class="confetti"
        :style="{
          left: piece.left,
          width: piece.size,
          height: `calc(${piece.size} * 1.35)`,
          opacity: piece.opacity,
          backgroundColor: piece.color,
          animationDelay: piece.delay,
          animationDuration: piece.duration,
          '--drift': piece.drift,
          '--rotate': piece.rotate,
        }"
      ></span>
    </div>

    <!-- sidebar navigation -->
    <aside class="sidebar">
      <h2>proDucktiv</h2>
      <p>Task Manager</p>

      <button :class="{ active: selectedView === 'active' }" @click="selectedView = 'active'">
        Active Tasks
        <span>{{ activeTasks.length }}</span>
      </button>

      <button :class="{ active: selectedView === 'reminders' }" @click="selectedView = 'reminders'">
        Due Reminders
        <span>{{ reminderTasks.length }}</span>
      </button>

      <button :class="{ active: selectedView === 'completed' }" @click="selectedView = 'completed'">
        Completed History
        <span>{{ completedTasks.length }}</span>
      </button>

      <button :class="{ active: selectedView === 'high' }" @click="selectedView = 'high'">
        High Priority
        <span>{{ highPriorityTasks.length }}</span>
      </button>

      <button :class="{ active: selectedView === 'all' }" @click="selectedView = 'all'">
        All Tasks
        <span>{{ totalTasks }}</span>
      </button>
    </aside>

    <!-- main dashboard -->
    <main class="main-content">
      <section class="top-bar">
        <div>
          <h1>Task Dashboard</h1>
          <p>manage tasks, track progress, and receive due soon reminders</p>
        </div>

        <div class="top-actions">
          <button class="add-btn" @click="router.push('/task-creator')">+</button>
          <input v-model="search" type="text" placeholder="search task title, type, location, or description" />
        </div>
      </section>

      <!-- due soon reminder banner -->
      <section v-if="reminderTasks.length" class="reminder-panel">
        <div>
          <h2>Reminder</h2>
          <p>
            {{ reminderTasks.length }} task{{ reminderTasks.length === 1 ? '' : 's' }} need attention.
            {{ overdueTasks.length }} overdue.
          </p>
        </div>

        <button @click="selectedView = 'reminders'">view reminders</button>
      </section>

      <!-- dashboard statistics -->
      <section class="summary-grid">
        <div class="summary-card">
          <h3>{{ totalTasks }}</h3>
          <p>Total Tasks</p>
        </div>

        <div class="summary-card">
          <h3>{{ activeTasks.length }}</h3>
          <p>Active</p>
        </div>

        <div class="summary-card warning-card">
          <h3>{{ reminderTasks.length }}</h3>
          <p>Due Soon</p>
        </div>

        <div class="summary-card warning-card">
          <h3>{{ highPriorityTasks.length }}</h3>
          <p>High Priority</p>
        </div>
      </section>

      <p v-if="celebrationMessage" class="celebration">
        {{ celebrationMessage }}
      </p>

      <!-- task display area -->
      <section class="task-section">
        <div class="section-header">
          <h2>{{ selectedView }} tasks</h2>
        </div>

        <p v-if="filteredTasks.length === 0" class="empty-message">
          no tasks found in this section.
        </p>

        <div class="task-list">
          <article
            v-for="task in filteredTasks"
            :key="task.id"
            :class="['task-card', { 'due-soon-card': isDueSoon(task), 'overdue-card': isOverdue(task) }]"
          >
            <div class="task-header">
              <div>
                <h3>{{ task.title }}</h3>
                <p>{{ task.description }}</p>
              </div>

              <span :class="['priority', task.priority]">
                {{ task.priority }}
              </span>
            </div>

            <p v-if="isDueSoon(task) || isOverdue(task)" :class="['reminder-chip', { danger: isOverdue(task) }]">
              {{ getReminderText(task) }}
            </p>

            <div class="task-details">
              <p><strong>type:</strong> {{ task.type }}</p>
              <p><strong>date:</strong> {{ task.date }}</p>
              <p><strong>time:</strong> {{ task.time }}</p>
              <p><strong>location:</strong> {{ task.location || 'not set' }}</p>
              <p><strong>created:</strong> {{ task.creationDate }}</p>
              <p><strong>status:</strong> {{ task.status }}</p>

              <p v-if="task.completedDate">
                <strong>completed:</strong> {{ task.completedDate }}
              </p>
            </div>

            <div class="task-actions">
              <select :value="task.status" @change="updateStatus(task, $event.target.value)">
                <option value="Active">Active</option>
                <option value="Complete">Complete</option>
              </select>

              <button class="delete-btn" @click="deleteTask(task.id)">
                Delete
              </button>

              <button class="edit-btn" @click="router.push(`/task-editor/${task.id}`)">
                Edit
              </button>
            </div>
          </article>
        </div>
      </section>
    </main>
  </div>
</template>

<style scoped>
.task-app {
  display: flex;
  min-height: 100vh;
  width: 100%;
  background: #f8f9fa;
  font-family: Arial, sans-serif;
  color: #212529;
}

.sidebar {
  width: 260px;
  background: #ffffff;
  color: #212529;
  padding: 30px 22px;
  flex-shrink: 0;
  border-right: 1px solid #dee2e6;
}

.sidebar h2 {
  margin: 0;
  font-size: 30px;
}

.sidebar p {
  color: #6c757d;
  margin-bottom: 35px;
}

.sidebar button {
  width: 100%;
  display: flex;
  justify-content: space-between;
  padding: 14px 16px;
  margin-bottom: 12px;
  border: none;
  border-radius: 12px;
  background: transparent;
  color: #495057;
  cursor: pointer;
  font-weight: 600;
}

.sidebar button:hover,
.sidebar button.active {
  background: #d1e7dd;
  color: #0f5132;
}

.sidebar span {
  background: #fff3cd;
  color: #664d03;
  padding: 2px 9px;
  border-radius: 999px;
}

.main-content {
  flex: 1;
  padding: 42px;
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.top-bar h1 {
  margin: 0;
  font-size: 40px;
}

.top-bar p {
  color: #6c757d;
}

.top-actions {
  display: flex;
  gap: 12px;
}

.top-actions input {
  width: 390px;
  padding: 14px;
  border-radius: 12px;
  border: 1px solid #ced4da;
  background: #ffffff;
}

.add-btn,
button {
  background: #198754;
  color: white;
  cursor: pointer;
}

.add-btn {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  border: none;
  font-size: 26px;
}

.add-btn:hover,
button:hover {
  background: #157347;
}

.reminder-panel {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff3cd;
  color: #664d03;
  border: 1px solid #ffecb5;
  border-left: 6px solid #ffc107;
  padding: 18px 20px;
  border-radius: 16px;
  margin-bottom: 24px;
}

.reminder-panel h2,
.reminder-panel p {
  margin: 0;
}

.reminder-panel button {
  border: none;
  border-radius: 10px;
  padding: 11px 14px;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 22px;
  margin-bottom: 28px;
}

.summary-card {
  background: white;
  padding: 24px;
  border-radius: 18px;
  border: 1px solid #dee2e6;
  border-top: 5px solid #198754;
}

.warning-card {
  border-top-color: #ffc107;
}

.summary-card h3 {
  font-size: 34px;
  margin: 0;
}

.summary-card p {
  color: #6c757d;
  margin-bottom: 0;
}

.task-section {
  background: white;
  border-radius: 20px;
  padding: 28px;
  border: 1px solid #dee2e6;
}

.task-list {
  display: grid;
  gap: 18px;
}

.task-card {
  background: #ffffff;
  border: 1px solid #dee2e6;
  border-left: 6px solid #198754;
  border-radius: 16px;
  padding: 22px;
  box-shadow: 0 8px 20px rgba(33, 37, 41, 0.04);
}

.task-card.due-soon-card {
  border-left-color: #ffc107;
}

.task-card.overdue-card {
  border-left-color: #dc3545;
}

.task-header {
  display: flex;
  justify-content: space-between;
  gap: 20px;
}

.task-header h3 {
  margin: 0;
  font-size: 24px;
}

.task-header p {
  color: #6c757d;
}

.reminder-chip {
  display: inline-block;
  background: #fff3cd;
  color: #664d03;
  border: 1px solid #ffecb5;
  border-radius: 999px;
  padding: 7px 12px;
  font-weight: bold;
}

.reminder-chip.danger {
  background: #f8d7da;
  color: #842029;
  border-color: #f5c2c7;
}

.task-details, .location, .due-date {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 4px 20px;
  margin-top: 12px;
}

.task-actions {
  display: flex;
  gap: 10px;
  margin-top: 18px;
}

select,
button {
  padding: 11px 14px;
  border-radius: 10px;
  border: 1px solid #ced4da;
}

.delete-btn {
  background: #dc3545;
}

.delete-btn:hover {
  background: #bb2d3b;
}

.priority {
  height: fit-content;
  padding: 7px 14px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: bold;
}

.priority.Low {
  background: #d1e7dd;
  color: #0f5132;
}

.priority.Medium {
  background: #fff3cd;
  color: #664d03;
}

.priority.High {
  background: #f8d7da;
  color: #842029;
}

.celebration {
  background: #d1e7dd;
  color: #0f5132;
  padding: 14px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-weight: bold;
  border: 1px solid #badbcc;
}

.empty-message {
  color: #6c757d;
  text-align: center;
  padding: 45px;
}

.confetti-layer {
  position: fixed;
  inset: 0;
  pointer-events: none;
  overflow: hidden;
  z-index: 999;
}

.confetti {
  position: absolute;
  top: -32px;
  display: block;
  border-radius: 1px;
  animation-name: smooth-confetti-fall;
  animation-timing-function: cubic-bezier(0.22, 0.61, 0.36, 1);
  animation-fill-mode: forwards;
  will-change: transform, opacity;
}

@keyframes smooth-confetti-fall {
  0% {
    transform: translate3d(0, -40px, 0) rotate(0deg);
    opacity: 0;
  }

  12% {
    opacity: 1;
  }

  70% {
    opacity: 0.9;
  }

  100% {
    transform: translate3d(var(--drift), 110vh, 0) rotate(var(--rotate));
    opacity: 0;
  }
}

@media (max-width: 900px) {
  .task-app {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
  }

  .summary-grid,
  .task-details {
    grid-template-columns: 1fr;
  }

  .top-bar {
    align-items: flex-start;
    flex-direction: column;
    gap: 15px;
  }

  .top-actions,
  .top-actions input {
    width: 100%;
  }
}
</style>
