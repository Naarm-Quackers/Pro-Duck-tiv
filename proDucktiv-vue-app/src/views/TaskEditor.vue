<template>
    <!-- Page container for the task editor view. -->
    <div class="container py-4">
        <!-- Main card wrapper around all editor content. -->
        <fieldset class="card p-2 mb-5 border-0 shadow-sm h-100 rounded bg-body">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <!-- Page heading and supporting text. -->
                    <div>
                        <h1 class="mb-1">Edit Task</h1>
                        <p v-if="!showSelectTaskMessage" class="text-secondary mb-0">Edit a task from your planner</p>
                    </div>
                    

                    <!-- Guidance shown when no task id is available in the route. -->
                    <div v-if="showSelectTaskMessage" class="alert alert-info" role="alert">
                        Please choose a task first from the Task page, then click Edit to open it here.
                    </div>

                    <!-- API or validation error message. -->
                    <div v-else-if="err" class="alert alert-danger" role="alert">
                        {{ err }}
                    </div>

                    <!-- Editable task form shown when task data is available. -->
                    <form v-else @submit.prevent="saveTask">

                        <!-- Task title input. -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input v-model="task.Title" type="text" id="title" class="form-control" placeholder="Task title" required />
                        </div>

                        <!-- Task type selector. -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select v-model="task.Type" id="type" class="form-select" required>
                                <option value="Study">Study</option>
                                <option value="Meeting">Meeting</option>
                                <option value="Task">Task</option>
                                <option value="Work">Work</option>
                                <option value="Personal">Personal</option>
                                <option value="Assessment">Assessment</option>
                                <option value="Development">Development</option>
                                <option value="Health">Health</option>
                                <option value="Reminder">Reminder</option>
                            </select>
                        </div>

                        <!-- Date and time scheduling fields. -->
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

                        <!-- Location input. -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input v-model="task.Location" type="text" id="location" class="form-control" placeholder="Location" />
                        </div>

                        <!-- Description text area. -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea v-model="task.Description" id="description" class="form-control" rows="4" placeholder="Task description"></textarea>
                        </div>

                        <!-- Completion status toggle. -->
                        <div class="mb-4 form-check">
                            <input
                                id="status"
                                v-model="task.Completed"
                                class="form-check-input"
                                type="checkbox"
                            />
                            <label for="status" class="form-check-label">Mark as complete</label>
                        </div>

                        <!-- Primary and secondary form actions. -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-success" :disabled="isSaving">
                                {{ isSaving ? 'Saving...' : 'Save Changes' }}
                            </button>
                            <button type="button" class="btn btn-outline-warning" @click="router.push('/task')">Cancel</button>
                        </div>

                    </form>

                    <!-- Quick navigation back to task list when no task is selected. -->
                    <div v-if="showSelectTaskMessage" class="mt-3">
                        <button type="button" class="btn btn-primary" @click="router.push('/task')">Go to Tasks</button>
                    </div>
                </div>
            </div>
        </fieldset>    
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// UI state for error display, no-selection guidance, and submit loading.
const err = ref('')
const showSelectTaskMessage = ref(false)
const isSaving = ref(false)

// Tasks API endpoint used for reading and updating a single task.
const TASKS_API = '/resources/api_tasks.php'

// Default task shape so the form always has all expected properties.
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

// Reads the signed-in user id from localStorage for scoped API calls.
function getLoggedInUserId() {
    try {
        const user = localStorage.getItem('currentUser')
        if (!user) {
            return 0
        }

        const parsed = JSON.parse(user)
        
        const id = Number(parsed.user_id)
        return Number.isFinite(id) ? id : 0
    } catch {
        return 0
    }
}

onMounted(() => {
    const userId = getLoggedInUserId()
    // If Task Editor selected from navbar then return to Task page
    if (!route.params.id) {
        showSelectTaskMessage.value = true
        return
    }

    loadTask()
})

// Parses JSON safely and throws a clear message for non-JSON API responses.
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

// Loads the selected task and maps API data into the form model.
async function loadTask() {
    err.value = ''

    try {
        const userId = getLoggedInUserId()
        if (userId <= 0) {
            throw new Error('No logged-in user found. Please sign in before editing tasks.')
        }

        const res = await fetch(`${TASKS_API}?UserId=${userId}&TaskId=${route.params.id}`)
        const dataText = await res.text()
        let data

        try {
            data = JSON.parse(dataText)
        } catch {
            throw new Error(
                'The task API did not return JSON. Check that api_tasks.php is running and TaskApiUrl is correct.'
            )
        }

        if (!Array.isArray(data) || data.length === 0) {
            err.value = 'Task not found'
            return
        }

        // Merge the API row into a default object so the form always has every field it expects.
        task.value = { ...emptyTask, ...data[0] }
    } catch (error) {
        err.value = error instanceof Error ? error.message : 'Could not load task'
    }
}

// Validates and submits task updates to the API.
async function saveTask() {
    err.value = ''

    if (!task.value.Title || !task.value.Type) {
        err.value = 'Title and type are required'
        return
    }

    isSaving.value = true

    const userId = getLoggedInUserId()
    if (userId <= 0) {
        err.value = 'No logged-in user found. Please sign in before editing tasks.'
        isSaving.value = false
        return
    }

    const payload = {
        ...task.value,
        UserId: userId,
        Completed: task.value.Completed === true
    }

    try {
        const taskId = task.value.Id
        // The task API identifies the record by both the signed-in user and the task id.
        const res = await fetch(`${TASKS_API}?UserId=${userId}&TaskId=${taskId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })

        const data = await ReadJsonResponse(res)

        if (!data.success) {
            throw new Error(data.error)
        }

        router.push('/task')
    } catch (error) {
        err.value = error instanceof Error ? error.message : 'Could not update task'
    } finally {
        isSaving.value = false
    }
}
</script>