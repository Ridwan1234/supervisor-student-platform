<template>
  <div class="projects-list">
    <div class="row g-3">
      <div class="col-12" v-for="(project, index) in projects" :key="index">
        <div class="card hover-lift">
          <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-2">
                  <h6 class="card-title mb-0 me-3">{{ project.name }}</h6>
                  <span class="badge" :class="getStatusClass(project.status)">
                    {{ project.status }}
                  </span>
                </div>
                <p class="text-muted mb-2">{{ project.description }}</p>
                <div class="d-flex align-items-center gap-4">
                  <small class="text-muted">
                    <i class="bi bi-calendar me-1"></i>
                    Due: {{ project.due }}
                  </small>
                  <small class="text-muted">
                    <i class="bi bi-person me-1"></i>
                    {{ project.students }} students
                  </small>
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>
                    {{ project.progress }}% complete
                  </small>
                </div>
              </div>
              <div class="d-flex gap-2">
                <router-link :to="{ name: 'project-management' }" class="btn btn-outline-primary btn-sm">
                  <i class="bi bi-eye"></i>
                </router-link>
                <router-link :to="{ name: 'project-management' }" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-pencil"></i>
                </router-link>
              </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-3">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">Progress</small>
                <small class="text-muted">{{ project.progress }}%</small>
              </div>
              <div class="progress" style="height: 6px;">
                <div 
                  class="progress-bar" 
                  :class="getProgressClass(project.progress)"
                  :style="{ width: project.progress + '%' }"
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
      projects: [
        { 
          name: 'Project Alpha', 
          description: 'Research and development of new algorithms for data processing',
          due: 'July 15, 2025', 
          status: 'In Progress',
          students: 3,
          progress: 65
        },
        { 
          name: 'Project Beta', 
          description: 'Implementation of machine learning models for predictive analysis',
          due: 'August 1, 2025', 
          status: 'Completed',
          students: 2,
          progress: 100
        },
        { 
          name: 'Project Gamma', 
          description: 'Development of web application for student management system',
          due: 'September 10, 2025', 
          status: 'Planning',
          students: 4,
          progress: 15
        },
      ],
    };
  },
  methods: {
    getStatusClass(status) {
      const statusClasses = {
        'In Progress': 'bg-warning text-dark',
        'Completed': 'bg-success',
        'Planning': 'bg-info',
        'On Hold': 'bg-secondary',
        'Cancelled': 'bg-danger'
      };
      return statusClasses[status] || 'bg-secondary';
    },
    getProgressClass(progress) {
      if (progress >= 80) return 'bg-success';
      if (progress >= 50) return 'bg-warning';
      return 'bg-info';
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
