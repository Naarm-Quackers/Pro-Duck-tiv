<template>
    <!-- Welcome page container that swaps views by auth state. -->
    <div class='container align-items-center text-center mt-5 <!--bg-dark text-white-->'>
        <!-- Public marketing/entry view. -->
        <WelcomeUnauthenticatedView
            v-if="!isAuthenticated"
        />

       <!-- Authenticated dashboard view. -->
       <WelcomeAuthenticatedView
            v-else
            :username="currentUser.user_name"
            :user-id="currentUser.user_id"
            @toggle-auth="onLogout"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import WelcomeAuthenticatedView from '../components/WelcomeAuthenticatedView.vue'
import WelcomeUnauthenticatedView from '../components/WelcomeUnauthenticatedView.vue'

// Signed-in user snapshot loaded from localStorage.
const currentUser = ref(null)

// Controls which welcome view (public vs dashboard) is rendered.
const isAuthenticated = computed(() => !!currentUser.value)

onMounted(() => {
    try {
        const user = localStorage.getItem('currentUser')
        if (!user) {
            currentUser.value = null
            return
        }

        const parsed = JSON.parse(user)

        currentUser.value = {
            user_id: parsed.user_id,
            user_name: parsed.user_name,
            user_first_name: parsed.user_first_name,
            user_last_name: parsed.user_last_name,
            user_date_of_birth: parsed.user_date_of_birth
        }
    } catch {
        currentUser.value = null
    }
})

// Clears local user state and returns to public welcome view.
const onLogout = () => {
    currentUser.value = null
    localStorage.removeItem('currentUser')
}
</script>

