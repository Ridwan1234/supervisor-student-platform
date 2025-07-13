<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <StudentSidebar />

    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Navigation Bar -->
      <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid">
          <button class="navbar-toggler d-lg-none" type="button" @click="toggleSidebar">
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <div class="d-flex align-items-center">
            <h4 class="mb-0 fw-bold text-success">My Files</h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Notifications -->
            <div class="dropdown position-relative">
              <button class="btn btn-light position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                <span v-if="notificationCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  {{ notificationCount }}
                </span>
              </button>
              <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                <li><h6 class="dropdown-header">Notifications</h6></li>
                <li v-for="notification in notifications" :key="notification.id">
                  <a class="dropdown-item" href="#">{{ notification.message }}</a>
                </li>
              </ul>
            </div>

            <!-- User Profile -->
            <div class="dropdown position-relative">
              <button class="btn btn-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="https://via.placeholder.com/32" alt="Profile" class="rounded-circle" width="32" height="32" />
                <span class="d-none d-md-block">{{ user?.name || 'Student' }}</span>
                <i class="bi bi-chevron-down"></i>
              </button>
              <ul class="dropdown-menu dropdown-menu-end profile-dropdown">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" @click="logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              </ul>
            </div>
          </div>
        </div>
      </nav>

      <!-- File Management Content -->
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header bg-transparent border-0">
                <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0 fw-bold">File Management</h5>
                  <button @click="showUploadModal = true" class="btn btn-success">
                    <i class="bi bi-cloud-upload me-2"></i>
                    Upload Files
                  </button>
                </div>
              </div>
              <div class="card-body">
                <!-- File Management Component -->
                <FileManagement />
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import StudentSidebar from './StudentSidebar.vue';
import FileManagement from '../FileManagement.vue';

export default {
  name: 'StudentFileManagement',
  components: {
    StudentSidebar,
    FileManagement
  },
  data() {
    return {
      showUploadModal: false,
      notificationCount: 2,
      notifications: [
        { id: 1, message: 'New task assigned by supervisor' },
        { id: 2, message: 'Project deadline reminder' },
      ],
      user: null
    };
  },
  mounted() {
    this.loadUserData();
  },
  methods: {
    toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    },
    logout() {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      localStorage.removeItem('role');
      this.$router.push('/login');
    },
    loadUserData() {
      const userData = localStorage.getItem('user');
      if (userData) {
        this.user = JSON.parse(userData);
      }
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

.notification-dropdown,
.profile-dropdown {
  min-width: 250px;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  border: none;
  border-radius: 0.5rem;
}

.notification-dropdown .dropdown-header {
  background-color: #f8f9fa;
  font-weight: 600;
  color: #495057;
}

.profile-dropdown .dropdown-item {
  padding: 0.5rem 1rem;
  color: #495057;
}

.profile-dropdown .dropdown-item:hover {
  background-color: #f8f9fa;
}

.profile-dropdown .dropdown-item.text-danger:hover {
  background-color: #f8d7da;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
}
</style> 