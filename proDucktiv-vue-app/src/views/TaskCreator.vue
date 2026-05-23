<script setup>
// TaskCreator.vue
// purpose:
// - provide a create task form.
// - match the TaskEditor field structure:
//   Title, Type, Date, Time, Location, Description.
// - send the new task to api_tasks.php using POST.


import { ref } from 'vue'
import { useRouter } from 'vue-router'

// vue router returns the user to the task list after creating/cancelling.
const Router = useRouter()


// api setup
// api_tasks.php must run through a php server.

const TaskApiUrl =
  import.meta.env.VITE_TASK_API_URL || 'http://localhost/Pro-Duck-tiv/proDucktiv-vue-app/resources/api_tasks.php'


// form state
// variable names are capitalised to match TaskEditor and the task api response.
const Title = ref('')
const Type = ref('')
const Date = ref('')
const Time = ref('')
const Location = ref('')
const Description = ref('')

// feedback state for validation, api errors, and success messages.
const ErrorMessage = ref('')
const SuccessMessage = ref('')
const IsSubmitting = ref(false)

// helper: safely read json response from php
// if the api url is wrong, the browser often receives html instead of json.
// this helper makes that problem easier to understand.
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

// form validation
// only Title and Type are required.
// Date, Time, Location, and Description are optional but supported by the database.
function ValidateTaskForm() {
  if (!Title.value.trim()) {
    return 'Please enter a task title.'
  }

  if (!Type.value.trim()) {
    return 'Please select a task type.'
  }

  return ''
}

// database create: create new task
// sends the task data to api_tasks.php using POST.

async function CreateTask() {
  ErrorMessage.value = ''
  SuccessMessage.value = ''

  const ValidationError = ValidateTaskForm()

  if (ValidationError) {
    ErrorMessage.value = ValidationError
    return
  }

  const NewTask = {
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
    const Response = await fetch(TaskApiUrl, {
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

// navigation: cancel and return to task list
function CancelCreate() {
  Router.push('/task')
}
</script>

<template>
  <!-- main page wrapper: bootstrap light theme -->
  <main class="creator-page bg-light min-vh-100 py-4 py-lg-5">
    <div class="container">
      <!-- heading mirrors the TaskEditor structure -->
      <section class="mx-auto mb-4 creator-width">
        <h1 class="display-6 fw-bold text-dark mb-1">Create Task</h1>
        <p class="text-secondary mb-0">Add a new task to your planner</p>
      </section>

      <!-- bootstrap card with green success accent -->
      <form class="card shadow-sm border-0 border-top border-success border-4 mx-auto creator-width"
        @submit.prevent="CreateTask">
        <div class="card-body p-4 p-lg-5">
          <!-- feedback messages -->
          <div v-if="ErrorMessage" class="alert alert-danger" role="alert">
            {{ ErrorMessage }}
          </div>

          <div v-if="SuccessMessage" class="alert alert-success" role="status">
            {{ SuccessMessage }}
          </div>

          <!-- title field -->
          <div class="mb-3">
            <label for="Title" class="form-label fw-semibold">Title</label>
            <input id="Title" v-model="Title" type="text" class="form-control form-control-lg"
              placeholder="Enter task title" required />
          </div>

          <!-- type field -->
          <div class="mb-3">
            <label for="Type" class="form-label fw-semibold">Type</label>
            <select id="Type" v-model="Type" class="form-select form-select-lg" required>
              <option value="">Select task type</option>
              <option value="Study">Study</option>
              <option value="Meeting">Meeting</option>
              <option value="Work">Work</option>
              <option value="Personal">Personal</option>
              <option value="Assessment">Assessment</option>
            </select>
          </div>

          <!-- date and time fields -->
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

          <!-- location field -->
          <div class="mb-3">
            <label for="Location" class="form-label fw-semibold">Location</label>
            <input id="Location" v-model="Location" type="text" class="form-control form-control-lg"
              placeholder="Location" />
          </div>

          <!-- description field -->
          <div class="mb-4">
            <label for="Description" class="form-label fw-semibold">Description</label>
            <textarea id="Description" v-model="Description" class="form-control form-control-lg" rows="5"
              placeholder="Task description"></textarea>
          </div>

          <!-- form actions -->
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
