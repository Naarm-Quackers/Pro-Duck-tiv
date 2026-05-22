<script setup>
import { ref, computed, onMounted } from 'vue'

const search = ref('')
const currentTab = ref('active')

const tasks = ref([])

onMounted(() => {
  const savedTasks =
    JSON.parse(localStorage.getItem('tasks')) || []

  tasks.value = savedTasks
})

const filteredTasks = computed(() => {
  return tasks.value.filter((task) => {
    const matchesSearch = task.title
      .toLowerCase()
      .includes(search.value.toLowerCase())

    if (currentTab.value === 'active') {
      return !task.completed && matchesSearch
    }

    return task.completed && matchesSearch
  })
})

function saveTasks() {
  localStorage.setItem(
    'tasks',
    JSON.stringify(tasks.value)
  )
}

function toggleTask(task) {
  task.completed = !task.completed
  saveTasks()
}

function deleteTask(id) {
  tasks.value = tasks.value.filter(
    (task) => task.id !== id
  )

  saveTasks()
}
</script>

<template>
  <div class="task-page">

    <div class="page-header">
      <div>
        <h1>Tasks</h1>
        <p>Manage your tasks in one place</p>
      </div>
    </div>

    <div class="controls">
      <input v-model="search" type="text" placeholder="Search tasks..." class="search-input" />

      <div class="tabs">
        <button :class="{ activeTab: currentTab === 'active' }" @click="currentTab = 'active'">
          Active
        </button>

        <button :class="{ activeTab: currentTab === 'done' }" @click="currentTab = 'done'">
          Done
        </button>
      </div>
    </div>

    <div class="task-grid">
      <div v-for="task in filteredTasks" :key="task.id" class="task-card">
        <div class="task-top">
          <h2>{{ task.title }}</h2>

          <span class="status">
            {{ task.completed ? 'Done' : 'Active' }}
          </span>
        </div>

        <p class="task-type">
          {{ task.type }}
        </p>

        <p class="description">
          {{ task.description }}
        </p>

        <div class="actions">
          <button @click="toggleTask(task)">
            Toggle
          </button>

          <button class="delete-btn" @click="deleteTask(task.id)">
            Delete
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
.task-page {
  max-width: 1400px;
  margin: auto;
  padding: 40px;
  font-family: Arial, sans-serif;
}

.page-header {
  margin-bottom: 30px;
}

.page-header h1 {
  font-size: 42px;
  margin-bottom: 8px;
  color: #222;
}

.page-header p {
  color: #666;
}

.controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 35px;
  flex-wrap: wrap;
  gap: 20px;
}

.search-input {
  width: 320px;
  padding: 14px;
  border-radius: 10px;
  border: 1px solid #ccc;
  font-size: 15px;
}

.tabs {
  display: flex;
  gap: 10px;
}

.tabs button {
  padding: 12px 18px;
  border: 1px solid #ccc;
  background: white;
  border-radius: 10px;
  cursor: pointer;
}

.activeTab {
  background: #e5e5e5 !important;
}

.task-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 24px;
}

.task-card {
  border: 1px solid #ddd;
  border-radius: 16px;
  padding: 24px;
  background: white;
}

.task-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.task-top h2 {
  margin: 0;
  font-size: 24px;
}

.status {
  background: #efefef;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 13px;
}

.task-type {
  margin-top: 14px;
  color: #555;
}

.description {
  margin: 20px 0;
  color: #666;
  line-height: 1.5;
}

.actions {
  display: flex;
  gap: 12px;
}

.actions button {
  flex: 1;
  padding: 12px;
  border: 1px solid #ccc;
  background: white;
  border-radius: 10px;
  cursor: pointer;
}

.delete-btn:hover {
  background: #f5f5f5;
}
</style>