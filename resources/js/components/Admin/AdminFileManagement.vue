<template>
  <div class="admin-file-management">
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
                <i class="bi bi-file-earmark me-2"></i>File Management
              </h4>
              <p class="text-muted mb-0">Manage and monitor all files across the platform</p>
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
        <!-- File Stats -->
        <div class="row mb-4">
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-primary">
                    <i class="bi bi-file-earmark text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Total Files</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.totalFiles }}</h3>
                    <small class="text-muted">{{ stats.newFilesThisMonth }} new this month</small>
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
                    <small class="text-muted">{{ stats.storagePercentage }}% of total</small>
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
                    <i class="bi bi-share text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Shared Files</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.sharedFiles }}</h3>
                    <small class="text-muted">{{ stats.sharedPercentage }}% of total</small>
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
                    <i class="bi bi-download text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Downloads</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.totalDownloads }}</h3>
                    <small class="text-muted">This month</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- File List -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>All Files
                  </h5>
                  <div class="d-flex gap-2">
                    <select v-model="typeFilter" class="form-select form-select-sm" style="width: auto;">
                      <option value="all">All Types</option>
                      <option value="document">Documents</option>
                      <option value="image">Images</option>
                      <option value="video">Videos</option>
                      <option value="other">Other</option>
                    </select>
                    <input v-model="search" type="text" class="form-control form-control-sm" placeholder="Search files..." style="width: 200px;" />
                  </div>
                </div>
              </div>
              <div class="card-body">
                <!-- Loading State -->
                <div v-if="loading" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Loading files...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="alert alert-danger" role="alert">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  {{ error }}
                </div>

                <!-- Files Table -->
                <div v-else class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>File</th>
                        <th>Owner</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Downloads</th>
                        <th>Created</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="file in files" :key="file.id">
                        <td>
                          <div class="d-flex align-items-center">
                            <i :class="getFileIcon(file.type)" class="me-2 text-primary"></i>
                            <div>
                              <h6 class="mb-1">{{ file.name }}</h6>
                              <small class="text-muted">{{ file.description }}</small>
                            </div>
                          </div>
                        </td>
                        <td>{{ file.owner?.name || 'N/A' }}</td>
                        <td>
                          <span class="badge bg-secondary">{{ file.type }}</span>
                        </td>
                        <td>{{ formatFileSize(file.size) }}</td>
                        <td>{{ file.downloads_count || 0 }}</td>
                        <td>{{ formatDate(file.created_at) }}</td>
                        <td>
                          <button class="btn btn-outline-secondary btn-sm me-1" title="View" @click="viewFile(file)">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button class="btn btn-outline-primary btn-sm me-1" title="Download" @click="downloadFile(file)">
                            <i class="bi bi-download"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm" title="Delete" @click="deleteFile(file)">
                            <i class="bi bi-trash"></i>
                          </button>
                        </td>
                      </tr>
                      <tr v-if="files.length === 0">
                        <td colspan="7" class="text-center text-muted">No files found.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <nav v-if="totalPages > 1" aria-label="File pagination">
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

    <!-- File Detail Modal -->
    <AdminFileDetailModal
      :visible="showFileDetailModal"
      :file-id="selectedFileId"
      @close="closeFileDetailModal"
      @delete-file="handleDeleteFile"
    />
  </div>
</template>

<script>
import AdminSidebar from './AdminSidebar.vue';
import NotificationBell from '../NotificationBell.vue';
import AdminFileDetailModal from './AdminFileDetailModal.vue';
import { auth } from '../../utils/auth';

export default {
  name: 'AdminFileManagement',
  components: {
    AdminSidebar,
    NotificationBell,
    AdminFileDetailModal
  },
  data() {
    return {
      currentUser: null,
      files: [],
      loading: false,
      error: null,
      search: '',
      typeFilter: 'all',
      currentPage: 1,
      perPage: 15,
      total: 0,
      totalPages: 0,
      showFileDetailModal: false,
      selectedFileId: null,
      stats: {
        totalFiles: 0,
        storageUsed: '0 MB',
        sharedFiles: 0,
        totalDownloads: 0,
        newFilesThisMonth: 0,
        storagePercentage: 0,
        sharedPercentage: 0
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
    this.fetchFiles();
    this.fetchStats();
  },
  methods: {
    async fetchFiles() {
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
        
        if (this.typeFilter && this.typeFilter !== 'all') {
          params.append('type', this.typeFilter);
        }

        const response = await fetch(`/api/admin/files?${params}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch files');
        }

        const data = await response.json();
        this.files = data.data;
        this.currentPage = data.current_page;
        this.perPage = data.per_page;
        this.total = data.total;
        this.totalPages = data.last_page;
      } catch (error) {
        console.error('Error fetching files:', error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchStats() {
      try {
        const response = await fetch('/api/admin/file-stats', {
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
        console.error('Error fetching file stats:', error);
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchFiles();
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString();
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(1024));
      return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    },

    getFileIcon(type) {
      const iconMap = {
        'document': 'bi bi-file-earmark-text',
        'image': 'bi bi-file-earmark-image',
        'video': 'bi bi-file-earmark-play',
        'pdf': 'bi bi-file-earmark-pdf',
        'spreadsheet': 'bi bi-file-earmark-spreadsheet',
        'presentation': 'bi bi-file-earmark-slides'
      };
      return iconMap[type] || 'bi bi-file-earmark';
    },

    viewFile(file) {
      this.selectedFileId = file.id;
      this.showFileDetailModal = true;
    },

    closeFileDetailModal() {
      this.showFileDetailModal = false;
      this.selectedFileId = null;
    },

    handleDeleteFile(file) {
      // TODO: Implement file deletion functionality
      console.log('Delete file:', file);
    },

    downloadFile(file) {
      // TODO: Implement file download
      console.log('Download file:', file);
    },

    deleteFile(file) {
      // TODO: Implement file deletion
      console.log('Delete file:', file);
    },

    logout() {
      auth.logout();
      this.$router.push('/login');
    }
  },
  watch: {
    search() {
      this.currentPage = 1;
      this.fetchFiles();
    },
    typeFilter() {
      this.currentPage = 1;
      this.fetchFiles();
    }
  }
};
</script>

<style scoped>
.admin-file-management {
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
