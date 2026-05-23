<template>
    <div class="d-flex justify-content-center align-items-start py-5" style="min-height:calc(100vh - 65px);">
        <div style="width:100%;max-width:600px;">

            <h1 class="text-h3 font-weight-bold">Account Page</h1>

            <p class="text-body-2 text-medium-emphasis mt-2">Manage your account information below</p>

            <div v-if="loading" class="text-muted text-center">Loading...</div>

            <!-- Error message for database -->
            <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

            <div v-else-if="user" class="card">
                <div class="card-body p-4">
                    <table class="table table-borderless mb-0 fs-6">
                        <tbody>
                            <tr>
                                <th class="text-muted fw-normal" style="width:180px;">First name</th>
                                <td>
                                    <input v-if="editing" v-model="editForm.user_first_name"
                                        class="form-control" />
                                    <span v-else>{{ user.user_first_name }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Last name</th>
                                <td>
                                    <input v-if="editing" v-model="editForm.user_last_name"
                                        class="form-control" />
                                    <span v-else>{{ user.user_last_name }}</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Username</th>
                                <td>{{ user.user_name }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted fw-normal">Date of birth</th>
                                <td>
                                    <input v-if="editing" v-model="editForm.user_date_of_birth"
                                        type="date" class="form-control" />
                                    <span v-else>{{ formatDate(user.user_date_of_birth) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

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
const USERS_API = '/cos30043/s105337347/proDucktiv/resources/api_users.php'
const USER_ID = 2 // replace with the logged-in user's real ID

export default {
    data() {
        return {
            user: null,
            loading: false,
            error: null,
            editing: false,
            saving: false,
            saveError: null,
            editForm: {
                user_first_name:   '',
                user_last_name:    '',
                user_date_of_birth: '',
            },
        }
    },

    mounted() {
        this.fetchUser()
    },

    methods: {
        async fetchUser() {
            this.loading = true
            try {
                const res = await fetch(`${USERS_API}?fld=user_id&key=${USER_ID}`)
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
            this.editForm.user_first_name    = this.user.user_first_name
            this.editForm.user_last_name     = this.user.user_last_name
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
                const res = await fetch(`${USERS_API}?fld=user_id&key=${USER_ID}`, {
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
