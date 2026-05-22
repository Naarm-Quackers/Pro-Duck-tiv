<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// form fields match the task editor page fields
const title = ref('')
const type = ref('task')
const date = ref('')
const time = ref('')
const location = ref('')
const description = ref('')

// extra task-management fields used by the dashboard
const priority = ref('medium')
const status = ref('in-progress')

// saved task list
const tasks = ref([])

// success message after creating a task
const successMessage = ref('')

// smooth confetti animation state
const showConfetti = ref(false)
const confettiPieces = ref([])

// confetti colours from the example: green, yellow, and soft white
const confettiColors = ['#006b5a', '#198754', '#6fb13f', '#c9d900', '#ffc107', '#f8f9fa']

// loads existing tasks for sidebar statistics
onMounted(() => {
  tasks.value = JSON.parse(localStorage.getItem('tasks')) || []
})

// sidebar statistics
const totalTasks = computed(() => tasks.value.length)

const activeTasks = computed(() => {
  return tasks.value.filter((task) => task.status === 'in-progress')
})

const completedTasks = computed(() => {
  return tasks.value.filter((task) => task.status === 'complete' || task.status === 'finished')
})

// generates smooth falling confetti pieces
function createSmoothConfettiPieces() {
  return Array.from({ length: 90 }, (_, index) => {
    return {
      id: `${Date.now()}-${index}`,
      left: `${Math.random() * 100}%`,
      delay: `${Math.random() * 1.1}s`,
      duration: `${4 + Math.random() * 2.2}s`,
      size: `${6 + Math.random() * 6}px`,
      opacity: 0.35 + Math.random() * 0.6,
      drift: `${Math.random() * 220 - 110}px`,
      rotate: `${Math.random() * 720 - 360}deg`,
      color: confettiColors[index % confettiColors.length],
    }
  })
}

// starts the create-task confetti animation
function triggerConfetti() {
  confettiPieces.value = createSmoothConfettiPieces()
  showConfetti.value = true

  setTimeout(() => {
    showConfetti.value = false
    confettiPieces.value = []
  }, 6200)
}

// saves a new task into localstorage
function createTask() {
  if (!title.value || !type.value || !date.value || !time.value || !location.value || !description.value) {
    alert('please complete title, type, date, time, location, and description')
    return
  }

  const newTask = {
    id: Date.now(),
    title: title.value,
    type: type.value,
    date: date.value,
    time: time.value,
    location: location.value,
    description: description.value,
    priority: priority.value,
    status: status.value,
    creationDate: new Date().toLocaleDateString(),
    completedDate:
      status.value === 'complete' || status.value === 'finished'
        ? new Date().toLocaleString()
        : '',
  }

  tasks.value.push(newTask)
  localStorage.setItem('tasks', JSON.stringify(tasks.value))

  successMessage.value = 'task created successfully. reminder will appear when it is due soon.'
  triggerConfetti()

  // clears the form after the task is saved
  title.value = ''
  type.value = 'task'
  date.value = ''
  time.value = ''
  location.value = ''
  description.value = ''
  priority.value = 'medium'
  status.value = 'in-progress'

  setTimeout(() => {
    router.push('/task')
  }, 1200)
}
</script>

