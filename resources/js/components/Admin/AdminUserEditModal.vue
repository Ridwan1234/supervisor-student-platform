<template>
  <div v-if="visible" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-pencil me-2"></i>
          Edit User
        </h5>
        <button type="button" class="btn-close" @click="$emit('close')"></button>
      </div>
      
      <form @submit.prevent="handleSubmit">
        <div class="modal-body">
          <div v-if="loading" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading user data...</p>
          </div>

          <div v-else-if="error" class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            {{ error }}
          </div>

          <div v-else class="edit-form">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="edit-name" class="form-label">Full Name</label>
                <input 
                  type="text" 
                  id="edit-name"
                  v-model="form.name"
                  class="form-control"
                  :class="{ 'is-invalid': errors.name }"
                  placeholder="Enter full name"
                  required
                />
                <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="edit-email" class="form-label">Email Address</label>
                <input 
                  type="email" 
                  id="edit-email"
                  v-model="form.email"
                  class="form-control"
                  :class="{ 'is-invalid': errors.email }"
                  placeholder="Enter email address"
                  required
                />
                <div v-if="errors.email" class="invalid-feedback">{{ errors.email }}</div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="edit-role" class="form-label">Role</label>
                <select 
                  id="edit-role"
                  v-model="form.role"
                  class="form-select"
                  :class="{ 'is-invalid': errors.role }"
                  required
                >
                  <option value="">Select a role</option>
                  <option value="student">Student</option>
                  <option value="supervisor">Supervisor</option>
                  <option value="admin">Admin</option>
                </select>
                <div v-if="errors.role" class="invalid-feedback">{{ errors.role }}</div>
              </div>
              
              <div class="col-md-6 mb-3">
                <label for="edit-status" class="form-label">Status</label>
                <select 
                  id="edit-status"
                  v-model="form.status"
                  class="form-select"
                  :class="{ 'is-invalid': errors.status }"
                  required
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                  <option value="suspended">Suspended</option>
                </select>
                <div v-if="errors.status" class="invalid-feedback">{{ errors.status }}</div>
              </div>
            </div>

            <div class="mb-3">
              <label for="edit-password" class="form-label">New Password (leave blank to keep current)</label>
              <input 
                type="password" 
                id="edit-password"
                v-model="form.password"
                class="form-control"
                :class="{ 'is-invalid': errors.password }"
                placeholder="Enter new password"
              />
              <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
            </div>

            <div v-if="form.role === 'supervisor'" class="mb-3">
              <label for="edit-department" class="form-label">Department</label>
              <input 
                type="text" 
                id="edit-department"
                v-model="form.department"
                class="form-control"
                placeholder="Enter department"
              />
            </div>

            <div v-if="form.role === 'supervisor'" class="mb-3">
              <label for="edit-expertise" class="form-label">Expertise</label>
              <textarea 
                id="edit-expertise"
                v-model="form.expertise"
                class="form-control"
                rows="3"
                placeholder="Enter areas of expertise"
              ></textarea>
            </div>

            <div v-if="form.role === 'student'" class="mb-3">
              <label for="edit-student-id" class="form-label">Student ID</label>
              <input 
                type="text" 
                id="edit-student-id"
                v-model="form.student_id"
                class="form-control"
                placeholder="Enter student ID"
              />
            </div>

            <div v-if="form.role === 'student'" class="mb-3">
              <label for="edit-major" class="form-label">Major</label>
              <input 
                type="text" 
                id="edit-major"
                v-model="form.major"
                class="form-control"
                placeholder="Enter major"
              />
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">Cancel</button>
          <button type="submit" class="btn btn-primary" :disabled="submitting">
            {{ submitting ? 'Saving...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { apiPut, handleApiError } from '../../utils/api';

export default {
  name: 'AdminUserEditModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      loading: false,
      submitting: false,
      error: null,
      errors: {},
      form: {
        name: '',
        email: '',
        role: '',
        status: 'active',
        password: '',
        department: '',
        expertise: '',
        student_id: '',
        major: ''
      }
    };
  },
  watch: {
    visible(newVal) {
      if (newVal && this.user) {
        this.loadUserData();
      }
    }
  },
  methods: {
    loadUserData() {
      this.form = {
        name: this.user.name || '',
        email: this.user.email || '',
        role: this.user.role || '',
        status: this.user.status || 'active',
        password: '',
        department: this.user.supervisor?.department || '',
        expertise: this.user.supervisor?.expertise || '',
        student_id: this.user.student?.student_id || '',
        major: this.user.student?.major || ''
      };
    },

    async handleSubmit() {
      this.submitting = true;
      this.errors = {};
      
      try {
        await apiPut(`/api/admin/users/${this.user.id}`, this.form);
        this.$emit('user-updated');
        this.$emit('close');
      } catch (error) {
        console.error('Error updating user:', error);
        if (error.errors) {
          this.errors = error.errors;
        } else {
          this.error = handleApiError(error, 'updating user');
        }
      } finally {
        this.submitting = false;
      }
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
</style>
