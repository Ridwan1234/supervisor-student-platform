<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="d-flex align-items-center">
        <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
          <i class="bi bi-briefcase text-white"></i>
        </div>
        <div>
          <h6 class="mb-0 fw-bold text-primary">Supervisor</h6>
          <small class="text-light">Dashboard</small>
        </div>
      </div>
    </div>
    
    <nav class="sidebar-menu">
      <div class="px-3 py-2">
        <small class="text-light text-uppercase fw-semibold text-light">Main Menu</small>
      </div>
      
      <router-link :to="{ name: 'supervisor-dashboard' }" class="menu-item" active-class="active">
        <i class="bi bi-house me-3"></i>
        <span>Dashboard</span>
      </router-link>
      
      <router-link :to="{ name: 'supervisor-messaging' }" class="menu-item">
        <i class="bi bi-chat-dots me-3"></i>
        <span>Messages</span>
        <span class="badge bg-danger ms-auto">3</span>
      </router-link>
      
      <div class="px-3 py-2 mt-3">
        <small class="text-light text-uppercase fw-semibold">Management</small>
      </div>
      
      <router-link :to="{name: 'expertise-management'}" class="menu-item">
        <i class="bi bi-award me-3"></i>
        <span>Expertise</span>
      </router-link>
      
      <router-link :to="{name: 'project-management'}" class="menu-item">
        <i class="bi bi-kanban me-3"></i>
        <span>Projects</span>
      </router-link>
      
      <router-link :to="{ name: 'group-management' }" class="menu-item">
        <i class="bi bi-people-fill me-3"></i>
        <span>Groups</span>
      </router-link>
      
      <router-link :to="{ name: 'task-management' }" class="menu-item">
        <i class="bi bi-list-check me-3"></i>
        <span>Tasks</span>
        <span class="badge bg-warning ms-auto">5</span>
      </router-link>
      
      <router-link :to="{ name: 'file-management' }" class="menu-item">
        <i class="bi bi-file-earmark me-3"></i>
        <span>Files</span>
      </router-link>
      
      <div class="px-3 py-2 mt-3">
        <small class="text-light text-uppercase fw-semibold">Account</small>
      </div>
      
      <router-link to="#" class="menu-item">
        <i class="bi bi-gear me-3"></i>
        <span>Settings</span>
      </router-link>
      
      <a href="#" @click.prevent="handleLogout" class="menu-item text-danger">
        <i class="bi bi-box-arrow-right me-3"></i>
        <span>Logout</span>
      </a>
    </nav>
  </aside>
</template>

<script>
import axios from 'axios';
import { auth } from '../../utils/auth';

export default {
  name: 'SupervisorSidebar',
  methods: {
    handleLogout() {
      // Call logout API to invalidate token on server
      axios.post('/api/logout').catch(() => {
        // Ignore errors during logout
      }).finally(() => {
        // Always logout locally
        auth.logout();
      });
    }
  }
};
</script>

<style scoped>
.sidebar {
  width: 280px;
  background: linear-gradient(180deg, #212529 0%, #495057 100%);
  min-height: 100vh;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
  color: #fff;
  z-index: 100;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-menu {
  flex: 1;
  padding: 1rem 0;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1.5rem;
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: all 0.15s ease-in-out;
  border-left: 3px solid transparent;
}

.menu-item:hover {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  text-decoration: none;
  border-left-color: var(--success-color);
}

.menu-item.active {
  background-color: rgba(255, 255, 255, 0.1);
  color: white;
  border-left-color: var(--success-color);
}

.menu-item i {
  font-size: 1.1rem;
  width: 20px;
  text-align: center;
}

.badge {
  font-size: 0.75rem;
}

@media (max-width: 768px) {
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
</style>
