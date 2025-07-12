<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <SupervisorSidebar v-if="userRole === 'supervisor'" />
    <StudentSidebar v-else-if="userRole === 'student'" />
    <Sidebar v-else />

    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Navigation Bar -->
      <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid">
          <button class="navbar-toggler d-lg-none" type="button" @click="toggleSidebar">
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <div class="d-flex align-items-center">
            <h4 class="mb-0 fw-bold text-primary">
              {{ getDashboardTitle() }}
            </h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Search Bar -->
            <div class="position-relative">
              <input 
                type="text" 
                class="form-control form-control-sm" 
                placeholder="Search..." 
                v-model="searchQuery"
              />
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
            </div>

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
                <span class="d-none d-md-block">{{ user?.name || 'User' }}</span>
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
      <div class="container-fluid">
        <!-- Welcome Section -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card glass-effect">
              <div class="card-body">
                <h5 class="card-title fw-bold">Welcome back, {{ user?.name || getUserTitle() }}!</h5>
                <p class="card-text text-muted">{{ getWelcomeMessage() }}</p>
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

        <!-- Role-specific Content -->
        <div v-if="userRole === 'supervisor'" class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">Active Projects</h5>
                <router-link :to="{ name: 'project-management' }" class="btn btn-primary btn-sm">
                  <i class="bi bi-plus me-1"></i>New Project
                </router-link>
              </div>
              <div class="card-body">
                <ProjectsList />
              </div>
            </div>
          </div>
        </div>

        <div v-else-if="userRole === 'student'" class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">My Projects</h5>
                <router-link :to="{ name: 'student-project' }" class="btn btn-primary btn-sm">
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
                        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
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
import Sidebar from './Sidebar.vue';
import SupervisorSidebar from './Supervisor/SupervisorSidebar.vue';
import StudentSidebar from './Student/StudentSidebar.vue';
import OverviewCards from './OverviewCards.vue';
import ProjectsList from './ProjectsList.vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'Dashboard',
  components: {
    Sidebar,
    SupervisorSidebar,
    StudentSidebar,
    OverviewCards,
    ProjectsList,
  },
  data() {
    return {
      searchQuery: '',
      notificationCount: 0,
      notifications: [],
      user: null,
      userRole: 'supervisor', // This should be determined from localStorage or API
      recentActivities: [],
      loading: true,
      error: null
    }
  },
  async mounted() {
    await this.loadUserData();
    await this.fetchDashboardData();
  },
  methods: {
    async loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
      
      const role = localStorage.getItem('role');
      if (role) {
        this.userRole = role;
      }
    },
    
    async fetchDashboardData() {
      try {
        this.loading = true;
        this.error = null;
        
        const response = await fetch('/api/dashboard/stats', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (!response.ok) {
          throw new Error('Failed to fetch dashboard data');
        }
        
        const data = await response.json();
        
        this.notifications = data.data.notifications;
        this.notificationCount = data.data.notificationCount;
        this.recentActivities = data.data.recentActivities;
        
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        this.error = 'Failed to load dashboard data. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
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
    
    getDashboardTitle() {
      if (this.userRole === 'supervisor') {
        return 'Supervisor Dashboard';
      } else if (this.userRole === 'student') {
        return 'Student Dashboard';
      }
      return 'Dashboard';
    },
    
    getUserTitle() {
      if (this.userRole === 'supervisor') {
        return 'Supervisor';
      } else if (this.userRole === 'student') {
        return 'Student';
      }
      return 'User';
    },
    
    getWelcomeMessage() {
      if (this.userRole === 'supervisor') {
        return 'Here\'s what\'s happening with your projects today.';
      } else if (this.userRole === 'student') {
        return 'Track your progress and manage your academic projects.';
      }
      return 'Welcome to your dashboard.';
    }
  }
}
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
