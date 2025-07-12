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
            <h4 class="mb-0 fw-bold text-primary">Project Management</h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Search Bar -->
            <div class="position-relative">
              <input 
                type="text" 
                class="form-control form-control-sm" 
                placeholder="Search projects..." 
                v-model="searchQuery"
              />
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
            </div>

            <!-- Add New Project Button -->
            <button class="btn btn-primary" @click="showCreateModal = true">
              <i class="bi bi-plus me-2"></i>New Project
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

        <!-- Stats Cards -->
        <div v-else class="row mb-4">
          <div class="col-md-3" v-for="(stat, index) in stats" :key="index">
            <div class="card hover-lift">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h6 class="text-muted mb-1">{{ stat.label }}</h6>
                    <h3 class="fw-bold mb-0">{{ stat.value }}</h3>
                    <small class="text-success">
                      <i class="bi bi-arrow-up me-1"></i>
                      {{ stat.change }}% from last month
                    </small>
                  </div>
                  <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i :class="stat.icon" class="text-primary"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Projects Table -->
        <div class="card">
          <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">All Projects</h5>
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm" v-model="statusFilter" style="width: auto;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="on-hold">On Hold</option>
              </select>
              <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-funnel"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Project Name</th>
                    <th>Students</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="project in filteredProjects" :key="project.id">
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                          <i class="bi bi-folder text-primary"></i>
                        </div>
                        <div>
                          <h6 class="mb-0">{{ project.title }}</h6>
                          <small class="text-muted">{{ project.description }}</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar-group me-2">
                          <img v-for="student in project.students.slice(0, 3)" :key="student.id" 
                               :src="student.avatar" :alt="student.name" 
                               class="rounded-circle" style="width: 30px; height: 30px; margin-left: -5px;">
                        </div>
                        <span v-if="project.students.length > 3" class="badge bg-secondary">
                          +{{ project.students.length - 3 }}
                        </span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="progress me-2" style="width: 100px; height: 6px;">
                          <div class="progress-bar" :class="getProgressClass(project.progress)" 
                               :style="{ width: project.progress + '%' }"></div>
                        </div>
                        <small>{{ project.progress }}%</small>
                      </div>
                    </td>
                    <td>
                      <span class="badge" :class="getStatusClass(project.status)">
                        {{ project.status }}
                      </span>
                    </td>
                    <td>
                      <small class="text-muted">{{ formatDate(project.dueDate) }}</small>
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary btn-sm" @click="viewProject(project)">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" @click="editProject(project)">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" @click="deleteProject(project)">
                          <i class="bi bi-trash"></i>
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
    </main>

    <!-- Create Project Modal -->
    <div class="modal fade" id="createProjectModal" tabindex="-1" v-if="showCreateModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Create New Project</h5>
            <button type="button" class="btn-close" @click="showCreateModal = false"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="createProject">
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-3">
                    <label class="form-label">Project Name</label>
                    <input type="text" class="form-control" v-model="newProject.name" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" v-model="newProject.status" required>
                      <option value="active">Active</option>
                      <option value="planning">Planning</option>
                      <option value="on-hold">On Hold</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" rows="3" v-model="newProject.description" required></textarea>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" v-model="newProject.startDate" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" class="form-control" v-model="newProject.dueDate" required>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Assign Students</label>
                <select class="form-select" multiple v-model="newProject.students" size="4">
                  <option v-for="student in availableStudents" :key="student.id" :value="student.id">
                    {{ student.name }} - {{ student.email }}
                  </option>
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple students</small>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showCreateModal = false">Cancel</button>
            <button type="button" class="btn btn-primary" @click="createProject">Create Project</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SupervisorSidebar from './SupervisorSidebar.vue';

