<template>
  <div class="admin-dashboard">
    <!-- Sidebar -->
    <AdminSidebar />
    
    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <div class="header bg-white shadow-sm border-bottom">
        <div class="container-fluid">
          <div class="d-flex justify-content-between align-items-center py-3">
            <div>
              <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-shield-check me-2"></i>Admin Dashboard
              </h4>
              <p class="text-muted mb-0">System administration and user management</p>
            </div>
            <div class="d-flex align-items-center gap-3">
              <NotificationBell />
              <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                  <i class="bi bi-person-circle me-1"></i>
                  {{ currentUser?.name || 'Admin' }}
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#" @click="logout">Logout</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="container-fluid py-4">
        <!-- Welcome Section -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title fw-bold">Welcome back, {{ currentUser?.name || 'Administrator' }}!</h5>
                <p class="card-text text-muted">Here's an overview of your platform's activity and system status.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-primary">
                    <i class="bi bi-people text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Total Users</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.totalUsers }}</h3>
                    <small class="text-muted">{{ stats.newUsersThisMonth }} new this month</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-success">
                    <i class="bi bi-person-check text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Supervisors</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.supervisors }}</h3>
                    <small class="text-muted">{{ stats.activeSupervisors }} active</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-info">
                    <i class="bi bi-mortarboard text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Students</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.students }}</h3>
                    <small class="text-muted">{{ stats.activeStudents }} active</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-warning">
                    <i class="bi bi-folder text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Projects</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.projects }}</h3>
                    <small class="text-muted">{{ stats.activeProjects }} active</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0 fw-bold">Quick Actions</h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3 mb-3">
                    <router-link to="/admin/users" class="btn btn-outline-primary w-100">
                      <i class="bi bi-people me-2"></i>
                      Manage Users
                    </router-link>
                  </div>
                  <div class="col-md-3 mb-3">
                    <button class="btn btn-outline-success w-100" @click="createUser">
                      <i class="bi bi-person-plus me-2"></i>
                      Add User
                    </button>
                  </div>
                  <div class="col-md-3 mb-3">
                    <button class="btn btn-outline-info w-100" @click="viewSystemLogs">
                      <i class="bi bi-list-check me-2"></i>
                      System Logs
                    </button>
                  </div>
                  <div class="col-md-3 mb-3">
                    <button class="btn btn-outline-warning w-100" @click="backupSystem">
                      <i class="bi bi-download me-2"></i>
                      Backup System
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity & System Status -->
        <div class="row">
          <div class="col-lg-8 mb-4">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0 fw-bold">Recent Activity</h5>
              </div>
              <div class="card-body">
                <div v-if="loading" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                </div>
                <div v-else-if="recentActivity.length === 0" class="text-center py-4">
                  <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                  <p class="text-muted mt-2">No recent activity</p>
                </div>
                <div v-else class="activity-list">
                  <div v-for="activity in recentActivity" :key="activity.id" class="activity-item">
                    <div class="activity-icon">
                      <i :class="getActivityIcon(activity.type)"></i>
                    </div>
                    <div class="activity-content">
                      <div class="activity-text">{{ activity.description }}</div>
                      <div class="activity-meta">
                        <small class="text-muted">{{ formatTime(activity.created_at) }}</small>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4 mb-4">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0 fw-bold">System Status</h5>
              </div>
              <div class="card-body">
                <div class="status-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <span>Database</span>
                    <span class="badge bg-success">Online</span>
                  </div>
                </div>
                <div class="status-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <span>File Storage</span>
                    <span class="badge bg-success">Online</span>
                  </div>
                </div>
                <div class="status-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <span>Email Service</span>
                    <span class="badge bg-success">Online</span>
                  </div>
                </div>
                <div class="status-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <span>Notifications</span>
                    <span class="badge bg-success">Online</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import NotificationBell from '../NotificationBell.vue'
import AdminSidebar from './AdminSidebar.vue'

export default {
  name: 'AdminDashboard',
  components: {
    NotificationBell,
    AdminSidebar
  },
  setup() {
    const currentUser = ref(null)
    const stats = ref({
      totalUsers: 0,
      supervisors: 0,
      students: 0,
      projects: 0,
      newUsersThisMonth: 0,
      activeSupervisors: 0,
      activeStudents: 0,
      activeProjects: 0
    })
    const recentActivity = ref([])
    const loading = ref(true)

    const fetchDashboardData = async () => {
      try {
        loading.value = true
        const response = await axios.get('/api/admin/dashboard-stats')
        
        if (response.data && response.data.stats) {
          stats.value = response.data.stats
          recentActivity.value = response.data.recentActivity || []
        } else {
          console.warn('Invalid dashboard data format')
        }
      } catch (error) {
        console.error('Error fetching admin dashboard data:', error)
        if (error.response) {
          console.error('Response status:', error.response.status)
          console.error('Response data:', error.response.data)
        }
      } finally {
        loading.value = false
      }
    }

    const fetchCurrentUser = async () => {
      try {
        const response = await axios.get('/api/user')
        if (response.data) {
          currentUser.value = response.data
        }
      } catch (error) {
        console.error('Error fetching current user:', error)
        if (error.response) {
          console.error('Response status:', error.response.status)
          console.error('Response data:', error.response.data)
        }
      }
    }

    const getActivityIcon = (type) => {
      const icons = {
        'user_created': 'bi bi-person-plus text-success',
        'user_updated': 'bi bi-person-check text-info',
        'user_deleted': 'bi bi-person-x text-danger',
        'project_created': 'bi bi-folder-plus text-primary',
        'project_updated': 'bi bi-folder-check text-info',
        'system_backup': 'bi bi-download text-warning',
        'system_maintenance': 'bi bi-tools text-secondary'
      }
      return icons[type] || 'bi bi-circle text-muted'
    }

    const formatTime = (timestamp) => {
      return new Date(timestamp).toLocaleString()
    }

    const createUser = () => {
      // TODO: Implement user creation modal
      console.log('Create user clicked')
    }

    const viewSystemLogs = () => {
      // TODO: Implement system logs view
      console.log('View system logs clicked')
    }

    const backupSystem = () => {
      // TODO: Implement system backup
      console.log('Backup system clicked')
    }

    const logout = () => {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }

    onMounted(() => {
      fetchCurrentUser()
      fetchDashboardData()
    })

    return {
      currentUser,
      stats,
      recentActivity,
      loading,
      getActivityIcon,
      formatTime,
      createUser,
      viewSystemLogs,
      backupSystem,
      logout
    }
  }
}
</script>

<style scoped>
.admin-dashboard {
  min-height: 100vh;
  background-color: #f8f9fa;
  display: flex;
}

.main-content {
  flex: 1;
  margin-left: 280px;
}

.stat-card {
  border: none;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  transition: transform 0.15s ease-in-out;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.activity-list {
  max-height: 400px;
  overflow-y: auto;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  padding: 1rem 0;
  border-bottom: 1px solid #f1f3f4;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 1rem;
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-text {
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.activity-meta {
  font-size: 0.875rem;
}

.status-item {
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f3f4;
}

.status-item:last-child {
  border-bottom: none;
}

.card {
  border: none;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
  background-color: transparent;
  border-bottom: 1px solid #f1f3f4;
  padding: 1rem 1.25rem;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
}
</style> 