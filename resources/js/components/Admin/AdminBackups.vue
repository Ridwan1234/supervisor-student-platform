<template>
  <div class="admin-backups">
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
                <i class="bi bi-download me-2"></i>Backups
              </h4>
              <p class="text-muted mb-0">Manage system backups and data recovery</p>
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
        <!-- Backup Stats -->
        <div class="row mb-4">
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-primary">
                    <i class="bi bi-download text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Total Backups</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.totalBackups }}</h3>
                    <small class="text-muted">{{ stats.successfulBackups }} successful</small>
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
                    <i class="bi bi-hdd text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Storage Used</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.storageUsed }}</h3>
                    <small class="text-muted">{{ stats.storagePercentage }}% of quota</small>
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
                    <i class="bi bi-clock text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Last Backup</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.lastBackupTime }}</h3>
                    <small class="text-muted">{{ stats.lastBackupStatus }}</small>
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
                    <i class="bi bi-calendar text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Next Backup</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.nextBackupTime }}</h3>
                    <small class="text-muted">Automated schedule</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Backup Actions -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="bi bi-gear me-2"></i>Backup Actions
                </h5>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-3">
                    <button class="btn btn-primary w-100" @click="createBackup" :disabled="creatingBackup">
                      <i class="bi bi-download me-1"></i>
                      {{ creatingBackup ? 'Creating...' : 'Create Backup' }}
                    </button>
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn-success w-100" @click="scheduleBackup">
                      <i class="bi bi-calendar-plus me-1"></i>Schedule Backup
                    </button>
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn-info w-100" @click="testBackup">
                      <i class="bi bi-check-circle me-1"></i>Test Backup
                    </button>
                  </div>
                  <div class="col-md-3">
                    <button class="btn btn-warning w-100" @click="configureBackup">
                      <i class="bi bi-gear me-1"></i>Configure
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Backup List -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>Backup History
                  </h5>
                  <div class="d-flex gap-2">
                    <select v-model="statusFilter" class="form-select form-select-sm" style="width: auto;">
                      <option value="all">All Status</option>
                      <option value="successful">Successful</option>
                      <option value="failed">Failed</option>
                      <option value="in_progress">In Progress</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <!-- Loading State -->
                <div v-if="loading" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Loading backups...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="alert alert-danger" role="alert">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  {{ error }}
                </div>

                <!-- Backups Table -->
                <div v-else class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Backup Name</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Duration</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="backup in backups" :key="backup.id">
                        <td>
                          <div>
                            <h6 class="mb-1">{{ backup.name }}</h6>
                            <small class="text-muted">{{ backup.description }}</small>
                          </div>
                        </td>
                        <td>
                          <span class="badge bg-secondary">{{ backup.type }}</span>
                        </td>
                        <td>{{ formatFileSize(backup.size) }}</td>
                        <td>
                          <span class="badge" :class="getStatusBadgeClass(backup.status)">
                            {{ formatStatus(backup.status) }}
                          </span>
                        </td>
                        <td>{{ formatDateTime(backup.created_at) }}</td>
                        <td>{{ backup.duration || 'N/A' }}</td>
                        <td>
                          <button class="btn btn-outline-secondary btn-sm me-1" title="Download" @click="downloadBackup(backup)">
                            <i class="bi bi-download"></i>
                          </button>
                          <button class="btn btn-outline-primary btn-sm me-1" title="Restore" @click="restoreBackup(backup)">
                            <i class="bi bi-arrow-clockwise"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm" title="Delete" @click="deleteBackup(backup)">
                            <i class="bi bi-trash"></i>
                          </button>
                        </td>
                      </tr>
                      <tr v-if="backups.length === 0">
                        <td colspan="7" class="text-center text-muted">No backups found.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <nav v-if="totalPages > 1" aria-label="Backup pagination">
                  <ul class="pagination justify-content-center">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                      <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
                    </li>
                    <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === currentPage }">
                      <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                      <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AdminSidebar from './AdminSidebar.vue';
