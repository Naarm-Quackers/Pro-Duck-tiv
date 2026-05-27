<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const Router = useRouter()

const TaskApiUrl = '/cos30043/s103071591/proDucktiv/resources/api_tasks.php'

const Search = ref('')
const CurrentTab = ref('Active')
const Tasks = ref([])
const IsLoading = ref(false)
const ErrorMessage = ref('')

const ShowConfetti = ref(false)
const ConfettiPieces = ref([])

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
        throw new Error('No logged-in user found. Please sign in before using tasks.')
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

function TriggerConfetti() {
    const Styles = ['circle', 'square', 'strip']

    ConfettiPieces.value = Array.from({ length: 80 }, (_, Index) => ({
        Id: Index,
        Left: Math.random() * 100,
        Delay: Math.random() * 0.4,
        Duration: 1.8 + Math.random() * 1.6,
        Size: 6 + Math.random() * 10,
        Drift: -80 + Math.random() * 160,
        Style: Styles[Math.floor(Math.random() * Styles.length)]
    }))

    ShowConfetti.value = true

    setTimeout(() => {
        ShowConfetti.value = false
        ConfettiPieces.value = []
    }, 2600)
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

async function LoadTasks() {
    IsLoading.value = true
    ErrorMessage.value = ''

    try {
        const Response = await fetch(BuildTaskApiUrl())
        const Data = await ReadJsonResponse(Response)

        if (!Array.isArray(Data)) {
            throw new Error(Data.error || 'Unable to load tasks from the database.')
        }

        Tasks.value = Data
    } catch (Error) {
        ErrorMessage.value = Error.message
    } finally {
        IsLoading.value = false
    }
}

function GetTaskDateTime(Task) {
    if (!Task.Date) return null

    const SafeTime = Task.Time ? String(Task.Time).slice(0, 5) : '23:59'
    const DateTime = new Date(`${Task.Date}T${SafeTime}`)

    if (Number.isNaN(DateTime.getTime())) return null

    return DateTime
}

function GetReminderStatus(Task) {
    const IsCompleted = Number(Task.Completed) === 1 || Task.Completed === true

    if (IsCompleted) {
        return {
            Label: 'Completed',
            ClassName: 'bg-success-subtle text-success border border-success-subtle'
        }
    }

    const DateTime = GetTaskDateTime(Task)

    if (!DateTime) {
        return {
            Label: 'No Due Date',
            ClassName: 'bg-secondary-subtle text-secondary border border-secondary-subtle'
        }
    }

    const Now = new Date()
    const HoursLeft = (DateTime.getTime() - Now.getTime()) / (1000 * 60 * 60)

    if (HoursLeft < 0) {
        return {
            Label: 'Overdue',
            ClassName: 'bg-danger-subtle text-danger border border-danger-subtle'
        }
    }

    if (HoursLeft <= 48) {
        return {
            Label: 'Due Soon',
            ClassName: 'bg-warning-subtle text-warning-emphasis border border-warning-subtle'
        }
    }

    return {
        Label: 'Upcoming',
        ClassName: 'bg-info-subtle text-info-emphasis border border-info-subtle'
    }
}

const ActiveCount = computed(() =>
    Tasks.value.filter((Task) => !(Number(Task.Completed) === 1 || Task.Completed === true)).length
)

const DoneCount = computed(() =>
    Tasks.value.filter((Task) => Number(Task.Completed) === 1 || Task.Completed === true).length
)

const DueSoonCount = computed(() =>
    Tasks.value.filter((Task) => GetReminderStatus(Task).Label === 'Due Soon').length
)

const OverdueCount = computed(() =>
    Tasks.value.filter((Task) => GetReminderStatus(Task).Label === 'Overdue').length
)

const FilteredTasks = computed(() => {
    return Tasks.value.filter((Task) => {
        const SearchText = Search.value.toLowerCase().trim()

        const MatchesSearch =
            (Task.Title || '').toLowerCase().includes(SearchText) ||
            (Task.Type || '').toLowerCase().includes(SearchText) ||
            (Task.Location || '').toLowerCase().includes(SearchText) ||
            (Task.Description || '').toLowerCase().includes(SearchText)

        const IsCompleted = Number(Task.Completed) === 1 || Task.Completed === true

        if (CurrentTab.value === 'Active') {
            return !IsCompleted && MatchesSearch
        }

        return IsCompleted && MatchesSearch
    })
})

async function ToggleTask(Task) {
    const IsCompleted = Number(Task.Completed) === 1 || Task.Completed === true
    const UserId = GetLoggedInUserId()

    const UpdatedTask = {
        ...Task,
        UserId,
        Completed: !IsCompleted
    }

    try {
        const Response = await fetch(BuildTaskApiUrl({ TaskId: Task.Id }), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(UpdatedTask)
        })

        const Data = await ReadJsonResponse(Response)

        if (!Data.success) {
            throw new Error(Data.error || 'Unable to update task.')
        }

        if (!IsCompleted) {
            TriggerConfetti()
        }

        await LoadTasks()
    } catch (Error) {
        ErrorMessage.value = Error.message
    }
}

