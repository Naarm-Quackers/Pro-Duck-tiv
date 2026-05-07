<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import tasksData from '../data/tasks.json'


// For current task editor to work make a folder called "data" in the path: C:\...\Pro-Duck-tiv\proDucktiv-vue-app\src\data
// Within the folder put the file "tasks.json" with the following content:

/*
  {
    "id": 1,
    "title": "Team Meeting",
    "type": "Meeting",
    "date": "2026-05-10",
    "time": "10:30",
    "location": "Room 204",
    "description": "Weekly project progress meeting",
    "completed": false
  },
  {
    "id": 2,
    "title": "Submit COS30043 Lab",
    "type": "Reminder",
    "date": "2026-05-15",
    "time": "23:59",
    "location": "Online",
    "description": "Upload lab task before deadline",
    "completed": true
  }
]
*/

// Current logic for task editor is based off of Task.vue and as such, this also needs the following codes to function:

/*
// In the script section of Task.vue

import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import tasksData from '../data/tasks.json'

const router = useRouter()
const tasks = ref([])

onMounted(() => {
  tasks.value = JSON.parse(localStorage.getItem('tasks')) || tasksData
})

function editTask(id) {
  router.push('/task-editor/' + id)
}

// In the template section of Task.vue


    <div class='container'>
        <div class='row'>
            <div class='col-12'>
                <h1>Task Page</h1>
                <button type="button" class="btn btn-success mb-3" @click="router.push('/task-creator')">+ New Task</button>
                <ul class="list-group">
                    <li v-for="task in tasks" :key="task.id" class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ task.title }} <small class="text-muted">({{ task.date }})</small></span>
                        <button class="btn btn-primary btn-sm" @click="editTask(task.id)">Edit</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
*/

const route = useRoute()
const router = useRouter()

const err = ref('')
const tasks = ref([])

const task = ref({
    id: '',
    title: '',
    type: '',
    date: '',
    time: '',
    location: '',
    description: '',
    completed: false
})

onMounted(() => {
    // Load tasks from src/data/tasks.json
    tasks.value = tasksData

    const existingTask = tasks.value.find(t => t.id == route.params.id)
    if (existingTask) {
        task.value = { ...existingTask }
    } else {
        err.value = 'Task not found'
    }
})

function saveTask() {
    const index = tasks.value.findIndex(t => t.id == task.value.id)
    if (index !== -1) {
        tasks.value[index] = { ...task.value }
        localStorage.setItem('tasks', JSON.stringify(tasks.value))
        router.push('/task')
    } else {
        err.value = 'Could not update task'
    }
}
</script>

<template>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <h1 class="mb-4">Edit Task</h1>

                <div v-if="err" class="alert alert-danger" role="alert">
                    {{ err }}
                </div>

                <form v-else @submit.prevent="saveTask">

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input v-model="task.title" type="text" id="title" class="form-control" placeholder="Task title" required />
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select v-model="task.type" id="type" class="form-select">
                            <option>Meeting</option>
                            <option>Appointment</option>
                            <option>Reminder</option>
                            <option>Other</option>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label for="date" class="form-label">Date</label>
                            <input v-model="task.date" type="date" id="date" class="form-control" />
                        </div>
                        <div class="col">
                            <label for="time" class="form-label">Time</label>
                            <input v-model="task.time" type="time" id="time" class="form-control" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input v-model="task.location" type="text" id="location" class="form-control" placeholder="Location" />
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea v-model="task.description" id="description" class="form-control" rows="4" placeholder="Task description"></textarea>
                    </div>

                    <div class="mb-4 form-check">
                        <input v-model="task.completed" type="checkbox" id="completed" class="form-check-input" />
                        <label for="completed" class="form-check-label">Mark as Completed</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-outline-secondary" @click="router.push('/task')">Cancel</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</template>
