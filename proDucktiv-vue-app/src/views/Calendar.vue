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
                            <tr v-for="(week, wi) in weeks()">
                                <td v-for="(day, di) in week">
                                    {{ day || "" }}
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
        // Out put a weeks array that calaculate the weeks in each month
        // each week is an array of 7 days (mon-sun)
        // with null padding for empty days in a week
        weeks() {
            // total days in the month
            const totalDays = this.getTotalDaysInMonth();
            // get the first day in selected month
            const firstDay = this.getMonthFirstDay();
            // get the last day in selected month
            const lastDay = this.getMonthLastDay();

            // the getDay() method gets the days in Sun-sat order
            // meaning that sun is index 0 and mon is index 1
            // so we can use a remainder or modulo operator % to off set
            // this way it fits our mon-sun week order
            // thus monday (1) -> (1 + 6) = 7 -> 7 % 7  = 0
            const startWeekOffset = (firstDay.getDay() + 6) % 7;
            // last day off set
            const lastDayMonStart = (lastDay.getDay() + 6) % 7;
            const endWeekOffset =
                lastDayMonStart === 6 ? 0 : 6 - lastDayMonStart;
            // month array with null padding using the first day as an off set
            const month = [
                // the ... spread operator merges the nested ar0rays into 1
                // uses the firstday to add null padding to the month array
                ...Array(startWeekOffset).fill(null),
                // creates an array with the length of the total month days
                // (_, i) is a mapping function the _ is the current value
                // since we are using a integer and not an array we use _ to mean undefined
                ...Array.from({ length: totalDays }, (_, i) => i + 1),
                ...Array(endWeekOffset).fill(null),
            ];
            // split the month array into diffrent rows of 7
            const weeks = [];
            for (let i = 0; i < month.length; i += 7) {
                weeks.push(month.slice(i, i + 7));
            }
            return weeks;
        },
    },
};
</script>
