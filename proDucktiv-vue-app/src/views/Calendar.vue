<script setup>
import dayTimeData from "../assets/data/calendarDayData.json";
import monthData from "../assets/data/calendarMonthData.json";
</script>
<template>
    <div class="container">
        <div class="row">
            <div class="col">
                <h1>Calendar</h1>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <button type="button" class="btn" @click="selectedView = 'day'">
                    Daily
                </button>
                <button
                    type="button"
                    class="btn"
                    @click="selectedView = 'week'"
                >
                    Weekly
                </button>
                <button
                    type="button"
                    class="btn"
                    @click="selectedView = 'month'"
                >
                    Monthly
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <!-- DAY -->
                <div v-if="selectedView === 'day'">
                    <h2>Daily Calendar</h2>
                    <button class="btn" type="button" @click="prevDay">
                        Previous
                    </button>
                    <button class="btn" type="button" @click="nextDay">
                        Next
                    </button>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Time</th>
                                <th scope="col">
                                    <h4>{{ getStringDate }}</h4>
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
                <!-- WEEK -->
                <div v-if="selectedView === 'week'">
                    <h2>Weekly Calendar</h2>
                    <button class="btn" type="button" @click="prevWeek">
                        Previous
                    </button>
                    <button class="btn" type="button" @click="nextWeek">
                        Next
                    </button>
                    <h2>{{ getStringDate }}</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">Time</th>
                                <th scope="col" v-for="day in getWeekArray()">
                                    {{
                                        day
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
                <!-- MONTH -->
                <div v-if="selectedView === 'month'">
                    <h2>Monthly Calendar</h2>
                    <button class="btn" type="button" @click="prevMonth">
                        Previous
                    </button>
                    <button class="btn" type="button" @click="nextMonth">
                        Next
                    </button>
                    <h2>Selected Date: {{ getStringDate }}</h2>
                    <h1>{{ getMonthName(selectedDate?.getMonth()) }}</h1>
                    <table class="table table-striped-columns">
                        <thead>
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
                                <td v-for="(day, di) in week">
                                    {{ day?.getDate() || "" }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
// for daily calendar view use a table layout with the time on the side column
// nested router for viewing individual tasks and journal entries
export default {
    data() {
        return {
            calendarView: ["day", "week", "month"],
            selectedView: "day",
            selectedDate: null, // must be stored as date object
            task: null, // stores task information start and due dates
        };
    },
    computed: {
        // print the seltected date as a string
        getStringDate() {
            //return the date as a string, or an empty string if there's no date set.
            return this.selectedDate?.toDateString() ?? "";
        },
    },
    mounted() {
        // on mount loads the current date into the calendar view
        this.selectedDate = this.getCurrentDate();
    },
    methods: {
        // time iterator for day
        // gets the current date
        getCurrentDate() {
            return new Date();
        },
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
                monthArray.push(
                    new Date(
                        this.selectedDate.getFullYear(),
                        this.selectedDate.getMonth(),
                        1 + i,
                    ),
                );
            }
            return monthArray;
        },
        // reutrns padded month array with week split
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
                weekArray.push(
                    new Date(
                        monday.getFullYear(),
                        monday.getMonth(),
                        monday.getDate() + i,
                    ),
                );
            }
            return weekArray;
        },
    },
};
</script>
