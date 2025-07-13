<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="sidebar-header">
        <h5 class="mb-0">
          <i class="bi bi-folder-fill me-2"></i>
          File Management
        </h5>
      </div>
      <div class="sidebar-content">
        <div class="mb-3">
          <button @click="showUploadModal = true" class="btn btn-primary w-100">
            <i class="bi bi-cloud-upload me-2"></i>
            Upload Files
          </button>
        </div>
        
        <!-- File Stats -->
        <div class="file-stats mb-3">
          <div class="stat-item">
            <i class="bi bi-file-earmark"></i>
            <span>{{ fileStats.total_files || 0 }}</span>
            <small>Files</small>
          </div>
          <div class="stat-item">
            <i class="bi bi-hdd"></i>
            <span>{{ formatSize(fileStats.total_size) }}</span>
            <small>Storage</small>
          </div>
        </div>

        <!-- Folders -->
        <div class="folders-section">
          <h6 class="sidebar-title">Folders</h6>
          <div class="folder-list">
            <div 
              v-for="folder in folders" 
              :key="folder"
              @click="selectFolder(folder)"
              :class="['folder-item', { active: selectedFolder === folder }]"
            >
              <i class="bi bi-folder me-2"></i>
              {{ folder }}
            </div>
            <div 
              v-if="folders.length === 0"
              class="text-muted small"
            >
              No folders yet
            </div>
          </div>
        </div>

        <!-- File Types -->
        <div class="file-types-section">
          <h6 class="sidebar-title">File Types</h6>
          <div class="file-type-list">
            <div 
              v-for="type in fileTypes" 
              :key="type"
              @click="selectFileType(type)"
              :class="['file-type-item', { active: selectedFileType === type }]"
            >
              <i :class="getFileTypeIcon(type)"></i>
              {{ type.toUpperCase() }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="mb-1">
              <i class="bi bi-folder-fill me-2"></i>
              {{ selectedFolder ? selectedFolder : 'All Files' }}
            </h4>
            <p class="text-muted mb-0">
              {{ filteredFiles.length }} files found
            </p>
          </div>
          <div class="header-actions">
            <button @click="refreshFiles" class="btn btn-outline-secondary me-2">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button @click="showUploadModal = true" class="btn btn-primary">
              <i class="bi bi-plus"></i>
              Upload
            </button>
          </div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="search-filters mb-3">
        <div class="row">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-search"></i>
              </span>
              <input 
                v-model="searchQuery" 
                type="text" 
                class="form-control" 
                placeholder="Search files..."
              >
            </div>
          </div>
          <div class="col-md-3">
            <select v-model="selectedProject" class="form-select">
              <option value="">All Projects</option>
              <option v-for="project in projects" :key="project.id" :value="project.id">
                {{ project.title }}
              </option>
            </select>
          </div>
          <div class="col-md-3">
            <select v-model="sortBy" class="form-select">
              <option value="created_at">Date Created</option>
              <option value="name">Name</option>
              <option value="size">Size</option>
              <option value="type">Type</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Files Grid -->
      <div class="files-grid" v-if="!loading">
        <div 
          v-for="file in paginatedFiles" 
          :key="file.id"
          class="file-card"
        >
          <div class="file-card-header">
            <div class="file-icon">
              <i :class="file.icon"></i>
            </div>
            <div class="file-actions">
              <button @click="openPreviewModal(file)" class="btn btn-sm btn-outline-info">
                <i class="bi bi-eye"></i>
              </button>
              <button @click="downloadFile(file)" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-download"></i>
              </button>
              <button @click="openVersionModal(file)" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-clock-history"></i>
              </button>
              <button @click="deleteFile(file)" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
          <div class="file-card-body">
            <h6 class="file-name" :title="file.name">{{ file.name }}</h6>
            <p class="file-info">
              <small class="text-muted">
                {{ file.formatted_size }} • {{ file.type.toUpperCase() }}
              </small>
            </p>
            <p class="file-description" v-if="file.description">
              {{ file.description }}
            </p>
            <div class="file-meta">
              <small class="text-muted">
                Uploaded by {{ file.uploader?.name }} on {{ formatDate(file.created_at) }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3">Loading files...</p>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredFiles.length === 0" class="text-center py-5">
        <i class="bi bi-folder-x display-1 text-muted"></i>
        <h5 class="mt-3">No files found</h5>
        <p class="text-muted">Upload your first file to get started</p>
        <button @click="showUploadModal = true" class="btn btn-primary">
          <i class="bi bi-cloud-upload me-2"></i>
          Upload Files
        </button>
      </div>

      <!-- Pagination -->
      <nav v-if="totalPages > 1" class="mt-4">
        <ul class="pagination justify-content-center">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <a class="page-link" href="#" @click.prevent="currentPage--">Previous</a>
          </li>
          <li 
            v-for="page in visiblePages" 
            :key="page"
            class="page-item"
            :class="{ active: page === currentPage }"
          >
            <a class="page-link" href="#" @click.prevent="currentPage = page">{{ page }}</a>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <a class="page-link" href="#" @click.prevent="currentPage++">Next</a>
          </li>
        </ul>
      </nav>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" :class="{ show: showUploadModal }" :style="{ display: showUploadModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-cloud-upload me-2"></i>
              Upload Files
            </h5>
            <button @click="showUploadModal = false" type="button" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="uploadFiles">
              <div class="mb-3">
                <label class="form-label">Select Files</label>
                <input 
                  ref="fileInput"
                  type="file" 
                  multiple 
                  class="form-control"
                  @change="handleFileSelect"
                  accept="*/*"
                >
                <div class="form-text">Maximum file size: 10MB per file</div>
              </div>

              <div v-if="selectedFiles.length > 0" class="mb-3">
                <label class="form-label">Selected Files</label>
                <div class="selected-files">
                  <div 
                    v-for="(file, index) in selectedFiles" 
                    :key="index"
                    class="selected-file-item"
                  >
                    <i :class="getFileIcon(file.name)"></i>
                    <span>{{ file.name }}</span>
                    <small class="text-muted">({{ formatFileSize(file.size) }})</small>
                    <button @click="removeFile(index)" type="button" class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-x"></i>
                    </button>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Folder (Optional)</label>
                    <input v-model="uploadFolder" type="text" class="form-control" placeholder="Enter folder name">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Project (Optional)</label>
                    <select v-model="uploadProject" class="form-select">
                      <option value="">Select Project</option>
                      <option v-for="project in projects" :key="project.id" :value="project.id">
                        {{ project.title }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Description (Optional)</label>
                <textarea v-model="uploadDescription" class="form-control" rows="3" placeholder="Add a description for these files"></textarea>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button @click="showUploadModal = false" type="button" class="btn btn-secondary">Cancel</button>
            <button @click="uploadFiles" type="button" class="btn btn-primary" :disabled="uploading">
              <span v-if="uploading" class="spinner-border spinner-border-sm me-2"></span>
              {{ uploading ? 'Uploading...' : 'Upload Files' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Backdrop -->
    <div v-if="showUploadModal" class="modal-backdrop fade show"></div>

    <!-- File Version Modal -->
    <FileVersionModal
      :visible="showVersionModal"
      :file="selectedFileForVersion"
      @close="closeVersionModal"
      @version-uploaded="handleVersionUploaded"
      @preview-file="handlePreviewFile"
    />

    <!-- File Preview Modal -->
    <FilePreviewModal
      :visible="showPreviewModal"
      :file="selectedFileForPreview"
      @close="closePreviewModal"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import FileVersionModal from './FileVersionModal.vue'
import FilePreviewModal from './FilePreviewModal.vue'

export default {
  name: 'FileManagement',
  components: {
    FileVersionModal,
    FilePreviewModal
  },
  setup() {
    const files = ref([])
    const loading = ref(false)
    const uploading = ref(false)
    const searchQuery = ref('')
    const selectedFolder = ref('')
    const selectedFileType = ref('')
    const selectedProject = ref('')
    const sortBy = ref('created_at')
    const currentPage = ref(1)
    const itemsPerPage = 12
    const showUploadModal = ref(false)
    const selectedFiles = ref([])
    const uploadFolder = ref('')
    const uploadProject = ref('')
    const uploadDescription = ref('')
    const fileStats = ref({})
    const folders = ref([])
    const fileTypes = ref([])
    const projects = ref([])
    
    // Version modal
    const showVersionModal = ref(false)
    const selectedFileForVersion = ref(null)
    
    // Preview modal
    const showPreviewModal = ref(false)
    const selectedFileForPreview = ref(null)

    // Computed properties
    const filteredFiles = computed(() => {
      let filtered = files.value

      if (searchQuery.value) {
        filtered = filtered.filter(file => 
          file.name.toLowerCase().includes(searchQuery.value.toLowerCase())
        )
      }

      if (selectedFolder.value) {
        filtered = filtered.filter(file => file.folder === selectedFolder.value)
      }

      if (selectedFileType.value) {
        filtered = filtered.filter(file => file.type === selectedFileType.value)
      }

      if (selectedProject.value) {
        filtered = filtered.filter(file => file.project_id === selectedProject.value)
      }

      // Sort files
      filtered.sort((a, b) => {
        if (sortBy.value === 'name') {
          return a.name.localeCompare(b.name)
        } else if (sortBy.value === 'size') {
          return b.size - a.size
        } else if (sortBy.value === 'type') {
          return a.type.localeCompare(b.type)
        } else {
          return new Date(b.created_at) - new Date(a.created_at)
        }
      })

      return filtered
    })

    const paginatedFiles = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage
      const end = start + itemsPerPage
      return filteredFiles.value.slice(start, end)
    })

    const totalPages = computed(() => {
      return Math.ceil(filteredFiles.value.length / itemsPerPage)
    })

    const visiblePages = computed(() => {
      const pages = []
      const maxVisible = 5
      let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
      let end = Math.min(totalPages.value, start + maxVisible - 1)
      
      if (end - start + 1 < maxVisible) {
        start = Math.max(1, end - maxVisible + 1)
      }
      
      for (let i = start; i <= end; i++) {
        pages.push(i)
      }
      
      return pages
    })

    // Methods
    const fetchFiles = async () => {
      loading.value = true
      try {
        const response = await axios.get('/api/files')
        files.value = response.data.data || response.data
      } catch (error) {
        console.error('Error fetching files:', error)
        showToast('Error loading files', 'error')
      } finally {
        loading.value = false
      }
    }

    const fetchFileStats = async () => {
      try {
        const response = await axios.get('/api/files/stats')
        fileStats.value = response.data.data
      } catch (error) {
        console.error('Error fetching file stats:', error)
      }
    }

    const fetchFolders = async () => {
      try {
        const response = await axios.get('/api/files/folders')
        folders.value = response.data.data
      } catch (error) {
        console.error('Error fetching folders:', error)
      }
    }

    const fetchFileTypes = async () => {
      try {
        const response = await axios.get('/api/files/types')
        fileTypes.value = response.data.data
      } catch (error) {
        console.error('Error fetching file types:', error)
      }
    }

    const fetchProjects = async () => {
      try {
        const response = await axios.get('/api/projects')
        projects.value = response.data.data || response.data
      } catch (error) {
        console.error('Error fetching projects:', error)
      }
    }

    const selectFolder = (folder) => {
      selectedFolder.value = selectedFolder.value === folder ? '' : folder
      currentPage.value = 1
    }

    const selectFileType = (type) => {
      selectedFileType.value = selectedFileType.value === type ? '' : type
      currentPage.value = 1
    }

    const downloadFile = async (file) => {
      try {
        const response = await axios.get(`/api/files/download/${file.id}`, {
          responseType: 'blob'
        })
        
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', file.name)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        showToast('File downloaded successfully', 'success')
      } catch (error) {
        console.error('Error downloading file:', error)
        showToast('Error downloading file', 'error')
      }
    }

    const deleteFile = async (file) => {
      if (!confirm('Are you sure you want to delete this file?')) return
      
      try {
        await axios.delete(`/api/files/${file.id}`)
        await fetchFiles()
        await fetchFileStats()
        showToast('File deleted successfully', 'success')
      } catch (error) {
        console.error('Error deleting file:', error)
        showToast('Error deleting file', 'error')
      }
    }

    const handleFileSelect = (event) => {
      selectedFiles.value = Array.from(event.target.files)
    }

    const removeFile = (index) => {
      selectedFiles.value.splice(index, 1)
    }

    const uploadFiles = async () => {
      if (selectedFiles.value.length === 0) {
        showToast('Please select files to upload', 'error')
        return
      }

      uploading.value = true
      const formData = new FormData()
      
      selectedFiles.value.forEach(file => {
        formData.append('files', file)
      })
      
      if (uploadFolder.value) {
        formData.append('folder', uploadFolder.value)
      }
      
      if (uploadProject.value) {
        formData.append('project_id', uploadProject.value)
      }
      
      if (uploadDescription.value) {
        formData.append('description', uploadDescription.value)
      }

      try {
        await axios.post('/api/files/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        showUploadModal.value = false
        selectedFiles.value = []
        uploadFolder.value = ''
        uploadProject.value = ''
        uploadDescription.value = ''
        
        await fetchFiles()
        await fetchFileStats()
        await fetchFolders()
        
        showToast('Files uploaded successfully', 'success')
      } catch (error) {
        console.error('Error uploading files:', error)
        showToast('Error uploading files', 'error')
      } finally {
        uploading.value = false
      }
    }

    const refreshFiles = () => {
      fetchFiles()
      fetchFileStats()
      fetchFolders()
      fetchFileTypes()
    }

    const formatSize = (bytes) => {
      if (!bytes) return '0 B'
      const units = ['B', 'KB', 'MB', 'GB']
      let size = bytes
      let unitIndex = 0
      
      while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024
        unitIndex++
      }
      
      return `${size.toFixed(1)} ${units[unitIndex]}`
    }

    const formatFileSize = (bytes) => {
      return formatSize(bytes)
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString()
    }

    const getFileIcon = (filename) => {
      const ext = filename.split('.').pop().toLowerCase()
      const icons = {
        pdf: 'bi bi-file-pdf',
        doc: 'bi bi-file-word',
        docx: 'bi bi-file-word',
        xls: 'bi bi-file-excel',
        xlsx: 'bi bi-file-excel',
        ppt: 'bi bi-file-ppt',
        pptx: 'bi bi-file-ppt',
        txt: 'bi bi-file-text',
        jpg: 'bi bi-file-image',
        jpeg: 'bi bi-file-image',
        png: 'bi bi-file-image',
        gif: 'bi bi-file-image',
        zip: 'bi bi-file-zip',
        rar: 'bi bi-file-zip',
        mp4: 'bi bi-file-play',
        avi: 'bi bi-file-play',
        mp3: 'bi bi-file-music',
        wav: 'bi bi-file-music'
      }
      return icons[ext] || 'bi bi-file-earmark'
    }

    const getFileTypeIcon = (type) => {
      return getFileIcon(`file.${type}`)
    }

    const showToast = (message, type = 'info') => {
      // Simple toast implementation
      const toast = document.createElement('div')
      toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`
      toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;'
      toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `
      document.body.appendChild(toast)
      
      setTimeout(() => {
        toast.remove()
      }, 5000)
    }

    // Version modal methods
    const openVersionModal = (file) => {
      selectedFileForVersion.value = file
      showVersionModal.value = true
    }

    const closeVersionModal = () => {
      showVersionModal.value = false
      selectedFileForVersion.value = null
    }

    const handleVersionUploaded = () => {
      fetchFiles()
      fetchFileStats()
    }

    const handlePreviewFile = (file) => {
      // Open preview modal with the file
      openPreviewModal(file)
    }

    // Preview modal methods
    const openPreviewModal = (file) => {
      selectedFileForPreview.value = file
      showPreviewModal.value = true
    }

    const closePreviewModal = () => {
      showPreviewModal.value = false
      selectedFileForPreview.value = null
    }

    // Watchers
    watch([searchQuery, selectedFolder, selectedFileType, selectedProject], () => {
      currentPage.value = 1
    })

    // Lifecycle
    onMounted(() => {
      fetchFiles()
      fetchFileStats()
      fetchFolders()
      fetchFileTypes()
      fetchProjects()
    })

    return {
      files,
      loading,
      uploading,
      searchQuery,
      selectedFolder,
      selectedFileType,
      selectedProject,
      sortBy,
      currentPage,
      showUploadModal,
      selectedFiles,
      uploadFolder,
      uploadProject,
      uploadDescription,
      fileStats,
      folders,
      fileTypes,
      projects,
      filteredFiles,
      paginatedFiles,
      totalPages,
      visiblePages,
      selectFolder,
      selectFileType,
      downloadFile,
      deleteFile,
      handleFileSelect,
      removeFile,
      uploadFiles,
      refreshFiles,
      formatSize,
      formatFileSize,
      formatDate,
      getFileIcon,
      getFileTypeIcon,
      showToast,
      // Version modal
      showVersionModal,
      selectedFileForVersion,
      openVersionModal,
      closeVersionModal,
      handleVersionUploaded,
      handlePreviewFile,
      // Preview modal
      showPreviewModal,
      selectedFileForPreview,
      openPreviewModal,
      closePreviewModal
    }
  }
}
</script>

<style scoped>
.dashboard-container {
  display: flex;
  height: 100vh;
  background-color: #f8f9fa;
}

.sidebar {
  width: 280px;
  background: white;
  border-right: 1px solid #dee2e6;
  display: flex;
  flex-direction: column;
  overflow-y: auto;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
  background: #f8f9fa;
}

.sidebar-content {
  padding: 1.5rem;
  flex: 1;
}

.file-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 0.5rem;
}

.stat-item {
  text-align: center;
  padding: 0.5rem;
}

.stat-item i {
  font-size: 1.5rem;
  color: #6c757d;
  display: block;
  margin-bottom: 0.25rem;
}

.stat-item span {
  font-weight: bold;
  display: block;
  font-size: 1.1rem;
}

.stat-item small {
  color: #6c757d;
  font-size: 0.8rem;
}

.sidebar-title {
  font-weight: 600;
  margin-bottom: 0.75rem;
  color: #495057;
}

.folder-list, .file-type-list {
  margin-bottom: 1.5rem;
}

.folder-item, .file-type-item {
  padding: 0.5rem 0.75rem;
  margin-bottom: 0.25rem;
  border-radius: 0.375rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
}

.folder-item:hover, .file-type-item:hover {
  background-color: #e9ecef;
}

.folder-item.active, .file-type-item.active {
  background-color: #0d6efd;
  color: white;
}

.main-content {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
}

.content-header {
  margin-bottom: 2rem;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
}

.search-filters {
  background: white;
  padding: 1.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.files-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.file-card {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
}

.file-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.file-card-header {
  padding: 1rem;
  background: #f8f9fa;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.file-icon i {
  font-size: 2rem;
  color: #6c757d;
}

.file-actions {
  display: flex;
  gap: 0.25rem;
}

.file-card-body {
  padding: 1rem;
}

.file-name {
  margin-bottom: 0.5rem;
  font-weight: 600;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.file-info {
  margin-bottom: 0.5rem;
}

.file-description {
  font-size: 0.875rem;
  color: #6c757d;
  margin-bottom: 0.5rem;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.file-meta {
  font-size: 0.75rem;
}

.selected-files {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  padding: 0.5rem;
}

.selected-file-item {
  display: flex;
  align-items: center;
  padding: 0.5rem;
  border-bottom: 1px solid #f1f3f4;
  gap: 0.5rem;
}

.selected-file-item:last-child {
  border-bottom: none;
}

.selected-file-item i {
  color: #6c757d;
}

.selected-file-item span {
  flex: 1;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  .dashboard-container {
    flex-direction: column;
  }
  
  .sidebar {
    width: 100%;
    height: auto;
    border-right: none;
    border-bottom: 1px solid #dee2e6;
  }
  
  .main-content {
    padding: 1rem;
  }
  
  .files-grid {
    grid-template-columns: 1fr;
  }
  
  .search-filters .row {
    gap: 1rem;
  }
}
</style> 