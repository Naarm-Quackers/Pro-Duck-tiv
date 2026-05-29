<template><!-- Authenticated dashboard container -->
<div class="row">
    <!-- Header row: greeting and logout action -->
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h4 class="mb-0 fw-semibold">Hello, {{ currentUser?.user_first_name }}!</h4>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-outline-danger" @click="logout">
                Log Out
            </button>
        </div>
    </div>

    <!-- Top cards: productivity summary and mood voting -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Today's Task Productivity</small>

                    <div class="d-flex align-items-center gap-3 mt-2">
                        <div class="flex-grow-1">
                            <span class="display-6 fw-bold text-success">
                                {{ productivityScore }}%
                            </span>

                            <div>
                                <small class="text-muted">Keep going!</small>
                            </div>

                            <div class="progress mt-2" style="height:6px;">
                                <div class="progress-bar bg-success" :style="{ width: productivityScore + '%' }"></div>
                            </div>

                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">
                                    {{ completedTasks }} completed today
                                </small>

                                <small class="text-muted">
                                    {{ todaysTasks.length - completedTasks }} remaining
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted fw-semibold">How was your day?</small>

                    <p class="text-muted mb-3" style="font-size:0.85rem;">
                        Let us know how you're feeling
                    </p>

                    <div class="d-flex gap-3 justify-content-center">
                        <button class="btn btn-sm" :class="userMood === 'up' ? 'btn-success' : 'btn-outline-success'"
                            :disabled="!!todaysMood" @click="onSetMood('up')">
                            <i class="bi bi-hand-thumbs-up"></i>
                            Good
                        </button>

                        <button class="btn btn-sm" :class="userMood === 'down' ? 'btn-danger' : 'btn-outline-danger'"
                            :disabled="!!todaysMood" @click="onSetMood('down')">
                            <i class="bi bi-hand-thumbs-down"></i>
                            Bad
                        </button>
                    </div>

                    <div v-if="todaysMood" class="mt-2 text-center">
                        <span v-if="todaysMood === 'up'" class="badge bg-success">
                            You voted <i class="bi bi-hand-thumbs-up"></i> today
                        </span>

                        <span v-else-if="todaysMood === 'down'" class="badge bg-danger">
                            You voted <i class="bi bi-hand-thumbs-down"></i> today
                        </span>
                    </div>

                    <div v-if="moodsError" class="mt-2 text-center">
                        <small class="text-danger">
                            Mood database error: {{ moodsError }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Middle cards: task counters and aggregate mood stats -->
    <div class="row g-3 mb-3">
        <div v-if="tasksError" class="col-12">
            <div class="alert alert-danger py-2 mb-0" role="alert">
                Task database error: {{ tasksError }}
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Completed Tasks</small>
                        <i class="bi bi-check-circle-fill text-success"></i>
                    </div>

                    <div class="fs-2 fw-bold mt-2">
                        {{ completedTasksThisWeek }}
                    </div>

                    <small class="text-muted">
                        {{ completedTasksThisWeek }} this week
                    </small>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Active Tasks</small>
                        <i class="bi bi-clock-fill text-warning"></i>
                    </div>

                    <div class="fs-2 fw-bold mt-2">
                        {{ activeTasks }}
                    </div>

                    <small class="text-muted">
                        {{ overdueTasks }} overdue {{ overdueTasks === 1 ? 'task' : 'tasks' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Users mood for today</small>
                        <i class="bi bi-emoji-smile-fill text-warning"></i>
                    </div>

                    <div class="fs-2 fw-bold mt-2">
                        <span class="text-success me-2">
                            <i class="bi bi-hand-thumbs-up"></i>
                            {{ moodUp }}
                        </span>

                        <span class="text-danger">
                            <i class="bi bi-hand-thumbs-down"></i>
                            {{ moodDown }}
                        </span>
                    </div>

                    <small class="text-muted">
                        {{ moodUp + moodDown }} users voted today
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom cards: today's tasks, deadlines, and recent journals -->
    <div class="row g-3">
        <div class="col-12 col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar3 me-2 text-success"></i>
                            Today's Tasks
                        </h6>
                    </div>

                    <div v-if="todaysTasks.length === 0" class="text-center text-muted py-4">
                        <p>You're all clear!</p>

                        <router-link to="/task-creator">
                            <button class="btn btn-outline-success btn-sm">
                                Add a task
                            </button>
                        </router-link>
                    </div>

                    <ul v-else class="list-group list-group-flush">
                        <li v-for="task in todaysTasks" :key="task.Id" class="list-group-item">
                            {{ task.Title }}

                            <span class="badge ms-2" :class="isTaskComplete(task)
                                    ? 'bg-success'
                                    : isTaskOverdue(task)
                                        ? 'bg-danger'
                                        : 'bg-secondary'
                                ">
                                {{
                                    isTaskComplete(task)
                                        ? 'Done'
                                        : isTaskOverdue(task)
                                ? 'Overdue'
                                : 'Active'
                                }}
                            </span>
                        </li>
                    </ul>

                    <div v-if="todaysTasks.length > 0" class="mt-auto pt-3">
                        <router-link to="/task">
                            <button class="btn btn-outline-success btn-sm">
                                View Tasks
                            </button>
                        </router-link>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-5 d-flex flex-column gap-3">
            <!-- Upcoming deadlines list card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6>
                        <i class="bi bi-alarm text-success me-2"></i>
                        Upcoming Deadlines
                    </h6>

                    <p v-if="upcomingDeadlines.length === 0" class="text-muted mb-0" style="font-size:0.85rem;">
                        No upcoming deadlines
                    </p>

                    <ul v-else class="list-group list-group-flush">
                        <li v-for="d in upcomingDeadlines" :key="d.Id" class="list-group-item px-0">
                            {{ d.Title }} {{ d.Date }} at {{ d.Time }}
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recent journals list card with mood status badges -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6>
                        <i class="bi bi-journal-text me-2"></i>
                        Recent Journal
                    </h6>

                    <ul class="list-group list-group-flush">
                        <li v-for="entry in recentJournal" :key="entry.title"
                            class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold" style="font-size:0.9rem;">
                                    {{ entry.title }}

                                    <small class="text-muted">
                                        {{ entry.date }}
                                    </small>
                                </div>
                            </div>

                            <span v-if="entry.status === 'great'" class="badge bg-success-subtle text-success">

                                {{ entry.status }}
                            </span>

                            <span v-if="entry.status === 'average'" class="badge bg-warning-subtle text-warning">

                                {{ entry.status }}
                            </span>

                            <span v-if="entry.status === 'bad'" class="badge bg-danger-subtle text-danger">

                                {{ entry.status }}
                            </span>
                        </li>
                    </ul>

                    <div v-if="journalsError" class="mt-2 text-center">
                        <small class="text-danger">
                            Journal database error: {{ journalsError }}
                        </small>
                    </div>

                    <router-link to="/journal">
                        <button class="btn btn-outline-secondary btn-sm">
                            View Journal
                        </button>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</div>
</template>

<script>
export default {
    emits: ['toggle-auth'],
    data() {
        return {
            // API endpoints
            USERS_API: '/cos30043/s103071591/proDucktiv/resources/api_users.php',
            MOODS_API: '/cos30043/s103071591/proDucktiv/resources/api_moods.php',
            TASKS_API: '/cos30043/s103071591/proDucktiv/resources/api_tasks.php',
            JOURNAL_API: '/cos30043/s103071591/proDucktiv/resources/api_journals.php',

            currentUser: null,
            moodUp: 0,
            moodDown: 0,
            userMood: null,
            todaysMood: null,
            allTasks: [],
            recentJournal: [],

            // Error states for each API
            journalsError: '',
            moodsError: '',
            tasksError: '',
        }
    },
    computed: {
        // Filter all the tasks to to the ones due today
        todaysTasks() {
            const today = this.getLocalDateString()
            return this.allTasks.filter((task) => task.Date === today)
        },

        // Couunts how many tasks completed today through filtering to check complete status
        completedTasks() {
            return this.todaysTasks.filter((task) => this.isTaskComplete(task)).length
        },

        // Counts how many tasks are completed in the current week
        completedTasksThisWeek() {
            const now = new Date()
            const currentDay = now.getDay() // Sunday = 0
            const daysSinceMonday = (currentDay + 6) % 7
            const weekStart = new Date(now)

            weekStart.setHours(0, 0, 0, 0)
            weekStart.setDate(now.getDate() - daysSinceMonday)

            const weekStartKey = this.getLocalDateString(weekStart)
            const todayKey = this.getLocalDateString(now)

            return this.allTasks.filter((task) => {
                return (
                    this.isTaskComplete(task) &&
                    typeof task.Date === 'string' &&
                    task.Date >= weekStartKey &&
                    task.Date <= todayKey
                )
            }).length
        },

        // Counts active tasks by filtering not complete status independent of due date
        activeTasks() {
            return this.allTasks.filter((task) => !this.isTaskComplete(task)).length
        },

        // Counts overdue tasks
        overdueTasks() {
            // Filters to tasks that are not complete and past due date by checking hours until due is negative
            return this.allTasks.filter((task) => {
                const hoursLeft = this.getHoursUntilDue(task)

                return !this.isTaskComplete(task) && hoursLeft !== null && hoursLeft < 0
            }).length
        },

        // Gets upcoming deadlines by filtering tasks that are not complete and due in the future
        upcomingDeadlines() {
            return this.allTasks
                .filter((task) => {
                    const hoursLeft = this.getHoursUntilDue(task)

                    return !this.isTaskComplete(task) && hoursLeft !== null && hoursLeft >= 0
                })
                // Sorts by due date/time by parsing to getDueDateTime and comparing timestamps, then takes the 5 soonest deadlines
                .sort((a, b) => this.getDueDateTime(a).getTime() - this.getDueDateTime(b).getTime())
                .slice(0, 5)
        },

        // Calculates productivity score as percentage of today's tasks completed
        productivityScore() {
            // If no tasks are completed todday, returns 0 to display
            if (this.todaysTasks.length === 0) return 0

            // Gets the amount of completed tasks today by status and divides by all tasks due today to get percentage
            const done = this.todaysTasks.filter((task) => this.isTaskComplete(task)).length
            return Math.round((done / this.todaysTasks.length) * 100)
        },
    },
    mounted() {
        // If Task Editor selected from Task page, load user from local storage
        try {
            const stored = localStorage.getItem('currentUser')

            if (!stored) {
                return
            }

            const parsed = JSON.parse(stored)
            this.currentUser = {
                user_id: parsed.user_id,
                user_first_name: parsed.user_first_name,
            }
        } catch (error) {
            this.currentUser = null
            return
        }

        // Fetch all necessary data for the dashboard on mount
        this.fetchTasks()
        this.fetchMoods()
        this.fetchRecentJournals()
    },

    methods: {
        // Emits event to parent to toggle auth state and return to unauthenticated welcome view when logout clicked
        logout() {
            this.$emit('toggle-auth')
        },

        // Fetches the 5 most recent journal entries for current user
        fetchRecentJournals() {
            this.journalsError = ''

            if (!this.currentUser) {
                this.recentJournal = []
                return
            }

            return fetch(`${this.JOURNAL_API}?fld=user_id&key=${this.currentUser.user_id}`)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`)
                    }

                    return response.json()
                })
                .then((rows) => {
                    const journals = Array.isArray(rows) ? rows : []

                    // Take the 5 most recent journal entries and map them into display rows
                    this.recentJournal = journals.slice(0, 5).map((journal) => ({
                        title: journal.journal_name,
                        status: journal.journal_status,
                        date: this.formatJournalDate(journal.journal_date),
                    }))
                })
                .catch((error) => {
                    this.journalsError =
                        error instanceof Error ? error.message : 'Failed to load journals'
                    this.recentJournal = []
                })
        },

        // Fetches mood data for current user
        // Fetches mood data for all users for today
        fetchMoods() {
            this.moodsError = ''

            if (!this.currentUser) {
                this.moodUp = 0
                this.moodDown = 0
                this.userMood = null
                this.todaysMood = null
                return
            }

            return fetch(this.MOODS_API)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`)
                    }

                    return response.json()
                })
                .then((data) => {
                    const rows = Array.isArray(data) ? data : []
                    const today = this.getLocalDateString()
                    const todaysRows = rows.filter((row) => row.mood_date === today)
                    // Count how many users voted mood up vs down today by filtering to mood value and counting length of results
                    this.moodUp = todaysRows.filter((row) => row.mood_value === 'up').length
                    this.moodDown = todaysRows.filter((row) => row.mood_value === 'down').length

                    // Find the current user's mood entry for today, if it exists use this to display on UI
                    const myMoodToday = todaysRows.find(
                        (row) => Number(row.user_id) === Number(this.currentUser.user_id)
                    )

                    // If user voted today set their mood to value
                    if (myMoodToday) {
                        this.userMood = myMoodToday.mood_value
                        this.todaysMood = myMoodToday.mood_value
                        // If not, reset user mood and allow them to vote
                    } else {
                        this.userMood = null
                        this.todaysMood = null
                    }
                })
                .catch((error) => {
                    this.moodsError = error instanceof Error ? error.message : 'Failed to load moods'
                    this.moodUp = 0
                    this.moodDown = 0
                    this.userMood = null
                    this.todaysMood = null
                })
        },


        onSetMood(mood) {
            const today = this.getLocalDateString()

            if (!this.currentUser) {
                return
            }

            // Sends POST request to mood API for saving today mood for current user
            return fetch(this.MOODS_API, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: this.currentUser.user_id,
                    mood_date: today,
                    mood_value: mood,
                }),
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`)
                    }

                    // After successful mood saving update user mood
                    // Displays user mood in How was your day card and disables voting buttons
                    // Finally refetch all moods to show on dashboard
                    this.userMood = mood
                    this.todaysMood = mood
                    return this.fetchMoods()
                })
                .catch((error) => {
                    console.error(error)
                    alert('Could not save mood to database.')
                })
        },
        // Fetches tasks for current user
        fetchTasks() {
            this.tasksError = ''

            if (!this.currentUser) {
                this.allTasks = []
                return
            }

            return fetch(`${this.TASKS_API}?UserId=${this.currentUser.user_id}`)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`)
                    }

                    return response.json()
                })
                .then((data) => {
                    const rows = Array.isArray(data) ? data : []

                    this.allTasks = rows
                })
                .catch((error) => {
                    this.tasksError = error instanceof Error ? error.message : 'Failed to load tasks'
                    this.allTasks = []
                })
        },
        formatJournalDate(iso) {
            if (!iso) return ''

            return new Date(`${iso}T00:00:00`).toLocaleDateString('en-AU', {
                day: 'numeric',
                month: 'short',
            })
        },
        // Converts a Date object into a local YYYY-MM-DD string for comparison with task dates
        getLocalDateString(date = new Date()) {
            const year = date.getFullYear()
            const month = String(date.getMonth() + 1).padStart(2, '0')
            const day = String(date.getDate()).padStart(2, '0')

            return `${year}-${month}-${day}`
        },
        // Checks if task is complete based on field value, returns boolean
        isTaskComplete(task) {
            const completed = task?.Completed

            if (typeof completed === 'boolean') return completed
            if (typeof completed === 'number') return completed === 1
            if (typeof completed === 'string') {
                const normalized = completed.trim().toLowerCase()
                return normalized === '1' || normalized === 'true'
            }

            return false
        },
        // Parses task date and time into due object
        getDueDateTime(task) {
            if (!task.Date) return null

            const taskTime = task.Time || '23:59'
            const due = new Date(`${task.Date}T${taskTime}`)
            // Returns null if task does not have specified due date on creation
            if (Number.isNaN(due.getTime())) return null

            return due
        },
        // Returns hours until task is due, or null if no valid due date
        getHoursUntilDue(task) {
            const due = this.getDueDateTime(task)

            if (!due) return null

            // Calculate hours until due by finding the difference in milliseconds and converting to hours
            return Math.ceil((due.getTime() - Date.now()) / (1000 * 60 * 60))
        },
        // Task counts as overdue if not complete & past the due date/time
        isTaskOverdue(task) {
            const hoursLeft = this.getHoursUntilDue(task)

            return !this.isTaskComplete(task) && hoursLeft !== null && hoursLeft < 0
        },
    },
}
</script>
