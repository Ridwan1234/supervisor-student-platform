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
            <h4 class="mb-0 fw-bold text-primary">
              <i class="bi bi-folder-fill me-2"></i>
              File Management
            </h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Search Bar -->
            <div class="position-relative">
              <input 
                type="text" 
                class="form-control form-control-sm" 
                placeholder="Search files..." 
                v-model="searchQuery"
              />
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
            </div>

            <!-- Upload Files Button -->
            <button class="btn btn-primary" @click="showUploadModal = true">
              <i class="bi bi-cloud-upload me-2"></i>Upload Files
            </button>
          </div>
        </div>
      </nav>

      <!-- File Management Content -->
      <div class="file-management-wrapper">
        <FileManagement 
          ref="fileManagement"
          @upload-success="handleUploadSuccess"
          @file-deleted="handleFileDeleted"
          @file-updated="handleFileUpdated"
        />
      </div>
    </main>
  </div>
</template>

<script>
import SupervisorSidebar from './SupervisorSidebar.vue';
import FileManagement from '../FileManagement.vue';

export default {
  name: 'SupervisorFileManagement',
  components: {
    SupervisorSidebar,
    FileManagement
  },
  data() {
    return {
      searchQuery: '',
      showUploadModal: false
    };
  },
  methods: {
    toggleSidebar() {
      // Toggle sidebar on mobile
      const sidebar = document.querySelector('.sidebar');
      if (sidebar) {
        sidebar.classList.toggle('show');
      }
    },
    handleUploadSuccess() {
      // Refresh file list after successful upload
      this.$refs.fileManagement?.refreshFiles();
    },
    handleFileDeleted() {
      // Handle file deletion
      this.$refs.fileManagement?.refreshFiles();
    },
    handleFileUpdated() {
      // Handle file update
      this.$refs.fileManagement?.refreshFiles();
    }
  }
};
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
  background-color: #f8f9fa;
}

.main-content {
  flex: 1;
  padding: 1.5rem;
  margin-left: 20px;
  transition: margin-left 0.3s ease;
}

.file-management-wrapper {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  overflow: hidden;
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

/* Ensure proper z-index for modals */
:deep(.modal) {
  z-index: 1050;
}

:deep(.modal-backdrop) {
  z-index: 1040;
}
</style> 