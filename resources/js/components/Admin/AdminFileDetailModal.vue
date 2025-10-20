<template>
  <div v-if="visible" class="modal-overlay" @click="closeModal">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-file-earmark me-2"></i>File Details
        </h5>
        <button type="button" class="btn-close" @click="closeModal"></button>
      </div>

      <div class="modal-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading file details...</p>
        </div>

        <div v-else-if="file">
          <!-- File Info -->
          <div class="row mb-4">
            <div class="col-md-8">
              <div class="d-flex align-items-center mb-2">
                <i :class="getFileIcon(file.type)" class="me-3 fs-2 text-primary"></i>
                <div>
                  <h4 class="mb-1">{{ file.name }}</h4>
                  <p class="text-muted mb-0">{{ file.description }}</p>
                </div>
              </div>
            </div>
            <div class="col-md-4 text-end">
              <span class="badge fs-6" :class="typeBadgeClass(file.type)">
                {{ file.type }}
              </span>
            </div>
          </div>

          <!-- File Preview -->
          <div v-if="isImageFile(file.type)" class="file-preview mb-4">
            <img :src="file.url" :alt="file.name" class="img-fluid rounded" />
          </div>

          <!-- File Details -->
          <div class="row">
            <div class="col-md-6">
              <div class="detail-section">
                <h6 class="section-title">File Information</h6>
                <div class="detail-item">
                  <strong>Owner:</strong> {{ file.owner?.name || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Type:</strong> {{ file.type }}
                </div>
                <div class="detail-item">
                  <strong>Size:</strong> {{ formatFileSize(file.size) }}
                </div>
                <div class="detail-item">
                  <strong>Downloads:</strong> {{ file.downloads_count || 0 }}
                </div>
                <div class="detail-item">
                  <strong>Created:</strong> {{ formatDate(file.created_at) }}
                </div>
                <div class="detail-item">
                  <strong>Last Modified:</strong> {{ formatDate(file.updated_at) }}
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="detail-section">
                <h6 class="section-title">File Statistics</h6>
                <div class="detail-item">
                  <strong>Views:</strong> {{ file.views_count || 0 }}
                </div>
                <div class="detail-item">
                  <strong>Shares:</strong> {{ file.shares_count || 0 }}
                </div>
                <div class="detail-item">
                  <strong>Comments:</strong> {{ file.comments_count || 0 }}
                </div>
                <div class="detail-item">
                  <strong>Project:</strong> {{ file.project?.title || 'N/A' }}
                </div>
                <div class="detail-item">
                  <strong>Access Level:</strong>
                  <span class="badge ms-2" :class="accessBadgeClass(file.access_level)">
                    {{ formatAccessLevel(file.access_level) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- File Actions -->
          <div class="file-actions mt-4">
            <div class="d-flex gap-2">
              <button class="btn btn-outline-primary" @click="downloadFile">
                <i class="bi bi-download me-1"></i>Download
              </button>
              <button v-if="isPreviewable(file.type)" class="btn btn-outline-info" @click="previewFile">
                <i class="bi bi-eye me-1"></i>Preview
              </button>
              <button class="btn btn-outline-secondary" @click="shareFile">
                <i class="bi bi-share me-1"></i>Share
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
        <button type="button" class="btn btn-danger" @click="$emit('delete-file', file)">Delete File</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminFileDetailModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    fileId: {
      type: [Number, String],
      default: null
    }
  },
  emits: ['close', 'delete-file'],
  data() {
    return {
      file: null,
      loading: false
    };
  },
  watch: {
    visible(newVal) {
      if (newVal && this.fileId) {
        this.fetchFileDetails();
      }
    },
    fileId(newVal) {
      if (newVal && this.visible) {
        this.fetchFileDetails();
      }
    }
  },
  methods: {
    async fetchFileDetails() {
      if (!this.fileId) return;

      this.loading = true;
      try {
        const response = await fetch(`/api/admin/files/${this.fileId}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch file details');
        }

        this.file = await response.json();
      } catch (error) {
        console.error('Error fetching file details:', error);
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

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      const sizes = ['B', 'KB', 'MB', 'GB'];
      const i = Math.floor(Math.log(bytes) / Math.log(1024));
      return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
    },

    getFileIcon(type) {
      const iconMap = {
        'document': 'bi bi-file-earmark-text',
        'image': 'bi bi-file-earmark-image',
        'video': 'bi bi-file-earmark-play',
        'pdf': 'bi bi-file-earmark-pdf',
        'spreadsheet': 'bi bi-file-earmark-spreadsheet',
        'presentation': 'bi bi-file-earmark-slides'
      };
      return iconMap[type] || 'bi bi-file-earmark';
    },

    typeBadgeClass(type) {
      const classMap = {
        'document': 'bg-primary',
        'image': 'bg-success',
        'video': 'bg-warning',
        'pdf': 'bg-danger',
        'spreadsheet': 'bg-info',
        'presentation': 'bg-secondary'
      };
      return classMap[type] || 'bg-secondary';
    },

    accessBadgeClass(level) {
      const classMap = {
        'public': 'bg-success',
        'private': 'bg-danger',
        'shared': 'bg-warning'
      };
      return classMap[level] || 'bg-secondary';
    },

    formatAccessLevel(level) {
      const levelMap = {
        'public': 'Public',
        'private': 'Private',
        'shared': 'Shared'
      };
      return levelMap[level] || level;
    },

    isImageFile(type) {
      return type === 'image';
    },

    isPreviewable(type) {
      return ['document', 'pdf', 'image'].includes(type);
    },

    downloadFile() {
      if (this.file && this.file.url) {
        window.open(this.file.url, '_blank');
      }
    },

    previewFile() {
      // TODO: Implement file preview modal
      console.log('Preview file:', this.file);
    },

    shareFile() {
      // TODO: Implement file sharing
      console.log('Share file:', this.file);
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

.file-preview {
  text-align: center;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 1rem;
  background: #f8f9fa;
}

.file-preview img {
  max-width: 100%;
  max-height: 300px;
  object-fit: contain;
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

.file-actions {
  border-top: 1px solid #dee2e6;
  padding-top: 1rem;
}
</style>
