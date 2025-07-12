<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <StudentSidebar />

    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Navigation Bar -->
      <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid">
          <button class="navbar-toggler d-lg-none" type="button" @click="toggleSidebar">
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <div class="d-flex align-items-center">
            <h4 class="mb-0 fw-bold text-success">Student Dashboard</h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            <div class="dropdown">
              <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span v-if="notificationCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  {{ notificationCount }}
                </span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li v-for="notification in notifications" :key="notification.id">
                  <a class="dropdown-item" href="#">{{ notification.message }}</a>
                </li>
              </ul>
            </div>

            <!-- User Profile -->
            <div class="dropdown">
              <button class="btn btn-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <img src="https://via.placeholder.com/32" alt="Profile" class="rounded-circle" width="32" height="32" />
                <span class="d-none d-md-block">{{ user?.name || 'Student' }}</span>
                <i class="bi bi-chevron-down"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" @click="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <!-- Dashboard Content -->
      <div>
        <!-- Welcome Section -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card glass-effect">
              <div class="card-body">
                <h5 class="card-title fw-bold">Welcome back, {{ user?.name || 'Student' }}!</h5>
                <p class="card-text text-muted">Track your progress and manage your assignments.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Overview Cards -->
        <div class="row mb-4">
          <div class="col-12">
            <OverviewCards />
          </div>
        </div>

        <!-- My Projects Section -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">My Projects</h5>
                <router-link :to="{ name: 'student-project' }" class="btn btn-success btn-sm">
                  <i class="bi bi-eye me-1"></i>View All
                </router-link>
              </div>
              <div class="card-body">
                <ProjectsList />
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="row mt-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0 fw-bold">Recent Activity</h5>
              </div>
              <div class="card-body">
                <div class="list-group list-group-flush">
                  <div v-for="activity in recentActivities" :key="activity.id" class="list-group-item border-0 px-0">
                    <div class="d-flex align-items-center">
                      <div class="flex-shrink-0">
                        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                          <i class="bi bi-person text-white"></i>
                        </div>
                      </div>
                      <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">{{ activity.title }}</h6>
                        <p class="mb-0 text-muted small">{{ activity.description }}</p>
                        <small class="text-muted">{{ activity.time }}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import StudentSidebar from './StudentSidebar.vue';
import OverviewCards from '../OverviewCards.vue';
import ProjectsList from '../ProjectsList.vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'StudentDashboard',
  components: {
    StudentSidebar,
    OverviewCards,
    ProjectsList,
  },
  data() {
    return {
      searchQuery: '',
      notificationCount: 2,
      notifications: [
        { id: 1, message: 'New task assigned by supervisor' },
        { id: 2, message: 'Project deadline reminder' },
      ],
      user: null,
      recentActivities: [
        {
          id: 1,
          title: 'Task Completed',
          description: 'Submitted research analysis for Project Alpha',
          time: '1 hour ago'
        },
        {
          id: 2,
          title: 'Progress Updated',
          description: 'Updated project progress to 75%',
          time: '3 hours ago'
        },
        {
          id: 3,
          title: 'Meeting Scheduled',
          description: 'Weekly progress meeting with supervisor',
          time: '1 day ago'
        }
      ]
    };
  },
  mounted() {
    this.loadUserData();
  },
  methods: {
    toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    },
    logout() {
      const toast = useToast();
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      localStorage.removeItem('role');
      this.$router.push('/login');
      toast.success('Logged out successfully');
    },
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
    }
  }
};
</script>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  background-color: #f8f9fa;
  display: flex;
}

.main-content {
  flex: 1;
  padding: 0;
  margin-left: 20px;
}

.navbar {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.card {
  border: none;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  transition: all 0.15s ease-in-out;
}

.glass-effect {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
  
  .sidebar {
    position: fixed;
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .sidebar.show {
    transform: translateX(0);
  }
}
</style>
