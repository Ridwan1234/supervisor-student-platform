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
            <h4 class="mb-0 fw-bold text-primary">Task Management</h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Search Bar -->
            <div class="position-relative">
              <input 
                type="text" 
                class="form-control form-control-sm" 
                placeholder="Search tasks..." 
                v-model="searchQuery"
                @input="filterTasks"
              />
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
            </div>

            <!-- Add New Task Button -->
            <button class="btn btn-primary" @click="showCreateModal = true">
              <i class="bi bi-plus me-2"></i>New Task
            </button>
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
          <p class="mt-3">Loading tasks...</p>
        </div>

        <div v-else class="row">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-body">
                <!-- Search and Filter -->
                <div class="row mb-4">
                  <div class="col-md-6">
                    <!-- Search handled in navbar -->
                  </div>
                  <div class="col-md-3">
                    <select class="form-select" v-model="statusFilter" @change="filterTasks">
                      <option value="">All Status</option>
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                      <option value="overdue">Overdue</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <select class="form-select" v-model="priorityFilter" @change="filterTasks">
                      <option value="">All Priorities</option>
                      <option value="low">Low</option>
                      <option value="medium">Medium</option>
                      <option value="high">High</option>
                      <option value="urgent">Urgent</option>
                    </select>
                  </div>
                </div>

                <!-- Tasks Table -->
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead class="table-light">
                      <tr>
                        <th>Task</th>
                        <th>Assigned To</th>
                        <th>Project</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Progress</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="task in paginatedTasks" :key="task.id">
                        <td>
                          <div>
                            <strong>{{ task.title }}</strong>
                            <br>
                            <small class="text-muted">{{ task.description ? task.description.substring(0, 50) + '...' : 'No description' }}</small>
                          </div>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                              {{ task.assigned_to ? task.assigned_to.charAt(0).toUpperCase() : 'U' }}
                            </div>
                            {{ task.assigned_to || 'Unassigned' }}
                          </div>
                        </td>
                        <td>
                          <span class="badge bg-info">{{ task.project_name || 'No Project' }}</span>
                        </td>
                        <td>
                          <span :class="getPriorityClass(task.priority)">
                            {{ task.priority || 'low' }}
                          </span>
                        </td>
                        <td>
                          <span :class="getStatusClass(task.status)">
                            {{ task.status || 'pending' }}
                          </span>
                        </td>
                        <td>
                          <span :class="getDueDateClass(task.due_date)">
                            {{ formatDate(task.due_date) }}
                          </span>
                        </td>
                        <td>
                          <div class="progress" style="height: 8px;">
                            <div 
                              class="progress-bar" 
                              :class="getProgressClass(task.progress || 0)"
                              :style="{ width: (task.progress || 0) + '%' }"
                            ></div>
                          </div>
                          <small class="text-muted">{{ task.progress || 0 }}%</small>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" @click="editTask(task)">
                              <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-success" @click="viewTask(task)">
                              <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-danger" @click="deleteTask(task.id)">
                              <i class="bi bi-trash"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <nav v-if="totalPages > 1">
                  <ul class="pagination justify-content-center">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                      <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">
                        <i class="bi bi-chevron-left"></i>
                      </a>
                    </li>
                    <li 
                      v-for="page in visiblePages" 
                      :key="page" 
                      class="page-item"
                      :class="{ active: page === currentPage }"
                    >
                      <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                      <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">
                        <i class="bi bi-chevron-right"></i>
                      </a>
                    </li>
                  </ul>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Create/Edit Task Modal -->
    <div class="modal fade" :class="{ show: showCreateModal || showEditModal }" 
         :style="{ display: (showCreateModal || showEditModal) ? 'block' : 'none' }" 
         tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-list-task me-2"></i>
              {{ isEditing ? 'Edit Task' : 'Create New Task' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveTask">
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-3">
                    <label class="form-label">Task Title *</label>
                    <input 
                      type="text" 
                      class="form-control" 
                      v-model="taskForm.title"
                      required
                    >
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Priority *</label>
                    <select class="form-select" v-model="taskForm.priority" required>
                      <option value="">Select Priority</option>
                      <option value="low">Low</option>
                      <option value="medium">Medium</option>
                      <option value="high">High</option>
                      <option value="urgent">Urgent</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Assigned To *</label>
                    <select class="form-select" v-model="taskForm.assigned_to" required>
                      <option value="">Select Student</option>
                      <option v-for="student in students" :key="student.id" :value="student.name">
                        {{ student.name }}
                      </option>
                    </select>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Project *</label>
                    <select class="form-select" v-model="taskForm.project_id" required>
                      <option value="">Select Project</option>
                      <option v-for="project in projects" :key="project.id" :value="project.id">
                        {{ project.name }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Due Date *</label>
                    <input 
                      type="date" 
                      class="form-control" 
                      v-model="taskForm.due_date"
                      required
                    >
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" v-model="taskForm.status">
                      <option value="pending">Pending</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                      <option value="overdue">Overdue</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Description *</label>
                <textarea 
                  class="form-control" 
                  rows="4"
                  v-model="taskForm.description"
                  required
                ></textarea>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Progress (%)</label>
                    <input 
                      type="range" 
                      class="form-range" 
                      min="0" 
                      max="100" 
                      v-model="taskForm.progress"
                    >
                    <div class="d-flex justify-content-between">
                      <small>0%</small>
                      <small>{{ taskForm.progress }}%</small>
                      <small>100%</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Estimated Hours</label>
                    <input 
                      type="number" 
                      class="form-control" 
                      v-model="taskForm.estimated_hours"
                      min="0"
                      step="0.5"
                    >
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveTask" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
              <i class="bi bi-check-circle me-1"></i>
              {{ isEditing ? 'Update Task' : 'Create Task' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Task Details Modal -->
    <div class="modal fade" :class="{ show: showDetailsModal }" 
         :style="{ display: showDetailsModal ? 'block' : 'none' }" 
         tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-eye me-2"></i>
              Task Details
            </h5>
            <button type="button" class="btn-close" @click="showDetailsModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedTask">
              <div class="row">
                <div class="col-md-8">
                  <h4>{{ selectedTask.title }}</h4>
                  <p class="text-muted">{{ selectedTask.description }}</p>
                </div>
                <div class="col-md-4">
                  <div class="d-flex flex-column gap-2">
                    <span :class="getPriorityClass(selectedTask.priority)">
                      {{ selectedTask.priority }}
                    </span>
                    <span :class="getStatusClass(selectedTask.status)">
                      {{ selectedTask.status }}
                    </span>
                  </div>
                </div>
              </div>
              
              <hr>
              
              <div class="row">
                <div class="col-md-6">
                  <h6>Task Information</h6>
                  <ul class="list-unstyled">
                    <li><strong>Assigned To:</strong> {{ selectedTask.assigned_to || 'Unassigned' }}</li>
                    <li><strong>Project:</strong> {{ selectedTask.project_name || 'No Project' }}</li>
                    <li><strong>Due Date:</strong> {{ formatDate(selectedTask.due_date) }}</li>
                    <li><strong>Estimated Hours:</strong> {{ selectedTask.estimated_hours || 0 }}h</li>
                  </ul>
                </div>
                <div class="col-md-6">
                  <h6>Progress</h6>
                  <div class="progress mb-2" style="height: 20px;">
                    <div 
                      class="progress-bar" 
                      :class="getProgressClass(selectedTask.progress || 0)"
                      :style="{ width: (selectedTask.progress || 0) + '%' }"
                    >
                      {{ selectedTask.progress || 0 }}%
                    </div>
                  </div>
                  <small class="text-muted">Last updated: {{ formatDate(selectedTask.updated_at) }}</small>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showDetailsModal = false">Close</button>
            <button type="button" class="btn btn-primary" @click="editTask(selectedTask)">
              <i class="bi bi-pencil me-1"></i>
              Edit Task
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Backdrop -->
    <div class="modal-backdrop fade show" 
         v-if="showCreateModal || showEditModal || showDetailsModal"></div>
  </div>
</template>

<script>
import SupervisorSidebar from './SupervisorSidebar.vue';
import axios from 'axios';

export default {
  name: 'TaskManagement',
  components: {
    SupervisorSidebar
  },
  data() {
    return {
      loading: false,
      saving: false,
      searchQuery: '',
      statusFilter: '',
      priorityFilter: '',
      showCreateModal: false,
      showEditModal: false,
      showDetailsModal: false,
      isEditing: false,
      selectedTask: null,
      currentPage: 1,
      itemsPerPage: 10,
      students: [],
      projects: [],
      tasks: [],
      filteredTasks: [],
      taskForm: {
        title: '',
        description: '',
        assigned_to: '',
        project_id: '',
        priority: '',
        status: 'pending',
        due_date: '',
        progress: 0,
        estimated_hours: 0
      }
    }
  },
  computed: {
    paginatedTasks() {
      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return this.filteredTasks.slice(start, end);
    },
    totalPages() {
      return Math.ceil(this.filteredTasks.length / this.itemsPerPage)
    },
    visiblePages() {
      const pages = []
      const start = Math.max(1, this.currentPage - 2)
      const end = Math.min(this.totalPages, this.currentPage + 2)
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      return pages
    }
  },
  async mounted() {
    await this.fetchData();
  },
  methods: {
    async fetchData() {
      this.loading = true;
      try {
        await Promise.all([
          this.fetchTasks(),
          this.fetchStudents(),
          this.fetchProjects()
        ]);
      } catch (error) {
        console.error('Error fetching data:', error);
        this.showToast('Error loading data', 'error');
      } finally {
        this.loading = false;
      }
    },
    async fetchTasks() {
      try {
        const response = await axios.get('/api/supervisor/tasks');
        this.tasks = response.data.data || response.data;
        this.filterTasks();
      } catch (error) {
        console.error('Error fetching tasks:', error);
        this.showToast('Error loading tasks', 'error');
      }
    },
    async fetchStudents() {
      try {
        const response = await axios.get('/api/supervisor/students');
        this.students = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching students:', error);
      }
    },
    async fetchProjects() {
      try {
        const response = await axios.get('/api/supervisor/projects');
        this.projects = response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching projects:', error);
      }
    },
    toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    },
    filterTasks() {
      let filtered = this.tasks

      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase()
        filtered = filtered.filter(task => 
          task.title.toLowerCase().includes(query) ||
          (task.description && task.description.toLowerCase().includes(query)) ||
          (task.assigned_to && task.assigned_to.toLowerCase().includes(query)) ||
          (task.project_name && task.project_name.toLowerCase().includes(query))
        )
      }

      if (this.statusFilter) {
        filtered = filtered.filter(task => task.status === this.statusFilter)
      }

      if (this.priorityFilter) {
        filtered = filtered.filter(task => task.priority === this.priorityFilter)
      }

      this.filteredTasks = filtered
      this.currentPage = 1
    },
    
    getPriorityClass(priority) {
      const classes = {
        low: 'badge bg-success',
        medium: 'badge bg-warning',
        high: 'badge bg-danger',
        urgent: 'badge bg-dark'
      }
      return classes[priority] || 'badge bg-secondary'
    },
    
    getStatusClass(status) {
      const classes = {
        pending: 'badge bg-secondary',
        in_progress: 'badge bg-primary',
        completed: 'badge bg-success',
        overdue: 'badge bg-danger'
      }
      return classes[status] || 'badge bg-secondary'
    },
    
    getDueDateClass(dueDate) {
      if (!dueDate) return 'text-muted';
      const today = new Date()
      const due = new Date(dueDate)
      const diffTime = due - today
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
      
      if (diffDays < 0) return 'text-danger'
      if (diffDays <= 3) return 'text-warning'
      return 'text-muted'
    },
    
    getProgressClass(progress) {
      if (progress >= 80) return 'bg-success'
      if (progress >= 50) return 'bg-warning'
      return 'bg-info'
    },
    
    formatDate(date) {
      if (!date) return 'No date';
      return new Date(date).toLocaleDateString()
    },
    
    editTask(task) {
      this.isEditing = true
      this.selectedTask = task
      this.taskForm = { 
        title: task.title,
        description: task.description,
        assigned_to: task.assigned_to,
        project_id: task.project_id,
        priority: task.priority,
        status: task.status,
        due_date: task.due_date,
        progress: task.progress || 0,
        estimated_hours: task.estimated_hours || 0
      }
      this.showEditModal = true
    },
    
    viewTask(task) {
      this.selectedTask = task
      this.showDetailsModal = true
    },
    
    async deleteTask(taskId) {
      if (confirm('Are you sure you want to delete this task?')) {
        try {
          await axios.delete(`/api/tasks/${taskId}`);
          await this.fetchTasks();
          this.showToast('Task deleted successfully', 'success');
        } catch (error) {
          console.error('Error deleting task:', error);
          this.showToast('Error deleting task', 'error');
        }
      }
    },
    
    async saveTask() {
      if (!this.taskForm.title || !this.taskForm.description) {
        this.showToast('Please fill in all required fields', 'error');
        return;
      }
      
      this.saving = true;
      try {
        if (this.isEditing) {
          await axios.put(`/api/tasks/${this.selectedTask.id}`, this.taskForm);
          this.showToast('Task updated successfully', 'success');
        } else {
          await axios.post('/api/tasks', this.taskForm);
          this.showToast('Task created successfully', 'success');
        }
        
        await this.fetchTasks();
        this.closeModal();
      } catch (error) {
        console.error('Error saving task:', error);
        this.showToast('Error saving task', 'error');
      } finally {
        this.saving = false;
      }
    },
    
    closeModal() {
      this.showCreateModal = false
      this.showEditModal = false
      this.showDetailsModal = false
      this.isEditing = false
      this.selectedTask = null
      this.resetForm()
    },
    
    resetForm() {
      this.taskForm = {
        title: '',
        description: '',
        assigned_to: '',
        project_id: '',
        priority: '',
        status: 'pending',
        due_date: '',
        progress: 0,
        estimated_hours: 0
      }
    },
    
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page
      }
    },
    
    showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
      toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.remove();
      }, 5000);
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

.avatar-sm {
  width: 32px;
  height: 32px;
  font-size: 14px;
}

.progress {
  border-radius: 10px;
}

.progress-bar {
  border-radius: 10px;
}

.modal.show {
  background-color: rgba(0, 0, 0, 0.5);
}

.btn-group .btn {
  border-radius: 0;
}

.btn-group .btn:first-child {
  border-top-left-radius: 0.375rem;
  border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
  border-top-right-radius: 0.375rem;
  border-bottom-right-radius: 0.375rem;
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