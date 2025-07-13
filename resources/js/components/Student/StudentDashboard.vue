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
            <NotificationBell />

            <!-- User Profile -->
            <div class="dropdown position-relative">
              <button class="btn btn-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://via.placeholder.com/32" alt="Profile" class="rounded-circle" width="32" height="32" />
                <span class="d-none d-md-block">{{ user?.name || 'Student' }}</span>
                <i class="bi bi-chevron-down"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end profile-dropdown">
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
        <!-- <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <ProjectsList />
              </div>
            </div>
          </div>
        </div> -->

        <!-- Quick Actions -->
        <div class="row mb-4">
          <div class="col-md-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                  <i class="bi bi-folder2-open text-white fs-4"></i>
                </div>
                <h6 class="card-title">My Files</h6>
                <p class="card-text text-muted small">Upload and manage your project files</p>
                <router-link :to="{ name: 'student-files' }" class="btn btn-primary btn-sm">
                  View Files
                </router-link>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                  <i class="bi bi-folder text-white fs-4"></i>
                </div>
                <h6 class="card-title">My Projects</h6>
                <p class="card-text text-muted small">View your assigned projects</p>
                <router-link :to="{ name: 'student-project' }" class="btn btn-success btn-sm">
                  View Projects
                </router-link>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card h-100">
              <div class="card-body text-center">
                <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 60px; height: 60px;">
                  <i class="bi bi-list-check text-white fs-4"></i>
                </div>
                <h6 class="card-title">My Tasks</h6>
                <p class="card-text text-muted small">Manage your assigned tasks</p>
                <router-link :to="{ name: 'student-task' }" class="btn btn-warning btn-sm">
                  View Tasks
                </router-link>
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
import NotificationBell from '../NotificationBell.vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'StudentDashboard',
  components: {
    StudentSidebar,
    OverviewCards,
    ProjectsList,
    NotificationBell,
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

/* Dropdown positioning fixes */
.notification-dropdown,
.profile-dropdown {
  z-index: 1050 !important;
  position: absolute !important;
  top: 100% !important;
  right: 0 !important;
  min-width: 280px;
  margin-top: 0.125rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 0.375rem;
}

.notification-dropdown {
  max-height: 400px;
  overflow-y: auto;
}

.dropdown-menu.show {
  display: block !important;
  opacity: 1 !important;
  transform: translateY(0) !important;
}

/* Ensure navbar has proper z-index */
.navbar {
  z-index: 1030;
  position: relative;
}

/* Dropdown container positioning */
.dropdown.position-relative {
  position: relative !important;
}
</style>