async function DeleteTask(TaskId) {
    const ConfirmDelete = confirm('Are you sure you want to delete this task?')

    if (!ConfirmDelete) return

    try {
        const Response = await fetch(BuildTaskApiUrl({ TaskId }), {
            method: 'DELETE'
        })

        const Data = await ReadJsonResponse(Response)

        if (!Data.success) {
            throw new Error(Data.error || 'Unable to delete task.')
        }

        await LoadTasks()
    } catch (Error) {
        ErrorMessage.value = Error.message
    }
}

function EditTask(TaskId) {
    Router.push(`/task-editor/${TaskId}`)
}

function CreateTask() {
    Router.push('/task-creator')
}

onMounted(() => {
    LoadTasks()
})
</script>

<template>
<main class="task-page bg-light min-vh-100 py-4 py-lg-5">
    <div v-if="ShowConfetti" class="confetti-layer" aria-hidden="true">
        <span v-for="Piece in ConfettiPieces" :key="Piece.Id" class="confetti-piece" :class="Piece.Style" :style="{
            left: Piece.Left + '%',
            width: Piece.Size + 'px',
            height: Piece.Size + 'px',
            animationDelay: Piece.Delay + 's',
            animationDuration: Piece.Duration + 's',
            '--drift': Piece.Drift + 'px'
        }"></span>
    </div>

    <div class="container-fluid px-3 px-lg-5">
        <section
            class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-3 mb-4">
            <div>
                <h1 class="display-6 fw-bold text-dark mb-1">Tasks</h1>
                <p class="text-secondary mb-0">Manage your tasks in one place</p>
            </div>

            <button class="btn btn-success btn-lg px-4" type="button" @click="CreateTask">
                + New Task
            </button>
        </section>

        <section class="row g-3 mb-4" aria-label="Task summary">
            <div class="col-12 col-sm-6 col-xl-3">
                <article class="card h-100 shadow-sm border-0 border-start border-success border-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Total</p>
                        <h2 class="h3 fw-bold mb-0">{{ Tasks.length }}</h2>
                    </div>
                </article>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <article class="card h-100 shadow-sm border-0 border-start border-success border-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Active</p>
                        <h2 class="h3 fw-bold mb-0">{{ ActiveCount }}</h2>
                    </div>
                </article>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <article class="card h-100 shadow-sm border-0 border-start border-warning border-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Due Soon</p>
                        <h2 class="h3 fw-bold mb-0 text-warning-emphasis">{{ DueSoonCount }}</h2>
                    </div>
                </article>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <article class="card h-100 shadow-sm border-0 border-start border-danger border-4">
                    <div class="card-body">
                        <p class="text-secondary mb-1">Overdue</p>
                        <h2 class="h3 fw-bold mb-0 text-danger">{{ OverdueCount }}</h2>
                    </div>
                </article>
            </div>
        </section>

        <section class="row g-3 align-items-center mb-4">
            <div class="col-12 col-lg-7">
                <label for="TaskSearch" class="visually-hidden">Search tasks</label>
                <input id="TaskSearch" v-model="Search" type="search" class="form-control form-control-lg"
                    placeholder="Search by title, type, location, or description..." />
            </div>

            <div class="col-12 col-lg-auto">
                <div class="btn-group" role="tablist" aria-label="Task status filter">
                    <button type="button" class="btn"
                        :class="CurrentTab === 'Active' ? 'btn-success' : 'btn-outline-success'"
                        @click="CurrentTab = 'Active'">
                        Active
                        <span class="badge text-bg-light ms-2">{{ ActiveCount }}</span>
                    </button>

                    <button type="button" class="btn"
                        :class="CurrentTab === 'Done' ? 'btn-success' : 'btn-outline-success'"
                        @click="CurrentTab = 'Done'">
                        Done
                        <span class="badge text-bg-light ms-2">{{ DoneCount }}</span>
                    </button>
                </div>
            </div>
        </section>

        <div v-if="ErrorMessage" class="alert alert-danger" role="alert">
            {{ ErrorMessage }}
        </div>

        <div v-if="IsLoading" class="alert alert-info" role="status">
            Loading tasks...
        </div>

        <section v-else class="row g-4" aria-label="Task list">
            <div v-for="Task in FilteredTasks" :key="Task.Id" class="col-12 col-md-6 col-xl-4">
                <article class="card task-card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column gap-3">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <h2 class="h5 fw-bold mb-2">{{ Task.Title }}</h2>

                                <div class="d-flex flex-wrap gap-2">
                                    <span
                                        class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                                        {{ Task.Type || 'Task' }}
                                    </span>

                                    <span class="badge rounded-pill" :class="GetReminderStatus(Task).ClassName">
                                        {{ GetReminderStatus(Task).Label }}
                                    </span>
                                </div>
                            </div>

                            <input class="form-check-input mt-1" type="checkbox"
                                :checked="Number(Task.Completed) === 1 || Task.Completed === true"
                                aria-label="Mark task as completed" @change="ToggleTask(Task)" />
                        </div>

                        <p class="card-text text-secondary mb-0">
                            {{ Task.Description || 'No description added.' }}
                        </p>

                        <div class="row g-2 mt-auto small">
                            <div class="col-12 col-sm-4">
                                <div class="border rounded-3 p-2 bg-light h-100">
                                    <span class="text-secondary d-block">Date</span>
                                    <strong>{{ Task.Date || 'Not set' }}</strong>
                                </div>
                            </div>

                            <div class="col-12 col-sm-4">
                                <div class="border rounded-3 p-2 bg-light h-100">
                                    <span class="text-secondary d-block">Time</span>
                                    <strong>{{ Task.Time ? String(Task.Time).slice(0, 5) : 'Not set' }}</strong>
                                </div>
                            </div>

                            <div class="col-12 col-sm-4">
                                <div class="border rounded-3 p-2 bg-light h-100">
                                    <span class="text-secondary d-block">Location</span>
                                    <strong>{{ Task.Location || 'Not set' }}</strong>
                                </div>
                            </div>
                        </div>



                        <div class="d-flex flex-wrap gap-2 pt-2">
                            <button type="button" class="btn btn-outline-success" @click="ToggleTask(Task)">
                                {{ Number(Task.Completed) === 1 || Task.Completed === true ? 'Mark Active' : 'Mark Done'
                                }}
                            </button>

                            <button type="button" class="btn btn-outline-warning" @click="EditTask(Task.Id)">
                                Edit
                            </button>

                            <button type="button" class="btn btn-outline-danger" @click="DeleteTask(Task.Id)">
                                Delete
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            <div v-if="FilteredTasks.length === 0" class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-secondary py-5">
                        No tasks found.
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>
</template>

<style scoped>
.task-page {
    font-family: Arial, sans-serif;
}

.task-card {
    border-top: 0.35rem solid #198754 !important;
}

.confetti-layer {
    position: fixed;
    inset: 0;
    pointer-events: none;
    overflow: hidden;
    z-index: 9999;
}

.confetti-piece {
    position: absolute;
    top: -20px;
    display: block;
    background: #198754;
    animation-name: confetti-fall;
    animation-timing-function: ease-out;
    animation-fill-mode: forwards;
}

.confetti-piece.circle {
    border-radius: 50%;
    background: #ffc107;
}

.confetti-piece.square {
    border-radius: 3px;
    background: #198754;
}

.confetti-piece.strip {
    width: 6px !important;
    height: 16px !important;
    border-radius: 4px;
    background: #0d6efd;
}

@keyframes confetti-fall {
    0% {
        transform: translate3d(0, -20px, 0) rotate(0deg);
        opacity: 1;
    }

    100% {
        transform: translate3d(var(--drift), 110vh, 0) rotate(720deg);
        opacity: 0;
    }
}
</style>
