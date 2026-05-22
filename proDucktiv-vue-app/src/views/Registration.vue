<template>
    <div class="container">
        <section id="Header" class="row">
            <h1 class="col-12">Registration Form</h1>
        </section>


        <form id="jobForm" @submit.prevent="submitForm">
            <fieldset class="card p-2 mb-5 shadow rounded bg-body">
                <h4 class="sectionTitle">User Details:</h4>

                <!-- fName -- user_first_name -- Text -- Letters only. -->
                <div class="mb-3 row">
                    <label for="fName" class="col-sm-3 col-form-label">First Name:</label>
                    <div class="col-sm-9">
                        <input v-model="form.fName" type="text" class="form-control" id="fName" name="fName">
                        <span id="fNameError" class="text-danger"></span>
                    </div>
                </div>

                <!-- lName -- user_last_name -- Text -- Letters only. -->
                <div class="mb-3 row">
                    <label for="lName" class="col-sm-3 col-form-label">Last Name:</label>
                    <div class="col-sm-9">
                        <input v-model="form.lName" type="text" class="form-control" id="lName" name="lName">
                        <span id="lNameError" class="text-danger"></span>
                    </div>
                </div>

                <!-- bDay -- user_date_of_birth -- Date -- Must be a valid date. -->
                <div class="mb-3 row">
                    <label for="bDay" class="col-sm-3 col-form-label">Date of Birth:</label>
                    <div class="col-sm-9">
                        <input v-model="form.bDay" type="date" class="form-control" id="bDay" name="bDay">
                        <span id="bDayError" class="text-danger"></span>
                    </div>
                </div>
            </fieldset>


            <fieldset class="card p-2 shadow rounded bg-body">
                <h4 class="sectionTitle">Login Details:</h4>

                <!-- uName  -- Text -- Unique, min 3. -->
                <div class="mb-3 row">
                    <label for="uName" class="col-sm-3 col-form-label">Username:</label>
                    <div class="col-sm-9">
                        <input v-model="form.uName" type="text" class="form-control" id="uName" name="uName">
                        <span id="uNameError" class="text-danger"></span>
                    </div>
                </div>

                <!-- pwd -- Password -- Min-8, must include at least one special character. -->
                <div class="mb-3 row">
                    <label for="pwd" class="col-sm-3 col-form-label">Password:</label>
                    <div class="col-sm-9">
                        <input v-model="form.pwd" type="password" class="form-control" id="pwd" name="pwd">
                        <span id="pwdError" class="text-danger"></span>
                    </div>
                </div>

                <!-- pwd2 -- Password -- Must be identical to pwd. -->
                <div class="mb-3 row">
                    <label for="pwd2" class="col-sm-3 col-form-label">Confirm Password:</label>
                    <div class="col-sm-9">
                        <input v-model="form.pwd2" type="password" class="form-control" id="pwd2" name="pwd2">
                        <span id="pdw2Error" class="text-danger"></span>
                    </div>
                </div>
            </fieldset>


            <div class="mt-3 d-flex justify-content-center">
                <input type="submit" id="SubmitButton" class="btn btn-primary" value="Submit">
            </div>
        </form>
    </div>
</template>

<script setup>
import {reactive} from "vue"

const form = reactive({
    fName: "",
    lName: "",
    bDay: "",
    uName: "",
    pwd: "",
    pwd2: ""
})

const errors = reactive({
    fName: "",
    lName: "",
    bDay: "",
    uName: "",
    pwd: "",
    pwd2: ""
})

function validate() {
    errors.fName = /^[A-Za-z]+$/.test(form.fName) ? "" : "First name must contain letters only."
    errors.lName = /^[A-Za-z]+$/.test(form.lName) ? "" : "Last name must contain letters only."
    errors.bDay = form.bDay ? "" : "Date of birth is required."
    errors.uName = form.uName.length >= 3 ? "" : "Username must be at least 3 characters."
    errors.pwd = /^(?=.*[A-Za-z0-9]).{8,}$/.test(form.pwd) ? "" : "Password must be at least 8 characters and include a special character."
    errors.pwd2 = form.pwd === form.pwd2 ? "" : "Passwords do not match."

    return !Object.values(errors).some(Boolean)
}

async function submitForm() {
    if (!validate()) return
    
    const payload = {
        fName: form.fName,
        lName: form.lName,
        bDay: form.bDay,
        uName: form.uName,
        pwd: form.pwd
    }

    const res = await fetch("")
}

</script>
