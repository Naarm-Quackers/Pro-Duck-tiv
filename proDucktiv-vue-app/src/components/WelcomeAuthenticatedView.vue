<template>
	<!-- Authenticated dashboard container. -->
	<div class="row">
		<!-- Header row: greeting and logout action. -->
		<div class="d-flex justify-content-between align-items-start mb-3">
			<div>
				<h4 class="mb-0 fw-semibold">Hello, {{ props.username }}!</h4>
			</div>

			<div class="d-flex gap-2">
				<button class="btn btn-outline-danger" @click="emit('toggle-auth')">
					Log Out
				</button>
			</div>
		</div>

		<!-- Top cards: productivity summary and mood voting. -->
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
									<div
										class="progress-bar bg-success"
										:style="{ width: productivityScore + '%' }"
									></div>
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
							<button
								class="btn btn-sm"
								:class="userMood === 'up' ? 'btn-success' : 'btn-outline-success'"
								:disabled="!!todaysMood"
								@click="onSetMood('up')"
							>
								<i class="bi bi-hand-thumbs-up"></i>
								Good
							</button>

							<button
								class="btn btn-sm"
								:class="userMood === 'down' ? 'btn-danger' : 'btn-outline-danger'"
								:disabled="!!todaysMood"
								@click="onSetMood('down')"
							>
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

		<!-- Middle cards: task counters and aggregate mood stats. -->
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
							{{ completedTasks }}
						</div>

						<small class="text-muted">
							{{ completedTasks }} this week
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

		<!-- Bottom cards: today's tasks, deadlines, and recent journals. -->
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
							<li
								v-for="task in todaysTasks"
								:key="task.id"
								class="list-group-item"
							>
								{{ task.title }}

								<span
									class="badge ms-2"
									:class="
										isTaskComplete(task)
											? 'bg-success'
											: isTaskOverdue(task)
												? 'bg-danger'
												: 'bg-secondary'
									"
								>
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
				<!-- Upcoming deadlines list card. -->
				<div class="card border-0 shadow-sm">
					<div class="card-body">
						<h6>
							<i class="bi bi-alarm text-success me-2"></i>
							Upcoming Deadlines
						</h6>

						<p
							v-if="upcomingDeadlines.length === 0"
							class="text-muted mb-0"
							style="font-size:0.85rem;"
						>
							No upcoming deadlines
						</p>

						<ul v-else class="list-group list-group-flush">
							<li
								v-for="d in upcomingDeadlines"
								:key="d.id"
								class="list-group-item px-0"
							>
								{{ d.title }} {{ d.date }} at {{ d.time }}
							</li>
						</ul>
					</div>
				</div>

				<!-- Recent journals list card with mood status badges. -->
				<div class="card border-0 shadow-sm">
					<div class="card-body">
						<h6>
							<i class="bi bi-journal-text me-2"></i>
							Recent Journal
						</h6>

						<ul class="list-group list-group-flush">
							<li
								v-for="entry in recentJournal"
								:key="entry.title"
								class="list-group-item px-0 d-flex justify-content-between align-items-center"
							>
								<div>
									<div class="fw-semibold" style="font-size:0.9rem;">
										{{ entry.title }}
										
										<small class="text-muted">
											{{ entry.date }}
										</small>
									</div>
								</div>

								<span 
									v-if="entry.status === 'great'"
									class="badge bg-success-subtle text-success">
									
									{{ entry.status }}
								</span>

								<span 
									v-if="entry.status === 'average'"
									class="badge bg-warning-subtle text-warning">
									
									{{ entry.status }}
								</span>

								<span 
									v-if="entry.status === 'bad'"
									class="badge bg-danger-subtle text-danger">
									
									{{ entry.status }}
								</span>
							</li>
						</ul>

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

<script setup>
import { ref, computed, onMounted } from 'vue'

// Component contracts: parent-triggered logout and signed-in user context.
const emit = defineEmits(['toggle-auth'])
const props = defineProps({
	username: {
		type: String,
		default: 'User',
	},
	userId: {
		type: [Number, String],
		default: null,
	},
})


// API endpoint definitions.
const MOODS_API = '/resources/api_moods.php'
const TASKS_API = '/resources/api_tasks.php'
const JOURNAL_API = '/resources/api_journals.php'


// -----------------------------
// Journal fetching and formatting
// -----------------------------

const recentJournal = ref([])
const journalsError = ref('')

function formatJournalDate(iso) {
    if (!iso) return ''
    return new Date(`${iso}T00:00:00`).toLocaleDateString('en-AU', {
        day: 'numeric',
        month: 'short',
    })
}

