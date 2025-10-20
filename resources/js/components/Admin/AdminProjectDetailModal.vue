<template>
  <div v-if="visible" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-folder me-2"></i>Project Details
        </h5>
        <button type="button" class="btn-close" @click="closeModal"></button>
      </div>

      <div class="modal-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading project details...</p>
        </div>

        <div v-else-if="project">
          <!-- Project Info -->
          <div class="row mb-4">
            <div class="col-md-8">
              <h4 class="mb-1">{{ project.title }}</h4>
              <p class="text-muted mb-3">{{ project.description }}</p>
            </div>
            <div class="col-md-4 text-end">
              <span class="badge fs-6" :class="statusBadgeClass(project.status)">
                {{ formatStatus(project.status) }}
              </span>
            </div>
          </div>

          <!-- Project Stats -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="stat-box">
                <div class="stat-value">{{ project.students_count || 0 }}</div>
                <div class="stat-label">Students</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-box">
                <div class="stat-value">{{ project.tasks_count || 0 }}</div>
                <div class="stat-label">Tasks</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-box">
                <div class="stat-value">{{ project.progress }}%</div>
                <div class="stat-label">Progress</div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="stat-box">
                <div class="stat-value">{{ project.files_count || 0 }}</div>
                <div class="stat-label">Files</div>
              </div>
            </div>
          </div>

          <!-- Project Details -->
          <div class="row">
            <div class="col-md-6">
              <div class="detail-section">
                <h6 class="section-title">Project Information</h6>
                <div class="detail-item">
                  <strong>Supervisor:</strong> {{ project.supervisor?.name || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Created:</strong> {{ formatDate(project.created_at) }}
                </div>
                <div class="detail-item">
                  <strong>Last Updated:</strong> {{ formatDate(project.updated_at) }}
                </div>
                <div class="detail-item">
                  <strong>Status:</strong>
                  <span class="badge ms-2" :class="statusBadgeClass(project.status)">
                    {{ formatStatus(project.status) }}
                  </span>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="detail-section">
                <h6 class="section-title">Progress Information</h6>
                <div class="progress mb-3" style="height: 10px;">
                  <div class="progress-bar" :style="{ width: project.progress + '%' }"></div>
                </div>
                <div class="detail-item">
                  <strong>Progress:</strong> {{ project.progress }}%
                </div>
                <div class="detail-item">
                  <strong>Start Date:</strong> {{ formatDate(project.start_date) }}
                </div>
                <div class="detail-item">
                  <strong>Due Date:</strong> {{ formatDate(project.due_date) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Students List -->
          <div v-if="project.students && project.students.length > 0" class="mt-4">
            <h6 class="section-title">Assigned Students</h6>
            <div class="students-list">
              <div v-for="student in project.students" :key="student.id" class="student-item">
                <div class="student-avatar">
                  <i class="bi bi-person-circle"></i>
                </div>
                <div class="student-info">
                  <div class="student-name">{{ student.name }}</div>
                  <div class="student-email">{{ student.email }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
        <button type="button" class="btn btn-primary" @click="$emit('edit-project', project)">Edit Project</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminProjectDetailModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    projectId: {
      type: [Number, String],
      default: null
    }
  },
  emits: ['close', 'edit-project'],
  data() {
    return {
      project: null,
      loading: false
    };
  },
  watch: {
    visible(newVal) {
      if (newVal && this.projectId) {
        this.fetchProjectDetails();
      }
    },
    projectId(newVal) {
      if (newVal && this.visible) {
        this.fetchProjectDetails();
      }
    }
  },
  methods: {
    async fetchProjectDetails() {
      if (!this.projectId) return;

      this.loading = true;
      try {
        const response = await fetch(`/api/admin/projects/${this.projectId}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch project details');
        }

        this.project = await response.json();
      } catch (error) {
        console.error('Error fetching project details:', error);
      } finally {
        this.loading = false;
      }
    },

    closeModal() {
      this.$emit('close');
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
    }
  }
};
</script>

<style scoped>
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
  max-width: 800px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 500;
}

.btn-close {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  opacity: 0.5;
}

.btn-close:hover {
  opacity: 1;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
}

.stat-box {
  text-align: center;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: bold;
  color: #495057;
}

.stat-label {
  font-size: 0.875rem;
  color: #6c757d;
  margin-top: 0.25rem;
}

.detail-section {
  margin-bottom: 1.5rem;
}

.section-title {
  font-weight: 600;
  color: #495057;
  margin-bottom: 1rem;
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 0.5rem;
}

.detail-item {
  margin-bottom: 0.75rem;
  font-size: 0.875rem;
}

.students-list {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.student-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 8px;
  min-width: 200px;
}

.student-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.75rem;
  color: #6c757d;
}

.student-name {
  font-weight: 500;
  color: #495057;
}

.student-email {
  font-size: 0.75rem;
  color: #6c757d;
}
</style>
