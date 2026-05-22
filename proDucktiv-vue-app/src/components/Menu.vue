<script setup>
import LogoDark from "../assets/images/DarkLogo.svg"
import LogoLight from "../assets/images/LightLogo.svg"
</script>

<template>
    <div class="container-fluid">
        <div class="row text-center">
            <nav class="navbar navbar-light  mb-0 bg-light  mb-0 justify-content-evenly">
                <p class="px-3"><img @click="toggleDarkMode" :src="isDarkMode ? LogoDark : LogoLight"
                        alt="ProDucktiv Logo" width="80px" height="80px" class="rounded-4" /></p>
                <router-link to="/"
                    class="navbar-brand menu-element py-3 px-1 rounded-4 fw-bold">proDucktiv</router-link>
                <router-link to="/task"
                    class="nav-link menu-element py-3 px-1 rounded-4 fw-semibold">Tasks</router-link>
                <router-link to="/journal"
                    class="nav-link menu-element py-3 px-1 rounded-4 fw-semibold">Journal</router-link>
                <router-link to="/calendar"
                    class="nav-link menu-element py-3 px-1 rounded-4 fw-semibold">Calendar</router-link>
                <button :class="isMenuOpen ? 'navbar-toggler menu-element bg-success' : 'navbar-toggler menu-element'"
                    type="button" @click="isMenuOpen = !isMenuOpen" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" :class="{ show: isMenuOpen }">
                    <router-link to="/"
                        class="nav-link active menu-element py-3 rounded-4 fw-semibold">Welcome</router-link>
                    <router-link to="/about"
                        class="nav-link menu-element py-3 rounded-4 fw-semibold">About</router-link>
                    <router-link to="/sign-in" class="nav-link menu-element py-3 rounded-4 fw-semibold">Sign
                        In</router-link>
                    <router-link to="/registration"
                        class="nav-link menu-element py-3 rounded-4 fw-semibold">Registration</router-link>
                    <router-link to="/account"
                        class="nav-link menu-element py-3 rounded-4 fw-semibold">Account</router-link>
                    <router-link to="/task-creator" class="nav-link menu-element py-3 rounded-4 fw-semibold">Task
                        Creator</router-link>
                    <router-link to="/task-editor" class="nav-link menu-element py-3 rounded-4 fw-semibold">Task
                        Editor</router-link>
                </div>
            </nav>
            <hr>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            isDarkMode: false,
            isMenuOpen: false
        }
    },
    methods: {
        toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
        },
    }
}
</script>
<style>
.menu-element:hover {
    background-color: var(--bs-success);
    background-image: var(--bs-gradient);
    color: black;
    transition: ease 0.5s;
}

.nav-collapse.show {
    border-top: 1px solid #dee2e6;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
}

.navbar-collapse.show .nav-link {
    text-align: center;
}
</style>
