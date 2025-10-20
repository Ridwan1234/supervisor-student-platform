<template>
  <div class="admin-task-overview">
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
                <i class="bi bi-list-check me-2"></i>Task Overview
              </h4>
              <p class="text-muted mb-0">Monitor and manage all tasks across the platform</p>
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
        <!-- Task Stats -->
        <div class="row mb-4">
          <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stat-card">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="stat-icon bg-primary">
                    <i class="bi bi-list-check text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Total Tasks</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.totalTasks }}</h3>
                    <small class="text-muted">{{ stats.newTasksThisMonth }} new this month</small>
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
                    <h6 class="card-title mb-1">Pending</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.pendingTasks }}</h3>
                    <small class="text-muted">{{ stats.pendingPercentage }}% of total</small>
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
                    <i class="bi bi-play-circle text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">In Progress</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.inProgressTasks }}</h3>
                    <small class="text-muted">{{ stats.inProgressPercentage }}% of total</small>
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
                    <i class="bi bi-check-circle text-white"></i>
                  </div>
                  <div class="ms-3">
                    <h6 class="card-title mb-1">Completed</h6>
                    <h3 class="mb-0 fw-bold">{{ stats.completedTasks }}</h3>
                    <small class="text-muted">{{ stats.completedPercentage }}% of total</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Task List -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-2"></i>All Tasks
                  </h5>
                  <div class="d-flex gap-2">
                    <select v-model="statusFilter" class="form-select form-select-sm" style="width: auto;">
                      <option value="all">All Status</option>
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                    </select>
                    <input v-model="search" type="text" class="form-control form-control-sm" placeholder="Search tasks..." style="width: 200px;" />
                  </div>
                </div>
              </div>
              <div class="card-body">
                <!-- Loading State -->
                <div v-if="loading" class="text-center py-4">
                  <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p class="mt-2 text-muted">Loading tasks...</p>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="alert alert-danger" role="alert">
                  <i class="bi bi-exclamation-triangle me-2"></i>
                  {{ error }}
                </div>

                <!-- Tasks Table -->
                <div v-else class="table-responsive">
                  <table class="table table-hover align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="task in tasks" :key="task.id">
                        <td>
                          <div>
                            <h6 class="mb-1">{{ task.title }}</h6>
                            <small class="text-muted">{{ task.description }}</small>
                          </div>
                        </td>
                        <td>{{ task.project?.title || 'N/A' }}</td>
                        <td>{{ task.assigned_to?.name || 'Unassigned' }}</td>
                        <td>
                          <span class="badge" :class="statusBadgeClass(task.status)">
                            {{ formatStatus(task.status) }}
                          </span>
                        </td>
                        <td>
                          <span class="badge" :class="priorityBadgeClass(task.priority)">
                            {{ formatPriority(task.priority) }}
                          </span>
                        </td>
                        <td>{{ formatDate(task.due_date) }}</td>
                        <td>
                          <button class="btn btn-outline-secondary btn-sm me-1" title="View" @click="viewTask(task)">
                            <i class="bi bi-eye"></i>
                          </button>
                          <button class="btn btn-outline-primary btn-sm me-1" title="Edit" @click="editTask(task)">
                            <i class="bi bi-pencil"></i>
                          </button>
                          <button class="btn btn-outline-danger btn-sm" title="Delete" @click="deleteTask(task)">
                            <i class="bi bi-trash"></i>
                          </button>
                        </td>
                      </tr>
                      <tr v-if="tasks.length === 0">
                        <td colspan="7" class="text-center text-muted">No tasks found.</td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <nav v-if="totalPages > 1" aria-label="Task pagination">
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

    <!-- Task Detail Modal -->
    <AdminTaskDetailModal
      :visible="showTaskDetailModal"
      :task-id="selectedTaskId"
      @close="closeTaskDetailModal"
      @edit-task="editTask"
    />
  </div>
</template>

<script>
import AdminSidebar from './AdminSidebar.vue';
import NotificationBell from '../NotificationBell.vue';
import AdminTaskDetailModal from './AdminTaskDetailModal.vue';
import { auth } from '../../utils/auth';

export default {
  name: 'AdminTaskOverview',
  components: {
    AdminSidebar,
    NotificationBell,
    AdminTaskDetailModal
  },
  data() {
    return {
      currentUser: null,
      tasks: [],
      loading: false,
      error: null,
      search: '',
      statusFilter: 'all',
      currentPage: 1,
      perPage: 15,
      total: 0,
      totalPages: 0,
      showTaskDetailModal: false,
      selectedTaskId: null,
      stats: {
        totalTasks: 0,
        pendingTasks: 0,
        inProgressTasks: 0,
        completedTasks: 0,
        newTasksThisMonth: 0,
        pendingPercentage: 0,
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
    this.fetchTasks();
    this.fetchStats();
  },
  methods: {
    async fetchTasks() {
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

        const response = await fetch(`/api/admin/tasks?${params}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch tasks');
        }

        const data = await response.json();
        this.tasks = data.data;
        this.currentPage = data.current_page;
        this.perPage = data.per_page;
        this.total = data.total;
        this.totalPages = data.last_page;
      } catch (error) {
        console.error('Error fetching tasks:', error);
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchStats() {
      try {
        const response = await fetch('/api/admin/task-stats', {
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
        console.error('Error fetching task stats:', error);
      }
    },

    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchTasks();
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString();
    },

    formatStatus(status) {
      const statusMap = {
        'pending': 'Pending',
        'in_progress': 'In Progress',
        'completed': 'Completed'
      };
      return statusMap[status] || status;
    },

    formatPriority(priority) {
      const priorityMap = {
        'low': 'Low',
        'medium': 'Medium',
        'high': 'High',
        'urgent': 'Urgent'
      };
      return priorityMap[priority] || priority;
    },

    statusBadgeClass(status) {
      const classMap = {
        'pending': 'bg-warning',
        'in_progress': 'bg-info',
        'completed': 'bg-success'
      };
      return classMap[status] || 'bg-secondary';
    },

    priorityBadgeClass(priority) {
      const classMap = {
        'low': 'bg-secondary',
        'medium': 'bg-info',
        'high': 'bg-warning',
        'urgent': 'bg-danger'
      };
      return classMap[priority] || 'bg-secondary';
    },

    viewTask(task) {
      this.selectedTaskId = task.id;
      this.showTaskDetailModal = true;
    },

    closeTaskDetailModal() {
      this.showTaskDetailModal = false;
      this.selectedTaskId = null;
    },

    editTask(task) {
      // TODO: Implement task edit
      console.log('Edit task:', task);
    },

    deleteTask(task) {
      // TODO: Implement task deletion
      console.log('Delete task:', task);
    },

    logout() {
      auth.logout();
      this.$router.push('/login');
    }
  },
  watch: {
    search() {
      this.currentPage = 1;
      this.fetchTasks();
    },
    statusFilter() {
      this.currentPage = 1;
      this.fetchTasks();
    }
  }
};
</script>

<style scoped>
.admin-task-overview {
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
