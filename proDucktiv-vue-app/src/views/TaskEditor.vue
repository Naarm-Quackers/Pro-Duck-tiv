<template><!-- Page container for the task editor view -->
<div class="container py-4">
    <!-- Main card wrapper around all editor content -->
    <fieldset class="card p-2 mb-5 border-0 shadow-sm h-100 rounded bg-body">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <!-- Page heading and supporting text -->
                <div>
                    <h1 class="mb-1">Edit Task</h1>
                    <p v-if="!showSelectTaskMessage" class="text-secondary mb-0">Edit a task from your planner</p>
                    <p v-else-if="showSelectTaskMessage" class="text-secondary mb-0">Choose a task from your planner</p>
                </div>

                <!-- Task selector shown when no task is selected to edit or when user navigates to the editor without a task id in the route -->
                <div class="card border-0 bg-light-subtle p-3 mt-3 mb-3">
                    <label for="taskSelector" class="form-label mb-1">Select Task</label>
                    <div class="d-flex gap-2">
                        <select id="taskSelector" v-model="selectedTaskId" class="form-select">
                            <option value="">Choose a task</option>
                            <option v-for="item in availableTasks" :key="item.Id" :value="String(item.Id)">
                                {{ item.Title }}
                            </option>
                        </select>
                        <button type="button" class="btn btn-outline-success" :disabled="!selectedTaskId" @click="openSelectedTask">
                            Load
                        </button>
                    </div>
                </div>

                <!-- API or validation error message -->
                <div v-if="err" class="alert alert-danger" role="alert">
                    {{ err }}
                </div>

                <!-- Editable task form shown when task data is available -->
                <form v-if="!showSelectTaskMessage && !err" @submit.prevent="saveTask">

                    <!-- Task title input -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input v-model="task.Title" type="text" id="title" class="form-control" placeholder="Task title"
                            required />
                    </div>

                    <!-- Task type selector -->
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

                    <!-- Date and time scheduling fields -->
                    <div class="row mb-3">
                        <div class="col">
                            <label for="date" class="form-label">Date</label>
                            <input v-model="task.Date" type="date" id="date" class="form-control" required/>
                        </div>
                        <div class="col">
                            <label for="time" class="form-label">Time</label>
                            <input v-model="task.Time" type="time" id="time" class="form-control" required/>
                        </div>
                    </div>

                    <!-- Location input -->
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input v-model="task.Location" type="text" id="location" class="form-control"
                            placeholder="Location" required/>
                    </div>

                    <!-- Description text area -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea v-model="task.Description" id="description" class="form-control" rows="4"
                            placeholder="Task description" required></textarea>
                    </div>

                    <!-- Completion status toggle -->
                    <div class="mb-4 form-check">
                        <input id="status" v-model="task.Completed" class="form-check-input" type="checkbox" />
                        <label for="status" class="form-check-label">Mark as complete</label>
                    </div>

                    <!-- Primary and secondary form actions -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-outline-success" :disabled="isSaving">
                            {{ isSaving ? 'Saving...' : 'Save Changes' }}
                        </button>
                        <button type="button" class="btn btn-outline-warning"
                            @click="$router.push('/task')">Cancel
                        </button>
                    </div>

                </form>

                <!-- Quick navigation back to task list when no task is selected -->
                <div v-if="showSelectTaskMessage" class="mt-3">
                    <button type="button" class="btn btn-outline-success" @click="$router.push('/task')">Go to
                        Tasks</button>
                </div>
            </div>
        </div>
    </fieldset>
</div>
</template>

<script>
// Creates default task object to merge with API data so form always has all expected fields
const temporaryTask = () => ({
    Id: '',
    Title: '',
    Type: '',
    Date: '',
    Time: '',
    Location: '',
    Description: '',
    Completed: false,
    UserId: ''
})

