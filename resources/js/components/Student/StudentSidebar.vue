<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="d-flex align-items-center">
        <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
          <i class="bi bi-mortarboard text-white"></i>
        </div>
        <div>
          <h6 class="mb-0 fw-bold text-white">Student</h6>
          <small class="text-light">Dashboard</small>
        </div>
      </div>
    </div>
    
    <nav class="sidebar-menu">
      <div class="px-3 py-2">
        <small class="text-light text-uppercase fw-semibold">Main Menu</small>
      </div>
      
      <router-link :to="{ name: 'student-dashboard' }" class="menu-item" active-class="active">
        <i class="bi bi-house me-3"></i>
        <span>Dashboard</span>
      </router-link>
      
      <router-link :to="{ name: 'student-project' }" class="menu-item">
        <i class="bi bi-folder me-3"></i>
        <span>My Projects</span>
      </router-link>
      
      <router-link :to="{ name: 'student-task' }" class="menu-item">
        <i class="bi bi-list-check me-3"></i>
        <span>My Tasks</span>
      </router-link>
      
      <router-link :to="{ name: 'student-messaging' }" class="menu-item">
        <i class="bi bi-chat-dots me-3"></i>
        <span>Messages</span>
        <!-- <span class="badge bg-danger ms-auto">2</span> -->
      </router-link>
      
      <router-link :to="{ name: 'notifications' }" class="menu-item">
        <i class="bi bi-bell me-3"></i>
        <span>Notifications</span>
        <span v-if="unreadCount > 0" class="badge bg-danger ms-auto">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
      </router-link>
      
      <router-link :to="{ name: 'student-files' }" class="menu-item">
        <i class="bi bi-folder2-open me-3"></i>
        <span>My Files</span>
      </router-link>
      
      <!-- <div class="px-3 py-2 mt-3">
        <small class="text-light text-uppercase fw-semibold">Academic</small>
      </div>
      
      <router-link to="#" class="menu-item">
        <i class="bi bi-file-earmark-text me-3"></i>
        <span>Submissions</span>
      </router-link>
      
      <router-link to="#" class="menu-item">
        <i class="bi bi-calendar-event me-3"></i>
        <span>Meetings</span>
      </router-link>
      
      <router-link to="#" class="menu-item">
        <i class="bi bi-graph-up me-3"></i>
        <span>Progress</span>
      </router-link> -->
      
      <div class="px-3 py-2 mt-3">
        <small class="text-light text-uppercase fw-semibold">Account</small>
      </div>
      
      <router-link to="#" class="menu-item">
        <i class="bi bi-person me-3"></i>
        <span>Profile</span>
      </router-link>
      
      <router-link to="#" class="menu-item">
        <i class="bi bi-gear me-3"></i>
        <span>Settings</span>
      </router-link>
      
      <router-link :to="{ name: 'logout' }" class="menu-item text-danger">
        <i class="bi bi-box-arrow-right me-3"></i>
        <span>Logout</span>
      </router-link>
    </nav>
  </aside>
</template>

<script>
import axios from 'axios';
import notificationService from '../../services/NotificationService';

export default {
  name: 'StudentSidebar',
  data() {
    return {
      unreadCount: 0
    };
  },
  methods: {
    async fetchUnreadCount() {
      this.unreadCount = await notificationService.fetchUnreadCount();
    }
  },
  mounted() {
    this.fetchUnreadCount();
    // Subscribe to notification updates
    notificationService.subscribe((count) => {
      this.unreadCount = count;
    });
  },
  beforeUnmount() {
    // Unsubscribe from notification updates
    notificationService.unsubscribe((count) => {
      this.unreadCount = count;
    });
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
