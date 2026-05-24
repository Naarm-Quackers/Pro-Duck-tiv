import { createRouter, createWebHistory } from "vue-router";
import Welcome from "../views/Welcome.vue";
import About from "../views/About.vue";
import SignIn from "../views/SignIn.vue";
import Registration from "../views/Registration.vue";
import Account from "../views/Account.vue";
import Task from "../views/Task.vue";
import TaskCreator from "../views/TaskCreator.vue";
import TaskEditor from "../views/TaskEditor.vue";
import Calendar from "../views/Calendar.vue";
import Journal from "../views/Journal.vue";

const routes = [
  { path: "/:userId?", component: Welcome },
  { path: "/about/:userId?", component: About },
  { path: "/sign-in", component: SignIn },
  { path: "/registration", component: Registration },
  { path: "/account/:userId?", component: Account },
  { path: "/task/:userId?", component: Task },
  { path: "/task-creator/:userId?", component: TaskCreator },
  { path: "/task-editor/:userId?/:id?", component: TaskEditor },
  { path: "/calendar/:userId?", component: Calendar },
  { path: "/journal/:userId?", component: Journal },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});
export default router;
