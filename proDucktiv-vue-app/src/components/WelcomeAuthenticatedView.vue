<script setup>
import { ref, computed, onMounted } from 'vue'

const emit = defineEmits(['toggle-auth'])

// Dashboard placeholder data
const username = ref('User')
const allTasks = ref([])

function getLocalDateString(date = new Date()) {
	const year = date.getFullYear()
	const month = String(date.getMonth() + 1).padStart(2, '0')
	const day = String(date.getDate()).padStart(2, '0')
	return `${year}-${month}-${day}`
}

// Recent journal entries
const recentJournal = ref([
	{ title: 'Productive Monday', mood: 'great', date: 'Apr 27' },
	{ title: 'Weekend Reflection', mood: 'good', date: 'Apr 26' },
])

// -----------------------------
// Mood database setup
// -----------------------------

const MOODS_API = '/resources/api_moods.php'

// Temporary testing user.
// Replace this later with the logged-in user's real user_id.
const currentUserId = ref(2)

// Temporary testind date.
const testMoodDate = ref('2026-05-24')

const moodUp = ref(0)
const moodDown = ref(0)
const userMood = ref(null)
const todaysMood = ref(null)
const moodRows = ref([])
const moodsLoading = ref(false)
const moodsError = ref('')

const moodColumns = computed(() => {
	if (!moodRows.value.length) return []
	return Object.keys(moodRows.value[0])
})

async function fetchMoods() {
	moodsLoading.value = true
	moodsError.value = ''

	try {
		const response = await fetch(MOODS_API)

		if (!response.ok) {
			throw new Error(`HTTP ${response.status}`)
		}

		const data = await response.json()
		const rows = Array.isArray(data) ? data : []

		moodRows.value = rows

		//const today = getLocalDateString()
		const today = testMoodDate.value

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
		moodRows.value = []
		moodUp.value = 0
		moodDown.value = 0
		userMood.value = null
		todaysMood.value = null
	} finally {
		moodsLoading.value = false
	}
}

// Mood voting logic using database
const onSetMood = async (mood) => {
	//const today = getLocalDateString()
	const today = testMoodDate.value

	if (todaysMood.value) {
		alert('You can only vote once per day.')
		return
	}

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
// Task calculations
// -----------------------------

const todaysTasks = computed(() => {
	const today = getLocalDateString()
	
	return allTasks.value.filter((t) => t.date === today)
})

const completedTasks = computed(() => {
	return todaysTasks.value.filter((t) => t.done).length
})

const activeTasks = computed(() => {
	return allTasks.value.filter((t) => !t.done).length
})

const overdueTasks = computed(() => {
	const today = getLocalDateString()
	return allTasks.value.filter((t) => !t.done && t.date && t.date < today).length
})

const upcomingDeadlines = computed(() => {
	const today = getLocalDateString()

	return allTasks.value
		.filter((t) => !t.done && t.date && t.date >= today)
		.sort((a, b) => a.date.localeCompare(b.date))
		.slice(0, 5)
})

// Productivity score calculation
const productivityScore = computed(() => {
	if (todaysTasks.value.length === 0) return 0

	const done = todaysTasks.value.filter((t) => t.done).length
	return Math.round((done / todaysTasks.value.length) * 100)
})

onMounted(() => {
	const storedTasks = JSON.parse(localStorage.getItem('tasks') || 'null')

	// If you have a tasksData variable elsewhere, keep using it.
	// Otherwise, this safely falls back to an empty array.
	const sourceTasks =
		Array.isArray(storedTasks) && storedTasks.length > 0
			? storedTasks
			: typeof tasksData !== 'undefined'
				? tasksData
				: []

	allTasks.value = sourceTasks.map((task) => ({
		...task,
		done: task.done ?? task.completed ?? false,
	}))

	fetchMoods()
})
</script>

<template>
	<div class="row">
		<div class="d-flex justify-content-between align-items-start mb-3">
			<div>
				<h4 class="mb-0 fw-semibold">Hello, {{ username }}!</h4>
			</div>

			<div class="d-flex gap-2">
				<button class="btn btn-outline-danger" @click="emit('toggle-auth')">
					Log Out
				</button>
			</div>
		</div>

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
										{{ todaysTasks.filter(t => t.done).length }} completed today
									</small>

									<small class="text-muted">
										{{ todaysTasks.filter(t => !t.done).length }} remaining
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

		<div class="row g-3 mb-3">
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
							Loaded from pdt_moods table
						</small>
					</div>
				</div>
			</div>
		</div>

		<div class="card border-0 shadow-sm mb-3">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<h6 class="mb-0">
						<i class="bi bi-table me-2"></i>
						PDT moods testing
					</h6>

					<button class="btn btn-outline-secondary btn-sm" @click="fetchMoods">
						Refresh
					</button>
				</div>

				<p v-if="moodsLoading" class="text-muted mb-0">
					Loading moods...
				</p>

				<p v-else-if="moodsError" class="text-danger mb-0">
					Failed to load moods: {{ moodsError }}
				</p>

				<p v-else-if="moodRows.length === 0" class="text-muted mb-0">
					No mood rows returned.
				</p>

				<div v-else class="table-responsive">
					<table class="table table-sm table-striped align-middle mb-0">
						<thead>
							<tr>
								<th v-for="column in moodColumns" :key="column">
									{{ column }}
								</th>
							</tr>
						</thead>

						<tbody>
							<tr v-for="(row, rowIndex) in moodRows" :key="rowIndex">
								<td
									v-for="column in moodColumns"
									:key="`${rowIndex}-${column}`"
								>
									{{ row[column] }}
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

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
									:class="(task.done ?? task.completed) ? 'bg-success' : 'bg-secondary'"
								>
									{{ (task.done ?? task.completed) ? 'Done' : 'Active' }}
								</span>

								<span
									class="badge ms-2"
									:class="(task.priority || '').toLowerCase() === 'high'
										? 'bg-danger'
										: (task.priority || '').toLowerCase() === 'medium'
											? 'bg-warning text-dark'
											: (task.priority || '').toLowerCase() === 'low'
												? 'bg-info text-dark'
												: 'bg-secondary'"
								>
									{{ task.priority ? `${task.priority} Priority` : 'No Priority' }}
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
									</div>

									<small class="text-muted">
										{{ entry.date }}
									</small>
								</div>

								<span class="badge bg-success-subtle text-success">
									{{ entry.mood }}
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