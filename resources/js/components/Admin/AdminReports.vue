<template>
  <div class="admin-reports">
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
                <i class="bi bi-graph-up me-2"></i>Reports
              </h4>
              <p class="text-muted mb-0">Generate and view system reports and analytics</p>
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
        <!-- Report Types -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="bi bi-file-earmark-text me-2"></i>Generate Reports
                </h5>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-4">
                    <div class="report-card">
                      <div class="card h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-people text-primary fs-1 mb-3"></i>
                          <h6 class="card-title">User Activity Report</h6>
                          <p class="card-text text-muted">Track user registrations, logins, and activity patterns</p>
                          <button class="btn btn-primary" @click="generateUserReport">
                            <i class="bi bi-download me-1"></i>Generate
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                    <div class="report-card">
                      <div class="card h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-folder text-success fs-1 mb-3"></i>
                          <h6 class="card-title">Project Progress Report</h6>
                          <p class="card-text text-muted">Monitor project completion rates and timelines</p>
                          <button class="btn btn-success" @click="generateProjectReport">
                            <i class="bi bi-download me-1"></i>Generate
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                    <div class="report-card">
                      <div class="card h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-list-check text-info fs-1 mb-3"></i>
                          <h6 class="card-title">Task Performance Report</h6>
                          <p class="card-text text-muted">Analyze task completion rates and efficiency</p>
                          <button class="btn btn-info" @click="generateTaskReport">
                            <i class="bi bi-download me-1"></i>Generate
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                    <div class="report-card">
                      <div class="card h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-file-earmark text-warning fs-1 mb-3"></i>
                          <h6 class="card-title">File Usage Report</h6>
                          <p class="card-text text-muted">Track file uploads, downloads, and storage usage</p>
                          <button class="btn btn-warning" @click="generateFileReport">
                            <i class="bi bi-download me-1"></i>Generate
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                    <div class="report-card">
                      <div class="card h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-graph-up text-danger fs-1 mb-3"></i>
                          <h6 class="card-title">System Performance Report</h6>
                          <p class="card-text text-muted">Monitor system performance and resource usage</p>
                          <button class="btn btn-danger" @click="generateSystemReport">
                            <i class="bi bi-download me-1"></i>Generate
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                    <div class="report-card">
                      <div class="card h-100">
                        <div class="card-body text-center">
                          <i class="bi bi-calendar text-secondary fs-1 mb-3"></i>
                          <h6 class="card-title">Custom Date Range Report</h6>
                          <p class="card-text text-muted">Generate custom reports for specific date ranges</p>
                          <button class="btn btn-secondary" @click="showCustomReportModal = true">
                            <i class="bi bi-calendar-plus me-1"></i>Custom
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Reports -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-clock-history me-2"></i>Recent Reports
                  </h5>
                  <div class="d-flex gap-2">
                    <select v-model="reportTypeFilter" class="form-select form-select-sm" style="width: auto;">
                      <option value="all">All Types</option>
                      <option value="user">User Reports</option>
                      <option value="project">Project Reports</option>
                      <option value="task">Task Reports</option>
                      <option value="file">File Reports</option>
                      <option value="system">System Reports</option>
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
                  <p class="mt-2 text-muted">Loading reports...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="alert alert-danger" role="alert">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  {{ error }}
                </div>

                <!-- Reports Table -->
                <div v-else class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Report Name</th>
                        <th>Type</th>
                        <th>Generated By</th>
                        <th>Date Range</th>
                        <th>Status</th>
                        <th>Size</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="report in reports" :key="report.id">
                        <td>
                          <div>
                            <h6 class="mb-1">{{ report.name }}</h6>
                            <small class="text-muted">{{ report.description }}</small>
                          </div>
                        </td>
                        <td>
                          <span class="badge" :class="getReportTypeBadgeClass(report.type)">
                            {{ formatReportType(report.type) }}
                          </span>
                        </td>
                        <td>{{ report.generated_by?.name || 'System' }}</td>
                        <td>{{ report.date_range }}</td>
                        <td>
                          <span class="badge" :class="getStatusBadgeClass(report.status)">
                            {{ formatStatus(report.status) }}
                          </span>
                        </td>
                        <td>{{ formatFileSize(report.size) }}</td>
                        <td>
                          <button class="btn btn-outline-secondary btn-sm me-1" title="Download" @click="downloadReport(report)">
                            <i class="bi bi-download"></i>
                          </button>
                          <button class="btn btn-outline-primary btn-sm me-1" title="View" @click="viewReport(report)">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm" title="Delete" @click="deleteReport(report)">
                            <i class="bi bi-trash"></i>
                          </button>
                        </td>
                      </tr>
                      <tr v-if="reports.length === 0">
                        <td colspan="7" class="text-center text-muted">No reports found.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <nav v-if="totalPages > 1" aria-label="Report pagination">
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

    <!-- Custom Report Modal -->
    <div v-if="showCustomReportModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-calendar-plus me-2"></i>Generate Custom Report
          </h5>
          <button type="button" class="btn-close" @click="showCustomReportModal = false"></button>
        </div>
        
        <form @submit.prevent="generateCustomReport">
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Report Type</label>
                <select v-model="customReport.type" class="form-select" required>
                  <option value="">Select Report Type</option>
                  <option value="user">User Activity</option>
                  <option value="project">Project Progress</option>
                  <option value="task">Task Performance</option>
                  <option value="file">File Usage</option>
                  <option value="system">System Performance</option>
                </select>
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label">Report Format</label>
                <select v-model="customReport.format" class="form-select" required>
                  <option value="pdf">PDF</option>
                  <option value="excel">Excel</option>
                  <option value="csv">CSV</option>
                </select>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Start Date</label>
                <input v-model="customReport.startDate" type="date" class="form-control" required />
              </div>
              
              <div class="col-md-6 mb-3">
                <label class="form-label">End Date</label>
                <input v-model="customReport.endDate" type="date" class="form-control" required />
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Report Name</label>
              <input v-model="customReport.name" type="text" class="form-control" placeholder="Enter report name" required />
            </div>
            
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea v-model="customReport.description" class="form-control" rows="3" placeholder="Enter report description"></textarea>
            </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showCustomReportModal = false">Cancel</button>
            <button type="submit" class="btn btn-primary" :disabled="generatingReport">
              {{ generatingReport ? 'Generating...' : 'Generate Report' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Report Detail Modal -->
    <AdminReportDetailModal
      :visible="showReportDetailModal"
      :report-id="selectedReportId"
      @close="closeReportDetailModal"
      @report-action="handleReportAction"
    />
  </div>
</template>

<script>
import AdminSidebar from './AdminSidebar.vue';
import NotificationBell from '../NotificationBell.vue';
import AdminReportDetailModal from './AdminReportDetailModal.vue';
import { auth } from '../../utils/auth';

export default {
  name: 'AdminReports',
  components: {
    AdminSidebar,
    NotificationBell,
    AdminReportDetailModal
  },
  data() {
    return {
      currentUser: null,
      reports: [],
      loading: false,
      error: null,
      reportTypeFilter: 'all',
      currentPage: 1,
      perPage: 15,
      total: 0,
      totalPages: 0,
      showCustomReportModal: false,
      showReportDetailModal: false,
      selectedReportId: null,
      generatingReport: false,
      customReport: {
        type: '',
        format: 'pdf',
        startDate: '',
        endDate: '',
        name: '',
        description: ''
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
    this.fetchReports();
  },
  methods: {
    async fetchReports() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = new URLSearchParams({
          page: this.currentPage,
          per_page: this.perPage
        });
        
        if (this.reportTypeFilter && this.reportTypeFilter !== 'all') {
          params.append('type', this.reportTypeFilter);
        }

        const response = await fetch(`/api/admin/reports?${params}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch reports');
        }

        const data = await response.json();
        this.reports = data.data;
        this.currentPage = data.current_page;
        this.perPage = data.per_page;
        this.total = data.total;
        this.totalPages = data.last_page;
      } catch (error) {
        console.error('Error fetching reports:', error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchReports();
      }
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(1024));
      return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    },

    formatReportType(type) {
      const typeMap = {
        'user': 'User Report',
        'project': 'Project Report',
        'task': 'Task Report',
        'file': 'File Report',
        'system': 'System Report'
      };
      return typeMap[type] || type;
    },

    formatStatus(status) {
      const statusMap = {
        'completed': 'Completed',
        'generating': 'Generating',
        'failed': 'Failed'
      };
      return statusMap[status] || status;
    },

    getReportTypeBadgeClass(type) {
      const classMap = {
        'user': 'bg-primary',
        'project': 'bg-success',
        'task': 'bg-info',
        'file': 'bg-warning',
        'system': 'bg-danger'
      };
      return classMap[type] || 'bg-secondary';
    },

    getStatusBadgeClass(status) {
      const classMap = {
        'completed': 'bg-success',
        'generating': 'bg-warning',
        'failed': 'bg-danger'
      };
      return classMap[status] || 'bg-secondary';
    },

    async generateUserReport() {
      await this.generateReport('user');
    },

    async generateProjectReport() {
      await this.generateReport('project');
    },

    async generateTaskReport() {
      await this.generateReport('task');
    },

    async generateFileReport() {
      await this.generateReport('file');
    },

    async generateSystemReport() {
      await this.generateReport('system');
    },

    async generateReport(type) {
      this.generatingReport = true;
      try {
        const response = await fetch('/api/admin/reports', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            type: type,
            format: 'pdf'
          })
        });

        if (response.ok) {
          alert('Report generation started successfully!');
          this.fetchReports();
        } else {
          throw new Error('Failed to generate report');
        }
      } catch (error) {
        console.error('Error generating report:', error);
        alert('Failed to generate report');
      } finally {
        this.generatingReport = false;
      }
    },

    async generateCustomReport() {
      this.generatingReport = true;
      try {
        const response = await fetch('/api/admin/reports/custom', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.customReport)
        });

        if (response.ok) {
          alert('Custom report generation started successfully!');
          this.showCustomReportModal = false;
          this.fetchReports();
          this.resetCustomReport();
        } else {
          throw new Error('Failed to generate custom report');
        }
      } catch (error) {
        console.error('Error generating custom report:', error);
        alert('Failed to generate custom report');
      } finally {
        this.generatingReport = false;
      }
    },

    resetCustomReport() {
      this.customReport = {
        type: '',
        format: 'pdf',
        startDate: '',
        endDate: '',
        name: '',
        description: ''
      };
    },

    downloadReport(report) {
      // TODO: Implement report download
      console.log('Download report:', report);
    },

    viewReport(report) {
      this.selectedReportId = report.id;
      this.showReportDetailModal = true;
    },

    closeReportDetailModal() {
      this.showReportDetailModal = false;
      this.selectedReportId = null;
    },

    handleReportAction(report) {
      // TODO: Implement report action functionality
      console.log('Handle report action:', report);
    },

    deleteReport(report) {
      // TODO: Implement report deletion
      console.log('Delete report:', report);
    },

    logout() {
      auth.logout();
      this.$router.push('/login');
    }
  },
  watch: {
    reportTypeFilter() {
      this.currentPage = 1;
      this.fetchReports();
    }
  }
};
</script>

<style scoped>
.admin-reports {
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

.report-card {
  transition: transform 0.2s;
}

.report-card:hover {
  transform: translateY(-2px);
}

.table td {
  vertical-align: middle;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1050;
}

.modal-content {
  background: white;
  border-radius: 8px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}
</style>
