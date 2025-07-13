<template>
  <div class="dashboard-container">
    <!-- Student Sidebar -->
    <StudentSidebar />
    
    <!-- Main Content -->
    <div class="main-content">
      <!-- Top Navigation -->
      <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container-fluid">
          <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            <div class="dropdown">
              <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown">
                <i class="bi bi-bell"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  5
                </span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><a class="dropdown-item" href="#">New task assigned</a></li>
                <li><a class="dropdown-item" href="#">Task deadline reminder</a></li>
                <li><a class="dropdown-item" href="#">Supervisor feedback received</a></li>
              </ul>
            </div>

            <!-- User Profile -->
            <div class="dropdown">
              <button class="btn btn-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                  <i class="bi bi-person text-white"></i>
                </div>
                <span>John Doe</span>
                <i class="bi bi-chevron-down"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h4 class="mb-1">My Tasks</h4>
                <p class="text-muted mb-0">Manage and track your assigned tasks</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3 text-muted">Loading tasks...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          {{ error }}
          <button class="btn btn-outline-danger btn-sm ms-3" @click="fetchTasks">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Retry
          </button>
        </div>

        <!-- Task Overview Cards -->
        <div v-else class="row mb-4">
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="card-title">Total Tasks</h6>
                    <h3 class="mb-0">{{ totalTasks }}</h3>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-list-task display-6"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="card-title">Completed</h6>
                    <h3 class="mb-0">{{ completedTasks }}</h3>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-check-circle display-6"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="card-title">In Progress</h6>
                    <h3 class="mb-0">{{ inProgressTasks }}</h3>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-clock display-6"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-danger text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="card-title">Overdue</h6>
                    <h3 class="mb-0">{{ overdueTasks }}</h3>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-exclamation-triangle display-6"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Task Management -->
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-body">
                <!-- Search and Filter -->
                <div class="row mb-4">
                  <div class="col-md-6">
                    <div class="input-group">
                      <span class="input-group-text">
                        <i class="bi bi-search"></i>
                      </span>
                      <input 
                        type="text" 
                        class="form-control" 
                        placeholder="Search tasks..." 
                        v-model="searchQuery"
                        @input="filterTasks"
                      >
                    </div>
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
                      <option value="">All Priority</option>
                      <option value="high">High</option>
                      <option value="medium">Medium</option>
                      <option value="low">Low</option>
                    </select>
                  </div>
                </div>

                <!-- Task List -->
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead class="table-light">
                      <tr>
                        <th>Task</th>
                        <th>Project</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Progress</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="task in filteredTasks" :key="task.id">
                        <td>
                          <div>
                            <strong>{{ task.title }}</strong>
                            <br>
                            <small class="text-muted">{{ task.description.substring(0, 50) }}...</small>
                          </div>
                        </td>
                        <td>
                          <span class="badge bg-info">{{ task.project }}</span>
                        </td>
                        <td>
                          <span :class="getPriorityClass(task.priority)">
                            {{ task.priority }}
                          </span>
                        </td>
                        <td>
                          <span :class="getStatusClass(task.status)">
                            {{ task.status }}
                          </span>
                        </td>
                        <td>
                          <span :class="getDueDateClass(task.dueDate)">
                            {{ formatDate(task.dueDate) }}
                          </span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="progress me-2" style="width: 100px; height: 8px;">
                              <div 
                                class="progress-bar" 
                                :class="getProgressClass(task.progress)"
                                :style="{ width: task.progress + '%' }"
                              ></div>
                            </div>
                            <small>{{ task.progress }}%</small>
                          </div>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" @click="viewTask(task)">
                              <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-success" @click="updateTask(task)">
                              <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-info" @click="submitTask(task)">
                              <i class="bi bi-check-circle"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Empty State -->
                <div v-if="filteredTasks.length === 0" class="text-center py-5">
                  <i class="bi bi-list-task display-1 text-muted"></i>
                  <h5 class="mt-3">No tasks found</h5>
                  <p class="text-muted">No tasks match your current filters.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Task Details Modal -->
    <div class="modal fade" :class="{ show: showTaskModal }" 
         :style="{ display: showTaskModal ? 'block' : 'none' }" 
         tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-list-task me-2"></i>
              Task Details
            </h5>
            <button type="button" class="btn-close" @click="showTaskModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedTask">
              <div class="row">
                <div class="col-md-8">
                  <h4>{{ selectedTask.title }}</h4>
                  <p class="text-muted">{{ selectedTask.description }}</p>
                  
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <h6>Task Information</h6>
                      <ul class="list-unstyled">
                        <li><strong>Project:</strong> {{ selectedTask.project }}</li>
                        <li><strong>Assigned:</strong> {{ formatDate(selectedTask.assignedDate) }}</li>
                        <li><strong>Due Date:</strong> {{ formatDate(selectedTask.dueDate) }}</li>
                        <li><strong>Priority:</strong> 
                          <span :class="getPriorityClass(selectedTask.priority)">
                            {{ selectedTask.priority }}
                          </span>
                        </li>
                      </ul>
                    </div>
                    <div class="col-md-6">
                      <h6>Progress Overview</h6>
                      <div class="progress mb-2" style="height: 20px;">
                        <div 
                          class="progress-bar" 
                          :class="getProgressClass(selectedTask.progress)"
                          :style="{ width: selectedTask.progress + '%' }"
                        >
                          {{ selectedTask.progress }}%
                        </div>
                      </div>
                      <small class="text-muted">Last updated: {{ formatDate(selectedTask.lastUpdated) }}</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="mb-0">Task Stats</h6>
                    </div>
                    <div class="card-body">
                      <div class="d-flex justify-content-between mb-2">
                        <span>Status:</span>
                        <span :class="getStatusClass(selectedTask.status)">
                          {{ selectedTask.status }}
                        </span>
                      </div>
                      <div class="d-flex justify-content-between mb-2">
                        <span>Priority:</span>
                        <span :class="getPriorityClass(selectedTask.priority)">
                          {{ selectedTask.priority }}
                        </span>
                      </div>
                      <div class="d-flex justify-content-between mb-2">
                        <span>Days Left:</span>
                        <strong :class="getDueDateClass(selectedTask.dueDate)">{{ selectedTask.daysLeft }}</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <hr>
              
              <div class="row">
                <div class="col-12">
                  <h6>Task Requirements</h6>
                  <div class="bg-light p-3 rounded">
                    <ul class="mb-0">
                      <li v-for="requirement in selectedTask.requirements" :key="requirement">
                        {{ requirement }}
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showTaskModal = false">Close</button>
            <button type="button" class="btn btn-primary" @click="updateTask(selectedTask)">
              <i class="bi bi-pencil me-1"></i>
              Update Task
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Update Task Modal -->
    <div class="modal fade" :class="{ show: showUpdateModal }" 
         :style="{ display: showUpdateModal ? 'block' : 'none' }" 
         tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-pencil me-2"></i>
              Update Task
            </h5>
            <button type="button" class="btn-close" @click="showUpdateModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedTask">
              <h6>{{ selectedTask.title }}</h6>
              
              <div class="mb-3">
                <label class="form-label">Progress (%)</label>
                <input 
                  type="range" 
                  class="form-range" 
                  min="0" 
                  max="100" 
                  v-model="progressUpdate"
                >
                <div class="d-flex justify-content-between">
                  <small>0%</small>
                  <small>{{ progressUpdate }}%</small>
                  <small>100%</small>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select" v-model="statusUpdate">
                  <option value="pending">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="overdue">Overdue</option>
                </select>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Update Notes</label>
                <textarea 
                  class="form-control" 
                  rows="3"
                  v-model="taskNotes"
                  placeholder="Describe your progress and any challenges faced..."
                ></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showUpdateModal = false">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveTaskUpdate">
              <i class="bi bi-check-circle me-1"></i>
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Task Modal -->
    <div class="modal fade" :class="{ show: showCreateTaskModal }" 
         :style="{ display: showCreateTaskModal ? 'block' : 'none' }" 
         tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-plus-circle me-2"></i>
              Create New Task
            </h5>
            <button type="button" class="btn-close" @click="showCreateTaskModal = false"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Task Title</label>
              <input type="text" class="form-control" v-model="newTask.title" placeholder="Enter task title">
            </div>
            
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" rows="3" v-model="newTask.description" placeholder="Enter task description"></textarea>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Project</label>
                  <select class="form-select" v-model="newTask.project">
                    <option value="">Select Project</option>
                    <option value="E-commerce Platform">E-commerce Platform</option>
                    <option value="Mobile App Backend">Mobile App Backend</option>
                    <option value="Data Analytics Dashboard">Data Analytics Dashboard</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="mb-3">
                  <label class="form-label">Priority</label>
                  <select class="form-select" v-model="newTask.priority">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                  </select>
                </div>
              </div>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Due Date</label>
              <input type="date" class="form-control" v-model="newTask.dueDate">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showCreateTaskModal = false">Cancel</button>
            <button type="button" class="btn btn-primary" @click="createTask">
              <i class="bi bi-plus-circle me-1"></i>
              Create Task
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Backdrop -->
    <div class="modal-backdrop fade show" 
         v-if="showTaskModal || showUpdateModal || showCreateTaskModal"></div>
  </div>
