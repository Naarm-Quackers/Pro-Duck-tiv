<template><!-- Public content shown before sign-in -->
<div class="row">
    <!-- Hero copy and product positioning -->
    <div class="col-12">
        <h1>
            Welcome to proDucktiv
            <span class="badge bg-success">Manage Your Tasks, Manage Your Day</span>
        </h1>
        <p class="lead">The all-in-one productivity app to help you stay organized and focused.</p>
    </div>

    <!-- Links to sign-in and about pages -->
    <div class="row">
        <div class="col d-flex justify-content-center gap-2">
            <router-link to="/sign-in" class="btn btn-outline-success">
                Sign In
            </router-link>
            <router-link to="/about" class="btn btn-outline-warning">
                Learn More
            </router-link>
        </div>
    </div>

    <hr class="featurette-divider m-4">

    <!-- Feature overview heading -->
    <div class="col-12">
        <h2>Everything you need</h2>
        <p class="lead">Features designed to keep you productive and organized.</p>
    </div>

    <!-- Product showcase carousel -->
    <div id="myCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
        </div>

        <div class="carousel-inner rounded overflow-hidden">
            <div class="carousel-item active">
                <img :src="taskManagement" class="d-block w-100" alt="Task Management"
                    style="max-height: 600px; object-fit: cover;">
                <div class="carousel-caption text-bg-light rounded">
                    <h5>Task Management</h5>
                    <p>Organize and prioritize your tasks effectively.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img :src="calendarView" class="d-block w-100" alt="Calendar View"
                    style="max-height: 600px; object-fit: cover;">
                <div class="carousel-caption text-bg-light rounded">
                    <h5>Calendar View</h5>
                    <p>Visualize your schedule and plan your day effectively.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img :src="dailyJournal" class="d-block w-100" alt="Daily Journal"
                    style="max-height: 600px; object-fit: cover;">
                <div class="carousel-caption text-bg-light rounded">
                    <h5>Daily Journal</h5>
                    <p>Reflect on your day and track your progress.</p>
                </div>
            </div>

            <div class="carousel-item">
                <img :src="progressDashboard" class="d-block w-100" alt="Progress Dashboard"
                    style="max-height: 600px; object-fit: cover;">
                <div class="carousel-caption text-bg-light rounded">
                    <h5>Progress Dashboard</h5>
                    <p>Track your progress and achievements.</p>
                </div>
            </div>
        </div>

        <button class="carousel-control-prev carousel-control-dark" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next carousel-control-dark" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <hr class="featurette-divider m-4">

    <!-- Social statistics section -->
    <div class="col-12">
        <h2>Tried and Tested</h2>
        <p class="lead">Features designed to keep you productive and organized.</p>
    </div>

    <!-- Stats section -->
    <div class="row text-center g-4 mb-5">
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
</template>

<script setup>
import { ref } from 'vue'
// Carousel  images
import taskManagement from '../assets/images/Tasks.webp'
import calendarView from '../assets/images/Calendar.webp'
import dailyJournal from '../assets/images/Journal.webp'
import progressDashboard from '../assets/images/Dashboard.webp'
// Marketing stats (replace with API data later)
const productivityIncrease = ref('85%')
const activeUsers = ref('1K+')
const tasksCompleted = ref('1M+')


</script>

<style scoped>
.carousel-control-dark {
    width: 3rem;
    height: 4rem;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.75);
    border-radius: 0.5rem;
}

.carousel-indicators [data-bs-target] {
    background-color: rgba(0, 0, 0, 0.55);
    opacity: 0.7;
}

.carousel-indicators .active {
    background-color: rgba(0, 0, 0, 0.9);
    opacity: 1;
}
</style>
