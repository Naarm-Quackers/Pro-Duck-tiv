<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const Router = useRouter()

const TaskApiUrl =
    '/cos30043/s103071591/proDucktiv/resources/api_tasks.php'

const Title = ref('')
const Type = ref('')
const Date = ref('')
const Time = ref('')
const Location = ref('')
const Description = ref('')

const ErrorMessage = ref('')
const SuccessMessage = ref('')
const IsSubmitting = ref(false)

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
        localStorage.getItem('user_id') ||
        localStorage.getItem('UserId') ||
        localStorage.getItem('userId') ||
        sessionStorage.getItem('user_id') ||
        sessionStorage.getItem('UserId') ||
        sessionStorage.getItem('userId')

    if (directId && Number(directId) > 0) {
        return Number(directId)
    }

    return 0
}

function BuildTaskApiUrl(params = {}) {
    const UserId = GetLoggedInUserId()

    if (UserId <= 0) {
        throw new Error('No logged-in user found. Please sign in before creating a task.')
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
            'The task API did not return JSON. Check that api_tasks.php is running through XAMPP/Mercury and that TaskApiUrl is correct.'
        )
    }
}

function ValidateTaskForm() {
    if (!Title.value.trim()) {
        return 'Please enter a task title.'
    }

    if (!Type.value.trim()) {
        return 'Please select a task type.'
    }

    if (GetLoggedInUserId() <= 0) {
        return 'No logged-in user found. Please sign in before creating a task.'
    }

    return ''
}

async function CreateTask() {
    ErrorMessage.value = ''
    SuccessMessage.value = ''

    const ValidationError = ValidateTaskForm()

    if (ValidationError) {
        ErrorMessage.value = ValidationError
        return
    }

    const UserId = GetLoggedInUserId()

    const NewTask = {
        UserId,
        Title: Title.value.trim(),
        Type: Type.value,
        Date: Date.value || null,
        Time: Time.value || null,
        Location: Location.value.trim(),
        Description: Description.value.trim(),
        Completed: false
    }

    IsSubmitting.value = true

    try {
        const Response = await fetch(BuildTaskApiUrl(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(NewTask)
        })

        const Data = await ReadJsonResponse(Response)

        if (!Data.success) {
            throw new Error(Data.error || 'Unable to create task.')
        }

        SuccessMessage.value = 'Task created successfully.'

        setTimeout(() => {
            Router.push('/task')
        }, 600)
    } catch (Error) {
        ErrorMessage.value = Error.message
    } finally {
        IsSubmitting.value = false
    }
}

function CancelCreate() {
    Router.push('/task')
}
</script>

<template>
<main class="creator-page bg-light min-vh-100 py-4 py-lg-5">
    <div class="container">
        <section class="mx-auto mb-4 creator-width">
            <h1 class="display-6 fw-bold text-dark mb-1">Create Task</h1>
            <p class="text-secondary mb-0">Add a new task to your planner</p>
        </section>

        <form class="card shadow-sm border-0 border-top border-success border-4 mx-auto creator-width"
            @submit.prevent="CreateTask">
            <div class="card-body p-4 p-lg-5">
                <div v-if="ErrorMessage" class="alert alert-danger" role="alert">
                    {{ ErrorMessage }}
                </div>

                <div v-if="SuccessMessage" class="alert alert-success" role="status">
                    {{ SuccessMessage }}
                </div>

                <div class="mb-3">
                    <label for="Title" class="form-label fw-semibold">Title</label>
                    <input id="Title" v-model="Title" type="text" class="form-control form-control-lg"
                        placeholder="Enter task title" required />
                </div>

                <div class="mb-3">
                    <label for="Type" class="form-label fw-semibold">Type</label>
                    <select id="Type" v-model="Type" class="form-select form-select-lg" required>
                        <option value="">Select task type</option>
                        <option value="Study">Study</option>
                        <option value="Meeting">Meeting</option>
                        <option value="Work">Work</option>
                        <option value="Personal">Personal</option>
                        <option value="Assessment">Assessment</option>
                        <option value="Development">Development</option>
                        <option value="Health">Health</option>
                        <option value="Reminder">Reminder</option>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label for="Date" class="form-label fw-semibold">Date</label>
                        <input id="Date" v-model="Date" type="date" class="form-control form-control-lg" />
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="Time" class="form-label fw-semibold">Time</label>
                        <input id="Time" v-model="Time" type="time" class="form-control form-control-lg" />
                    </div>
                </div>

                <div class="mb-3">
                    <label for="Location" class="form-label fw-semibold">Location</label>
                    <input id="Location" v-model="Location" type="text" class="form-control form-control-lg"
                        placeholder="Location" />
                </div>

                <div class="mb-4">
                    <label for="Description" class="form-label fw-semibold">Description</label>
                    <textarea id="Description" v-model="Description" class="form-control form-control-lg" rows="5"
                        placeholder="Task description"></textarea>
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                    <button type="button" class="btn btn-outline-warning btn-lg px-4" @click="CancelCreate">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success btn-lg px-4" :disabled="IsSubmitting">
                        {{ IsSubmitting ? 'Creating...' : 'Create Task' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>
</template>

<style scoped>
.creator-page {
    font-family: Arial, sans-serif;
}

.creator-width {
    max-width: 850px;
}
</style>
