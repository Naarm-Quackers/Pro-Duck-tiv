<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

const err = ref('')
const showSelectTaskMessage = ref(false)
const isSaving = ref(false)

const TaskApiUrl = '/resources/api_tasks.php'

function GetLoggedInUserId() {
    const possibleKeys = [
        'currentUser',
        'loggedInUser',
        'pdtUser',
        'user',
        'authUser',
        'User'
    ]

    for (const key of possibleKeys) {
        const value = localStorage.getItem(key) || sessionStorage.getItem(key)

        if (!value) continue

        try {
            const parsed = JSON.parse(value)
            const id = parsed.user_id || parsed.UserId || parsed.userId || parsed.id

            if (id && Number(id) > 0) {
                return Number(id)
            }
        } catch {
            if (Number(value) > 0) {
                return Number(value)
            }
        }
    }

    const directId =
        sessionStorage.getItem('user_id')

    if (directId && Number(directId) > 0) {
        return Number(directId)
    }

    return 0
}

function BuildTaskApiUrl(params = {}) {
    const UserId = GetLoggedInUserId()

    if (UserId <= 0) {
        throw new Error('No logged-in user found. Please sign in before editing tasks.')
    }

    const Url = new URL(TaskApiUrl, window.location.origin)
    Url.searchParams.set('UserId', UserId)

    Object.entries(params).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            Url.searchParams.set(key, value)
        }
    })

    return Url.toString()
}

async function ReadJsonResponse(Response) {
    const ResponseText = await Response.text()

    try {
        return JSON.parse(ResponseText)
    } catch {
        throw new Error(
            'The task API did not return JSON. Check that api_tasks.php is running and TaskApiUrl is correct.'
        )
    }
}

const emptyTask = {
    id: '',
    title: '',
    type: '',
    date: '',
    time: '',
    location: '',
    description: '',
    status: 'Active',
    creationDate: '',
    completedDate: ''
}

const task = ref({
    ...emptyTask
})

onMounted(() => {
    const UserId = GetLoggedInUserId()
    // If Task Editor selected from navbar then
    if (!route.params.id) {
        showSelectTaskMessage.value = true
        return
    }

    loadTask()
})

async function loadTask() {
    err.value = ''

    try {
        const Response = await fetch(BuildTaskApiUrl({ TaskId: route.params.id }))
        const Data = await ReadJsonResponse(Response)

        if (!Array.isArray(Data) || Data.length === 0) {
            err.value = 'Task not found'
            return
        }

        task.value = { ...emptyTask, ...Data[0] }
    } catch (error) {
        err.value = error instanceof Error ? error.message : 'Could not load task'
    }
}

async function saveTask() {
    err.value = ''

    if (!task.value.Title || !task.value.Type) {
        err.value = 'Title and type are required'
        return
    }

    isSaving.value = true

    const payload = {
        ...task.value,
        UserId: GetLoggedInUserId(),
        Completed: task.value.Completed === true || task.value.status === 'Complete'
    }

    try {
        const Response = await fetch(BuildTaskApiUrl({ TaskId: task.value.Id || route.params.id }), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })

        const Data = await ReadJsonResponse(Response)

        if (!Data.success) {
            throw new Error(Data.error || 'Could not update task')
        }

        router.push('/task')
    } catch (error) {
        err.value = error instanceof Error ? error.message : 'Could not update task'
    } finally {
        isSaving.value = false
    }
}
</script>

<template>
    <div class="container py-4">
        <fieldset class="card p-2 mb-5 border-0 shadow-sm h-100 rounded bg-body">
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
                            <input v-model="task.Title" type="text" id="title" class="form-control" placeholder="Task title" required />
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select v-model="task.Type" id="type" class="form-select" required>
                                <option value="Task">Task</option>
                                <option value="Meeting">Meeting</option>
                                <option value="Deadline">Deadline</option>
                                <option value="Study">Study</option>
                                <option value="Personal">Personal</option>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col">
                                <label for="date" class="form-label">Date</label>
                                <input v-model="task.Date" type="date" id="date" class="form-control" />
                            </div>
                            <div class="col">
                                <label for="time" class="form-label">Time</label>
                                <input v-model="task.Time" type="time" id="time" class="form-control" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input v-model="task.Location" type="text" id="location" class="form-control" placeholder="Location" />
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea v-model="task.Description" id="description" class="form-control" rows="4" placeholder="Task description"></textarea>
                        </div>

                        <div class="mb-4 form-check">
                            <input
                                id="status"
                                v-model="task.Completed"
                                class="form-check-input"
                                type="checkbox"
                            />
                            <label for="status" class="form-check-label">Mark as complete</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-success" :disabled="isSaving">
                                {{ isSaving ? 'Saving...' : 'Save Changes' }}
                            </button>
                            <button type="button" class="btn btn-outline-warning" @click="router.push('/task')">Cancel</button>
                        </div>

                    </form>

                    <div v-if="showSelectTaskMessage" class="mt-3">
                        <button type="button" class="btn btn-primary" @click="router.push('/task')">Go to Tasks</button>
                    </div>
                </div>
            </div>
        </fieldset>    
    </div>
</template>