import NotificationBell from '../NotificationBell.vue';
import { auth } from '../../utils/auth';

export default {
  name: 'AdminBackups',
  components: {
    AdminSidebar,
    NotificationBell
  },
  data() {
    return {
      currentUser: null,
      backups: [],
      loading: false,
      error: null,
      statusFilter: 'all',
      currentPage: 1,
      perPage: 15,
      total: 0,
      totalPages: 0,
      creatingBackup: false,
      stats: {
        totalBackups: 0,
        successfulBackups: 0,
        storageUsed: '0 MB',
        storagePercentage: 0,
        lastBackupTime: 'Never',
        lastBackupStatus: 'No backups',
        nextBackupTime: 'Not scheduled'
      }
    };
  },
  computed: {
    visiblePages() {
      const pages = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.totalPages, this.currentPage + 2);
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    }
  },
  mounted() {
    this.currentUser = auth.getUser();
    this.fetchBackups();
    this.fetchStats();
  },
  methods: {
    async fetchBackups() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = new URLSearchParams({
          page: this.currentPage,
          per_page: this.perPage
        });
        
        if (this.statusFilter && this.statusFilter !== 'all') {
          params.append('status', this.statusFilter);
        }

        const response = await fetch(`/api/admin/backups?${params}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch backups');
        }

        const data = await response.json();
        this.backups = data.data;
        this.currentPage = data.current_page;
        this.perPage = data.per_page;
        this.total = data.total;
        this.totalPages = data.last_page;
      } catch (error) {
        console.error('Error fetching backups:', error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchStats() {
      try {
        const response = await fetch('/api/admin/backup-stats', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          this.stats = data;
        }
      } catch (error) {
        console.error('Error fetching backup stats:', error);
      }
    },

    async createBackup() {
      this.creatingBackup = true;
      try {
        const response = await fetch('/api/admin/backups', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (response.ok) {
          // Refresh the list after creating backup
          this.fetchBackups();
          this.fetchStats();
        } else {
          throw new Error('Failed to create backup');
        }
      } catch (error) {
        console.error('Error creating backup:', error);
        this.error = error.message;
      } finally {
        this.creatingBackup = false;
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchBackups();
      }
    },

    formatDateTime(timestamp) {
      if (!timestamp) return 'N/A';
      return new Date(timestamp).toLocaleString();
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(1024));
      return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    },

    formatStatus(status) {
      const statusMap = {
        'successful': 'Successful',
        'failed': 'Failed',
        'in_progress': 'In Progress'
      };
      return statusMap[status] || status;
    },

    getStatusBadgeClass(status) {
      const classMap = {
        'successful': 'bg-success',
        'failed': 'bg-danger',
        'in_progress': 'bg-warning'
      };
      return classMap[status] || 'bg-secondary';
    },

    scheduleBackup() {
      // TODO: Implement backup scheduling
      console.log('Schedule backup');
    },

    testBackup() {
      // TODO: Implement backup testing
      console.log('Test backup');
    },

    configureBackup() {
      // TODO: Implement backup configuration
      console.log('Configure backup');
    },

    downloadBackup(backup) {
      // TODO: Implement backup download
      console.log('Download backup:', backup);
    },

    restoreBackup(backup) {
      // TODO: Implement backup restoration
      console.log('Restore backup:', backup);
    },

    deleteBackup(backup) {
      // TODO: Implement backup deletion
      console.log('Delete backup:', backup);
    },

    logout() {
      auth.logout();
      this.$router.push('/login');
    }
  },
  watch: {
    statusFilter() {
      this.currentPage = 1;
      this.fetchBackups();
    }
  }
};
</script>

<style scoped>
.admin-backups {
  display: flex;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  background-color: #f8f9fa;
  margin-left: 280px;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
}

.stat-card {
  transition: transform 0.2s;
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
}

.table td {
  vertical-align: middle;
}
</style>
