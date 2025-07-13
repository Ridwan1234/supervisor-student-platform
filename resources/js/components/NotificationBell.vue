<template>
  <div class="position-relative">
    <!-- Notification Bell -->
    <button
      @click="toggleDropdown"
      class="btn btn-link position-relative p-2 text-decoration-none"
      :class="{ 'text-primary': hasUnreadNotifications }"
    >
      <i class="bi bi-bell fs-5"></i>
      
      <!-- Unread Count Badge -->
      <span
        v-if="unreadCount > 0"
        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
        style="font-size: 0.6rem;"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
    </button>

    <!-- Notification Dropdown -->
    <div
      v-if="isDropdownOpen"
      class="dropdown-menu show position-absolute end-0 mt-2 notification-dropdown"
      style="width: 320px; max-height: 400px; overflow-y: auto;"
    >
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
        <h6 class="mb-0 fw-bold">Notifications</h6>
        <div class="d-flex gap-2">
          <button
            @click="markAllAsRead"
            class="btn btn-sm btn-link text-decoration-none p-0"
            :disabled="unreadCount === 0"
          >
            Mark all read
          </button>
          <button
            @click="clearAllNotifications"
            class="btn btn-sm btn-link text-decoration-none p-0 text-danger"
          >
            Clear all
          </button>
        </div>
      </div>

      <!-- Notifications List -->
      <div v-if="notifications.length > 0">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          class="p-3 border-bottom notification-item"
          :class="{ 'bg-light': !notification.read_at }"
          style="cursor: pointer;"
        >
          <div class="d-flex align-items-start gap-3">
            <!-- Notification Icon -->
            <div class="flex-shrink-0">
              <div
                class="rounded-circle d-flex align-items-center justify-content-center"
                :class="getNotificationIconClass(notification.type)"
                style="width: 32px; height: 32px;"
              >
                <i :class="getNotificationIcon(notification.type)" class="text-white"></i>
              </div>
            </div>

            <!-- Notification Content -->
            <div class="flex-grow-1 min-w-0">
              <div class="d-flex justify-content-between align-items-start">
                <p class="mb-1 fw-medium text-dark small">
                  {{ notification.title }}
                </p>
                <div class="d-flex align-items-center gap-2">
                  <small class="text-muted">
                    {{ formatTime(notification.created_at) }}
                  </small>
                  <button
                    @click="deleteNotification(notification.id)"
                    class="btn btn-sm btn-link text-decoration-none p-0 text-muted"
                    style="font-size: 0.75rem;"
                  >
                    <i class="bi bi-x"></i>
                  </button>
                </div>
              </div>
              <p class="mb-1 text-muted small">
                {{ notification.message }}
              </p>
              <div v-if="notification.data && notification.data.action_url" class="mt-2">
                <button
                  @click="handleNotificationAction(notification)"
                  class="btn btn-sm btn-link text-decoration-none p-0"
                  style="font-size: 0.75rem;"
                >
                  View Details
                </button>
              </div>
            </div>

            <!-- Unread Indicator -->
            <div v-if="!notification.read_at" class="flex-shrink-0">
              <div class="bg-primary rounded-circle" style="width: 8px; height: 8px;"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="p-4 text-center">
        <i class="bi bi-bell text-muted" style="font-size: 2rem;"></i>
        <h6 class="mt-2 mb-1">No notifications</h6>
        <p class="text-muted small mb-0">You're all caught up!</p>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'NotificationBell',
  data() {
    return {
      notifications: [],
      unreadCount: 0,
      isDropdownOpen: false,
      isLoading: false,
      unreadCountInterval: null,
      currentPage: 1,
      perPage: 20
    };
  },
  computed: {
    hasUnreadNotifications() {
      return this.unreadCount > 0;
    }
  },
  methods: {
    async fetchNotifications() {
      try {
        this.isLoading = true;
        console.log('Fetching notifications...');
        const response = await axios.get('/api/notifications', {
          params: {
            page: this.currentPage,
            per_page: this.perPage
          }
        });
        console.log('Notifications response:', response.data);
        this.notifications = response.data.notifications || response.data.data || response.data;
      } catch (error) {
        console.error('Error fetching notifications:', error);
      } finally {
        this.isLoading = false;
      }
    },

    async fetchUnreadCount() {
      try {
        console.log('Fetching unread count...');
        const response = await axios.get('/api/notifications/unread-count');
        console.log('Unread count response:', response.data);
        this.unreadCount = response.data.count || response.data.data?.count || 0;
      } catch (error) {
        console.error('Error fetching unread count:', error);
      }
    },

    toggleDropdown() {
      this.isDropdownOpen = !this.isDropdownOpen;
      if (this.isDropdownOpen) {
        this.fetchNotifications();
      }
    },

    async markAsRead(notificationId) {
      try {
        await axios.post('/api/notifications/mark-read', { notification_id: notificationId });
        const notification = this.notifications.find(n => n.id === notificationId);
        if (notification) {
          notification.read_at = new Date().toISOString();
        }
        await this.fetchUnreadCount();
      } catch (error) {
        console.error('Error marking notification as read:', error);
      }
    },

    async markAllAsRead() {
      try {
        await axios.post('/api/notifications/mark-all-read');
        this.notifications.forEach(n => n.read_at = new Date().toISOString());
        await this.fetchUnreadCount();
      } catch (error) {
        console.error('Error marking all notifications as read:', error);
      }
    },

    async deleteNotification(notificationId) {
      try {
        await axios.delete(`/api/notifications/${notificationId}`);
        this.notifications = this.notifications.filter(n => n.id !== notificationId);
        await this.fetchUnreadCount();
      } catch (error) {
        console.error('Error deleting notification:', error);
      }
    },

    async clearAllNotifications() {
      try {
        await axios.delete('/api/notifications');
        this.notifications = [];
        await this.fetchUnreadCount();
      } catch (error) {
        console.error('Error clearing all notifications:', error);
      }
    },

    handleNotificationAction(notification) {
      if (notification.data && notification.data.action_url) {
        // Mark as read when clicked
        this.markAsRead(notification.id);
        
        // Navigate to the action URL
        if (notification.data.action_url.startsWith('/')) {
          this.$router.push(notification.data.action_url);
        } else {
          window.open(notification.data.action_url, '_blank');
        }
      }
    },

    getNotificationIconClass(type) {
      const classes = {
        message: 'bg-primary',
        task: 'bg-success',
        project: 'bg-info',
        file: 'bg-warning',
        deadline: 'bg-danger',
        progress: 'bg-secondary',
        group: 'bg-dark',
        custom: 'bg-secondary'
      };
      return classes[type] || classes.custom;
    },

    getNotificationIcon(type) {
      const icons = {
        message: 'bi bi-chat-dots',
        task: 'bi bi-list-check',
        project: 'bi bi-folder',
        file: 'bi bi-file-earmark',
        deadline: 'bi bi-clock',
        progress: 'bi bi-graph-up',
        group: 'bi bi-people',
        custom: 'bi bi-bell'
      };
      return icons[type] || icons.custom;
    },

    formatTime(timestamp) {
      const now = new Date();
      const time = new Date(timestamp);
      const diffInMinutes = Math.floor((now - time) / (1000 * 60));
      
      if (diffInMinutes < 1) return 'Just now';
      if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
      
      const diffInHours = Math.floor(diffInMinutes / 60);
      if (diffInHours < 24) return `${diffInHours}h ago`;
      
      const diffInDays = Math.floor(diffInHours / 24);
      if (diffInDays < 7) return `${diffInDays}d ago`;
      
      return time.toLocaleDateString();
    },

    setupEchoListener() {
      // Listen for real-time notifications
      if (window.Echo && this.$auth && this.$auth.user) {
        window.Echo.private(`App.Models.User.${this.$auth.user.id}`)
          .notification((notification) => {
            // Add new notification to the top
            this.notifications.unshift(notification);
            this.fetchUnreadCount();
            // Show toast notification
            this.showToast(notification.title, notification.message);
          });
      }
    },

    showToast(title, message) {
      // Create a simple toast notification using Bootstrap
      const toast = document.createElement('div');
      toast.className = 'toast show position-fixed top-0 end-0 m-3';
      toast.style.zIndex = '9999';
      toast.innerHTML = `
        <div class="toast-header">
          <i class="bi bi-bell text-primary me-2"></i>
          <strong class="me-auto">${title}</strong>
          <button type="button" class="btn-close" onclick="this.closest('.toast').remove()"></button>
        </div>
        <div class="toast-body">
          ${message}
        </div>
      `;
      
      document.body.appendChild(toast);
      
      // Auto-remove after 5 seconds
      setTimeout(() => {
        if (toast.parentElement) {
          toast.remove();
        }
      }, 5000);
    }
  },
  
  mounted() {
    console.log('NotificationBell component mounted');
    this.fetchUnreadCount();
    this.setupEchoListener();
    // Poll unread count every 30 seconds
    this.unreadCountInterval = setInterval(this.fetchUnreadCount, 30000);
    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!this.$el.contains(e.target)) {
        this.isDropdownOpen = false;
      }
    });
  },
  
  beforeUnmount() {
    // Clean up Echo listener
    if (window.Echo && this.$auth && this.$auth.user) {
      window.Echo.leave(`App.Models.User.${this.$auth.user.id}`);
    }
    if (this.unreadCountInterval) {
      clearInterval(this.unreadCountInterval);
    }
  }
};
</script>

<style scoped>
.notification-dropdown {
  z-index: 1050 !important;
  position: absolute !important;
  top: 100% !important;
  right: 0 !important;
  min-width: 280px;
  margin-top: 0.125rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 0.375rem;
}

.notification-item:hover {
  background-color: rgba(0, 0, 0, 0.05) !important;
}

.btn-link {
  color: #6c757d;
}

.btn-link:hover {
  color: #495057;
}

.btn-link.text-primary {
  color: #0d6efd !important;
}
</style>
  