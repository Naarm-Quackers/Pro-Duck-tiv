<template>
    <section id="Header">
        <h1>Registration Form</h1>
    </section>
    
    <div class='container'>
        <form id="jobForm" method="post" action="" onsubmit="return validateForm()">
            <fieldset>
                <h4 class="sectionTitle">Login Details</h4>
                <div class="row">
                    <div class="col-6">
                    <!-- fName -- Letters only, required. -->
                    <label for="fName" class="form-label">First Name:</label><br>
                    <input id="fName" name="fName" class="form-control" type="text">
                    <span id="fNameError" class="error"></span>
                    </div>
                    <div class="col-6">
                    <!-- lName -- Letters only, required. -->
                    <label for="lName" class="form-label">Last Name:</label><br>
                    <input id="lName" name="lName" class="form-control" type="text">
                    <span id="lNameError" class="error"></span>
                    </div>
                    <div class="col-6">
                    <!-- bDay -- Required, must be a valid date, and the applicant is at least 16 years old. -->
                    <label for="bDay" class="form-label">Date of Birth:</label><br>
                    <input id="bDay" name="bDay" class="form-control" type="date">
                    <span id="bDayError" class="error"></span>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</template>
