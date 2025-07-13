<template>
  <div class="projects-list">
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-4">
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

    <!-- Empty State -->
    <div v-else-if="projects.length === 0" class="text-center py-4">
      <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
        <i class="bi bi-folder text-muted" style="font-size: 2rem;"></i>
      </div>
      <h5 class="text-muted">No projects found</h5>
      <p class="text-muted">Create your first project to get started</p>
      <router-link :to="{ name: 'project-management' }" class="btn btn-primary">
        <i class="bi bi-plus me-2"></i>Create Project
      </router-link>
    </div>

    <!-- Projects List -->
    <div v-else class="row g-3">
      <div class="col-12" v-for="project in projects" :key="project.id">
        <div class="card hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-2">
                  <h6 class="card-title mb-0 me-3">{{ project.title }}</h6>
                  <span class="badge" :class="getStatusClass(project.status)">
                    {{ formatStatus(project.status) }}
                  </span>
                </div>
                <p class="text-muted mb-2">{{ project.description }}</p>
                <div class="d-flex align-items-center gap-4">
                  <small class="text-muted">
                    <i class="bi bi-calendar me-1"></i>
                    Due: {{ formatDate(project.due_date) }}
                  </small>
                  <small class="text-muted">
                    <i class="bi bi-person me-1"></i>
                    {{ project.students ? project.students.length : 0 }} students
                  </small>
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    {{ project.progress || 0 }}% complete
                  </small>
                </div>
              </div>
              <div class="d-flex gap-2">
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
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">Progress</small>
                <small class="text-muted">{{ project.progress || 0 }}%</small>
              </div>
              <div class="progress" style="height: 6px;">
                <div 
                  class="progress-bar" 
                  :class="getProgressClass(project.progress || 0)"
                  :style="{ width: (project.progress || 0) + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProjectsList',
  data() {
    return {
      projects: [],
      loading: true,
      error: null
    };
  },
  async mounted() {
    await this.fetchProjects();
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
        this.projects = data.data || [];
        
      } catch (error) {
        console.error('Error fetching projects:', error);
        this.error = 'Failed to load projects. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    getStatusClass(status) {
      const statusClasses = {
        'in_progress': 'bg-warning text-dark',
        'completed': 'bg-success',
        'not_started': 'bg-info',
        'on_hold': 'bg-secondary'
      };
      return statusClasses[status] || 'bg-secondary';
    },
    
    formatStatus(status) {
      const statusMap = {
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'not_started': 'Not Started',
        'on_hold': 'On Hold'
      };
      return statusMap[status] || status;
    },
    
    getProgressClass(progress) {
      if (progress >= 80) return 'bg-success';
      if (progress >= 50) return 'bg-warning';
      return 'bg-info';
    },
    
    formatDate(date) {
      if (!date) return 'No due date';
      return new Date(date).toLocaleDateString();
    },
    
    viewProject(project) {
      // Navigate to project details or emit event
      this.$emit('view-project', project);
    },
    
    editProject(project) {
      // Navigate to project management with edit mode
      this.$router.push({ 
        name: 'project-management',
        query: { edit: project.id }
      });
    },
    
    async deleteProject(project) {
      if (confirm(`Are you sure you want to delete "${project.title}"? This action cannot be undone.`)) {
        try {
          const response = await fetch(`/api/projects/${project.id}`, {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
              'Content-Type': 'application/json'
            }
          });
          
          if (!response.ok) {
            throw new Error('Failed to delete project');
          }
          
          // Remove project from local array
          const index = this.projects.findIndex(p => p.id === project.id);
          if (index !== -1) {
            this.projects.splice(index, 1);
          }
          
          // Show success message
          this.$emit('project-deleted', project);
          
        } catch (error) {
          console.error('Error deleting project:', error);
          alert('Failed to delete project. Please try again.');
        }
      }
    }
  },
};
</script>

<style scoped>
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

.progress {
  border-radius: 0.25rem;
  background-color: #e9ecef;
}

.badge {
  font-size: 0.75rem;
  font-weight: 500;
}
</style>
