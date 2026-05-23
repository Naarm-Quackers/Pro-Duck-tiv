<template>
    <div class="container py-4">

        <h4 class="mb-4">Account</h4>

        <div v-if="loading" class="text-muted">Loading...</div>

        <div v-else-if="error" class="alert alert-danger">{{ error }}</div>

        <div v-else-if="user" class="card" style="max-width:480px;">
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tbody>
                        <tr>
                            <th class="text-muted fw-normal" style="width:160px;">First name</th>
                            <td>{{ user.user_first_name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Last name</th>
                            <td>{{ user.user_last_name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Username</th>
                            <td>{{ user.user_name }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted fw-normal">Date of birth</th>
                            <td>{{ formatDate(user.user_date_of_birth) }}</td>
                        </tr>
                    </tbody>
                </table>
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
            user:    null,
            loading: false,
            error:   null,
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

        formatDate(iso) {
            if (!iso) return '—'
            return new Date(iso + 'T00:00:00').toLocaleDateString('en-AU', {
                day: 'numeric', month: 'long', year: 'numeric',
            })
        },
    },
}
</script>