<template>
    <!-- Welcome page container that swaps views by auth state -->
    <div class='container align-items-center text-center mt-5'>
        <!-- Public marketing/entry view -->
        <WelcomeUnauthenticatedView
            v-if="!isAuthenticated"
        />

       <!-- Authenticated dashboard view -->
       <WelcomeAuthenticatedView
            v-else
            @toggle-auth="onLogout"
        />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import WelcomeAuthenticatedView from '../components/WelcomeAuthenticatedView.vue'
import WelcomeUnauthenticatedView from '../components/WelcomeUnauthenticatedView.vue'

function signedIn() {
    try {
        return !!localStorage.getItem('currentUser')
    } catch {
        return false
    }
}

// Controls which welcome view (public vs dashboard) is rendered
const isAuthenticated = ref(signedIn())

const onLogout = () => {
    localStorage.removeItem('currentUser')
    isAuthenticated.value = false
}
</script>

