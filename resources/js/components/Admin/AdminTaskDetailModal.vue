<template>
  <div v-if="visible" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-list-check me-2"></i>Task Details
        </h5>
        <button type="button" class="btn-close" @click="closeModal"></button>
      </div>

      <div class="modal-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading task details...</p>
        </div>

        <div v-else-if="task">
          <!-- Task Info -->
          <div class="row mb-4">
            <div class="col-md-8">
              <h4 class="mb-1">{{ task.title }}</h4>
              <p class="text-muted mb-3">{{ task.description }}</p>
            </div>
            <div class="col-md-4 text-end">
              <span class="badge fs-6 me-2" :class="statusBadgeClass(task.status)">
                {{ formatStatus(task.status) }}
              </span>
              <span class="badge fs-6" :class="priorityBadgeClass(task.priority)">
                {{ formatPriority(task.priority) }}
              </span>
            </div>
          </div>

          <!-- Task Details -->
          <div class="row">
            <div class="col-md-6">
              <div class="detail-section">
                <h6 class="section-title">Task Information</h6>
                <div class="detail-item">
                  <strong>Project:</strong> {{ task.project?.title || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Assigned To:</strong> {{ task.assigned_to?.name || 'Unassigned' }}
                </div>
                <div class="detail-item">
                  <strong>Created By:</strong> {{ task.created_by?.name || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Created:</strong> {{ formatDate(task.created_at) }}
                </div>
                <div class="detail-item">
                  <strong>Last Updated:</strong> {{ formatDate(task.updated_at) }}
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="detail-section">
                <h6 class="section-title">Status & Timeline</h6>
                <div class="detail-item">
                  <strong>Status:</strong>
                  <span class="badge ms-2" :class="statusBadgeClass(task.status)">
                    {{ formatStatus(task.status) }}
                  </span>
                </div>
                <div class="detail-item">
                  <strong>Priority:</strong>
                  <span class="badge ms-2" :class="priorityBadgeClass(task.priority)">
                    {{ formatPriority(task.priority) }}
                  </span>
                </div>
                <div class="detail-item">
                  <strong>Due Date:</strong> {{ formatDate(task.due_date) }}
                </div>
                <div class="detail-item">
                  <strong>Estimated Hours:</strong> {{ task.estimated_hours || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Actual Hours:</strong> {{ task.actual_hours || 'N/A' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Task History -->
          <div v-if="task.history && task.history.length > 0" class="mt-4">
            <h6 class="section-title">Task History</h6>
            <div class="history-list">
              <div v-for="history in task.history" :key="history.id" class="history-item">
                <div class="history-icon">
                  <i :class="getHistoryIcon(history.action)"></i>
                </div>
                <div class="history-content">
                  <div class="history-text">{{ history.description }}</div>
                  <div class="history-meta">
                    <small class="text-muted">{{ formatDateTime(history.created_at) }}</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
        <button type="button" class="btn btn-primary" @click="$emit('edit-task', task)">Edit Task</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminTaskDetailModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    taskId: {
      type: [Number, String],
      default: null
    }
  },
  emits: ['close', 'edit-task'],
  data() {
    return {
      task: null,
      loading: false
    };
  },
  watch: {
    visible(newVal) {
      if (newVal && this.taskId) {
        this.fetchTaskDetails();
      }
    },
    taskId(newVal) {
      if (newVal && this.visible) {
        this.fetchTaskDetails();
      }
    }
  },
  methods: {
    async fetchTaskDetails() {
      if (!this.taskId) return;

      this.loading = true;
      try {
        const response = await fetch(`/api/admin/tasks/${this.taskId}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch task details');
        }

        this.task = await response.json();
      } catch (error) {
        console.error('Error fetching task details:', error);
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

    formatDateTime(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleString();
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

    getHistoryIcon(action) {
      const icons = {
        'created': 'bi bi-plus-circle text-success',
        'updated': 'bi bi-pencil text-info',
        'status_changed': 'bi bi-arrow-repeat text-warning',
        'assigned': 'bi bi-person-check text-primary',
        'completed': 'bi bi-check-circle text-success'
      };
      return icons[action] || 'bi bi-circle text-muted';
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

.history-list {
  max-height: 300px;
  overflow-y: auto;
}

.history-item {
  display: flex;
  align-items: flex-start;
  padding: 0.75rem 0;
  border-bottom: 1px solid #f1f3f4;
}

.history-item:last-child {
  border-bottom: none;
}

.history-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f8f9fa;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.75rem;
  flex-shrink: 0;
}

.history-content {
  flex: 1;
}

.history-text {
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.history-meta {
  font-size: 0.75rem;
}
</style>
