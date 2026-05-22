<script setup>
import { ref } from 'vue'
import WelcomeAuthenticatedView from '../components/WelcomeAuthenticatedView.vue'
import WelcomeUnauthenticatedView from '../components/WelcomeUnauthenticatedView.vue'
//testing authentication logic, will replace with actual logic later
const isAuthenticated = ref(false) 
const toggleAuth = () => {
  isAuthenticated.value = !isAuthenticated.value
}
</script>


<template>
    <div class='container align-items-center text-center mt-5 <!--bg-dark text-white-->'>
        <WelcomeUnauthenticatedView
            v-if="!isAuthenticated"
            @toggle-auth="toggleAuth"
        />

        <WelcomeAuthenticatedView
            v-else
            @toggle-auth="toggleAuth"
        />
    </div>
</template>