<template>
  <div class="home-container">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="container">
        <div class="row align-items-center min-vh-100">
          <div class="col-lg-6">
            <h1 class="display-4 fw-bold text-white mb-4">
              Supervise & Learn Together
            </h1>
            <p class="lead text-white-50 mb-4">
              A modern platform connecting supervisors and students for seamless project management, 
              progress tracking, and collaborative learning.
            </p>
            <div class="d-flex gap-3">
              <router-link to="/register" class="btn btn-primary btn-lg">
                Get Started
              </router-link>
              <router-link to="/login" class="btn btn-outline-light btn-lg">
                Sign In
              </router-link>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="text-center">
              <div class="hero-image">
                <i class="bi bi-people-fill text-white" style="font-size: 8rem; opacity: 0.8;"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
      <div class="container">
        <div class="row text-center mb-5">
          <div class="col-12">
            <h2 class="fw-bold mb-3">Why Choose Our Platform?</h2>
            <p class="text-muted lead">Everything you need for effective supervision and learning</p>
          </div>
        </div>
        
        <div class="row g-4">
          <div class="col-md-4" v-for="(feature, index) in features" :key="index">
            <div class="card h-100 hover-lift border-0 shadow-sm">
              <div class="card-body text-center p-4">
                <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                  <i :class="feature.icon" class="text-primary fs-3"></i>
                </div>
                <h5 class="card-title fw-bold">{{ feature.title }}</h5>
                <p class="card-text text-muted">{{ feature.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works-section py-5 bg-light">
      <div class="container">
        <div class="row text-center mb-5">
          <div class="col-12">
            <h2 class="fw-bold mb-3">How It Works</h2>
            <p class="text-muted lead">Simple steps to get started</p>
          </div>
        </div>
        
        <div class="row g-4">
          <div class="col-md-3" v-for="(step, index) in steps" :key="index">
            <div class="text-center">
              <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                <span class="text-white fw-bold fs-4">{{ index + 1 }}</span>
              </div>
              <h5 class="fw-bold mb-2">{{ step.title }}</h5>
              <p class="text-muted">{{ step.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section py-5">
      <div class="container">
        <div class="row justify-content-center text-center">
          <div class="col-lg-8">
            <h2 class="fw-bold mb-4">Ready to Get Started?</h2>
            <p class="lead text-muted mb-4">
              Join thousands of supervisors and students who are already using our platform
            </p>
            <div class="d-flex gap-3 justify-content-center">
              <router-link to="/register" class="btn btn-primary btn-lg">
                Create Account
              </router-link>
              <router-link to="/login" class="btn btn-outline-primary btn-lg">
                Sign In
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer-section py-4 bg-dark text-white">
      <div class="container">
        <div class="row">
          <div class="col-md-6">
            <h5 class="fw-bold mb-3">Supervisor Student Platform</h5>
            <p class="text-muted mb-0">
              Connecting supervisors and students for better learning outcomes.
            </p>
          </div>
          <div class="col-md-6 text-md-end">
            <div class="d-flex gap-3 justify-content-md-end">
              <a href="#" class="text-white text-decoration-none">
                <i class="bi bi-facebook fs-5"></i>
              </a>
              <a href="#" class="text-white text-decoration-none">
                <i class="bi bi-twitter fs-5"></i>
              </a>
              <a href="#" class="text-white text-decoration-none">
                <i class="bi bi-linkedin fs-5"></i>
              </a>
            </div>
          </div>
        </div>
        <hr class="my-4">
        <div class="row">
          <div class="col-12 text-center">
            <small class="text-muted">
              © 2025 Supervisor Student Platform. All rights reserved.
            </small>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'Home',
  data() {
    return {
      features: [
        {
          title: 'Project Management',
          description: 'Easily create, assign, and track projects with real-time progress updates.',
          icon: 'bi bi-kanban'
        },
        {
          title: 'Communication',
          description: 'Built-in messaging system for seamless communication between supervisors and students.',
          icon: 'bi bi-chat-dots'
        },
        {
          title: 'Progress Tracking',
          description: 'Monitor student progress with detailed analytics and performance insights.',
          icon: 'bi bi-graph-up'
        },
        {
          title: 'File Sharing',
          description: 'Secure file upload and sharing system for documents and resources.',
          icon: 'bi bi-file-earmark'
        },
        {
          title: 'Task Management',
          description: 'Create and assign tasks with deadlines and status tracking.',
          icon: 'bi bi-list-check'
        },
        {
          title: 'Notifications',
          description: 'Stay updated with real-time notifications for important events and updates.',
          icon: 'bi bi-bell'
        }
      ],
      steps: [
        {
          title: 'Create Account',
          description: 'Sign up as a supervisor or student with your email and basic information.'
        },
        {
          title: 'Set Up Profile',
          description: 'Complete your profile with relevant information and preferences.'
        },
        {
          title: 'Start Collaborating',
          description: 'Begin creating projects, assigning tasks, and tracking progress.'
        },
        {
          title: 'Monitor Progress',
          description: 'Track performance, communicate effectively, and achieve goals together.'
        }
      ]
    };
  }
};
</script>

<style scoped>
.home-container {
  min-height: 100vh;
}

.hero-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
  opacity: 0.3;
}

.hero-image {
  position: relative;
  z-index: 1;
}

.features-section {
  background-color: #fff;
}

.how-it-works-section {
  background-color: #f8f9fa;
}

.cta-section {
  background-color: #fff;
}

.footer-section {
  background-color: #212529;
}

.hover-lift {
  transition: all 0.15s ease-in-out;
}

.hover-lift:hover {
  transform: translateY(-5px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.bg-opacity-10 {
  background-color: rgba(13, 110, 253, 0.1) !important;
}

@media (max-width: 768px) {
  .hero-section {
    text-align: center;
  }
  
  .d-flex.gap-3 {
    flex-direction: column;
    align-items: center;
  }
  
  .btn {
    width: 100%;
    max-width: 300px;
  }
}
</style>
