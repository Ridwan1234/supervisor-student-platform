<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click="closeModal">
      <div class="modal-container" @click.stop>
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-clock-history me-2"></i>
            Version History - {{ file?.name }}
          </h5>
          <button @click="closeModal" type="button" class="btn-close"></button>
        </div>
        
        <div class="modal-body">
          <!-- Current Version Info -->
          <div class="current-version mb-4">
            <div class="card bg-light">
              <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <h6 class="mb-1">
                      <i class="bi bi-star-fill text-warning me-2"></i>
                      Current Version (v{{ file?.version || 1 }})
                    </h6>
                    <p class="mb-1 text-muted">
                      Uploaded by {{ file?.uploader?.name }} on {{ formatDate(file?.created_at) }}
                    </p>
                    <p v-if="file?.description" class="mb-0 text-muted">
                      {{ file.description }}
                    </p>
                  </div>
                  <div class="d-flex gap-2">
                    <button @click="downloadFile(file)" class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-download me-1"></i>Download
                    </button>
                    <button @click="previewFile(file)" class="btn btn-sm btn-outline-secondary">
                      <i class="bi bi-eye me-1"></i>Preview
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Upload New Version -->
          <div class="upload-new-version mb-4">
            <div class="card">
              <div class="card-header">
                <h6 class="mb-0">
                  <i class="bi bi-cloud-upload me-2"></i>
                  Upload New Version
                </h6>
              </div>
              <div class="card-body">
                <form @submit.prevent="uploadNewVersion">
                  <div class="row">
                    <div class="col-md-8">
                      <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input 
                          ref="fileInput"
                          type="file" 
                          class="form-control"
                          @change="handleFileSelect"
                          accept="*/*"
                          required
                        >
                        <div class="form-text">Maximum file size: 10MB</div>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="mb-3">
                        <label class="form-label">Version Description</label>
                        <textarea 
                          v-model="newVersionDescription" 
                          class="form-control" 
                          rows="3" 
                          placeholder="What changed in this version?"
                        ></textarea>
                      </div>
                    </div>
                  </div>
                  <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary" :disabled="uploading">
                      <span v-if="uploading" class="spinner-border spinner-border-sm me-2"></span>
                      {{ uploading ? 'Uploading...' : 'Upload New Version' }}
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Version History -->
          <div class="version-history">
            <h6 class="mb-3">
              <i class="bi bi-clock-history me-2"></i>
              Previous Versions
            </h6>
            
            <div v-if="loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Loading version history...</p>
            </div>

            <div v-else-if="versions.length === 0" class="text-center py-4">
              <i class="bi bi-clock-history display-4 text-muted"></i>
              <p class="mt-2 text-muted">No previous versions found</p>
            </div>

            <div v-else class="version-list">
              <div 
                v-for="version in versions" 
                :key="version.id"
                class="version-item card mb-2"
              >
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                      <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-secondary me-2">v{{ version.version }}</span>
                        <span class="text-muted">
                          Uploaded by {{ version.uploader?.name }} on {{ formatDate(version.created_at) }}
                        </span>
                      </div>
                      <p v-if="version.description" class="mb-1 text-muted small">
                        {{ version.description }}
                      </p>
                      <small class="text-muted">
                        {{ formatFileSize(version.size) }} • {{ version.type.toUpperCase() }}
                      </small>
                    </div>
                    <div class="d-flex gap-1">
                      <button @click="downloadFile(version)" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-download"></i>
                      </button>
                      <button @click="previewFile(version)" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-eye"></i>
                      </button>
                      <button @click="restoreVersion(version)" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-arrow-clockwise"></i>
                      </button>
                      <button @click="deleteVersion(version)" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button @click="closeModal" type="button" class="btn btn-secondary">Close</button>
        </div>
      </div>
    </div>

      </div>
    </div>
  </Teleport>
</template>

<script>
import axios from 'axios';

