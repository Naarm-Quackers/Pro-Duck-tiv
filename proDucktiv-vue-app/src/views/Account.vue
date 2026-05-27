<template>
<div class="d-flex justify-content-center align-items-start py-4 py-md-5 px-3" style="min-height:calc(100vh - 65px);">
    <div class="w-100" style="max-width:600px;">

        <h1 class="text-h3 font-weight-bold">Account Page</h1>

        <p class="text-body-2 text-medium-emphasis mt-2">Manage your account information below</p>

        <div v-if="loading" class="text-muted text-center">Loading...</div>

        <!-- Error message for database -->
        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else-if="user" class="card">
            <div class="card-body p-3 p-md-4">

                <div class="row align-items-center mb-3">
                    <div class="col-12 col-sm-4 text-muted mb-1 mb-sm-0">First name</div>
                    <div class="col-12 col-sm-8">
                        <input v-if="editing" v-model="editForm.user_first_name" class="form-control" />
                        <span v-else>{{ user.user_first_name }}</span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-12 col-sm-4 text-muted mb-1 mb-sm-0">Last name</div>
                    <div class="col-12 col-sm-8">
                        <input v-if="editing" v-model="editForm.user_last_name" class="form-control" />
                        <span v-else>{{ user.user_last_name }}</span>
                    </div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-12 col-sm-4 text-muted mb-1 mb-sm-0">Username</div>
                    <div class="col-12 col-sm-8">{{ user.user_name }}</div>
                </div>

                <div class="row align-items-center mb-3">
                    <div class="col-12 col-sm-4 text-muted mb-1 mb-sm-0">Date of birth</div>
                    <div class="col-12 col-sm-8">
                        <input v-if="editing" v-model="editForm.user_date_of_birth" type="date" class="form-control" />
                        <span v-else>{{ formatDate(user.user_date_of_birth) }}</span>
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <template v-if="editing">
                        <button class="btn btn-success" :disabled="saving" @click="saveUser">
                            {{ saving ? 'Saving…' : 'Save' }}
                        </button>
                        <button class="btn btn-danger" @click="cancelEdit">Cancel</button>
                    </template>
                    <button v-else class="btn btn-success" @click="startEdit">Edit</button>
                </div>

                <div v-if="saveError" class="alert alert-danger mt-3 mb-0 py-2 small">{{ saveError }}</div>
            </div>
        </div>

    </div>
</div>
</template>

<script>
const USERS_API = '/cos30043/s103071591/proDucktiv/resources/api_users.php'
const USER_ID = getLoggedInUserId()

const JOURNALS_API = '/cos30043/s103071591/proDucktiv/resources/api_journals.php'

function getLoggedInUserId() {
    try {
        const user = localStorage.getItem('currentUser')
        if (!user) return 0 // if not logged in needs to sign in

        const parsed = JSON.parse(user)
        const id = Number(parsed.user_id)

        return Number.isFinite(id) ? id : 0
    } catch {
        return 0
    }
}

export default {
    data() {
        return {
            userId: getLoggedInUserId(),
            user: null,
            loading: false,
            error: null,
            editing: false,
            saving: false,
            saveError: null,
            editForm: {
                user_first_name: '',
                user_last_name: '',
                user_date_of_birth: '',
            },
        }
    },

    mounted() {
        if (!this.userId) {
            this.error = 'No logged-in user found. Please sign in again.'
            return
        }

        this.fetchUser()
    },

    methods: {
        async fetchUser() {
            this.loading = true
            try {
                if (!this.userId) {
                    throw new Error('Missing logged-in user id')
                }

                const res = await fetch(`${USERS_API}?fld=user_id&key=${this.userId}`)
                if (!res.ok) throw new Error(`HTTP ${res.status}`)
                const rows = await res.json()
                if (rows.length === 0) throw new Error('User not found')
                this.user = rows[0]
            } catch (err) {
                this.error = `Failed to load account: ${err.message}`
            } finally {
                this.loading = false
            }
        },

        startEdit() {
            this.editForm.user_first_name = this.user.user_first_name
            this.editForm.user_last_name = this.user.user_last_name
            this.editForm.user_date_of_birth = this.user.user_date_of_birth || ''
            this.saveError = null
            this.editing = true
        },

        cancelEdit() {
            this.editing = false
            this.saveError = null
        },

        async saveUser() {
            if (!this.editForm.user_first_name.trim()) {
                this.saveError = 'First name cannot be empty.'
                return
            }
            if (!this.editForm.user_last_name.trim()) {
                this.saveError = 'Last name cannot be empty.'
                return
            }
            this.saving = true
            this.saveError = null
            try {
                const res = await fetch(`${USERS_API}?fld=user_id&key=${this.userId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(this.editForm),
                })
                if (!res.ok) throw new Error(`HTTP ${res.status}`)
                const updated = await res.json()
                if (updated.error) throw new Error(updated.error)
                this.user = updated
                this.editing = false
            } catch (err) {
                this.saveError = `Save failed: ${err.message}`
            } finally {
                this.saving = false
            }
        },

        formatDate(iso) {
            if (!iso) return '—'
            return new Date(iso + 'T00:00:00').toLocaleDateString('en-AU', {
                day: 'numeric', month: 'long', year: 'numeric',
            })
        },
    },
}
</script>
