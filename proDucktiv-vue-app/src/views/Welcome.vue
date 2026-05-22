<script setup>
import { ref, computed } from 'vue'
//testing authentication logic, will replace with actual logic later
const isAuthenticated = ref(false) 
const toggleAuth = () => {
    isAuthenticated.value = !isAuthenticated.value
}

// Stats (replace with API data later)
const productivityIncrease = ref('85%')
const activeUsers = ref('1K+')
const tasksCompleted = ref('1M+')

// Dashboard placeholder data (replace with API/store data later)
const username = ref('Adam')

const productivityScore = computed(() => {
    if (todaysTasks.value.length === 0) return 0
    const done = todaysTasks.value.filter(t => t.done).length
    return Math.round((done / todaysTasks.value.length) * 100)
})
const streak = ref(5)
const completedTasks = ref(2)
const pendingTasks = ref(6)
const moodUp = ref(0)
const moodDown = ref(0)
const userMood = ref(null) // 'up', 'down', or null

// Mood voting logic (simplified for demo purposes, replace with API calls later)
const setMood = (mood) => {
    if (userMood.value === mood) {
        // toggle off
        userMood.value === 'up' ? moodUp.value-- : moodDown.value--
        userMood.value = null
    } else {
        // undo previous vote
        if (userMood.value === 'up') moodUp.value--
        if (userMood.value === 'down') moodDown.value--
        // apply new vote
        userMood.value = mood
        mood === 'up' ? moodUp.value++ : moodDown.value++
    }
}
const todaysTasks = ref([
    { id: 1, title: 'Morning standup', done: true },
    { id: 2, title: 'Review pull requests', done: true },
    { id: 3, title: 'Write unit tests', done: false },
    { id: 4, title: 'Update documentation', done: false },
])
const upcomingDeadlines = ref([])

// Recent journal entries (replace with API data later)
const recentJournal = ref([
    { title: 'Productive Monday', mood: 'great', date: 'Apr 27' },
    { title: 'Weekend Reflection', mood: 'good', date: 'Apr 26' },
])

// Placeholder images from picsum.photos (replace with real images later)
const taskManagement = 'https://picsum.photos/seed/taskmanagement/1200/600'
const calendarView  = 'https://picsum.photos/seed/calendar/1200/600'
const dailyJournal = 'https://picsum.photos/seed/dailyjournal/1200/600'
const progressDashboard = 'https://picsum.photos/seed/progressdashboard/1200/600'
</script>


