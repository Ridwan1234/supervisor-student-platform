<template>
  <div class="admin-project-overview">
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
                <i class="bi bi-folder me-2"></i>Project Overview
              </h4>
              <p class="text-muted mb-0">Monitor and manage all projects across the platform</p>
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
        <!-- Project Stats -->
        <div class="row mb-4">
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-primary">
                    <i class="bi bi-folder text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Total Projects</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.totalProjects }}</h3>
                    <small class="text-muted">{{ stats.newProjectsThisMonth }} new this month</small>
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
                    <i class="bi bi-play-circle text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Active Projects</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.activeProjects }}</h3>
                    <small class="text-muted">{{ stats.activePercentage }}% of total</small>
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
                    <i class="bi bi-clock text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">In Progress</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.inProgressProjects }}</h3>
                    <small class="text-muted">{{ stats.inProgressPercentage }}% of active</small>
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
                    <i class="bi bi-check-circle text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Completed</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.completedProjects }}</h3>
                    <small class="text-muted">{{ stats.completedPercentage }}% of total</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Project List -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>All Projects
                  </h5>
                  <div class="d-flex gap-2">
                    <select v-model="statusFilter" class="form-select form-select-sm" style="width: auto;">
                      <option value="all">All Status</option>
                      <option value="active">Active</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                      <option value="on_hold">On Hold</option>
                    </select>
                    <input v-model="search" type="text" class="form-control form-control-sm" placeholder="Search projects..." style="width: 200px;" />
                  </div>
                </div>
              </div>
              <div class="card-body">
                <!-- Loading State -->
                <div v-if="loading" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Loading projects...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="alert alert-danger" role="alert">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  {{ error }}
                </div>

                <!-- Projects Table -->
                <div v-else class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Project</th>
                        <th>Supervisor</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Created</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="project in projects" :key="project.id">
                        <td>
                          <div>
                            <h6 class="mb-1">{{ project.title }}</h6>
                            <small class="text-muted">{{ project.description }}</small>
                          </div>
                        </td>
                        <td>{{ project.supervisor?.name || 'N/A' }}</td>
                        <td>
                          <span class="badge bg-secondary">{{ project.students_count || 0 }} students</span>
                        </td>
                        <td>
                          <span class="badge" :class="statusBadgeClass(project.status)">
                            {{ formatStatus(project.status) }}
                          </span>
                        </td>
                        <td>
                          <div class="progress" style="height: 6px;">
                            <div class="progress-bar" :style="{ width: project.progress + '%' }"></div>
                          </div>
                          <small class="text-muted">{{ project.progress }}%</small>
                        </td>
                        <td>{{ formatDate(project.created_at) }}</td>
                          <td>
                            <button class="btn btn-outline-secondary btn-sm me-1" title="View" @click="viewProject(project)">
                              <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-primary btn-sm me-1" title="Edit" @click="editProject(project)">
                              <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm" title="Delete" @click="deleteProject(project)">
                              <i class="bi bi-trash"></i>
                            </button>
                        </td>
                      </tr>
                      <tr v-if="projects.length === 0">
                        <td colspan="7" class="text-center text-muted">No projects found.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <nav v-if="totalPages > 1" aria-label="Project pagination">
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

    <!-- Project Detail Modal -->
    <AdminProjectDetailModal
      :visible="showProjectDetailModal"
      :project-id="selectedProjectId"
      @close="closeProjectDetailModal"
      @edit-project="handleEditProject"
    />
  </div>
</template>

<script>
import AdminSidebar from './AdminSidebar.vue';
import NotificationBell from '../NotificationBell.vue';
import AdminProjectDetailModal from './AdminProjectDetailModal.vue';
import { auth } from '../../utils/auth';

export default {
  name: 'AdminProjectOverview',
  components: {
    AdminSidebar,
    NotificationBell,
    AdminProjectDetailModal
  },
  data() {
    return {
      currentUser: null,
      projects: [],
      loading: false,
      error: null,
      search: '',
      statusFilter: 'all',
      currentPage: 1,
      perPage: 15,
      total: 0,
      totalPages: 0,
      showProjectDetailModal: false,
      selectedProjectId: null,
      stats: {
        totalProjects: 0,
        activeProjects: 0,
        inProgressProjects: 0,
        completedProjects: 0,
        newProjectsThisMonth: 0,
        activePercentage: 0,
        inProgressPercentage: 0,
        completedPercentage: 0
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
    this.fetchProjects();
    this.fetchStats();
  },
  methods: {
    async fetchProjects() {
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
        
        if (this.statusFilter && this.statusFilter !== 'all') {
          params.append('status', this.statusFilter);
        }

        const response = await fetch(`/api/admin/projects?${params}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch projects');
        }

        const data = await response.json();
        this.projects = data.data;
        this.currentPage = data.current_page;
        this.perPage = data.per_page;
        this.total = data.total;
        this.totalPages = data.last_page;
      } catch (error) {
        console.error('Error fetching projects:', error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchStats() {
      try {
        const response = await fetch('/api/admin/project-stats', {
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
        console.error('Error fetching project stats:', error);
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchProjects();
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString();
    },

    formatStatus(status) {
      const statusMap = {
        'active': 'Active',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'on_hold': 'On Hold'
      };
      return statusMap[status] || status;
    },

    statusBadgeClass(status) {
      const classMap = {
        'active': 'bg-success',
        'in_progress': 'bg-warning',
        'completed': 'bg-info',
        'on_hold': 'bg-secondary'
      };
      return classMap[status] || 'bg-secondary';
    },

    viewProject(project) {
      this.selectedProjectId = project.id;
      this.showProjectDetailModal = true;
    },

    closeProjectDetailModal() {
      this.showProjectDetailModal = false;
      this.selectedProjectId = null;
    },

    handleEditProject(project) {
      // TODO: Implement project edit functionality
      console.log('Edit project:', project);
    },

    editProject(project) {
      // TODO: Implement project edit
      console.log('Edit project:', project);
    },

    deleteProject(project) {
      // TODO: Implement project deletion
      console.log('Delete project:', project);
    },

    logout() {
      auth.logout();
      this.$router.push('/login');
    }
  },
  watch: {
    search() {
      this.currentPage = 1;
      this.fetchProjects();
    },
    statusFilter() {
      this.currentPage = 1;
      this.fetchProjects();
    }
  }
};
</script>

<style scoped>
.admin-project-overview {
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

.progress {
  background-color: #e9ecef;
}
</style>
