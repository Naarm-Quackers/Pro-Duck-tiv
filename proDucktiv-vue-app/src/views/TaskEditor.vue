<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const err = ref('')
const tasks = ref([])
const showSelectTaskMessage = ref(false)

const emptyTask = ref({
    id: '',
    title: '',
    type: '',
    date: '',
    time: '',
    location: '',
    description: '',
    completed: false
})

const task = ref({
    ...emptyTask
})

onMounted(() => {
    tasks.value = JSON.parse(localStorage.getItem('tasks')) || []

    if (!route.params.id) {
        showSelectTaskMessage.value = true
        return
    }

    const existingTask = tasks.value.find(t => t.id == route.params.id)
    if (existingTask) {
        task.value = { ...emptyTask, ...existingTask }
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

                <div v-if="showSelectTaskMessage" class="alert alert-info" role="alert">
                    Please choose a task first from the Task page, then click Edit to open it here.
                </div>

                <div v-else-if="err" class="alert alert-danger" role="alert">
                    {{ err }}
                </div>

                <form v-else @submit.prevent="saveTask">

                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input v-model="task.title" type="text" id="title" class="form-control" placeholder="Task title" required />
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <input v-model="task.type" type="text" id="type" class="form-control" placeholder="Task type" required />
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
                        <label for="completed" class="form-check-label">Mark as Done</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-success">Save Changes</button>
                        <button type="button" class="btn btn-outline-warning" @click="router.push('/task')">Cancel</button>
                    </div>

                </form>

                <div v-if="showSelectTaskMessage" class="mt-3">
                    <button type="button" class="btn btn-primary" @click="router.push('/task')">Go to Tasks</button>
                </div>
            </div>
        </div>
    </div>
</template>