<template>
    <div class='container align-items-center text-center mt-5 <!--bg-dark text-white-->'>
        
        <!-- Conditional rendering based on authentication status -->
        
        <div v-if="!isAuthenticated" class='row'>
            <div class='col-12'>
                <h1>Welcome to Productiv
                    <span class="badge bg-secondary">Manage Your Tasks, Manage Your Day</span>
                </h1>
                <h2></h2>
                <p class="lead">The all-in-one productivity app to help you stay organized and focused.</p>
            </div>

            <!-- This section to be used for testing -->
            <div class="row">
                <div class="col d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-outline-secondary" @click="toggleAuth">Sign In</button>
                    <button type="button" class="btn btn-outline-secondary" @click="toggleAuth">Learn More</button>
                </div>
            </div>

            <!-- This section to be used for real linking -->

            <!-- 
            <div class='col-12'>
                <router-link to="/sign-in">
                    <button type="button" class="btn btn-success" @click="toggleAuth">Sign In</button>
                </router-link>

                <router-link to="/registration">
                    <button type="button" class="btn btn-success">Register</button>
                </router-link>
            </div>
             -->
            
            <hr class="featurette-divider m-4">

            <!-- Carousel showing the things that you can do on the website -->

            <div class='col-12'>
                <h2>Everything you need</h2>
                <p class="lead">Features designed to keep you productive and organized.</p>
            </div>
          
            <div id="myCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-indicators">
                    <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                    <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                    <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
                </div>

                <div class="carousel-inner rounded overflow-hidden">
                    <div class="carousel-item active">
                        <img :src="taskManagement" class="d-block w-100" alt="Task Management" style="max-height: 600px; object-fit: cover;">
                        <div class="carousel-caption text-bg-light rounded">
                            <h5>Task Management</h5>
                            <p>Organize and prioritize your tasks effectively.</p>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <img :src="calendarView" class="d-block w-100" alt="Calendar View" style="max-height: 600px; object-fit: cover;">
                        <div class="carousel-caption text-bg-light rounded">
                            <h5>Calendar View</h5>
                            <p>Visualize your schedule and plan your day effectively.</p>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <img :src="dailyJournal" class="d-block w-100" alt="Daily Journal" style="max-height: 600px; object-fit: cover;">
                        <div class="carousel-caption text-bg-light rounded">
                            <h5>Daily Journal</h5>
                            <p>Reflect on your day and track your progress.</p>
                        </div>
                    </div>

                    <div class="carousel-item">
                        <img :src="progressDashboard" class="d-block w-100" alt="Progress Dashboard" style="max-height: 600px; object-fit: cover;">
                        <div class="carousel-caption text-bg-light rounded">
                            <h5>Progress Dashboard</h5>
                            <p>Track your progress and achievements.</p>
                        </div>
                    </div>
                </div>

                <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>

            <hr class="featurette-divider m-4">

            <!-- Section about data statistics -->
            <div class='col-12'>
                <h2>Tried and Tested</h2>
                <p class="lead">Features designed to keep you productive and organized.</p>
            </div>

            <div class="row text-center g-4">
                <div class="col-12 col-md-4">
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
                        <i class="bi bi-lightning-charge-fill text-success fs-3"></i>
                        <span class="fw-bold fs-1">{{ productivityIncrease }}</span>
                    </div>
                    <p class="text-secondary mb-0">Increase in productivity</p>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
                        <i class="bi bi-people-fill text-success fs-3"></i>
                        <span class="fw-bold fs-1">{{ activeUsers }}</span>
                    </div>
                    <p class="text-secondary mb-0">Active users</p>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex justify-content-center align-items-center gap-2 mb-1">
                        <i class="bi bi-check2-square text-success fs-3"></i>
                        <span class="fw-bold fs-1">{{ tasksCompleted }}</span>
                    </div>
                    <p class="text-secondary mb-0">Tasks completed</p>
                </div>
            </div>
         
            
        </div>

        <div v-if="isAuthenticated" class="row">

            <!-- Dashboard Header -->
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="mb-0 fw-semibold">Good evening, {{ username }}!
                    </h4>
                </div>

                <div class="d-flex gap-2">

                    <router-link to="/tasks">
                        <button class="btn btn-outline-secondary btn-sm">View Tasks</button>
                    </router-link>

                     <router-link to="/create-task">
                        <button class="btn btn-outline-secondary btn-sm">Create Task</button>
                    </router-link>
                     <router-link to="/journal">
                        <button class="btn btn-outline-secondary btn-sm">View Journal</button>
                    </router-link>

                    <router-link to="/account">
                        <button class="btn btn-outline-secondary btn-sm">View Account</button>
                    </router-link>

                    <button class="btn btn-outline-danger btn-sm" @click="toggleAuth">Log Out</button>
                </div>
            </div>

            <!-- Productivity + Mood row -->
            <div class="row g-3 mb-3">
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted">Today's Productivity</small>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <div class="flex-grow-1">
                                    <span class="display-6 fw-bold text-success">{{ productivityScore }}%</span>
                                    <div><small class="text-muted">Keep going!</small></div>
                                    <div class="progress mt-2" style="height:6px;">
                                        <div class="progress-bar bg-success" :style="{ width: productivityScore + '%' }"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">{{ todaysTasks.filter(t => t.done).length }} completed today</small>
                                        <small class="text-muted">{{ todaysTasks.filter(t => !t.done).length }} remaining</small>
                                    </div>
                                </div>
                                <i class="bi bi-lightning-charge-fill text-success ms-2" style="font-size:2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <small class="text-muted fw-semibold">How was your day?</small>
                            <p class="text-muted mb-3" style="font-size:0.85rem;">Let us know how you're feeling</p>
                            <div class="d-flex gap-3">
                                <button
                                    class="btn btn-sm"
                                    :class="userMood === 'up' ? 'btn-success' : 'btn-outline-success'"
                                    @click="setMood('up')">
                                    <i class="bi bi-hand-thumbs-up"></i> {{ moodUp }}
                                </button>
                                <button
                                    class="btn btn-sm"
                                    :class="userMood === 'down' ? 'btn-danger' : 'btn-outline-danger'"
                                    @click="setMood('down')">
                                    <i class="bi bi-hand-thumbs-down"></i> {{ moodDown }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stat cards -->
            <div class="row g-3 mb-3">
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Completed Tasks</small>
                                <i class="bi bi-check-circle-fill text-success"></i>
                            </div>
                            <div class="fs-2 fw-bold mt-2">{{ completedTasks }}</div>
                            <small class="text-muted">0 this week</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Pending Tasks</small>
                                <i class="bi bi-clock-fill text-warning"></i>
                            </div>
                            <div class="fs-2 fw-bold mt-2">{{ pendingTasks }}</div>
                            <small class="text-muted">4 high priority</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">Users mood for today</small>
                                <i class="bi bi-emoji-smile text-info"></i>
                            </div>
                            <div class="mt-2">
                                <span class="text-success me-2"><i class="bi bi-hand-thumbs-up"></i> {{ moodUp }}</span>
                                <span class="text-danger"><i class="bi bi-hand-thumbs-down"></i> {{ moodDown }}</span>
                            </div>
                            <small class="text-muted">Click to see details</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content: Today's Tasks + Sidebar -->
            <div class="row g-3">
                <!-- Today's Tasks -->
                <div class="col-12 col-md-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="bi bi-calendar3 me-2 text-success"></i>Today's Tasks</h6>
                                <small class="text-muted">{{ todaysTasks.filter(t => t.done).length }}/{{ todaysTasks.length }}</small>
                            </div>
                            <div v-if="todaysTasks.length === 0" class="text-center text-muted py-4">
                                <p>You're all clear!</p>
                                <button class="btn btn-outline-success btn-sm">Add a task</button>
                            </div>
                            <ul v-else class="list-group list-group-flush">
                                <li v-for="task in todaysTasks" :key="task.id" class="list-group-item">{{ task.title }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-12 col-md-5 d-flex flex-column gap-3">
                    <!-- Productivity Streak -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6><i class="bi bi-fire text-warning me-2"></i>Productivity Streak</h6>
                            <div class="fs-1 fw-bold text-success">{{ streak }} <span class="fs-6 fw-normal text-muted">days</span></div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Best: 12</small>
                                <small class="text-muted">47 total completed</small>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Deadlines -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6><i class="bi bi-alarm text-danger me-2"></i>Upcoming Deadlines</h6>
                            <p v-if="upcomingDeadlines.length === 0" class="text-muted mb-0" style="font-size:0.85rem;">No upcoming deadlines</p>
                            <ul v-else class="list-group list-group-flush">
                                <li v-for="d in upcomingDeadlines" :key="d.id" class="list-group-item px-0">{{ d.title }}</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recent Journal -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6><i class="bi bi-journal-text me-2"></i>Recent Journal</h6>
                            <ul class="list-group list-group-flush">
                                <li v-for="entry in recentJournal" :key="entry.title" class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold" style="font-size:0.9rem;">{{ entry.title }}</div>
                                        <small class="text-muted">{{ entry.date }}</small>
                                    </div>
                                    <span class="badge bg-success-subtle text-success">{{ entry.mood }}</span>
                                </li>
                            </ul>

                            <router-link to="/journal">
                        <button class="btn btn-outline-secondary btn-sm">View Journal</button>
                    </router-link>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>
