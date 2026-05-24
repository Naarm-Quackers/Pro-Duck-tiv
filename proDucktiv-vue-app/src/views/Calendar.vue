<template>
    <div class="container">
        <div class="row py-2">
            <div class="col-12 text-center">
                <h1>Calendar</h1>
            </div>
        </div>
        <div class="row">
            <div class="col d-flex flex-row gap-3 py-2 justify-content-center">
                <button type="button" :class="selectedView === 'day'
                    ? 'btn btn-success '
                    : 'btn btn-secondary button-calendar-view green-hover'
                    " @click="selectedView = 'day'">
                    Daily
                </button>
                <button type="button" :class="selectedView === 'week'
                    ? 'btn btn-success'
                    : 'btn btn-secondary green-hover'
                    " @click="selectedView = 'week'">
                    Weekly
                </button>
                <button type="button" :class="selectedView === 'month'
                    ? 'btn btn-success'
                    : 'btn btn-secondary green-hover'
                    " @click="selectedView = 'month'">
                    Monthly
                </button>
            </div>
        </div>
    </div>
    <!-- -------------------------------------------------DAY------------------------------------------------- -->
    <div class="container" v-if="selectedView === 'day'">
        <div class="row text-center">
            <h2>Daily Calendar</h2>
        </div>
        <div class="row py-2">
            <div class="col text-center">
                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                    @click="prevDay">
                    < </button>
            </div>
            <div class="col text-center">
                <h3>{{ selectedDate.toDateString() }}</h3>
            </div>
            <div class="col text-center">
                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                    @click="nextDay">
                    >
                </button>
            </div>
        </div>
        <div class="row">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col" class="col text-center bg-success bg-opacity-75">
                            <h4>
                                {{
                                    selectedDate
                                        .toDateString()
                                        .split(" ")
                                        .slice(0, 3)
                                        .join(" ")
                                }}
                            </h4>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="col text-center bg-success bg-opacity-50">
                            <div class="card bday-hover " style="width: 100%" v-if="bDayCheck(selectedDate)">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">
                                        Happy Birthday &#x1F381
                                    </h5>
                                    <p class="card-text">Don't forget to celebrate &#x1F382 &#x1F389</p>
                                </div>
                            </div>
                            <div class="card task-hover" style="width: 100%" v-for="task in getSelectedDayTaskData()">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">
                                        {{ task.task_name }}
                                    </h5>
                                    <p class="card-text">{{ task.task_description }}</p>
                                    <p class="card-text">Due: {{ task.task_time }}</p>
                                </div>
                            </div>
                            <div class="card task-hover" style="width: 100%"
                                v-for="journal in getSelectedDayJournalData()">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">
                                        {{ journal.journal_title }}
                                    </h5>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- -------------------------------------------------WEEK------------------------------------------------- -->
    <div class="container" v-if="selectedView === 'week'">
        <div class="row text-center">
            <h2>Weekly Calendar</h2>
        </div>
        <div class="row">
            <div class="col justify-content-center d-flex flex-row gap-3 py-2">
                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                    @click="prevWeek">
                    << </button>
                        <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                            @click="prevDay">
                            < </button>
            </div>
            <div class="col text-center">
                <h3>{{ selectedDate.toDateString() }}</h3>
            </div>
            <div class="col justify-content-center d-flex flex-row gap-3 py-2">
                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                    @click="nextDay">
                    >
                </button>
                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                    @click="nextWeek">
                    >>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th scope="col" v-for="day in getWeekArray()" :class="day.isSelected
                            ? 'bg-success bg-opacity-75'
                            : 'green-hover bg-success bg-opacity-50'
                            " @click="selectedDate = day.date">
                            {{
                                day.date
                                    .toDateString()
                                    .split(" ")
                                    .slice(0, 3)
                                    .join(" ")
                            }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td v-for="day in getWeekArray()" @click="selectedDate = day.date" :class="day.isSelected ? 'bg-success bg-opacity-50' : 'green-hover'
                            ">
                            <div class="card bday-hover" style="width: 100%" v-if="bDayCheck(day.date)">
                                <div class="card-body p-1">
                                    <h6 class="card-title fw-bold">
                                        Happy Birthday &#x1F381
                                    </h6>
                                    <p class="card-text">Don't forget to celebrate &#x1F382 &#x1F389</p>
                                </div>
                            </div>
                            <div class="card task-hover" style="width: 100%" v-for="task in day.tasks">
                                <div class="card-body p-1">
                                    <h6 class="card-title fw-bold">
                                        Tasks for Today
                                    </h6>
                                    <p class="card-text">task description</p>
                                </div>
                            </div>
                            <div class="card task-hover" style="width: 100%" v-for="journal in day.journals">
                                <div class="card-body p-1">
                                    <h6 class="card-title fw-bold">
                                        Todays Journal
                                    </h6>
                                    <p class="card-text">Journal Entry</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- -------------------------------------------------MONTH------------------------------------------------- -->
    <div class="container" v-if="selectedView === 'month'">
        <div class="row text-center">
            <h2>Monthly Calendar</h2>
        </div>
        <div class="row">
            <div class="col justify-content-center d-flex flex-row gap-3 py-2">
                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                    @click="prevMonth">
                    <<< </button>
                        <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                            @click="prevWeek">
                            << </button>
                                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                                    @click="prevDay">
                                    < </button>
            </div>
            <div class="col text-center">
                <h3>{{ getStringDate }}</h3>
            </div>
            <div class="col justify-content-center d-flex flex-row gap-3 py-2">
                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                    @click="nextDay">
                    >
                </button>
                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                    @click="nextWeek">
                    >>
                </button>
                <button class="btn btn-secondary button-change-date rounded-5 green-hover" type="button"
                    @click="nextMonth">
                    >>>
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <caption class="text-center caption-top">
                    {{
                        getMonthName(selectedDate?.getMonth())
                    }}
                </caption>
                <thead>
                    <tr>
                        <th scope="col" v-for="(dayName, dayNameIndex) in monthData.dayName" :class="(selectedDate.getDay() + 6) % 7 === dayNameIndex
                            ? 'bg-success bg-opacity-75'
                            : 'bg-success bg-opacity-25'
                            ">
                            {{ dayName }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(week, wi) in getPaddedMonthArray()">
                        <td v-for="(day, di) in week" :class="day?.isSelected
                            ? 'bg-success bg-opacity-50 month-calendar-body'
                            : 'day-cell green-hover'
                            " @click="selectDateMonth(day)">
                            <h5 v-if="day != null" class="text-center">
                                {{ day?.date.getDate() }}
                                <hr>
                            </h5>
                            <div v-if="day != null">
                                <div class="card bday-hover" style="width: 100%" v-if="bDayCheck(day.date)">
                                    <div class="card-body p-1">
                                        <h6 class="card-title fw-bold">
                                            Happy Birthday &#x1F381
                                        </h6>
                                    </div>
                                </div>
                                <div class="card task-hover" style="width: 100%" v-for="task in day.tasks">
                                    <div class="card-body p-1">
                                        <h6 class="card-title fw-bold">
                                            {{ task.task_name }}
                                        </h6>
                                    </div>
                                </div>
                                <div class="card task-hover" style="width: 100%" v-for="journal in day.journals">
                                    <div class="card-body p-1">
                                        <h6 class="card-title fw-bold">
                                            {{ journal.journal_title }}
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col d-flex justify-content-end">

                <button class="btn btn-warning" @click="readTasksData(); readJournalData()">
                    Refresh
                </button>
            </div>
        </div>
    </div>
</template>

<script>
// for daily calendar view use a table layout with the time on the side column
// nested router for viewing individual tasks and journal entries
import dayTimeData from "../assets/data/calendarDayData.json";
import monthData from "../assets/data/calendarMonthData.json";

export default {
    data() {
        return {
            calendarView: ["day", "week", "month"],
            selectedView: "day",
            selectedDate: new Date(), // set to the current date option
            dayTimeData: dayTimeData,
            monthData: monthData,
            USER_API: "/resources/api_users.php",
            TASK_API: "/resources/api_tasks.php",
            JOURNAL_API: "/resources/api_journals.php",
            user: null,
            tasks: [],
            journals: [],
            loggedInUserID: "",
            userDbStatusMessage: "",
            userDbError: "",
            taskDbStatusMessage: "",
            taskDbError: "",
            journalDbStatusMessage: "",
            journalDbError: "",
        };
    },
    computed: {
        isAuthenticated() {
            return !!this.user;
        },
        // print the seltected date as a string
        getStringDate() {
            //return the date as a string, or an empty string if there's no date set.
            return this.selectedDate?.toDateString() ?? "";
        },
    },
    mounted() {

        this.readTasksData();
        this.readJournalData();
        // read User Data
        try {
            const stored = localStorage.getItem('currentUser');
            if (!stored) return;

            const parsed = JSON.parse(stored);
            this.user = {
                user_id: parsed.user_id,
                user_date_of_birth: parsed.user_date_of_birth
            };
        } catch {
            this.user = null;
        }
    },
    methods: {
        // for time marker in the day view
        getCurrentTime() {
            // returns the current time in HH:MM:SS format
            return new Date().toTimeString().split(" ")[0];
        },
        // goes to the next day in the calendar view
        nextDay() {
            const nextDate = new Date(this.selectedDate);
            nextDate.setDate(nextDate.getDate() + 1);
            this.selectedDate = nextDate;
        },
        // goes to the previous day in the calendar view
        prevDay() {
            const prevDate = new Date(this.selectedDate);
            prevDate.setDate(prevDate.getDate() - 1);
            this.selectedDate = prevDate;
        },
        // goes to the next week in the calendar view
        nextWeek() {
            const nextDate = new Date(this.selectedDate);
            nextDate.setDate(nextDate.getDate() + 7);
            this.selectedDate = nextDate;
        },
        // goes to the previous week in the calendar view
        prevWeek() {
            const prevDate = new Date(this.selectedDate);
            prevDate.setDate(prevDate.getDate() - 7);
            this.selectedDate = prevDate;
        },
        // goes to the next month in the calendar view
        nextMonth() {
            const nextDate = new Date(this.selectedDate);
            nextDate.setMonth(nextDate.getMonth() + 1);
            this.selectedDate = nextDate;
        },
        // goes to the previous month in the calendar view
        prevMonth() {
            const prevDate = new Date(this.selectedDate);
            prevDate.setMonth(prevDate.getMonth() - 1);
            this.selectedDate = prevDate;
        },
        // calculates the total number of days in the current month
        getTotalDaysInMonth() {
            return new Date(
                this.selectedDate.getFullYear(),
                this.selectedDate.getMonth() + 1,
                0,
            ).getDate();
        },
        // gets The name of the Month
        getMonthName(month) {
            return monthData.monthName[month];
        },
        // get frist Day of the sleceted dates month
        getMonthFirstDay() {
            return new Date(
                this.selectedDate.getFullYear(),
                this.selectedDate.getMonth(),
                1,
            );
        },
        getMonthLastDay() {
            return new Date(
                this.selectedDate.getFullYear(),
                this.selectedDate.getMonth() + 1,
                0,
            );
        },

        // returns Array of selectedDates Current month
        getMonthArray() {
            const totalMonthDays = this.getTotalDaysInMonth();
            const monthArray = [];
            for (let i = 0; i < totalMonthDays; i += 1) {
                monthArray.push({
                    date: new Date(
                        this.selectedDate.getFullYear(),
                        this.selectedDate.getMonth(),
                        1 + i,
                    ),
                    isSelected: false,
                    tasks: this.getTaskDataByDate(new Date(
                        this.selectedDate.getFullYear(),
                        this.selectedDate.getMonth(),
                        1 + i,
                    )),
                    journals: this.getJournalDataByDate(new Date(
                        this.selectedDate.getFullYear(),
                        this.selectedDate.getMonth(),
                        1 + i,
                    )),
                });
            }
            return monthArray;
        },
        // returns padded month array with week split
        getPaddedMonthArray() {
            const monthArray = this.getMonthArray();
            // get the first day in selected month
            const firstDay = this.getMonthFirstDay();
            // get the last day in selected month
            const lastDay = this.getMonthLastDay();

            // the getDay() method gets the days in Sun-sat order
            // meaning that sun is index 0 and mon is index 1
            // so we can use a remainder or modulo operator % to off set
            // this way it fits our mon-sun week order
            // thus monday (1) -> (1 + 6) = 7 -> 7 % 7  = 0
            // Offset padding for start of month
            const firstWeekOffset = (firstDay.getDay() + 6) % 7;
            // Offset padding for end of month
            const lastWeekOffset = (lastDay.getDay() + 6) % 7;
            const endMonthOffset =
                lastWeekOffset === 0 ? 0 : 6 - lastWeekOffset;

            const paddedMonthArray = [
                // the ... spread operator merges the nested arrays into 1
                ...Array(firstWeekOffset).fill(null),
                ...monthArray,
                ...Array(endMonthOffset).fill(null),
            ];

            this.checkSelectedDate(paddedMonthArray);

            // split the month array into diffrent rows of 7
            const weekSplitMonth = [];
            for (let i = 0; i < paddedMonthArray.length; i += 7) {
                weekSplitMonth.push(paddedMonthArray.slice(i, i + 7));
            }

            return weekSplitMonth;
        },
        // get the monday for selected day for week view
        getWeekMonday() {
            // 0(sun) - 6(sat)
            const day = this.selectedDate.getDay();
            const dayOffset = (day + 6) % 7;
            return new Date(
                this.selectedDate.getFullYear(),
                this.selectedDate.getMonth(),
                this.selectedDate.getDate() - dayOffset,
            );
        },
        // Week Array based off selected date
        getWeekArray() {
            const monday = this.getWeekMonday();
            const weekArray = [];
            for (let i = 0; i < 7; i += 1) {
                weekArray.push({
                    date: new Date(
                        monday.getFullYear(),
                        monday.getMonth(),
                        monday.getDate() + i,
                    ),
                    isSelected: false,
                    tasks: this.getTaskDataByDate(new Date(
                        monday.getFullYear(),
                        monday.getMonth(),
                        monday.getDate() + i,
                    )),
                    journals: this.getJournalDataByDate(new Date(
                        monday.getFullYear(),
                        monday.getMonth(),
                        monday.getDate() + i,
                    )),
                });
            }
            this.checkSelectedDate(weekArray);
            return weekArray;
        },
        // check if the date is selected and set is seleceted to true
        checkSelectedDate(dateArray) {
            for (let i = 0; i < dateArray.length; i += 1) {
                if (dateArray[i] === null) continue;
                if (
                    dateArray[i].date.getDate() ===
                    this.selectedDate.getDate() &&
                    dateArray[i].date.getMonth() ===
                    this.selectedDate.getMonth() &&
                    dateArray[i].date.getFullYear() ===
                    this.selectedDate.getFullYear()
                ) {
                    dateArray[i].isSelected = true;
                }
            }
        },
        selectDateMonth(day) {
            if (day === null) return;
            this.selectedDate = day.date;
            // recall to update the array
            this.getPaddedMonthArray();
        },

        readTasksData() {
            // make sure to set the user id for the fetch request
            if (!this.user) return;
            fetch(`${this.TASK_API}?UserId=${this.user.user_id}`)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error:${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    this.tasks = data;
                    this.taskDbStatusMessage = "Successful :)";
                    this.taskDbError = "";
                })
                .catch((error) => {
                    this.taskDbError = error.message;
                });
        },
        // fetch and read Journal data
        readJournalData() {
            fetch(this.JOURNAL_API)
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`HTTP error:${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    this.journals = data;
                    this.journalDbStatusMessage = "Successful :)";
                    this.journalDbError = "";
                })
                .catch((error) => {
                    this.journalDbError = error.message;
                });
        },
        // formats the date to yyyy-mm-dd format to compare with the values in the database
        formatDateFull(day) {
            const formattedDate = day.toISOString().split("T")[0];
            return formattedDate

        },
        // remove yyyy from date returns  string mm-dd
        removeYearFromDate(formattedDay) {
            return formattedDay.slice(5);
        },
        // check for birthday ---> needs to be reformated to match user database
        bDayCheck(date) {
            if (!this.user) return false;
            const formattedDate = this.removeYearFromDate(this.formatDateFull(date));
            const bDay = this.removeYearFromDate(this.user.user_date_of_birth);
            return bDay === formattedDate;
        },
        // filter data Array by selected date and user id for tasks day view or a sepcific selected date
        getSelectedDayTaskData() {
            if (!this.user) return [];
            return this.tasks.filter(task => task.task_date === this.formatDateFull(this.selectedDate)
                && task.user_id === this.user.user_id);
        },
        // fillter task data for a date
        getTaskDataByDate(date) {
            if (!this.user) return [];
            return this.tasks.filter(task => task.task_date === this.formatDateFull(date)
                && task.user_id === this.user.user_id);
        },

        // filter data Array by selected date and user id for journals
        getSelectedDayJournalData() {
            if (!this.user) return [];
            return this.journals.filter(journal => journal.journal_date === this.formatDateFull(this.selectedDate)
                && journal.user_id === this.user.user_id);
        },
        // filter data Array by week array and user id for journal
        getJournalDataByDate(date) {
            if (!this.user) return [];
            return this.journals.filter(journal => journal.journal_date === this.formatDateFull(date)
                && journal.user_id === this.user.user_id);
        },
    },
};
</script>
<style>
.green-hover:hover {
    background-color: var(--bs-success);
    border-color: var(--bs-success);
    opacity: 45%;
    transition: ease 0.5s;
}

.task-hover:hover {
    background-color: mediumaquamarine;
    border-color: var(--bs-success);
    transition: ease 0.5s;
}

.bday-hover {
    background-color: var(--bs-warning);
    border-color: palevioletred;
    transition: ease 0.5s;
}

.yellow-hover:hover {
    background-color: var(--bs-warning);
    border-color: var(--bs-success);
    opacity: 10%;
    transition: ease 0.5s;
}

.day-cell {
    width: 250px;
    height: 150px;
}
</style>
