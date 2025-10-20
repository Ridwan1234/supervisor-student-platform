<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="text-center mb-4">
        <h2 class="fw-bold text-primary mb-2">Create Account</h2>
        <p class="text-muted">Join our platform to get started</p>
      </div>
      
      <form @submit.prevent="handleRegister">
        <!-- Name -->
        <div class="mb-3">
          <label for="name" class="form-label fw-semibold">Full Name</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-person"></i>
            </span>
            <input
              type="text"
              id="name"
              v-model="form.name"
              class="form-control"
              placeholder="Enter your full name"
              required
            />
          </div>
        </div>

        <!-- Email -->
        <div class="mb-3">
          <label for="email" class="form-label fw-semibold">Email Address</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-envelope"></i>
            </span>
            <input
              type="email"
              id="email"
              v-model="form.email"
              class="form-control"
              placeholder="Enter your email"
              required
            />
          </div>
        </div>

        <!-- Role Selection -->
        <div class="mb-3">
          <label for="role" class="form-label fw-semibold">I am a</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-briefcase"></i>
            </span>
            <select id="role" v-model="form.role" class="form-select" required>
              <option value="">Select your role</option>
              <option value="supervisor">Supervisor</option>
              <option value="student">Student</option>
            </select>
          </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label for="password" class="form-label fw-semibold">Password</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-lock"></i>
            </span>
            <input
              type="password"
              id="password"
              v-model="form.password"
              class="form-control"
              placeholder="Create a password"
              required
            />
          </div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
          <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-lock"></i>
            </span>
            <input
              type="password"
              id="password_confirmation"
              v-model="form.password_confirmation"
              class="form-control"
              placeholder="Confirm your password"
              required
            />
          </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="mb-4">
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="terms" v-model="form.terms" required>
            <label class="form-check-label" for="terms">
              I agree to the <a href="#" class="text-decoration-none text-primary">Terms of Service</a> and 
              <a href="#" class="text-decoration-none text-primary">Privacy Policy</a>
            </label>
          </div>
        </div>

        <!-- Register Button -->
        <button 
          type="submit" 
          class="btn btn-primary w-100 mb-3 register-btn"
          :disabled="loading"
        >
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          {{ loading ? 'Creating Account...' : 'Create Account' }}
        </button>
      </form>

      <!-- Divider -->
      <!-- <div class="text-center mb-3">
        <span class="text-muted">or</span>
      </div> -->

      <!-- Social Register -->
      <!-- <div class="d-grid gap-2 mb-4">
        <button class="btn btn-outline-secondary">
          <i class="bi bi-google me-2"></i>
          Continue with Google
        </button>
      </div> -->

      <!-- Login Link -->
      <div class="text-center">
        <p class="mb-0">
          Already have an account? 
          <router-link to="/login" class="text-decoration-none text-primary fw-semibold">
            Sign In
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useToast } from 'vue-toastification';

export default {
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role: '',
        terms: false,
      },
      loading: false,
    };
  },
  methods: {
    async handleRegister() {
      this.loading = true;
      axios.defaults.baseURL = 'http://supervisor-student-platform.test';
      const toast = useToast();

      try {
        const response = await axios.post("/api/register", this.form);
        
        toast.success('Account created successfully! Please sign in.');
        this.$router.push('/login');
      } catch (error) {
        if (error.response) {
          const errors = error.response.data.errors;
          if (errors) {
            Object.keys(errors).forEach(key => {
              toast.error(errors[key][0]);
            });
          } else {
            toast.error(error.response.data.message || 'Registration failed!');
          }
        } else {
          toast.error('An unexpected error occurred. Please try again.');
        }
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.auth-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.auth-card {
  background: white;
  border-radius: 1rem;
  box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1);
  padding: 2.5rem;
  width: 100%;
  max-width: 450px;
}

.input-group-text {
  background-color: #f8f9fa;
  border-color: #dee2e6;
}

.form-control:focus,
.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Explicit button styles to ensure visibility */
.register-btn {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
  color: white !important;
  font-weight: 500;
  padding: 0.75rem 1.5rem;
  border-radius: 0.375rem;
  transition: all 0.15s ease-in-out;
}

.register-btn:hover:not(:disabled) {
  background-color: #0b5ed7 !important;
  border-color: #0a58ca !important;
  transform: translateY(-1px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.register-btn:disabled {
  opacity: 0.7;
  background-color: #6c757d !important;
  border-color: #6c757d !important;
}

.btn-primary {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
  color: white !important;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0b5ed7 !important;
  border-color: #0a58ca !important;
  transform: translateY(-1px);
}

.btn-primary:disabled {
  opacity: 0.7;
  background-color: #6c757d !important;
  border-color: #6c757d !important;
}

@media (max-width: 768px) {
  .auth-card {
    padding: 1.5rem;
  }
}
</style>
