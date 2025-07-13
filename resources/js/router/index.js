import { createRouter, createWebHistory } from "vue-router";
import Login from "../components/Auth/Login.vue";
// import Logout from "../components/Auth/Logout.vue";
import Register from "../components/Auth/Register.vue";
import Dashboard from '../components/Dashboard.vue';
import Home from '../components/Home.vue';
import SupervisorDashboard from '../components/Supervisor/SupervisorDashboard.vue';
import StudentDashboard from '../components/Student/StudentDashboard.vue';
import Tasks from '../components/Supervisor/Task.vue';
import TaskManagement from '../components/Supervisor/TaskManagement.vue';
import ExpertiseManagement from '../components/Supervisor/ExpertiseManagement.vue';
import ProjectManagement from '../components/Supervisor/ProjectManagement.vue';
import GroupManagement from '../components/Supervisor/GroupManagement.vue';
import FileManagement from '../components/FileManagement.vue';
import SupervisorFileManagement from '../components/Supervisor/SupervisorFileManagement.vue';
import Messaging from '../components/Messaging.vue';
import SupervisorMessaging from '../components/Supervisor/SupervisorMessaging.vue';
import StudentMessaging from '../components/Student/StudentMessaging.vue';
import StudentProject from '../components/Student/Project.vue';
import StudentTask from '../components/Student/Task.vue';
import StudentFileManagement from '../components/Student/StudentFileManagement.vue';
import Notifications from '../components/NotificationBell.vue';
import NotFound from '../components/NotFound.vue';
import { auth } from '../utils/auth';

const routes = [
  { 
    path: "/",
    name: "home",
    component: Home
  },
  { 
    path: "/login",
    name: "login",
    component: Login
  },
  { 
    path: "/logout",
    name: "logout",
    component: Home
  },
  {
    path: "/dashboard",
    name: "dashboard",
    component: Dashboard,
    beforeEnter: (to, from, next) => {
      const user = auth.getUser();
      if (user && user.role === "supervisor") {
        next({ name: "supervisor-dashboard" });
      } else if (user && user.role === "student") {
        next({ name: "student-dashboard" });
      } else {
        next();
      }
    },
  },
  { 
    path: "/supervisor-dashboard", 
    name: "supervisor-dashboard", 
    component: SupervisorDashboard 
  },
  { 
    path: "/student-dashboard", 
    name: "student-dashboard", 
    component: StudentDashboard 
  },
  { 
    path: "/register", 
    name: "register", 
    component: Register 
  },
  { 
    path: "/tasks", 
    name: "tasks", 
    component: Tasks 
  },
  { 
    path: "/task-management", 
    name: "task-management", 
    component: TaskManagement 
  },
  { 
    path: "/expertise-management", 
    name: "expertise-management", 
    component: ExpertiseManagement 
  },
  { 
    path: "/project-management", 
    name: "project-management", 
    component: ProjectManagement 
  },
  { 
    path: "/group-management", 
    name: "group-management", 
    component: GroupManagement 
  },
  { 
    path: "/file-management", 
    name: "file-management", 
    component: SupervisorFileManagement 
  },
  { 
    path: "/messaging", 
    name: "messaging", 
    component: Messaging,
    beforeEnter: (to, from, next) => {
      const user = auth.getUser();
      if (user && user.role === "supervisor") {
        next({ name: "supervisor-messaging" });
      } else if (user && user.role === "student") {
        next({ name: "student-messaging" });
      } else {
        next();
      }
    },
  },
  { 
    path: "/supervisor-messaging", 
    name: "supervisor-messaging", 
    component: SupervisorMessaging 
  },
  { 
    path: "/student-messaging", 
    name: "student-messaging", 
    component: StudentMessaging 
  },
  { 
    path: "/student-project", 
    name: "student-project", 
    component: StudentProject 
  },
  { 
    path: "/student-task", 
    name: "student-task", 
    component: StudentTask 
  },
  { 
    path: "/student-files", 
    name: "student-files", 
    component: StudentFileManagement 
  },
  { 
    path: "/notifications", 
    name: "notifications", 
    component: Notifications 
  },
  { 
    path: "/:pathMatch(.*)*", 
    name: "NotFound", 
    component: NotFound 
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const isAuthenticated = auth.isAuthenticated();
  
  // Public routes that don't require authentication
  const publicRoutes = ['login', 'register', 'home'];
  
  if (!isAuthenticated && !publicRoutes.includes(to.name)) {
    // Redirect to login if not authenticated and trying to access protected route
    next({ name: "login" });
  } else if (isAuthenticated && (to.name === 'login' || to.name === 'register')) {
    // Redirect authenticated users away from login/register pages
    const user = auth.getUser();
    if (user && user.role === 'supervisor') {
      next({ name: "supervisor-dashboard" });
    } else if (user && user.role === 'student') {
      next({ name: "student-dashboard" });
    } else {
      next({ name: "dashboard" });
    }
  } else {
    next();
  }
});

export default router;
