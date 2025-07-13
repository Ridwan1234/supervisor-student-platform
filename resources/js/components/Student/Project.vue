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
                  3
                </span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li><a class="dropdown-item" href="#">New task assigned</a></li>
                <li><a class="dropdown-item" href="#">Project deadline reminder</a></li>
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
                <h4 class="mb-1">My Projects</h4>
                <p class="text-muted mb-0">Manage and track your academic projects</p>
              </div>
             
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3 text-muted">Loading projects...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          {{ error }}
          <button class="btn btn-outline-danger btn-sm ms-3" @click="fetchProjects">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Retry
          </button>
        </div>

        <!-- Project Overview Cards -->
        <div v-else class="row mb-4">
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="card bg-primary text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="card-title">Active Projects</h6>
                    <h3 class="mb-0">{{ activeProjects.length }}</h3>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-briefcase display-6"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="card bg-success text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="card-title">Completed</h6>
                    <h3 class="mb-0">{{ completedProjects.length }}</h3>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-check-circle display-6"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-md-4 col-sm-6 mb-3">
            <div class="card bg-warning text-white">
              <div class="card-body">
                <div class="d-flex justify-content-between">
                  <div>
                    <h6 class="card-title">In Progress</h6>
                    <h3 class="mb-0">{{ inProgressProjects.length }}</h3>
                  </div>
                  <div class="align-self-center">
                    <i class="bi bi-clock display-6"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Project List -->
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h5>Project Details</h5>
                  <div class="btn-group btn-group-sm">
                    <button 
                      class="btn" 
                      :class="viewMode === 'grid' ? 'btn-primary' : 'btn-outline-primary'"
                      @click="viewMode = 'grid'"
                    >
                      <i class="bi bi-grid-3x3-gap"></i>
                    </button>
                    <button 
                      class="btn" 
                      :class="viewMode === 'list' ? 'btn-primary' : 'btn-outline-primary'"
                      @click="viewMode = 'list'"
                    >
                      <i class="bi bi-list"></i>
                    </button>
                  </div>
                </div>

                <!-- Grid View -->
                <div v-if="viewMode === 'grid'" class="row">
                  <div 
                    v-for="project in projects" 
                    :key="project.id"
                    class="col-lg-4 col-md-6 mb-4"
                  >
                    <div class="card h-100 project-card">
                      <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">{{ project.title }}</h6>
                        <span :class="getStatusClass(project.status)">
                          {{ project.status }}
                        </span>
                      </div>
                      <div class="card-body">
                        <p class="card-text text-muted">{{ project.description }}</p>
                        
                        <div class="mb-3">
                          <div class="d-flex justify-content-between mb-1">
                            <small>Progress</small>
                            <small>{{ project.progress }}%</small>
                          </div>
                          <div class="progress" style="height: 8px;">
                            <div 
                              class="progress-bar" 
                              :class="getProgressClass(project.progress)"
                              :style="{ width: project.progress + '%' }"
                            ></div>
                          </div>
                        </div>

                        <div class="row text-center">
                          <div class="col-4">
                            <div class="border-end">
                              <h6 class="mb-0">{{ project.totalTasks }}</h6>
                              <small class="text-muted">Tasks</small>
                            </div>
                          </div>
                          <div class="col-4">
                            <div class="border-end">
                              <h6 class="mb-0">{{ project.completedTasks }}</h6>
                              <small class="text-muted">Completed</small>
                            </div>
                          </div>
                          <div class="col-4">
                            <h6 class="mb-0">{{ project.daysLeft }}</h6>
                            <small class="text-muted">Days Left</small>
                          </div>
                        </div>
                      </div>
                      <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                          <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>
                            Due: {{ formatDate(project.dueDate) }}
                          </small>
                          <button class="btn btn-primary btn-sm" @click="viewProject(project)">
                            View Details
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- List View -->
                <div v-else class="table-responsive">
                  <table class="table table-hover">
                    <thead class="table-light">
                      <tr>
                        <th>Project</th>
                        <th>Supervisor</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Tasks</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="project in projects" :key="project.id">
                        <td>
                          <div>
                            <strong>{{ project.title }}</strong>
                            <br>
                            <small class="text-muted">{{ project.description.substring(0, 50) }}...</small>
                          </div>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            {{ project.supervisor }}
                          </div>
                        </td>
                        <td>
                          <span :class="getStatusClass(project.status)">
                            {{ project.status }}
                          </span>
                        </td>
                        <td>
                          <div class="d-flex align-items-center">
                            <div class="progress me-2" style="width: 100px; height: 8px;">
                              <div 
                                class="progress-bar" 
                                :class="getProgressClass(project.progress)"
                                :style="{ width: project.progress + '%' }"
                              ></div>
                            </div>
                            <small>{{ project.progress }}%</small>
                          </div>
                        </td>
                        <td>
                          <span class="badge bg-info">{{ project.completedTasks }}/{{ project.totalTasks }}</span>
                        </td>
                        <td>
                          <span :class="getDueDateClass(project.dueDate)">
                            {{ formatDate(project.dueDate) }}
                          </span>
                        </td>
                        <td>
                          <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" @click="viewProject(project)">
                              <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-outline-success" @click="updateProgress(project)">
                              <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-outline-info" @click="viewTasks(project)">
                              <i class="bi bi-list-task"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Project Details Modal -->
    <div class="modal fade" :class="{ show: showProjectModal }" 
         :style="{ display: showProjectModal ? 'block' : 'none' }" 
         tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-briefcase me-2"></i>
              Project Details
            </h5>
            <button type="button" class="btn-close" @click="showProjectModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedProject">
              <div class="row">
                <div class="col-md-8">
                  <h4>{{ selectedProject.title }}</h4>
                  <p class="text-muted">{{ selectedProject.description }}</p>
                  
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <h6>Project Information</h6>
                      <ul class="list-unstyled">
                        <li><strong>Supervisor:</strong> {{ selectedProject.supervisor }}</li>
                        <li><strong>Start Date:</strong> {{ formatDate(selectedProject.startDate) }}</li>
                        <li><strong>Due Date:</strong> {{ formatDate(selectedProject.dueDate) }}</li>
                        <li><strong>Status:</strong> 
                          <span :class="getStatusClass(selectedProject.status)">
                            {{ selectedProject.status }}
                          </span>
                        </li>
                      </ul>
                    </div>
                    <div class="col-md-6">
                      <h6>Progress Overview</h6>
                      <div class="progress mb-2" style="height: 20px;">
                        <div 
                          class="progress-bar" 
                          :class="getProgressClass(selectedProject.progress)"
                          :style="{ width: selectedProject.progress + '%' }"
                        >
                          {{ selectedProject.progress }}%
                        </div>
                      </div>
                      <small class="text-muted">Last updated: {{ formatDate(selectedProject.lastUpdated) }}</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="card">
                    <div class="card-header">
                      <h6 class="mb-0">Quick Stats</h6>
                    </div>
                    <div class="card-body">
                      <div class="d-flex justify-content-between mb-2">
                        <span>Total Tasks:</span>
                        <strong>{{ selectedProject.totalTasks }}</strong>
                      </div>
                      <div class="d-flex justify-content-between mb-2">
                        <span>Completed:</span>
                        <strong class="text-success">{{ selectedProject.completedTasks }}</strong>
                      </div>
                      <div class="d-flex justify-content-between mb-2">
                        <span>Remaining:</span>
                        <strong class="text-warning">{{ selectedProject.totalTasks - selectedProject.completedTasks }}</strong>
                      </div>
                      <div class="d-flex justify-content-between">
                        <span>Days Left:</span>
                        <strong :class="getDueDateClass(selectedProject.dueDate)">{{ selectedProject.daysLeft }}</strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <hr>
              
              <div class="row">
                <div class="col-12">
                  <h6>Recent Activities</h6>
                  <div class="timeline">
                    <div v-for="activity in selectedProject.activities" :key="activity.id" class="timeline-item">
                      <div class="timeline-marker"></div>
                      <div class="timeline-content">
                        <div class="d-flex justify-content-between">
                          <strong>{{ activity.title }}</strong>
                          <small class="text-muted">{{ formatDate(activity.date) }}</small>
                        </div>
                        <p class="mb-0 text-muted">{{ activity.description }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showProjectModal = false">Close</button>
            <button type="button" class="btn btn-primary" @click="updateProgress(selectedProject)">
              <i class="bi bi-pencil me-1"></i>
              Update Progress
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Progress Update Modal -->
    <div class="modal fade" :class="{ show: showProgressModal }" 
         :style="{ display: showProgressModal ? 'block' : 'none' }" 
         tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-pencil me-2"></i>
              Update Progress
            </h5>
            <button type="button" class="btn-close" @click="showProgressModal = false"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedProject">
              <h6>{{ selectedProject.title }}</h6>
              
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
                  <option value="not_started">Not Started</option>
                  <option value="in_progress">In Progress</option>
                  <option value="completed">Completed</option>
                  <option value="on_hold">On Hold</option>
                </select>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Update Notes</label>
                <textarea 
                  class="form-control" 
                  rows="3"
                  v-model="progressNotes"
                  placeholder="Describe your progress and any challenges faced..."
                ></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showProgressModal = false">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveProgress">
              <i class="bi bi-check-circle me-1"></i>
              Save Progress
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Backdrop -->
    <div class="modal-backdrop fade show" 
         v-if="showProjectModal || showProgressModal"></div>
  </div>
</template>

<script>
import StudentSidebar from './StudentSidebar.vue'

export default {
  name: 'StudentProject',
  components: {
    StudentSidebar
  },
  data() {
    return {
      viewMode: 'grid',
      showProjectModal: false,
      showProgressModal: false,
      selectedProject: null,
      progressUpdate: 0,
      statusUpdate: 'in_progress',
      progressNotes: '',
      projects: [],
      loading: true,
      error: null
    }
  },
  computed: {
    activeProjects() {
      return this.projects.filter(p => p.status === 'in_progress')
    },
    completedProjects() {
      return this.projects.filter(p => p.status === 'completed')
    },
    inProgressProjects() {
      return this.projects.filter(p => p.status === 'in_progress')
    },
    totalTasks() {
      return this.projects.reduce((total, project) => total + project.totalTasks, 0)
    }
  },
  async mounted() {
    await this.fetchProjects()
  },
  methods: {
    async fetchProjects() {
      try {
        this.loading = true
        this.error = null
        
        const response = await fetch('/api/student/projects', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (!response.ok) {
          throw new Error('Failed to fetch projects')
        }
        
        const data = await response.json()
        this.projects = data.data
      } catch (error) {
        console.error('Error fetching projects:', error)
        this.error = 'Failed to load projects. Please try again.'
      } finally {
        this.loading = false
      }
    },
    
    async saveProgress() {
      if (this.selectedProject) {
        try {
          const response = await fetch(`/api/student/projects/${this.selectedProject.id}/progress`, {
            method: 'PUT',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              progress: this.progressUpdate,
              status: this.statusUpdate,
              notes: this.progressNotes
            })
          })
          
          if (!response.ok) {
            throw new Error('Failed to update progress')
          }
          
          // Update local project data
          this.selectedProject.progress = this.progressUpdate
          this.selectedProject.status = this.statusUpdate
          this.selectedProject.lastUpdated = new Date().toISOString().split('T')[0]
          
          // Add activity locally
          this.selectedProject.activities.unshift({
            id: this.selectedProject.activities.length + 1,
            title: 'Progress Updated',
            description: this.progressNotes || `Progress updated to ${this.progressUpdate}%`,
            date: new Date().toISOString().split('T')[0]
          })
          
          this.showProgressModal = false
          this.showToast('Progress updated successfully', 'success')
        } catch (error) {
          console.error('Error updating progress:', error)
          this.showToast('Failed to update progress', 'error')
        }
      }
    },
    
    getStatusClass(status) {
      const classes = {
        not_started: 'badge bg-secondary',
        in_progress: 'badge bg-primary',
        completed: 'badge bg-success',
        on_hold: 'badge bg-warning'
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
      if (diffDays <= 7) return 'text-warning'
      return 'text-muted'
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString()
    },
    
    viewProject(project) {
      this.selectedProject = project
      this.showProjectModal = true
    },
    
    updateProgress(project) {
      this.selectedProject = project
      this.progressUpdate = project.progress
      this.statusUpdate = project.status
      this.progressNotes = ''
      this.showProgressModal = true
    },
    
    viewTasks(project) {
      // Implementation for viewing project tasks
      console.log('Viewing tasks for project:', project.title)
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

.project-card {
  transition: transform 0.2s, box-shadow 0.2s;
}

.project-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.avatar-sm {
  width: 32px;
  height: 32px;
  font-size: 14px;
}

.timeline {
  position: relative;
  padding-left: 30px;
}

.timeline-item {
  position: relative;
  margin-bottom: 20px;
}

.timeline-marker {
  position: absolute;
  left: -35px;
  top: 5px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background-color: #0d6efd;
  border: 2px solid #fff;
  box-shadow: 0 0 0 2px #0d6efd;
}

.timeline-content {
  background-color: #f8f9fa;
  padding: 15px;
  border-radius: 8px;
  border-left: 3px solid #0d6efd;
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
}
</style> 