async function fetchRecentJournals() {
    journalsError.value = ''

    try {
        if (!currentUserId.value) {
            recentJournal.value = []
            return
        }

	const response = await fetch(`${JOURNAL_API}?fld=user_id&key=${currentUserId.value}`)
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`)
        }

        const rows = await response.json()
        const journals = Array.isArray(rows) ? rows : []

		// Take the 5 most recent journal entries and map them to shape to display
        recentJournal.value = journals.slice(0, 5).map((journal) => ({
            title: journal.journal_name,
            status: journal.journal_status || 'none',
            date: formatJournalDate(journal.journal_date),
        }))
    } catch (error) {
        journalsError.value =
            error instanceof Error ? error.message : 'Failed to load journals'
        recentJournal.value = []
    }
}

// -----------------------------
// Mood fetching and voting
// -----------------------------

// Signed-in user ID derived from props.
const currentUserId = computed(() => {
	const parsed = Number(props.userId)
	return Number.isFinite(parsed) ? parsed : null
})

// Mood UI state.
const moodUp = ref(0)
const moodDown = ref(0)
const userMood = ref(null)
const todaysMood = ref(null)
const moodsError = ref('')

async function fetchMoods() {
	moodsError.value = ''

	try {
		const response = await fetch(MOODS_API)

		if (!response.ok) {
			throw new Error(`HTTP ${response.status}`)
		}

		const data = await response.json()
		const rows = Array.isArray(data) ? data : []

		const today = getLocalDateString()

		const todaysRows = rows.filter((row) => row.mood_date === today)

		moodUp.value = todaysRows.filter((row) => row.mood_value === 'up').length
		moodDown.value = todaysRows.filter((row) => row.mood_value === 'down').length

		const myMoodToday = todaysRows.find(
			(row) => Number(row.user_id) === Number(currentUserId.value)
		)

		if (myMoodToday) {
			userMood.value = myMoodToday.mood_value
			todaysMood.value = myMoodToday.mood_value
		} else {
			userMood.value = null
			todaysMood.value = null
		}
	} catch (error) {
		moodsError.value = error instanceof Error ? error.message : 'Failed to load moods'
		moodUp.value = 0
		moodDown.value = 0
		userMood.value = null
		todaysMood.value = null
	}
}

// Posting mood data to the API when the user votes.
const onSetMood = async (mood) => {
	const today = getLocalDateString()
	try {
		const response = await fetch(MOODS_API, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify({
				user_id: currentUserId.value,
				mood_date: today,
				mood_value: mood,
			}),
		})

		if (!response.ok) {
			throw new Error(`HTTP ${response.status}`)
		}

		userMood.value = mood
		todaysMood.value = mood

		await fetchMoods()
	} catch (error) {
		console.error(error)
		alert('Could not save mood to database.')
	}
}

// -----------------------------
// Task fetching and calculations
// -----------------------------

// Task UI state.
const allTasks = ref([])
const tasksError = ref('')

function normalizeTask(task) {	
	return {
		...task,
		id: task.Id,
		title: task.Title,
		type: task.Type,
		date: task.Date,
		time: task.Time,
		location: task.Location,
		description: task.Description,
		completed: task.Completed === true,
		status: task.Completed === true ? 'Complete' : 'Active',
	}
}

async function fetchTasks() {
	tasksError.value = ''

	try {
		if (!currentUserId.value) {
			allTasks.value = []
			return
		}

		const response = await fetch(`${TASKS_API}?UserId=${currentUserId.value}`)

		if (!response.ok) {
			throw new Error(`HTTP ${response.status}`)
		}

		const data = await response.json()
		const rows = Array.isArray(data) ? data : []

		allTasks.value = rows.map((task) => normalizeTask(task))
	} catch (error) {
		tasksError.value = error instanceof Error ? error.message : 'Failed to load tasks'
		allTasks.value = []
	}
}

// Shared date formatter for API-compatible local date strings.
function getLocalDateString(date = new Date()) {
	const year = date.getFullYear()
	const month = String(date.getMonth() + 1).padStart(2, '0')
	const day = String(date.getDate()).padStart(2, '0')
	return `${year}-${month}-${day}`
}

function isTaskComplete(task) {
	const isCompleted =
		task.Completed === true
	return isCompleted
}

function getDueDateTime(task) {
	if (!task.date) return null

	const taskTime = task.time || '23:59'
	const due = new Date(`${task.date}T${taskTime}`)

	if (Number.isNaN(due.getTime())) return null

	return due
}

function getHoursUntilDue(task) {
	const due = getDueDateTime(task)

	if (!due) return null

	return Math.ceil((due.getTime() - Date.now()) / (1000 * 60 * 60))
}

function isTaskOverdue(task) {
	const hoursLeft = getHoursUntilDue(task)

	return !isTaskComplete(task) && hoursLeft !== null && hoursLeft < 0
}

// Task dashboard metrics.
const todaysTasks = computed(() => {
	const today = getLocalDateString()
	
	return allTasks.value.filter((t) => t.date === today)
})

const completedTasks = computed(() => {
	return todaysTasks.value.filter((task) => isTaskComplete(task)).length
})

const activeTasks = computed(() => {
	return allTasks.value.filter((task) => !isTaskComplete(task)).length
})

const overdueTasks = computed(() => {
	return allTasks.value.filter((task) => {
		const hoursLeft = getHoursUntilDue(task)

		return !isTaskComplete(task) && hoursLeft !== null && hoursLeft < 0
	}).length
})



const upcomingDeadlines = computed(() => {
	return allTasks.value
		.filter((task) => {
			const hoursLeft = getHoursUntilDue(task)

			return !isTaskComplete(task) && hoursLeft !== null && hoursLeft >= 0
		})
		.sort((a, b) => getDueDateTime(a).getTime() - getDueDateTime(b).getTime())
		.slice(0, 5)
})

// Productivity score shown in the hero card.
const productivityScore = computed(() => {
	if (todaysTasks.value.length === 0) return 0

	const done = todaysTasks.value.filter((task) => isTaskComplete(task)).length
	return Math.round((done / todaysTasks.value.length) * 100)
})

// Initial data load when the authenticated welcome view mounts.
onMounted(() => {
	fetchTasks()
	fetchMoods()
	fetchRecentJournals()
})
</script>