<template>
  <div class="creator-app">
    <!-- smooth confetti overlay for successful creation -->
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

    <!-- sidebar keeps the same structure as the task dashboard -->
    <aside class="sidebar">
      <h2>proDucktiv</h2>
      <p class="sidebar-subtitle">task creator</p>

      <div class="sidebar-card">
        <strong>{{ totalTasks }}</strong>
        <span>Total Tasks</span>
      </div>

      <div class="sidebar-card">
        <strong>{{ activeTasks.length }}</strong>
        <span>Active Tasks</span>
      </div>

      <div class="sidebar-card warning-card">
        <strong>{{ completedTasks.length }}</strong>
        <span>Completed Tasks</span>
      </div>

      <button @click="router.push('/task')">
        view task dashboard
      </button>
    </aside>

    <!-- main content -->
    <main class="main-content">
      <section class="top-bar">
        <div>
          <h1>Create New Task</h1>
          <p>create a task with the same fields as the task editor page</p>
        </div>
      </section>

      <section class="creator-layout">
        <form class="form-card" @submit.prevent="createTask">
          <h2>Task Details</h2>

          <!-- title matches the editor field -->
          <div class="form-group">
            <label>Title</label>
            <input v-model="title" type="text" placeholder="enter task title" />
          </div>

          <!-- type matches the editor field -->
          <div class="form-group">
            <label>Type</label>
            <select v-model="type">
              <option value="task">task</option>
              <option value="meeting">meeting</option>
              <option value="deadline">deadline</option>
              <option value="study">study</option>
              <option value="personal">personal</option>
            </select>
          </div>

          <!-- date and time now match the editor page fields -->
          <div class="form-row">
            <div class="form-group">
              <label>Date</label>
              <input v-model="date" type="date" />
            </div>

            <div class="form-group">
              <label>Time</label>
              <input v-model="time" type="time" />
            </div>
          </div>

          <!-- location now matches the editor page field -->
          <div class="form-group">
            <label>Location</label>
            <input v-model="location" type="text" placeholder="enter task location" />
          </div>

          <!-- description matches the editor field -->
          <div class="form-group">
            <label>Description</label>
            <textarea v-model="description" placeholder="enter task description"></textarea>
          </div>

          <!-- priority is kept for dashboard filtering and warning colours -->
          <div class="form-row">
            <div class="form-group">
              <label>Priority</label>
              <select v-model="priority">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
              </select>
            </div>

            <div class="form-group">
              <label>Status</label>
              <select v-model="status">
                <option value="in-progress">In Progress</option>
                <option value="complete">Complete</option>
                <option value="finished">finished</option>
              </select>
            </div>
          </div>

          <p class="help-text">
            REMINDER: Active tasks will be highlighted when they are due within 48 hours.
          </p>

          <p v-if="successMessage" class="success-message">
            {{ successMessage }}
          </p>

          <button type="submit" class="create-btn">
            create task
          </button>
        </form>
      </section>
    </main>
  </div>
</template>

<style scoped>
.creator-app {
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
  margin-bottom: 5px;
}

.sidebar-subtitle {
  color: #6c757d;
  margin-bottom: 30px;
}

.sidebar-card {
  background: #ffffff;
  padding: 16px;
  border-radius: 12px;
  margin-bottom: 14px;
  border: 1px solid #dee2e6;
  border-left: 5px solid #198754;
}

.sidebar-card.warning-card {
  border-left-color: #ffc107;
}

.sidebar-card strong {
  display: block;
  font-size: 28px;
}

.sidebar-card span {
  color: #6c757d;
}

.sidebar button {
  width: 100%;
  margin-top: 20px;
  padding: 12px;
  border: none;
  border-radius: 8px;
  background: #198754;
  color: white;
  cursor: pointer;
}

.sidebar button:hover {
  background: #157347;
}

.main-content {
  flex: 1;
  padding: 42px;
}

.top-bar {
  margin-bottom: 30px;
}

.top-bar h1 {
  margin: 0;
  font-size: 40px;
}

.top-bar p {
  color: #6c757d;
}

.creator-layout {
  display: flex;
  justify-content: center;
}

.form-card {
  width: min(780px, 100%);
  background: #ffffff;
  border: 1px solid #dee2e6;
  border-radius: 20px;
  padding: 30px;
  box-shadow: 0 8px 20px rgba(33, 37, 41, 0.04);
}

.form-card h2 {
  margin-top: 0;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  margin-bottom: 18px;
}

label {
  margin-bottom: 6px;
  font-weight: bold;
}

input,
select,
textarea {
  padding: 12px;
  border: 1px solid #ced4da;
  border-radius: 8px;
  background: #ffffff;
  color: #212529;
}

input:focus,
select:focus,
textarea:focus {
  outline: 3px solid #d1e7dd;
  border-color: #198754;
}

textarea {
  min-height: 120px;
}

.help-text {
  background: #fff3cd;
  color: #664d03;
  border: 1px solid #ffecb5;
  padding: 12px;
  border-radius: 8px;
}

.create-btn {
  width: 100%;
  padding: 13px;
  border: none;
  border-radius: 8px;
  background: #198754;
  color: white;
  cursor: pointer;
}

.create-btn:hover {
  background: #157347;
}

.success-message {
  background: #d1e7dd;
  color: #0f5132;
  padding: 12px;
  border-radius: 8px;
  border: 1px solid #badbcc;
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
  .creator-app {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
