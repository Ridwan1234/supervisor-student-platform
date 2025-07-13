<template>
  <div class="auth-container">
    <div class="auth-card">
      <div class="text-center mb-4">
        <h2 class="fw-bold text-primary mb-2">Welcome Back</h2>
        <p class="text-muted">Sign in to your account</p>
      </div>
      
      <form @submit.prevent="handleLogin">
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
              placeholder="Enter your password"
              required
            />
          </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div class="form-check">
            <input type="checkbox" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          <a href="#" class="text-decoration-none text-primary">Forgot Password?</a>
        </div>

        <!-- Login Button -->
        <button 
          type="submit" 
          class="btn btn-primary w-100 mb-3 sign-in-btn"
          :disabled="loading"
        >
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          {{ loading ? 'Signing in...' : 'Sign In' }}
        </button>
      </form>

      <!-- Divider -->
      <div class="text-center mb-3">
        <span class="text-muted">or</span>
      </div>

      <!-- Social Login -->
      <div class="d-grid gap-2 mb-4">
        <button class="btn btn-outline-secondary">
          <i class="bi bi-google me-2"></i>
          Continue with Google
        </button>
      </div>

      <!-- Register Link -->
      <div class="text-center">
        <p class="mb-0">
          Don't have an account? 
          <router-link to="/register" class="text-decoration-none text-primary fw-semibold">
            Sign Up
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { auth } from '../../utils/auth';

export default {
  data() {
    return {
      form: {
        email: '',
        password: '',
      },
      currentUserId: null,
      loading: false,
    };
  },
  methods: {
    async handleLogin() {
      this.loading = true;
      axios.defaults.baseURL = 'http://supervisor-student-platform.test';
      const toast = useToast();

      try {
        const response = await axios.post("/api/login", this.form);
        const token = response.data.token;
        
        // Use auth utility to set token and user
        auth.setToken(token);
        auth.setUser(response.data.user);
        this.currentUserId = response.data.user.id;

        const userRole = response.data.role;
        if (userRole === 'supervisor') {
          this.$router.push({ name: 'supervisor-dashboard' });
        } else if (userRole === 'student') {
          this.$router.push({ name: 'student-dashboard' });
        } else if (userRole === 'admin') {
          this.$router.push({ name: 'admin-dashboard' });
        } else {
          this.$router.push({ name: 'dashboard' });
        }

        toast.success('Welcome back! Login successful.');
      } catch (error) {
        if (error.response) {
          toast.error(error.response.data.message || 'Login failed! Please check your credentials.');
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
  max-width: 400px;
}

.input-group-text {
  background-color: #f8f9fa;
  border-color: #dee2e6;
}

.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

/* Explicit button styles to ensure visibility */
.sign-in-btn {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
  color: white !important;
  font-weight: 500;
  padding: 0.75rem 1.5rem;
  border-radius: 0.375rem;
  transition: all 0.15s ease-in-out;
}

.sign-in-btn:hover:not(:disabled) {
  background-color: #0b5ed7 !important;
  border-color: #0a58ca !important;
  transform: translateY(-1px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.sign-in-btn:disabled {
  opacity: 0.7;
  background-color: #6c757d !important;
  border-color: #6c757d !important;
}

/* Fallback for Bootstrap classes */
.btn-primary {
  background-color: #0d6efd !important;
  border-color: #0d6efd !important;
  color: white !important;
}

.btn-primary:hover:not(:disabled) {
  background-color: #0b5ed7 !important;
  border-color: #0a58ca !important;
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
