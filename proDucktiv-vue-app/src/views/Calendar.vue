<script setup>
import LogoDark from "../assets/images/DarkLogo.svg";
import LogoLight from "../assets/images/LightLogo.svg";
</script>
<template>
    <div class='container'>
        <div class="row py-2">
            <div class="col-3">
                <img @click="toggleDarkMode" :src="isDarkMode ? LogoDark : LogoLight" alt="ProDucktiv Logo"
                    width="80%" />
            </div>
            <div class="col-6 text-center">
                <h1>Calendar</h1>
            </div>
        </div>
        <div class="row">
            <div class="col d-flex flex-row gap-3 py-2 justify-content-center">
                <button type="button" :class="selectedView === 'day'
                    ? 'btn btn-success '
                    : 'btn btn-secondary button-calendar-view'
                    " @click="selectedView = 'day'">
                    Daily
                </button>
                <button type="button" :class="selectedView === 'week'
                    ? 'btn btn-success'
                    : 'btn btn-secondary button-calendar-view'
                    " @click="selectedView = 'week'">
                    Weekly
                </button>
                <button type="button" :class="selectedView === 'month'
                    ? 'btn btn-success'
                    : 'btn btn-secondary button-calendar-view'
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
                <button class="btn btn-secondary button-change-date" type="button" @click="prevDay">
                    Prev Day
                </button>
            </div>
            <div class="col text-center">
                <h3>{{ selectedDate.toDateString() }}</h3>
            </div>
            <div class="col text-center">
                <button class="btn btn-secondary button-change-date" type="button" @click="nextDay">
                    Next Day
                </button>
            </div>
        </div>
        <div class="row">
            <table
                :class="isDarkMode ? 'table table-bordered table-hover table-dark' : 'table table-bordered table-hover'">
                <thead>
                    <tr>
                        <th scope="col" class="col-1">Time</th>
                        <th scope="col" class="col-11 text-center bg-success">
                            <h4>{{ selectedDate.toDateString().split(" ").slice(0, 3).join(" ") }}</h4>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="t in dayTimeData.hourTimeArray">
                        <th scope="row">{{ t }}</th>
                        <td></td>
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
                <button class="btn btn-secondary button-change-date" type="button" @click="prevWeek">
                    Prev Week
                </button>
                <button class="btn btn-secondary button-change-date" type="button" @click="prevDay">
                    Prev Day
                </button>
            </div>
            <div class="col text-center">
                <h3>{{ selectedDate.toDateString() }}</h3>
            </div>
            <div class="col justify-content-center d-flex flex-row gap-3 py-2">
                <button class="btn btn-secondary button-change-date" type="button" @click="nextDay">
                    Next Day
                </button>
                <button class="btn btn-secondary button-change-date" type="button" @click="nextWeek">
                    Next Week
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table :class="isDarkMode ? 'table table-bordered table-dark' : 'table table-bordered '">
                <thead>
                    <tr>
                        <th scope="col" class="col-1">Time</th>
                        <th scope="col" v-for="day in getWeekArray()" :class="day.isSelected ? 'bg-success bg-opacity-100' : 'day-cell'
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
                    <tr v-for="t in dayTimeData.hourTimeArray">
                        <th scope="row">{{ t }}</th>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
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
                <button class="btn btn-secondary button-change-date" type="button" @click="prevMonth">
                    Prev Month
                </button>
                <button class="btn btn-secondary button-change-date" type="button" @click="prevWeek">
                    Prev Week
                </button>
                <button class="btn btn-secondary button-change-date" type="button" @click="prevDay">
                    Prev Day
                </button>
            </div>
            <div class="col text-center">
                <h3>{{ getStringDate }}</h3>
            </div>
            <div class="col justify-content-center d-flex flex-row gap-3 py-2">
                <button class="btn btn-secondary button-change-date" type="button" @click="nextDay">
                    Next Day
                </button>
                <button class="btn btn-secondary button-change-date" type="button" @click="nextWeek">
                    Next Week
                </button>
                <button class="btn btn-secondary button-change-date" type="button" @click="nextMonth">
                    Next Month
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table :class="isDarkMode ? 'table table-bordered  table-dark' : 'table table-bordered'">
                <caption class="text-center caption-top">
                    {{ getMonthName(selectedDate?.getMonth()) }}
                </caption>
                <thead class="month-calendar-head">
                    <tr>
                        <th scope="col">Monday</th>
                        <th scope="col">Tuesday</th>
                        <th scope="col">Wednesday</th>
                        <th scope="col">Thursday</th>
                        <th scope="col">Friday</th>
                        <th scope="col">Saturday</th>
                        <th scope="col">Sunday</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(week, wi) in getPaddedMonthArray()">
                        <td v-for="(day, di) in week" :class="day?.isSelected ? 'bg-success bg-opacity-75 month-calendar-body' : 'day-cell month-calendar-body'
                            " @click="selectDateMonth(day)">
                            {{ day?.date.getDate() }}
                        </td>
                    </tr>
                </tbody>
            </table>
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
            selectedDate: new Date(), // must be stored as date object
            task: null, // stores task information start and due dates
            isDarkMode: true,
        };
    },
    computed: {
        // print the seltected date as a string
        getStringDate() {
            //return the date as a string, or an empty string if there's no date set.
            return this.selectedDate?.toDateString() ?? "";
        },
    },
    mounted() { },
    methods: {
        // for time marker in the day view
        getCurrentTime() {
            // returns the current time in HH:MM:SS format
            return new Date().toTimeString().split(" ")[0];
        },
        toggleDarkMode() {
            this.isDarkMode = !this.isDarkMode;
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
                });
            }
            this.checkSelectedDate(weekArray);
            return weekArray;
        },
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
        // returns an array of minutes in a day
        getDayTimeArray() {

        }
    },
};
</script>
<style>
.day-cell:hover {
    background-color: var(--bs-success);
    border-color: var(--bs-success);
    opacity: 0.5;
    transition: ease 0.5s;
}

.button-calendar-view:hover {
    background-color: var(--bs-success);
    border-color: var(--bs-success);
    opacity: 0.5;
    transition: ease 0.5s;
}

.button-change-date:hover {
    background-color: var(--bs-success);
    border-color: var(--bs-success);
    transition: ease 0.5s;
}

.month-calendar-head {
    width: 200px;
    text-align: center;
}

.month-calendar-body {
    width: 250px;
    height: 130px;
}
</style>
