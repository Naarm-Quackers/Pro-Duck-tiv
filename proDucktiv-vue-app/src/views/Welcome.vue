<script setup>
import { computed, onMounted, ref } from 'vue'
import WelcomeAuthenticatedView from '../components/WelcomeAuthenticatedView.vue'
import WelcomeUnauthenticatedView from '../components/WelcomeUnauthenticatedView.vue'

const currentUser = ref(null)

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

const onLogout = () => {
    currentUser.value = null
    localStorage.removeItem('currentUser')
}
</script>


<template>
    <div class='container align-items-center text-center mt-5 <!--bg-dark text-white-->'>
        <WelcomeUnauthenticatedView
            v-if="!isAuthenticated"
        />

       <WelcomeAuthenticatedView
            v-else
            :username="currentUser.user_name"
            :user-id="currentUser.user_id"
            @toggle-auth="onLogout"
        />
    </div>
</template>
