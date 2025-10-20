<template>
  <div v-if="visible" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-person-circle me-2"></i>
          User Details
        </h5>
        <button type="button" class="btn-close" @click="$emit('close')"></button>
      </div>
      
      <div class="modal-body">
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading user details...</p>
        </div>

        <div v-else-if="error" class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          {{ error }}
        </div>

        <div v-else-if="user" class="user-details">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Full Name</label>
              <p class="form-control-plaintext">{{ user.name }}</p>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Email Address</label>
              <p class="form-control-plaintext">{{ user.email }}</p>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Role</label>
              <p class="form-control-plaintext">
                <span class="badge" :class="roleBadgeClass(user.role)">
                  {{ user.role }}
                </span>
              </p>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Status</label>
              <p class="form-control-plaintext">
                <span class="badge" :class="statusBadgeClass(user.status)">
                  {{ user.status || 'Active' }}
                </span>
              </p>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Joined Date</label>
              <p class="form-control-plaintext">{{ formatDate(user.created_at) }}</p>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Last Updated</label>
              <p class="form-control-plaintext">{{ formatDate(user.updated_at) }}</p>
            </div>
          </div>

          <div v-if="user.role === 'supervisor'" class="mb-3">
            <label class="form-label fw-bold">Supervisor Details</label>
            <div class="card">
              <div class="card-body">
                <p class="mb-1"><strong>Department:</strong> {{ user.supervisor?.department || 'N/A' }}</p>
                <p class="mb-1"><strong>Expertise:</strong> {{ user.supervisor?.expertise || 'N/A' }}</p>
                <p class="mb-0"><strong>Projects Managed:</strong> {{ user.supervisor?.projects_count || 0 }}</p>
              </div>
            </div>
          </div>

          <div v-if="user.role === 'student'" class="mb-3">
            <label class="form-label fw-bold">Student Details</label>
            <div class="card">
              <div class="card-body">
                <p class="mb-1"><strong>Student ID:</strong> {{ user.student?.student_id || 'N/A' }}</p>
                <p class="mb-1"><strong>Major:</strong> {{ user.student?.major || 'N/A' }}</p>
                <p class="mb-0"><strong>Projects Assigned:</strong> {{ user.student?.projects_count || 0 }}</p>
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Recent Activity</label>
            <div class="card">
              <div class="card-body">
                <div v-if="user.recent_activity && user.recent_activity.length > 0">
                  <div v-for="activity in user.recent_activity" :key="activity.id" class="activity-item">
                    <i :class="getActivityIcon(activity.type)" class="text-primary me-2"></i>
                    <span>{{ activity.description }}</span>
                    <small class="text-muted d-block">{{ formatDate(activity.created_at) }}</small>
                  </div>
                </div>
                <div v-else class="text-muted">
                  No recent activity
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" @click="$emit('close')">Close</button>
        <button type="button" class="btn btn-primary" @click="editUser">
          <i class="bi bi-pencil me-1"></i>Edit User
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { apiGet, handleApiError } from '../../utils/api';

export default {
  name: 'AdminUserDetailModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    userId: {
      type: [Number, String],
      default: null
    }
  },
  data() {
    return {
      user: null,
      loading: false,
      error: null
    };
  },
  watch: {
    visible(newVal) {
      if (newVal && this.userId) {
        this.fetchUserDetails();
      }
    }
  },
  methods: {
    async fetchUserDetails() {
      this.loading = true;
      this.error = null;
      
      try {
        this.user = await apiGet(`/api/admin/users/${this.userId}`);
      } catch (error) {
        console.error('Error fetching user details:', error);
        this.error = handleApiError(error, 'fetching user details');
      } finally {
        this.loading = false;
      }
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleString();
    },

    roleBadgeClass(role) {
      if (role === 'admin') return 'bg-dark text-white';
      if (role === 'supervisor') return 'bg-primary';
      if (role === 'student') return 'bg-success';
      return 'bg-secondary';
    },

    statusBadgeClass(status) {
      if (status === 'active') return 'bg-success';
      if (status === 'inactive') return 'bg-danger';
      return 'bg-secondary';
    },

    getActivityIcon(type) {
      const iconMap = {
        'login': 'bi bi-box-arrow-in-right',
        'logout': 'bi bi-box-arrow-left',
        'project_created': 'bi bi-folder-plus',
        'task_completed': 'bi bi-check-circle',
        'file_uploaded': 'bi bi-upload'
      };
      return iconMap[type] || 'bi bi-circle';
    },

    editUser() {
      this.$emit('edit-user', this.user);
      this.$emit('close');
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
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.activity-item {
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f3f4;
}

.activity-item:last-child {
  border-bottom: none;
}
</style>