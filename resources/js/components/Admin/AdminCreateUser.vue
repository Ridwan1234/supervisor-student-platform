<template>
  <div v-if="visible" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-person-plus me-2"></i>
          Create New User
        </h5>
        <button type="button" class="btn-close" @click="$emit('close')"></button>
      </div>
      
      <form @submit.prevent="handleSubmit">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label for="name" class="form-label">Full Name</label>
              <input 
                type="text" 
                id="name"
                v-model="form.name"
                class="form-control"
                :class="{ 'is-invalid': errors.name }"
                placeholder="Enter full name"
                required
              />
              <div v-if="errors.name" class="invalid-feedback">{{ errors.name }}</div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="email" class="form-label">Email Address</label>
              <input 
                type="email" 
                id="email"
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
              <label for="password" class="form-label">Password</label>
              <input 
                type="password" 
                id="password"
                v-model="form.password"
                class="form-control"
                :class="{ 'is-invalid': errors.password }"
                placeholder="Enter password"
                required
              />
              <div v-if="errors.password" class="invalid-feedback">{{ errors.password }}</div>
            </div>
            
            <div class="col-md-6 mb-3">
              <label for="password_confirmation" class="form-label">Confirm Password</label>
              <input 
                type="password" 
                id="password_confirmation"
                v-model="form.password_confirmation"
                class="form-control"
                :class="{ 'is-invalid': errors.password_confirmation }"
                placeholder="Confirm password"
                required
              />
              <div v-if="errors.password_confirmation" class="invalid-feedback">{{ errors.password_confirmation }}</div>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select 
              id="role"
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
          
          <div class="mb-3">
            <div class="form-check">
              <input 
                type="checkbox" 
                id="sendWelcomeEmail"
                v-model="form.sendWelcomeEmail"
                class="form-check-input"
              />
              <label class="form-check-label" for="sendWelcomeEmail">
                Send welcome email to user
              </label>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="$emit('close')">
            Cancel
          </button>
          <button type="submit" class="btn btn-primary" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Creating...' : 'Create User' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import axios from 'axios'

export default {
  name: 'AdminCreateUser',
  props: {
    visible: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close', 'user-created'],
  setup(props, { emit }) {
    const form = reactive({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: '',
      sendWelcomeEmail: true
    })
    
    const errors = reactive({})
    const loading = ref(false)
    
    const handleSubmit = async () => {
      // Reset errors
      Object.keys(errors).forEach(key => delete errors[key])
      
      // Basic validation
      if (!form.name.trim()) {
        errors.name = 'Name is required'
      }
      
      if (!form.email.trim()) {
        errors.email = 'Email is required'
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Please enter a valid email address'
      }
      
      if (!form.password) {
        errors.password = 'Password is required'
      } else if (form.password.length < 8) {
        errors.password = 'Password must be at least 8 characters'
      }
      
      if (form.password !== form.password_confirmation) {
        errors.password_confirmation = 'Passwords do not match'
      }
      
      if (!form.role) {
        errors.role = 'Please select a role'
      }
      
      if (Object.keys(errors).length > 0) {
        return
      }
      
      try {
        loading.value = true
        
        const response = await axios.post('/api/admin/users', {
          name: form.name,
          email: form.email,
          password: form.password,
          password_confirmation: form.password_confirmation,
          role: form.role,
          send_welcome_email: form.sendWelcomeEmail
        })
        
        // Reset form
        Object.keys(form).forEach(key => {
          if (key !== 'sendWelcomeEmail') {
            form[key] = ''
          }
        })
        
        emit('user-created', response.data.user)
        emit('close')
        
        // Show success message
        const toast = useToast()
        toast.success('User created successfully!')
        
      } catch (error) {
        if (error.response?.data?.errors) {
          Object.keys(error.response.data.errors).forEach(key => {
            errors[key] = error.response.data.errors[key][0]
          })
        } else {
          console.error('Error creating user:', error)
          const toast = useToast()
          toast.error('Failed to create user. Please try again.')
        }
      } finally {
        loading.value = false
      }
    }
    
    return {
      form,
      errors,
      loading,
      handleSubmit
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

.modal-content {
  background: white;
  border-radius: 0.5rem;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: space-between;
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
</style> 