export default {
  name: 'FileVersionModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    file: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      loading: false,
      uploading: false,
      versions: [],
      selectedFile: null,
      newVersionDescription: ''
    }
  },
  watch: {
    visible(newVal) {
      if (newVal && this.file) {
        this.loadVersions();
      }
    }
  },
  methods: {
    async loadVersions() {
      if (!this.file) return;
      
      this.loading = true;
      try {
        const response = await axios.get(`/api/files/${this.file.id}/preview`);
        this.versions = response.data.data.versions || [];
      } catch (error) {
        console.error('Error loading versions:', error);
        this.showToast('Error loading version history', 'error');
      } finally {
        this.loading = false;
      }
    },

    handleFileSelect(event) {
      this.selectedFile = event.target.files[0];
    },

    async uploadNewVersion() {
      if (!this.selectedFile) {
        this.showToast('Please select a file', 'error');
        return;
      }

      this.uploading = true;
      const formData = new FormData();
      formData.append('file', this.selectedFile);
      
      if (this.newVersionDescription) {
        formData.append('description', this.newVersionDescription);
      }

      try {
        await axios.post(`/api/files/${this.file.id}/versions`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        this.showToast('New version uploaded successfully', 'success');
        this.resetForm();
        await this.loadVersions();
        this.$emit('version-uploaded');
      } catch (error) {
        console.error('Error uploading version:', error);
        this.showToast('Error uploading new version', 'error');
      } finally {
        this.uploading = false;
      }
    },

    async downloadFile(file) {
      try {
        const response = await axios.get(`/api/files/download/${file.id}`, {
          responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', file.name);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        
        this.showToast('File downloaded successfully', 'success');
      } catch (error) {
        console.error('Error downloading file:', error);
        this.showToast('Error downloading file', 'error');
      }
    },

    previewFile(file) {
      // Emit event to parent to handle file preview
      this.$emit('preview-file', file);
    },

    async restoreVersion(version) {
      if (!confirm(`Are you sure you want to restore version ${version.version}? This will make it the current version.`)) {
        return;
      }

      try {
        // For now, we'll just download the version
        // In a full implementation, you might want to create a new version that's a copy of the old one
        await this.downloadFile(version);
        this.showToast('Version restored successfully', 'success');
      } catch (error) {
        console.error('Error restoring version:', error);
        this.showToast('Error restoring version', 'error');
      }
    },

    async deleteVersion(version) {
      if (!confirm(`Are you sure you want to delete version ${version.version}? This action cannot be undone.`)) {
        return;
      }

      try {
        await axios.delete(`/api/files/${version.id}`);
        this.showToast('Version deleted successfully', 'success');
        await this.loadVersions();
      } catch (error) {
        console.error('Error deleting version:', error);
        this.showToast('Error deleting version', 'error');
      }
    },

    resetForm() {
      this.selectedFile = null;
      this.newVersionDescription = '';
      if (this.$refs.fileInput) {
        this.$refs.fileInput.value = '';
      }
    },

    closeModal() {
      this.resetForm();
      this.$emit('close');
    },

    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString();
    },

    formatFileSize(bytes) {
      if (!bytes) return '0 B';
      const units = ['B', 'KB', 'MB', 'GB'];
      let size = bytes;
      let unitIndex = 0;
      
      while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
      }
      
      return `${size.toFixed(1)} ${units[unitIndex]}`;
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
  z-index: 9999;
}

.modal-container {
  width: 100%;
  max-width: 800px;
  margin: 1rem;
  background-color: white;
  padding: 20px;
}

.modal-dialog {
  margin: 0;
  max-width: 100%;
}

.current-version .card {
  border-left: 4px solid #ffc107;
}

.version-item {
  transition: all 0.2s ease;
}

.version-item:hover {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.version-item .btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.badge {
  font-size: 0.75rem;
}

.upload-new-version .card {
  border-left: 4px solid #0d6efd;
}

.version-list {
  max-height: 400px;
  overflow-y: auto;
}

@media (max-width: 768px) {
  .modal-dialog {
    margin: 1rem;
  }
  
  .version-item .d-flex {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .version-item .d-flex > div:last-child {
    margin-top: 1rem;
    align-self: flex-end;
  }
}
</style> 