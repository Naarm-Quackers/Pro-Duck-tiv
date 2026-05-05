import { createRouter, createWebHistory } from 'vue-router'
import Welcome from '../views/Welcome.vue'
import About from '../views/About.vue'
import SignIn from '../views/SignIn.vue'
import Registration from '../views/Registration.vue'
import Account from '../views/Account.vue'
import Task from '../views/Task.vue'
import TaskCreator from '../views/TaskCreator.vue'
import TaskEditor from '../views/TaskEditor.vue'
import Calendar from '../views/Calendar.vue'
import Journal from '../views/Journal.vue'

const routes = [
    { path: '/', component: Welcome },
    { path: '/about', component: About },
    { path: '/sign-in', component: SignIn },
    { path: '/registration', component: Registration },
    { path: '/account', component: Account },
    { path: '/task', component: Task },
    { path: '/task-creator', component: TaskCreator },
    { path: '/task-editor', component: TaskEditor },
    { path: '/calendar', component: Calendar },
    { path: '/journal', component: Journal }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})
export default router