export default {
    data() {
        return {
            // API task enpoint
            TASKS_API: '/cos30043/s103071591/proDucktiv/resources/api_tasks.php',
            
            showSelectTaskMessage: false,
            isSaving: false,
            task: temporaryTask(),
            currentUser: null,
            selectedTaskId: '',
            availableTasks: [],

            // Error message
            err: ''
        }
    },

    mounted() {
        // Load user from local storage to get user id for API calls
        try {
            const stored = localStorage.getItem('currentUser')

            // If no user in local storage, show error message to sign in
            if (!stored) {
                this.err = 'No logged-in user found. Please sign in before editing tasks.'
                return
            }

            const parsed = JSON.parse(stored)
            this.currentUser = {
                user_id: parsed.user_id,
            }
            // If Task Editor is selected but no user Id, put current user as default null
        } catch (error) {
            this.currentUser = null
            return
        }

        
        // Load all tasks for user
        this.fetchTasks().then(() => {
            // Take routed id
            const routeTaskId = this.$route.params.id

            // If no task id is provided in the route, show the select task message
            if (!routeTaskId) {
                this.showSelectTaskMessage = true
                return
            }
            
            // Load the task corresponding to the routed id
            this.selectedTaskId = String(routeTaskId)
            this.showSelectTaskMessage = false
            this.fetchRoutedTask(routeTaskId)
        })
    },

    // Show a message prompting the user to select a task if the route parameter changes and no id is present, otherwise load the new task
    watch: {
        // If route changes but no task Id provided show select task message
        '$route.params.id'(newId) {
            if (!newId) {
                this.selectedTaskId = ''
                this.task = temporaryTask()
                this.showSelectTaskMessage = true
                return
            }
            
            // If new route has task id, load the task accordingly
            this.selectedTaskId = String(newId)
            this.showSelectTaskMessage = false
            this.fetchRoutedTask(newId)
        }
    },

    methods: {
        // Parses JSON safely and throws a clear message for non-JSON API responses
        ReadJsonResponse(res) {
            return res.text().then((ResponseText) => {
                try {
                    return JSON.parse(ResponseText)
                } catch (error) {
                    throw new Error(
                        'The task API did not return JSON. Check that api_tasks.php is running and TaskApiUrl is correct.'
                    )
                }
            })
        },

        // Pushes selected id into the route so URL and editor stay in sync
        openSelectedTask() {
            if (!this.selectedTaskId) {
                return
            }

            this.$router.push(`/task-editor/${this.selectedTaskId}`)
        },

        // Loads all tasks for selector so editor can be used without route-provided ids
        fetchTasks() {
            this.err = ''

            try {
                const userId = String(this.currentUser?.user_id)

                return fetch(`${this.TASKS_API}?UserId=${encodeURIComponent(userId)}`)
                    .then((res) => this.ReadJsonResponse(res))
                    .then((data) => {
                        if (!Array.isArray(data)) {
                            throw new Error(data?.error || 'Could not load tasks list')
                        }

                        this.availableTasks = data
                    })
            } catch (error) {
                this.err = error instanceof Error ? error.message : 'Could not load tasks list'
                return Promise.resolve()
            }
        },

        // Loads the selected task and maps API data into the form model
        fetchRoutedTask(taskId = this.$route.params.id) {
            this.err = ''

            try {
                const userId = String(this.currentUser?.user_id)
                const selectedId = String(taskId).trim()

                
                if (!selectedId) {
                    this.showSelectTaskMessage = true
                    return Promise.resolve()
                }

                return fetch(`${this.TASKS_API}?UserId=${encodeURIComponent(userId)}&TaskId=${encodeURIComponent(selectedId)}`)
                    .then((res) => this.ReadJsonResponse(res))
                    .then((data) => {
                        if (!Array.isArray(data)) {
                            throw new Error('Task not found. Please select another task ID.')
                        }

                        // Merge the API row into a default object so the form always has every field it expects
                        this.task = { ...temporaryTask(), ...data[0] }
                        this.showSelectTaskMessage = false
                    })
            } catch (error) {
                this.err = error instanceof Error ? error.message : 'Could not load task'
                return Promise.resolve()
            }
        },

        // Validates and submits task updates to the API
        saveTask() {
            this.err = ''

            if (!this.task.Title || !this.task.Type) {
                this.err = 'Title and type are required'
                return
            }

            // Show saving state while API request sent to save task
            this.isSaving = true

            // Gets user id from local storage to use for API calling
            const userId = String(this.currentUser?.user_id)
            if (!userId.trim()) {
                this.isSaving = false
                return
            }

            const taskId = this.task.Id
            const selectedId = String(taskId).trim()
            
            if (!selectedId) {
                this.err = 'Please select a valid task to update'
                this.isSaving = false
                return
            }
            // The task API identifies the record by both the signed-in user and the task id
            return fetch(`${this.TASKS_API}?UserId=${encodeURIComponent(userId)}&TaskId=${encodeURIComponent(selectedId)}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ...this.task,
                    UserId: userId,
                    Completed: this.task.Completed === true
                })
            })
                .then((res) => this.ReadJsonResponse(res))
                .then((data) => {
                    if (!data.success) {
                        throw new Error(data.error)
                    }

                    this.$router.push('/task')
                })
                .catch((error) => {
                    this.err = error instanceof Error ? error.message : 'Could not update task'
                })
                .finally(() => {
                    this.isSaving = false
                })
        }
    }
}
</script>
