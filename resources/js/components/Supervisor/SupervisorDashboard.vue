<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <SupervisorSidebar />

    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Navigation Bar -->
      <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid">
          <button class="navbar-toggler d-lg-none" type="button" @click="toggleSidebar">
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <div class="d-flex align-items-center">
            <h4 class="mb-0 fw-bold text-primary">Supervisor Dashboard</h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            <NotificationBell />

            <!-- User Profile -->
            <div class="dropdown position-relative">
              <button class="btn btn-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://via.placeholder.com/32" alt="Profile" class="rounded-circle" width="32" height="32" />
                <span class="d-none d-md-block">{{ user?.name || 'Supervisor' }}</span>
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
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3 text-muted">Loading dashboard...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          {{ error }}
          <button class="btn btn-outline-danger btn-sm ms-3" @click="fetchDashboardData">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Retry
          </button>
        </div>

        <!-- Dashboard Content -->
        <div v-else>
          <!-- Welcome Section -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="card glass-effect">
                <div class="card-body">
                  <h5 class="card-title fw-bold">Welcome back, {{ user?.name || 'Supervisor' }}!</h5>
                  <p class="card-text text-muted">Here's what's happening with your projects today.</p>
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

          <!-- Active Projects Section -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0 fw-bold">Active Projects</h5>
                  <router-link :to="{ name: 'project-management' }" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus me-1"></i>New Project
                  </router-link>
                </div>
                <div class="card-body">
                  <ProjectsList 
                    @view-project="viewProject"
                    @project-deleted="handleProjectDeleted"
                  />
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
                  <div v-if="recentActivities.length === 0" class="text-center py-3">
                    <p class="text-muted">No recent activity</p>
                  </div>
                  <div v-else class="list-group list-group-flush">
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
      </div>
    </main>

    <!-- Project View Modal -->
    <div class="modal fade" id="projectViewModal" tabindex="-1" v-show="showProjectModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Project Details</h5>
            <button type="button" class="btn-close" @click="closeProjectModal"></button>
          </div>
          <div class="modal-body" v-if="selectedProject">
            <div class="row">
              <div class="col-md-8">
                <h4>{{ selectedProject.title }}</h4>
                <p class="text-muted">{{ selectedProject.description }}</p>
              </div>
              <div class="col-md-4">
                <span class="badge" :class="getStatusClass(selectedProject.status)">
                  {{ formatStatus(selectedProject.status) }}
                </span>
              </div>
            </div>
            
            <div class="row mt-3">
              <div class="col-md-6">
                <h6>Progress</h6>
                <div class="progress mb-2">
                  <div class="progress-bar" :class="getProgressClass(selectedProject.progress || 0)" 
                       :style="{ width: (selectedProject.progress || 0) + '%' }"></div>
                </div>
                <small>{{ selectedProject.progress || 0 }}% Complete</small>
              </div>
              <div class="col-md-6">
                <h6>Timeline</h6>
                <p><strong>Start:</strong> {{ formatDate(selectedProject.start_date) }}</p>
                <p><strong>Due:</strong> {{ formatDate(selectedProject.due_date) }}</p>
              </div>
            </div>
            
            <div class="mt-3">
              <h6>Assigned Students</h6>
              <div v-if="selectedProject.students && selectedProject.students.length > 0" class="d-flex flex-wrap gap-2">
                <div v-for="student in selectedProject.students" :key="student.id" 
                     class="d-flex align-items-center bg-light rounded p-2">
                  <img :src="student.avatar || 'https://via.placeholder.com/30'" :alt="student.name" 
                       class="rounded-circle me-2" style="width: 30px; height: 30px;">
                  <span>{{ student.name }}</span>
                </div>
              </div>
              <p v-else class="text-muted">No students assigned</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeProjectModal">Close</button>
            <button type="button" class="btn btn-primary" @click="editProject(selectedProject)">Edit Project</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SupervisorSidebar from './SupervisorSidebar.vue';
import OverviewCards from '../OverviewCards.vue';
import ProjectsList from '../ProjectsList.vue';
import NotificationBell from '../NotificationBell.vue';
import { useToast } from 'vue-toastification';

export default {
  name: 'SupervisorDashboard',
  components: {
    SupervisorSidebar,
    OverviewCards,
    ProjectsList,
    NotificationBell,
  },
  data() {
    return {
      searchQuery: '',
      notificationCount: 0,
      notifications: [],
      user: null,
      recentActivities: [],
      loading: true,
      error: null,
      showProjectModal: false,
      selectedProject: null
    };
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
        
        this.notifications = data.data.notifications || [];
        this.notificationCount = data.data.notificationCount || 0;
        this.recentActivities = data.data.recentActivities || [];
        
      } catch (error) {
        console.error('Error fetching dashboard data:', error);
        this.error = 'Failed to load dashboard data. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    filterProjects() {
      // This will be handled by the ProjectsList component
      // You can add filtering logic here if needed
    },
    
    viewProject(project) {
      this.selectedProject = project;
      this.showProjectModal = true;
      this.$nextTick(() => {
        const modal = document.getElementById('projectViewModal');
        if (modal && window.bootstrap) {
          try {
            const bootstrapModal = new window.bootstrap.Modal(modal);
            bootstrapModal.show();
          } catch (error) {
            console.error('Error opening project modal:', error);
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
          }
        }
      });
    },
    
    editProject(project) {
      this.closeProjectModal();
      this.$router.push({ 
        name: 'project-management',
        query: { edit: project.id }
      });
    },
    
    closeProjectModal() {
      const modal = document.getElementById('projectViewModal');
      if (modal && window.bootstrap) {
        try {
          const bootstrapModal = window.bootstrap.Modal.getInstance(modal);
          if (bootstrapModal) {
            bootstrapModal.hide();
          }
        } catch (error) {
          console.error('Error closing project modal:', error);
          modal.style.display = 'none';
          modal.classList.remove('show');
          document.body.classList.remove('modal-open');
          const backdrop = document.querySelector('.modal-backdrop');
          if (backdrop) {
            backdrop.remove();
          }
        }
      }
      this.showProjectModal = false;
      this.selectedProject = null;
    },
    
    handleProjectDeleted(project) {
      const toast = useToast();
      toast.success(`Project "${project.title}" deleted successfully`);
    },
    
    getStatusClass(status) {
      const statusClasses = {
        'in_progress': 'bg-warning text-dark',
        'completed': 'bg-success',
        'not_started': 'bg-info',
        'on_hold': 'bg-secondary'
      };
      return statusClasses[status] || 'bg-secondary';
    },
    
    formatStatus(status) {
      const statusMap = {
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'not_started': 'Not Started',
        'on_hold': 'On Hold'
      };
      return statusMap[status] || status;
    },
    
    getProgressClass(progress) {
      if (progress >= 80) return 'bg-success';
      if (progress >= 50) return 'bg-warning';
      return 'bg-info';
    },
    
    formatDate(date) {
      if (!date) return 'No date set';
      return new Date(date).toLocaleDateString();
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
