<template>
    <v-container class="py-10">
        <v-row justify="center">
            <v-col cols="12" md="8" lg="6">
                <div class="text-center mb-8">
                    <h1 class="text-h3 font-weight-bold">Registration Form</h1>
                    <p class="text-body-2 text-medium-emphasis mt-2">Create your account below</p>
                </div>

                <v-form ref="regFormRef" v-model="isValid" @submit.prevent="submitForm">
                    <v-card class="pa-6" elevation="8" rounded="x1">
                        <h4 class="text-h6 font-weight-bold mb-6">User Details</h4>

                        <v-row dense justify="center">
                            <!-- fName -- user_first_name -- Text -- Letters only. -->
                            <v-col cols="12" md="6">
                                <v-text-field
                                v-model="form.fName"
                                label="First Name"
                                type="text"
                                variant="outlined"
                                density="comfortable"
                                :rules="[rules.required, rules.lettersOnly]"
                                />
                            </v-col>

                            <!-- lName -- user_last_name -- Text -- Letters only. -->
                            <v-col cols="12" md="6">
                                <v-text-field
                                v-model="form.lName"
                                label="Last Name"
                                type="text"
                                variant="outlined"
                                density="comfortable"
                                :rules="[rules.required, rules.lettersOnly]"
                                />
                            </v-col>

                            <!-- bDay -- user_date_of_birth -- Date -- Must be a valid date. -->
                            <v-col cols="12" md="5" class="d-flex justify-center">
                                <v-text-field
                                v-model="form.bDay"
                                label="Date Of Birth"
                                type="date"
                                variant="outlined"
                                density="comfortable"
                                :rules="[rules.required]"
                                />                                
                            </v-col>

                            <!-- uName  -- Text -- Unique, min 3. -->
                            <v-col cols="12">
                                <v-text-field
                                v-model="form.uName"
                                label="Username"
                                type="text"
                                variant="outlined"
                                density="comfortable"
                                :rules="[rules.required, rules.min3]"
                                :error-messages="usernameError ? [usernameError] : []"
                                @update:model-value="usernameError = ''"
                                />            
                            </v-col>

                            <!-- pwd -- Password -- Min-8, must include at least one special character. -->
                            <v-col cols="12" md="6">
                                <v-text-field
                                v-model="form.pwd"
                                label="Password"
                                type="password"
                                variant="outlined"
                                density="comfortable"
                                :rules="[rules.required, rules.password]"
                                />            
                            </v-col>

                            <!-- pwd2 -- Password -- Must be identical to pwd. -->
                            <v-col cols="12" md="6">
                                <v-text-field
                                v-model="form.pwd2"
                                label="Confirm Password"
                                type="password"
                                variant="outlined"
                                density="comfortable"
                                :rules="[rules.required, rules.matchPassword]"
                                />
                            </v-col>

                            <v-col cols="12" class="pt-2">
                                <v-btn
                                    type="submit"
                                    color="primary"
                                    size="large"
                                    block
                                    rounded="lg"
                                    elevation="2"
                                >Register</v-btn>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-form>
            </v-col>
        </v-row>
    </v-container>
</template>

<script setup>
import {reactive, ref} from "vue"
import {useRouter} from "vue-router"

const router = useRouter()
const regFormRef = ref(null)
const isValid = ref(false)
const usernameError = ref("")

const form = reactive({
    fName: "",
    lName: "",
    bDay: "",
    uName: "",
    pwd: "",
    pwd2: ""
})

const rules = {
    required: v => !!v || "This field is required.",
    lettersOnly: v => /^[A-Za-z]+$/.test(v) || "Letters only.",
    min3: v => (v && v.length >= 3) || "Must be at least 3 characters.",
    password: v => /^(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/.test(v) || "Must be 8+ characters and include a special character.",
    matchPassword: v => v === form.pwd || "Passwords do not match."
}

async function submitForm() {
    usernameError.value = ""

    const {valid} = await regFormRef.value.validate()
    if (!valid) return

    const response = await fetch('/resources/api_users.php', {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({
            user_name: form.uName,
            user_password: form.pwd,
            user_first_name: form.fName,
            user_last_name: form.lName,
            user_date_of_birth: form.bDay,
        })
    })

    const data = await response.json()

    // if response says username already exists, display relevant error message
    if (response.status === 409) {
        usernameError.value = data.error || "Username is already in use."
        return
    }
    // take user to sign-in page on successful registration
    router.push("/sign-in")
}
</script>