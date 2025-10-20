<template>
  <div class="admin-system-logs">
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
                <i class="bi bi-journal-text me-2"></i>System Logs
              </h4>
              <p class="text-muted mb-0">Monitor system activity and events</p>
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
        <!-- Log Filters -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="bi bi-funnel me-2"></i>Log Filters
                </h5>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-3">
                    <label class="form-label">Log Level</label>
                    <select v-model="levelFilter" class="form-select">
                      <option value="all">All Levels</option>
                      <option value="error">Error</option>
                      <option value="warning">Warning</option>
                      <option value="info">Info</option>
                      <option value="debug">Debug</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select v-model="dateFilter" class="form-select">
                      <option value="today">Today</option>
                      <option value="yesterday">Yesterday</option>
                      <option value="week">This Week</option>
                      <option value="month">This Month</option>
                      <option value="custom">Custom Range</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input v-model="search" type="text" class="form-control" placeholder="Search logs..." />
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100" @click="fetchLogs">
                      <i class="bi bi-search me-1"></i>Search
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Logs Table -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>System Logs
                  </h5>
                  <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" @click="exportLogs">
                      <i class="bi bi-download me-1"></i>Export
                    </button>
                    <button class="btn btn-outline-danger btn-sm" @click="clearLogs">
                      <i class="bi bi-trash me-1"></i>Clear Logs
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <!-- Loading State -->
                <div v-if="loading" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Loading logs...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="alert alert-danger" role="alert">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  {{ error }}
                </div>

                <!-- Logs Table -->
                <div v-else class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Timestamp</th>
                        <th>Level</th>
                        <th>Message</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="log in logs" :key="log.id" :class="getLogRowClass(log.level)">
                        <td>{{ formatDateTime(log.timestamp) }}</td>
                        <td>
                          <span class="badge" :class="getLogLevelClass(log.level)">
                            {{ log.level.toUpperCase() }}
                          </span>
                        </td>
                        <td>
                          <div>
                            <strong>{{ log.message }}</strong>
                            <br>
                            <small class="text-muted">{{ log.context }}</small>
                          </div>
                        </td>
                        <td>{{ log.user?.name || 'System' }}</td>
                        <td>{{ log.ip_address || 'N/A' }}</td>
                        <td>
                          <button class="btn btn-outline-secondary btn-sm" title="View Details" @click="viewLogDetails(log)">
                            <i class="bi bi-eye"></i>
                          </button>
                        </td>
                      </tr>
                      <tr v-if="logs.length === 0">
                        <td colspan="6" class="text-center text-muted">No logs found.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <nav v-if="totalPages > 1" aria-label="Log pagination">
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

    <!-- Log Detail Modal -->
    <AdminLogDetailModal
      :visible="showLogDetailModal"
      :log-id="selectedLogId"
      @close="closeLogDetailModal"
      @log-action="handleLogAction"
    />
  </div>
</template>

<script>
import AdminSidebar from './AdminSidebar.vue';
import NotificationBell from '../NotificationBell.vue';
import AdminLogDetailModal from './AdminLogDetailModal.vue';
import { auth } from '../../utils/auth';

export default {
  name: 'AdminSystemLogs',
  components: {
    AdminSidebar,
    NotificationBell,
    AdminLogDetailModal
  },
  data() {
    return {
      currentUser: null,
      logs: [],
      loading: false,
      error: null,
      search: '',
      levelFilter: 'all',
      dateFilter: 'today',
      currentPage: 1,
      perPage: 50,
      showLogDetailModal: false,
      selectedLogId: null,
      total: 0,
      totalPages: 0
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
    this.fetchLogs();
  },
  methods: {
    async fetchLogs() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = new URLSearchParams({
          page: this.currentPage,
          per_page: this.perPage
        });
        
        if (this.search) {
          params.append('search', this.search);
        }
        
        if (this.levelFilter && this.levelFilter !== 'all') {
          params.append('level', this.levelFilter);
        }
        
        if (this.dateFilter) {
          params.append('date_filter', this.dateFilter);
        }

        const response = await fetch(`/api/admin/logs?${params}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch logs');
        }

        const data = await response.json();
        this.logs = data.data;
        this.currentPage = data.current_page;
        this.perPage = data.per_page;
        this.total = data.total;
        this.totalPages = data.last_page;
      } catch (error) {
        console.error('Error fetching logs:', error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchLogs();
      }
    },

    formatDateTime(timestamp) {
      if (!timestamp) return 'N/A';
      return new Date(timestamp).toLocaleString();
    },

    getLogLevelClass(level) {
      const classMap = {
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info',
        'debug': 'bg-secondary'
      };
      return classMap[level] || 'bg-secondary';
    },

    getLogRowClass(level) {
      const classMap = {
        'error': 'table-danger',
        'warning': 'table-warning'
      };
      return classMap[level] || '';
    },

    viewLogDetails(log) {
      this.selectedLogId = log.id;
      this.showLogDetailModal = true;
    },

    closeLogDetailModal() {
      this.showLogDetailModal = false;
      this.selectedLogId = null;
    },

    handleLogAction(log) {
      // TODO: Implement log action functionality
      console.log('Handle log action:', log);
    },

    exportLogs() {
      // TODO: Implement log export
      console.log('Export logs');
    },

    clearLogs() {
      // TODO: Implement log clearing
      console.log('Clear logs');
    },

    logout() {
      auth.logout();
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
.admin-system-logs {
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

.table td {
  vertical-align: middle;
}
</style>
