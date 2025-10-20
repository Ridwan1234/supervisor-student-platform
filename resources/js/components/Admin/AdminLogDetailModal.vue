<template>
  <div v-if="visible" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-journal-text me-2"></i>Log Details
        </h5>
        <button type="button" class="btn-close" @click="closeModal"></button>
      </div>

      <div class="modal-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading log details...</p>
        </div>

        <div v-else-if="log">
          <!-- Log Header -->
          <div class="log-header mb-4">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h4 class="mb-2">{{ log.message }}</h4>
                <div class="d-flex gap-2 mb-2">
                  <span class="badge" :class="getLogLevelClass(log.level)">
                    {{ log.level.toUpperCase() }}
                  </span>
                  <span class="badge bg-secondary">
                    {{ log.category || 'General' }}
                  </span>
                </div>
              </div>
              <div class="text-end">
                <small class="text-muted">{{ formatDateTime(log.timestamp) }}</small>
              </div>
            </div>
          </div>

          <!-- Log Details -->
          <div class="row">
            <div class="col-md-6">
              <div class="detail-section">
                <h6 class="section-title">Log Information</h6>
                <div class="detail-item">
                  <strong>Log ID:</strong> {{ log.id }}
                </div>
                <div class="detail-item">
                  <strong>Level:</strong>
                  <span class="badge ms-2" :class="getLogLevelClass(log.level)">
                    {{ log.level.toUpperCase() }}
                  </span>
                </div>
                <div class="detail-item">
                  <strong>Category:</strong> {{ log.category || 'General' }}
                </div>
                <div class="detail-item">
                  <strong>User:</strong> {{ log.user?.name || 'System' }}
                </div>
                <div class="detail-item">
                  <strong>IP Address:</strong> {{ log.ip_address || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>User Agent:</strong> {{ log.user_agent || 'N/A' }}
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="detail-section">
                <h6 class="section-title">Context & Metadata</h6>
                <div class="detail-item">
                  <strong>Timestamp:</strong> {{ formatDateTime(log.timestamp) }}
                </div>
                <div class="detail-item">
                  <strong>Module:</strong> {{ log.module || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Action:</strong> {{ log.action || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Resource:</strong> {{ log.resource || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Resource ID:</strong> {{ log.resource_id || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Duration:</strong> {{ log.duration ? log.duration + 'ms' : 'N/A' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Log Context -->
          <div v-if="log.context" class="mt-4">
            <h6 class="section-title">Additional Context</h6>
            <div class="context-data">
              <pre>{{ JSON.stringify(log.context, null, 2) }}</pre>
            </div>
          </div>

          <!-- Log Stack Trace -->
          <div v-if="log.stack_trace" class="mt-4">
            <h6 class="section-title">Stack Trace</h6>
            <div class="stack-trace">
              <pre>{{ log.stack_trace }}</pre>
            </div>
          </div>

          <!-- Related Logs -->
          <div v-if="relatedLogs && relatedLogs.length > 0" class="mt-4">
            <h6 class="section-title">Related Logs</h6>
            <div class="related-logs">
              <div v-for="relatedLog in relatedLogs" :key="relatedLog.id" class="related-log-item">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <small class="text-muted">{{ formatDateTime(relatedLog.timestamp) }}</small>
                    <div class="related-log-message">{{ relatedLog.message }}</div>
                  </div>
                  <span class="badge" :class="getLogLevelClass(relatedLog.level)">
                    {{ relatedLog.level.toUpperCase() }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
        <button type="button" class="btn btn-outline-primary" @click="exportLog">Export Log</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminLogDetailModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    logId: {
      type: [Number, String],
      default: null
    }
  },
  emits: ['close'],
  data() {
    return {
      log: null,
      relatedLogs: [],
      loading: false
    };
  },
  watch: {
    visible(newVal) {
      if (newVal && this.logId) {
        this.fetchLogDetails();
      }
    },
    logId(newVal) {
      if (newVal && this.visible) {
        this.fetchLogDetails();
      }
    }
  },
  methods: {
    async fetchLogDetails() {
      if (!this.logId) return;

      this.loading = true;
      try {
        const response = await fetch(`/api/admin/logs/${this.logId}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch log details');
        }

        const data = await response.json();
        this.log = data.log;
        this.relatedLogs = data.related_logs || [];
      } catch (error) {
        console.error('Error fetching log details:', error);
      } finally {
        this.loading = false;
      }
    },

    closeModal() {
      this.$emit('close');
    },

    formatDateTime(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleString();
    },

    getLogLevelClass(level) {
      const classMap = {
        'error': 'bg-danger',
        'warning': 'bg-warning',
        'info': 'bg-info',
        'debug': 'bg-secondary'
      };
      return classMap[level] || 'bg-secondary';
    },

    exportLog() {
      // TODO: Implement log export functionality
      console.log('Export log:', this.log);
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
  max-width: 900px;
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

.log-header {
  border-bottom: 2px solid #e9ecef;
  padding-bottom: 1rem;
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

.context-data pre,
.stack-trace pre {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  padding: 1rem;
  font-size: 0.75rem;
  overflow-x: auto;
  max-height: 200px;
  overflow-y: auto;
}

.related-logs {
  max-height: 200px;
  overflow-y: auto;
}

.related-log-item {
  padding: 0.75rem;
  border: 1px solid #dee2e6;
  border-radius: 4px;
  margin-bottom: 0.5rem;
  background: #f8f9fa;
}

.related-log-message {
  font-size: 0.875rem;
  margin-top: 0.25rem;
  color: #495057;
}
</style>
