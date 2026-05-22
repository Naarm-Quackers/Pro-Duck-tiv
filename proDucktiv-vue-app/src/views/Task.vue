<script setup>
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
</script>

<template>
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
</template>