</template>

<script>
import StudentSidebar from './StudentSidebar.vue'

export default {
  name: 'StudentTask',
  components: {
    StudentSidebar
  },
  data() {
    return {
      searchQuery: '',
      statusFilter: '',
      priorityFilter: '',
      showTaskModal: false,
      showUpdateModal: false,
      showCreateTaskModal: false,
      selectedTask: null,
      progressUpdate: 0,
      statusUpdate: 'in_progress',
      taskNotes: '',
      newTask: {
        title: '',
        description: '',
        project: '',
        priority: 'medium',
        dueDate: ''
      },
      tasks: [],
      loading: true,
      error: null
    }
  },
  computed: {
    filteredTasks() {
      return this.tasks.filter(task => {
        const matchesSearch = task.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            task.description.toLowerCase().includes(this.searchQuery.toLowerCase())
        const matchesStatus = !this.statusFilter || task.status === this.statusFilter
        const matchesPriority = !this.priorityFilter || task.priority === this.priorityFilter
        
        return matchesSearch && matchesStatus && matchesPriority
      })
    },
    totalTasks() {
      return this.tasks.length
    },
    completedTasks() {
      return this.tasks.filter(t => t.status === 'completed').length
    },
    inProgressTasks() {
      return this.tasks.filter(t => t.status === 'in_progress').length
    },
    overdueTasks() {
      return this.tasks.filter(t => t.status === 'overdue').length
    }
  },
  async mounted() {
    await this.fetchTasks()
  },
  methods: {
    async fetchTasks() {
      try {
        this.loading = true
        this.error = null
        
        const response = await fetch('/api/student/tasks', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (!response.ok) {
          throw new Error('Failed to fetch tasks')
        }
        
        const data = await response.json()
        this.tasks = data.data
      } catch (error) {
        console.error('Error fetching tasks:', error)
        this.error = 'Failed to load tasks. Please try again.'
      } finally {
        this.loading = false
      }
    },
    
    async saveTaskUpdate() {
      if (this.selectedTask) {
        try {
          const response = await fetch(`/api/student/tasks/${this.selectedTask.id}/progress`, {
            method: 'PUT',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              progress: this.progressUpdate,
              status: this.statusUpdate,
              notes: this.taskNotes
            })
          })
          
          if (!response.ok) {
            throw new Error('Failed to update task')
          }
          
          // Update local task data
          this.selectedTask.progress = this.progressUpdate
          this.selectedTask.status = this.statusUpdate
          this.selectedTask.lastUpdated = new Date().toISOString().split('T')[0]
          
          this.showUpdateModal = false
          this.showToast('Task updated successfully', 'success')
        } catch (error) {
          console.error('Error updating task:', error)
          this.showToast('Failed to update task', 'error')
        }
      }
    },
    
    async createTask() {
      if (this.newTask.title && this.newTask.project && this.newTask.dueDate) {
        try {
          const response = await fetch('/api/student/tasks', {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              title: this.newTask.title,
              description: this.newTask.description,
              project_id: this.newTask.project,
              priority: this.newTask.priority,
              due_date: this.newTask.dueDate
            })
          })
          
          if (!response.ok) {
            throw new Error('Failed to create task')
          }
          
          const data = await response.json()
          
          // Add new task to local array
          this.tasks.push({
            id: data.data.id,
            title: this.newTask.title,
            description: this.newTask.description,
            project: this.newTask.project,
            priority: this.newTask.priority,
            status: 'pending',
            progress: 0,
            assignedDate: new Date().toISOString().split('T')[0],
            dueDate: this.newTask.dueDate,
            lastUpdated: new Date().toISOString().split('T')[0],
            daysLeft: this.calculateDaysLeft(this.newTask.dueDate),
            requirements: [
              'Complete the task according to specifications',
              'Submit progress updates regularly',
              'Meet the deadline requirements'
            ]
          })
          
          this.showCreateTaskModal = false
          this.resetNewTask()
          this.showToast('Task created successfully', 'success')
        } catch (error) {
          console.error('Error creating task:', error)
          this.showToast('Failed to create task', 'error')
        }
      } else {
        this.showToast('Please fill in all required fields', 'error')
      }
    },
    
    getPriorityClass(priority) {
      const classes = {
        high: 'badge bg-danger',
        medium: 'badge bg-warning',
        low: 'badge bg-success'
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
    
    getProgressClass(progress) {
      if (progress >= 80) return 'bg-success'
      if (progress >= 50) return 'bg-warning'
      return 'bg-info'
    },
    
    getDueDateClass(dueDate) {
      const today = new Date()
      const due = new Date(dueDate)
      const diffTime = due - today
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
      
      if (diffDays < 0) return 'text-danger'
      if (diffDays <= 3) return 'text-warning'
      return 'text-muted'
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString()
    },
    
    filterTasks() {
      // Filtering is handled by computed property
    },
    
    viewTask(task) {
      this.selectedTask = task
      this.showTaskModal = true
    },
    
    updateTask(task) {
      this.selectedTask = task
      this.progressUpdate = task.progress
      this.statusUpdate = task.status
      this.taskNotes = ''
      this.showUpdateModal = true
    },
    
    submitTask(task) {
      // Implementation for submitting task
      console.log('Submitting task:', task.title)
      this.showToast('Task submitted successfully', 'success')
    },
    
    calculateDaysLeft(dueDate) {
      const today = new Date()
      const due = new Date(dueDate)
      const diffTime = due - today
      return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    },
    
    resetNewTask() {
      this.newTask = {
        title: '',
        description: '',
        project: '',
        priority: 'medium',
        dueDate: ''
      }
    },
    
    showToast(message, type = 'info') {
      // You can implement a toast notification system here
      console.log(`${type.toUpperCase()}: ${message}`)
    }
  }
}
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  padding: 0;
  margin-left: 20px;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
}
</style> 