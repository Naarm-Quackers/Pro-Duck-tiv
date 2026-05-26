<template>
    <v-container class="py-10">
        <v-row justify="center">
            <v-col cols="12" md="8", lg="6">
                <v-form @submit.prevent="signIn">
                    <v-card class="pa-6" elevation="8" rounded="x1">
                        <h4 class="text-h6 font-weight-bold mb-3">Login</h4>

                        <v-row dense justify="center">
                            <v-col cols="12">
                                <v-text-field
                                    v-model="loginUsername"
                                    label="Username"
                                    type="text"
                                    variant="outlined"
                                    density="comfortable"
                                    autocomplete="username"
                                    :rules="[rules.required]"
                                />
                            </v-col>

                            <v-col cols="12">
                                <v-text-field
                                    v-model="loginPassword"
                                    label="Password"
                                    type="password"
                                    variant="outlined"
                                    density="comfortable"
                                    autocomplete="current-password"
                                    :rules="[rules.required]"
                                />
                            </v-col>

                            <v-alert
                                v-if="loginError"
                                type="error"
                                variant="tonal"
                                class="mb-5 text-center"
                            >{{ loginError }}</v-alert>

                            <v-col cols="12">
                                <v-btn
                                    type="submit"
                                    color="primary"
                                    size="large"
                                    block
                                    rounded="lg"
                                    elevation="2"
                                >{{ isSigningIn ? 'Signing In...' : 'Sign In' }}</v-btn>
                            </v-col>

                            <v-col cols="12">
                                <v-btn
                                    to="/registration"
                                    color="secondary"
                                    size="large"
                                    block
                                    rounded="lg"
                                    elevation="2"
                                >Create Account</v-btn>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-form>
            </v-col>
        </v-row>
    </v-container>

    <!-- <div class="container mt-5" style="max-width: 640px;">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Sign In</h1>

                <form @submit.prevent="signIn">
                    <div class="mb-3 text-start">
                        <label class="form-label">Username</label>
                        <input
                            v-model="loginUsername"
                            type="text"
                            class="form-control"
                            autocomplete="username"
                        />
                    </div>

                    <div class="mb-3 text-start">
                        <label class="form-label">Password</label>
                        <input
                            v-model="loginPassword"
                            type="password"
                            class="form-control"
                            autocomplete="current-password"
                        />
                    </div>

                    <div v-if="loginError" class="alert alert-danger py-2" role="alert">
                        {{ loginError }}
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" :disabled="isSigningIn">
                            {{ isSigningIn ? 'Signing In...' : 'Sign In' }}
                        </button>

                        <router-link to="/registration" class="btn btn-outline-secondary">
                            Create Account
                        </router-link>
                    </div>
                </form>
            </div>
        </div>
    </div> -->
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

const USERS_API = '/resources/api_users.php'

const router = useRouter()
const loginUsername = ref('')
const loginPassword = ref('')
const loginError = ref('')
const isSigningIn = ref(false)

const rules = {
    required: v => !!v || "This field is required."
}

onMounted(() => {
    try {
        const raw = localStorage.getItem('currentUser')
        if (raw) {
            router.replace('/')
        }
    } catch {
        // no-op
    }
})

const signIn = async () => {
    loginError.value = ''

    if (!loginUsername.value || !loginPassword.value) {
        loginError.value = 'Please enter both username and password.'
        return
    }

    isSigningIn.value = true

    try {
        const response = await fetch(USERS_API)
        

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`)
        }

        const users = await response.json()
        const rows = Array.isArray(users) ? users : []

        const matchedUser = rows.find(
            (user) =>
                String(user.user_name) === loginUsername.value &&
                String(user.user_password) === loginPassword.value
        )

        if (!matchedUser) {
            loginError.value = 'Invalid username or password.'
            return
        }

        const normalizedUser = {
            ...matchedUser,
            user_id: matchedUser.user_id,
            user_name: matchedUser.user_name,
            user_first_name: matchedUser.user_first_name,
            user_last_name: matchedUser.user_last_name,
            user_date_of_birth: matchedUser.user_date_of_birth
        }

        localStorage.setItem('currentUser', JSON.stringify(normalizedUser))
        router.push('/')
    } catch (error) {
        loginError.value = error instanceof Error ? error.message : 'Sign in failed. Please try again.'
    } finally {
        isSigningIn.value = false
    }
}
</script>