export default {
  name: 'ProjectManagement',
  components: {
    SupervisorSidebar
  },
  data() {
    return {
      searchQuery: '',
      statusFilter: '',
      showCreateModal: true,
      projects: [],
      availableStudents: [],
      loading: true,
      error: null,
      stats: [
        { label: 'Total Projects', value: 0, change: 12, icon: 'bi bi-folder' },
        { label: 'Active Projects', value: 0, change: 8, icon: 'bi bi-play-circle' },
        { label: 'Completed', value: 0, change: 15, icon: 'bi bi-check-circle' },
        { label: 'Students', value: 0, change: 5, icon: 'bi bi-people' }
      ],
      newProject: {
        name: '',
        description: '',
        status: 'active',
        startDate: '',
        dueDate: '',
        students: []
      }
    };
  },
  computed: {
    filteredProjects() {
      return this.projects.filter(project => {
        const matchesSearch = project.title.toLowerCase().includes(this.searchQuery.toLowerCase());
        const matchesStatus = !this.statusFilter || project.status === this.statusFilter;
        return matchesSearch && matchesStatus;
      });
    }
  },
  async mounted() {
    await this.fetchProjects();
    await this.fetchAvailableStudents();
  },
  methods: {
    async fetchProjects() {
      try {
        this.loading = true;
        this.error = null;
        
        const response = await fetch('/api/supervisor/projects', {
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
        
        // Update stats
        this.updateStats();
        
      } catch (error) {
        console.error('Error fetching projects:', error);
        this.error = 'Failed to load projects. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    async fetchAvailableStudents() {
      try {
        const response = await fetch('/api/supervisor/available-students', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch available students');
        }

        const data = await response.json();
        this.availableStudents = data.data;
      } catch (error) {
        console.error('Error fetching available students:', error);
        this.showToast('Failed to load available students', 'error');
      }
    },
    
    updateStats() {
      const totalProjects = this.projects.length;
      const activeProjects = this.projects.filter(p => p.status === 'in_progress').length;
      const completedProjects = this.projects.filter(p => p.status === 'completed').length;
      const totalStudents = this.projects.reduce((total, project) => total + project.students.length, 0);
      
      this.stats = [
        { label: 'Total Projects', value: totalProjects, change: 12, icon: 'bi bi-folder' },
        { label: 'Active Projects', value: activeProjects, change: 8, icon: 'bi bi-play-circle' },
        { label: 'Completed', value: completedProjects, change: 15, icon: 'bi bi-check-circle' },
        { label: 'Students', value: totalStudents, change: 5, icon: 'bi bi-people' }
      ];
    },
    
    async createProject() {
      try {
        const response = await fetch('/api/supervisor/projects', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            title: this.newProject.name,
            description: this.newProject.description,
            status: this.newProject.status,
            start_date: this.newProject.startDate,
            due_date: this.newProject.dueDate,
            student_ids: this.newProject.students
          })
        });
        
        if (!response.ok) {
          throw new Error('Failed to create project');
        }
        
        const data = await response.json();
        
        // Add new project to local array
        this.projects.push({
          id: data.data.id,
          title: this.newProject.name,
          description: this.newProject.description,
          status: this.newProject.status,
          progress: 0,
          students: [],
          startDate: this.newProject.startDate,
          dueDate: this.newProject.dueDate
        });
        
        this.showCreateModal = false;
        this.resetNewProject();
        this.updateStats();
        this.showToast('Project created successfully', 'success');
        
      } catch (error) {
        console.error('Error creating project:', error);
        this.showToast('Failed to create project', 'error');
      }
    },
    
    resetNewProject() {
      this.newProject = {
        name: '',
        description: '',
        status: 'active',
        startDate: '',
        dueDate: '',
        students: []
      };
    },
    
    getProgressClass(progress) {
      if (progress >= 80) return 'bg-success';
      if (progress >= 50) return 'bg-warning';
      return 'bg-info';
    },
    
    getStatusClass(status) {
      const classes = {
        'not_started': 'bg-secondary',
        'in_progress': 'bg-primary',
        'completed': 'bg-success',
        'on_hold': 'bg-warning'
      };
      return classes[status] || 'bg-secondary';
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    },
    
    viewProject(project) {
      console.log('Viewing project:', project);
    },
    
    editProject(project) {
      console.log('Editing project:', project);
    },
    
    deleteProject(project) {
      console.log('Deleting project:', project);
    },
    
    toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    },
    
    showToast(message, type = 'info') {
      console.log(`${type.toUpperCase()}: ${message}`);
    }
  }
};
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

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.bg-opacity-10 {
  background-color: rgba(13, 110, 253, 0.1) !important;
}

.progress {
  border-radius: 0.25rem;
  background-color: #e9ecef;
}

.avatar-group img {
  border: 2px solid white;
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
