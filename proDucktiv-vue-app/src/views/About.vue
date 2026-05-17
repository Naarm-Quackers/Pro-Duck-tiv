<script setup>
import LogoDark from "../assets/images/DarkLogo.svg";
import LogoLight from "../assets/images/LightLogo.svg";
</script>
<template>
    <div class="container">
        <div class="row">
            <div class="col-3 p-3">
                <img @click="toggleDarkMode" :src="isDarkMode ? LogoDark : LogoLight" alt="ProDucktiv Logo"
                    width="80%" />
            </div>
            <div class="col-6 text-center p-3">
                <h1 id="title">About ProDucktiv</h1>
            </div>
        </div>
        <div class="row">
            <div class="col p-3">
                <h2 id="about-subtitle">What is ProDucktiv</h2>
                <p id="about-subtext">
                    ProDucktiv is a productivity app which allows the user to
                    plan out their day, week or month, by setting tasks with
                    deadlines plus a journal which can be used to create
                    shopping lists or just jot down your thoughts.
                </p>
            </div>
            <div class="col p-3">
                <img src="" alt="Planner image" />
            </div>
            <hr />
        </div>
        <div class="row">
            <h2 id="about-subtitle">How to Use ProDucktiv</h2>
            <div class="col p-3">
                <h3>How to Create a Task</h3>
                <p></p>
            </div>
            <div class="col p-3">
                <img src="" alt="Task Creation" />
            </div>
        </div>
        <div class="row">
            <div class="col p-3">
                <h3>How to Use the Journal</h3>
                <p></p>
            </div>
            <div class="col p-3">
                <img src="" alt="Journal" />
            </div>
        </div>
        <div class="row">
            <div class="col p-3">
                <h3>How to Use the Calendar</h3>
                <p>
                    The calendar is used to get an overview of your tasks and
                    deadlines as well as view the journal notes you have made on
                    a particular day. It can be used to access all note and
                    tasks made through a visual interface, allowing you to make
                    any edits or deletions on the done on the fly. Task can also
                    be dragged with the mouse and moved to different date
                    changing the deadline without having to manually edit the
                    task.
                </p>
            </div>
            <div class="col p-3">
                <img src="" alt="Calendar" />
            </div>
        </div>
        <div class="row">
            <div class="col p-3">
                <h3>How We Keep Your Data Safe</h3>
                <p></p>
            </div>
            <div class="col p-3">
                <img src="" alt="Data Safety" />
            </div>
        </div>
        <hr />
    </div>
    <div class="container">
        <div class="row p-3">
            <h2 id="about-subtitle">About the ProDuctive Team</h2>
        </div>
        <div class="row">
            <div class="col p-3">
                <h3 id="about-name-title">Adam</h3>
                <p></p>
                <h4>Contributions</h4>
                <p></p>
            </div>
        </div>
        <div class="row">
            <div class="col p-3">
                <h3 id="about-name-title">Amy</h3>
                <p></p>
                <h4>Contributions</h4>
                <p></p>
            </div>
        </div>
        <div class="row">
            <div class="col p-3">
                <h3 id="about-name-title">Eilia</h3>
                <p></p>
                <h4>Contributions</h4>
                <p>Worked on the About and Calendar pages, adding advance fetures to the Calendar page</p>
            </div>
        </div>
        <div class="row">
            <div class="col p-3">
                <h3 id="about-name-title">Dylan</h3>
                <p></p>
                <h4>Contributions</h4>
                <p></p>
            </div>
        </div>
        <div class="row">
            <div class="col p-3">
                <h3 id="about-name-title">Justin</h3>
                <p></p>
                <h4>Contributions</h4>
                <p></p>
            </div>
        </div>
        <hr />
    </div>
</template>
<script>
export default {
    data() {
        return {
            isDarkMode: true,
        };
    },
    methods: {
        toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
        },
    },
};
</script>
