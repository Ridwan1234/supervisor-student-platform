import { createRouter, createWebHistory } from "vue-router";
import Login from "../components/Auth/Login.vue";
// import Logout from "../components/Auth/Logout.vue";
import Register from "../components/Auth/Register.vue";
import Dashboard from '../components/Dashboard.vue';
import Home from '../components/Home.vue';
import SupervisorDashboard from '../components/Supervisor/SupervisorDashboard.vue';
import StudentDashboard from '../components/Student/StudentDashboard.vue';
import Tasks from '../components/Supervisor/Task.vue';
import NotFound from '../components/NotFound.vue';

const routes = [
  { path: "/",
    name: "home",
    component: Home
},
{ path: "/login",
    name: "login",
    component: Login
},
{ path: "/logout",
    name: "logout",
    component: Home
},
  {
    path: "/dashboard",
    name: "dashboard",
    component: Dashboard,
    beforeEnter: (to, from, next) => {
      const role = localStorage.getItem("role");
      if (role === "supervisor") {
        next({ name: "supervisor-dashboard" });
      } else if (role === "student") {
        next({ name: "student-dashboard" });
      } else {
        next();
      }
    },
  },
  { path: "/supervisor-dashboard", name: "supervisor-dashboard", component: SupervisorDashboard },
  { path: "/student-dashboard", name: "student-dashboard", component: StudentDashboard },
  { path: "/register", name: "register", component: Register },
  { path: "/tasks", name: "tasks", component: Tasks },
  { path: "/:pathMatch(.*)*", name: "NotFound", component: NotFound },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem("token");
  if (to.name !== "login" && !token) {
    next({ name: "login" });
  } else {
    next();
  }
});

export default router;
