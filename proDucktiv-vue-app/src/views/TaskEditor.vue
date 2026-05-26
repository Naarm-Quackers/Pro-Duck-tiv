<template>
    <!-- Page container for the task editor view -->
    <div class="container py-4">
        <!-- Main card wrapper around all editor content -->
        <fieldset class="card p-2 mb-5 border-0 shadow-sm h-100 rounded bg-body">
            <div class="row justify-content-center">
                <div class="col-12 col-md-8 col-lg-6">
                    <!-- Page heading and supporting text -->
                    <div>
                        <h1 class="mb-1">Edit Task</h1>
                        <p v-if="!showSelectTaskMessage" class="text-secondary mb-0">Edit a task from your planner</p>
                    </div>
                    

                    <!-- Guidance shown when no task id is available in the route -->
                    <div v-if="showSelectTaskMessage" class="alert alert-info" role="alert">
                        Please choose a task first from the Task page, then click Edit to open it here.
                    </div>

                    <!-- API or validation error message -->
                    <div v-else-if="err" class="alert alert-danger" role="alert">
                        {{ err }}
                    </div>

                    <!-- Editable task form shown when task data is available -->
                    <form v-else @submit.prevent="saveTask">

                        <!-- Task title input -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input v-model="task.Title" type="text" id="title" class="form-control" placeholder="Task title" required />
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
                                <input v-model="task.Date" type="date" id="date" class="form-control" />
                            </div>
                            <div class="col">
                                <label for="time" class="form-label">Time</label>
                                <input v-model="task.Time" type="time" id="time" class="form-control" />
                            </div>
                        </div>

                        <!-- Location input -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input v-model="task.Location" type="text" id="location" class="form-control" placeholder="Location" />
                        </div>

                        <!-- Description text area -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea v-model="task.Description" id="description" class="form-control" rows="4" placeholder="Task description"></textarea>
                        </div>

                        <!-- Completion status toggle -->
                        <div class="mb-4 form-check">
                            <input
                                id="status"
                                v-model="task.Completed"
                                class="form-check-input"
                                type="checkbox"
                            />
                            <label for="status" class="form-check-label">Mark as complete</label>
                        </div>

                        <!-- Primary and secondary form actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-success" :disabled="isSaving">
                                {{ isSaving ? 'Saving...' : 'Save Changes' }}
                            </button>
                            <button type="button" class="btn btn-outline-warning" @click="$router.push('/task')">Cancel</button>
                        </div>

                    </form>

                    <!-- Quick navigation back to task list when no task is selected -->
                    <div v-if="showSelectTaskMessage" class="mt-3">
                        <button type="button" class="btn btn-outline-success" @click="$router.push('/task')">Go to Tasks</button>
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
            TASKS_API: '/resources/api_tasks.php',

            showSelectTaskMessage: false,
            isSaving: false,
            task: temporaryTask(),
            currentUser: null,
            
            // Error message 
            err: ''
        }
    },

    mounted() {
        // If Task Editor selected from navbar then return to Task page
        if (!this.$route.params.id) {
            this.showSelectTaskMessage = true
            return
        }

        // If Task Editor selected from Task page, load user from local storage
        try {
            const stored = localStorage.getItem('currentUser')

            if (!stored) {
                return
            }

            const parsed = JSON.parse(stored)
            this.currentUser = {
                user_id: parsed.user_id,
            }
        // If Task Editor is selected but no user is logged in, show error message instead of editing page
        } catch (error) {
            this.currentUser = null
            this.err = 'No logged-in user found. Please sign in before editing tasks.'
            return
        }

        this.fetchTask()
    },

    // Show a message prompting the user to select a task if the route parameter changes and no id is present, otherwise load the new task
    watch: {
        '$route.params.id'(newId) {
            if (!newId) {
                this.showSelectTaskMessage = true
                return
            }

            this.showSelectTaskMessage = false
            this.fetchTask()
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

        // Loads the selected task and maps API data into the form model
        fetchTask() {
            this.err = ''

            try {
                const userId = String(this.currentUser?.user_id)

                if (!userId.trim()) {
                    throw new Error('No logged-in user found. Please sign in before editing tasks.')
                }

                return fetch(`${this.TASKS_API}?UserId=${encodeURIComponent(userId)}&TaskId=${this.$route.params.id}`)
                    .then((res) => this.ReadJsonResponse(res))
                    .then((data) => {
                        // Merge the API row into a default object so the form always has every field it expects
                        this.task = { ...temporaryTask(), ...data[0] }
                    })
            } catch (error) {
                this.err = error instanceof Error ? error.message : 'Could not load task'
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
                this.err = 'No logged-in user found. Please sign in before editing tasks.'
                this.isSaving = false
                return
            }

            const taskId = this.task.Id
            // The task API identifies the record by both the signed-in user and the task id
            return fetch(`${this.TASKS_API}?UserId=${encodeURIComponent(userId)}&TaskId=${taskId}`, {
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