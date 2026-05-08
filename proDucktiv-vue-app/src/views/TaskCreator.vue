<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const title = ref('')
const type = ref('')
const description = ref('')

function createTask() {
  if (!title.value || !type.value) {
    alert('Please fill in all required fields')
    return
  }

  const existingTasks =
    JSON.parse(localStorage.getItem('tasks')) || []

  const newTask = {
    id: Date.now(),
    title: title.value,
    type: type.value,
    description: description.value,
    completed: false,
  }

  existingTasks.push(newTask)

  localStorage.setItem(
    'tasks',
    JSON.stringify(existingTasks)
  )

  alert('Task created successfully!')

  router.push('/task')
}
</script>

<template>
  <div class="creator-page">

    <div class="page-header">
      <h1>Create Task</h1>
      <p>Add a new task to your planner</p>
    </div>

    <div class="form-container">

      <div class="form-group">
        <label>Task Title</label>
        <input v-model="title" type="text" placeholder="Enter task title" />
      </div>

      <div class="form-group">
        <label>Task Type</label>
        <input v-model="type" type="text" placeholder="Study, Meeting, Work..." />
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea v-model="description" rows="6" placeholder="Enter task description"></textarea>
      </div>

      <button class="submit-btn" @click="createTask">
        Create Task
      </button>

    </div>

  </div>
</template>

<style scoped>
.creator-page {
  max-width: 1400px;
  margin: auto;
  padding: 40px;
  font-family: Arial, sans-serif;
}

.page-header {
  margin-bottom: 40px;
}

.page-header h1 {
  font-size: 42px;
  margin-bottom: 8px;
  color: #222;
}

.page-header p {
  color: #666;
}

.form-container {
  width: 100%;
  border: 1px solid #ddd;
  border-radius: 18px;
  padding: 40px;
  background: white;
}

.form-group {
  margin-bottom: 28px;
}

.form-group label {
  display: block;
  margin-bottom: 10px;
  font-weight: bold;
  color: #333;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 16px;
  border: 1px solid #ccc;
  border-radius: 12px;
  font-size: 15px;
  box-sizing: border-box;
}

.form-group textarea {
  resize: vertical;
}

.submit-btn {
  padding: 14px 28px;
  border: 1px solid #ccc;
  background: #f5f5f5;
  border-radius: 12px;
  cursor: pointer;
  font-size: 15px;
}

.submit-btn:hover {
  background: #ebebeb;
}
</style>