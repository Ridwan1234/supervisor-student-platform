<template>
  <div class="container-fluid py-4">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2">
        <div class="card">
          <div class="card-header">
            <h6 class="mb-0 fw-bold">
              <i class="bi bi-funnel me-2"></i>
              Filters & Actions
            </h6>
          </div>
          <div class="card-body">
            <!-- Quick Stats -->
            <div class="mb-4">
              <h6 class="text-muted mb-3">Quick Stats</h6>
              <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-content-between align-items-center">
                  <span class="small">Total</span>
                  <span class="badge bg-primary">{{ totalCount }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="small">Unread</span>
                  <span class="badge bg-danger">{{ unreadCount }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="small">Today</span>
                  <span class="badge bg-success">{{ todayCount }}</span>
                </div>
              </div>
            </div>

            <!-- Status Filter -->
            <div class="mb-4">
              <h6 class="text-muted mb-3">Status</h6>
              <div class="d-flex flex-column gap-2">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="statusFilter"
                    id="allStatus"
                    value=""
                    v-model="statusFilter"
                  />
                  <label class="form-check-label small" for="allStatus">
                    All ({{ totalCount }})
                  </label>
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="statusFilter"
                    id="unreadStatus"
                    value="unread"
                    v-model="statusFilter"
                  />
                  <label class="form-check-label small" for="unreadStatus">
                    Unread ({{ unreadCount }})
                  </label>
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="statusFilter"
                    id="readStatus"
                    value="read"
                    v-model="statusFilter"
                  />
                  <label class="form-check-label small" for="readStatus">
                    Read ({{ readCount }})
                  </label>
                </div>
              </div>
            </div>

            <!-- Type Filter -->
            <div class="mb-4">
              <h6 class="text-muted mb-3">Type</h6>
              <div class="d-flex flex-column gap-2">
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="typeFilter"
                    id="allType"
                    value=""
                    v-model="typeFilter"
                  />
                  <label class="form-check-label small" for="allType">
                    All Types
                  </label>
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="typeFilter"
                    id="messageType"
                    value="new_message"
                    v-model="typeFilter"
                  />
                  <label class="form-check-label small" for="messageType">
                    <i class="bi bi-chat-dots me-1"></i>
                    Messages
                  </label>
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="typeFilter"
                    id="taskType"
                    value="task_assigned"
                    v-model="typeFilter"
                  />
                  <label class="form-check-label small" for="taskType">
                    <i class="bi bi-list-check me-1"></i>
                    Tasks
                  </label>
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="typeFilter"
                    id="projectType"
                    value="project"
                    v-model="typeFilter"
                  />
                  <label class="form-check-label small" for="projectType">
                    <i class="bi bi-folder me-1"></i>
                    Projects
                  </label>
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="typeFilter"
                    id="fileType"
                    value="file"
                    v-model="typeFilter"
                  />
                  <label class="form-check-label small" for="fileType">
                    <i class="bi bi-file-earmark me-1"></i>
                    Files
                  </label>
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="typeFilter"
                    id="deadlineType"
                    value="deadline"
                    v-model="typeFilter"
                  />
                  <label class="form-check-label small" for="deadlineType">
                    <i class="bi bi-clock me-1"></i>
                    Deadlines
                  </label>
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="typeFilter"
                    id="progressType"
                    value="progress"
                    v-model="typeFilter"
                  />
                  <label class="form-check-label small" for="progressType">
                    <i class="bi bi-graph-up me-1"></i>
                    Progress
                  </label>
                </div>
                <div class="form-check">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="typeFilter"
                    id="groupType"
                    value="group"
                    v-model="typeFilter"
                  />
                  <label class="form-check-label small" for="groupType">
                    <i class="bi bi-people me-1"></i>
                    Group
                  </label>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="mb-4">
              <h6 class="text-muted mb-3">Quick Actions</h6>
              <div class="d-flex flex-column gap-2">
                <button
                  @click="markAllAsRead"
                  :disabled="unreadCount === 0"
                  class="btn btn-primary btn-sm w-100"
                >
                  <i class="bi bi-check-all me-1"></i>
                  Mark All Read
                </button>
                <button
                  @click="clearAllNotifications"
                  class="btn btn-outline-danger btn-sm w-100"
                >
                  <i class="bi bi-trash me-1"></i>
                  Clear All
                </button>
                <button
                  @click="clearFilters"
                  class="btn btn-outline-secondary btn-sm w-100"
                >
                  <i class="bi bi-arrow-clockwise me-1"></i>
                  Clear Filters
                </button>
              </div>
            </div>

            <!-- Notification Preferences -->
            <div class="mb-4">
              <h6 class="text-muted mb-3">Preferences</h6>
              <div class="d-flex flex-column gap-2">
                <div class="form-check form-switch">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="emailNotifications"
                    v-model="preferences.email"
                    @change="updatePreferences"
                  />
                  <label class="form-check-label small" for="emailNotifications">
                    Email Notifications
                  </label>
                </div>
                <div class="form-check form-switch">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="pushNotifications"
                    v-model="preferences.push"
                    @change="updatePreferences"
                  />
                  <label class="form-check-label small" for="pushNotifications">
                    Push Notifications
                  </label>
                </div>
                <div class="form-check form-switch">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="soundNotifications"
                    v-model="preferences.sound"
                    @change="updatePreferences"
                  />
                  <label class="form-check-label small" for="soundNotifications">
                    Sound Alerts
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="col-md-9 col-lg-10">
        <!-- Breadcrumb -->
        <nav v-if="user" aria-label="breadcrumb" class="mb-3">
          <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm align-items-center">
            <li class="breadcrumb-item">
              <router-link v-if="user.role === 'supervisor'" to="/supervisor-dashboard">
                <i class="bi bi-speedometer2 me-1"></i> Dashboard
              </router-link>
              <router-link v-else-if="user.role === 'student'" to="/student-dashboard">
                <i class="bi bi-person-circle me-1"></i> Dashboard
              </router-link>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
              <i class="bi bi-bell me-1"></i> Notifications
            </li>
          </ol>
        </nav>
        <!-- Header -->
        <div class="mb-4">
          <h1 class="display-6 fw-bold text-dark mb-2">Notifications</h1>
          <p class="text-muted">Stay updated with all your important activities and updates</p>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-bell text-primary fs-4"></i>
                  </div>
                  <div>
                    <p class="text-muted small mb-1">Total</p>
                    <h3 class="fw-bold mb-0">{{ totalCount }}</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="bg-danger bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-clock text-danger fs-4"></i>
                  </div>
                  <div>
                    <p class="text-muted small mb-1">Unread</p>
                    <h3 class="fw-bold mb-0">{{ unreadCount }}</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-check-circle text-success fs-4"></i>
                  </div>
                  <div>
                    <p class="text-muted small mb-1">Read</p>
                    <h3 class="fw-bold mb-0">{{ readCount }}</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-md-3">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="bg-info bg-opacity-10 rounded-circle p-3 me-3">
                    <i class="bi bi-calendar-event text-info fs-4"></i>
                  </div>
                  <div>
                    <p class="text-muted small mb-1">Today</p>
                    <h3 class="fw-bold mb-0">{{ todayCount }}</h3>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Search Bar -->
        <div class="card mb-4">
          <div class="card-body">
            <div class="input-group">
              <span class="input-group-text">
                <i class="bi bi-search"></i>
              </span>
              <input
                v-model="searchQuery"
                type="text"
                class="form-control"
                placeholder="Search notifications..."
              />
              <button
                v-if="searchQuery"
                @click="searchQuery = ''"
                class="btn btn-outline-secondary"
                type="button"
              >
                <i class="bi bi-x"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="card">
          <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0 fw-bold">
                <i class="bi bi-list-ul me-2"></i>
                Notifications
                <span v-if="filteredNotifications.length > 0" class="badge bg-secondary ms-2">
                  {{ filteredNotifications.length }}
                </span>
              </h6>
              <div class="d-flex gap-2">
                <button
                  @click="refreshNotifications"
                  class="btn btn-outline-primary btn-sm"
                  :disabled="isLoading"
                >
                  <i class="bi bi-arrow-clockwise" :class="{ 'animate-spin': isLoading }"></i>
                  Refresh
                </button>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="isLoading" class="card-body text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading notifications...</p>
          </div>

          <!-- Notifications List -->
          <div v-else-if="filteredNotifications.length > 0" class="list-group list-group-flush">
            <div
              v-for="notification in filteredNotifications"
              :key="notification.id"
              class="list-group-item"
              :class="{ 'bg-light': !notification.read_at }"
            >
              <div class="d-flex align-items-start gap-3">
                <!-- Notification Icon -->
                <div class="flex-shrink-0">
                  <div
                    class="rounded-circle d-flex align-items-center justify-content-center"
                    :class="getNotificationIconClass(notification.type)"
                    style="width: 40px; height: 40px;"
                  >
                    <i :class="getNotificationIcon(notification.type)" class="text-white"></i>
                  </div>
                </div>

                <!-- Notification Content -->
                <div class="flex-grow-1 min-w-0">
                  <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center gap-2">
                      <h6 class="mb-1 fw-bold text-dark">
                        {{ notification.title }}
                      </h6>
                      <span
                        v-if="!notification.read_at"
                        class="badge bg-primary"
                      >
                        New
                      </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <small class="text-muted">
                        {{ formatTime(notification.created_at) }}
                      </small>
                      <button
                        @click="deleteNotification(notification.id)"
                        class="btn btn-sm btn-link text-decoration-none p-0 text-muted"
                        title="Delete notification"
                      >
                        <i class="bi bi-x"></i>
                      </button>
                    </div>
                  </div>
                  
                  <p class="text-muted mb-2">
                    {{ notification.message }}
                  </p>

                  <!-- Additional Data -->
                  <div v-if="notification.data" class="mb-2">
                    <div v-if="notification.data.sender" class="text-muted small">
                      From: {{ notification.data.sender }}
                    </div>
                    <div v-if="notification.data.project" class="text-muted small">
                      Project: {{ notification.data.project }}
                    </div>
                    <div v-if="notification.data.task" class="text-muted small">
                      Task: {{ notification.data.task }}
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="d-flex gap-2">
                    <button
                      v-if="!notification.read_at"
                      @click="markAsRead(notification.id)"
                      class="btn btn-sm btn-outline-primary"
                    >
                      Mark as Read
                    </button>
                    <button
                      v-if="notification.data && notification.data.action_url"
                      @click="handleNotificationAction(notification)"
                      class="btn btn-sm btn-outline-success"
                    >
                      View Details
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="card-body text-center py-5">
            <i class="bi bi-bell text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3 mb-2">No notifications found</h5>
            <p class="text-muted">
              {{ searchQuery || statusFilter || typeFilter ? 'Try adjusting your filters or search terms.' : 'You\'re all caught up! No new notifications.' }}
            </p>
            <div v-if="searchQuery || statusFilter || typeFilter" class="mt-3">
              <button
                @click="clearFilters"
                class="btn btn-outline-primary btn-sm"
              >
                Clear filters
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="d-flex justify-content-between align-items-center mt-4">
          <div class="text-muted small">
            Showing {{ startIndex + 1 }} to {{ endIndex }} of {{ totalCount }} results
          </div>
          <nav>
            <ul class="pagination pagination-sm mb-0">
              <li class="page-item" :class="{ disabled: currentPage === 1 }">
                <button @click="previousPage" class="page-link">Previous</button>
              </li>
              <li class="page-item">
                <span class="page-link">Page {{ currentPage }} of {{ totalPages }}</span>
              </li>
              <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                <button @click="nextPage" class="page-link">Next</button>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Notification',
  data() {
    return {
      notifications: [],
      searchQuery: '',
      statusFilter: '',
      typeFilter: '',
      currentPage: 1,
      perPage: 20,
      totalCount: 0,
      unreadCount: 0,
      isLoading: false,
      preferences: {
        email: true,
        push: true,
        sound: false
      },
      unreadCountInterval: null,
      user: null
    };
  },
  computed: {
    filteredNotifications() {
      let filtered = this.notifications;

      // Search filter
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(notification =>
          notification.title.toLowerCase().includes(query) ||
          notification.message.toLowerCase().includes(query)
        );
      }

      // Status filter
      if (this.statusFilter === 'unread') {
        filtered = filtered.filter(notification => !notification.read_at);
      } else if (this.statusFilter === 'read') {
        filtered = filtered.filter(notification => notification.read_at);
      }

      // Type filter
      if (this.typeFilter) {
        filtered = filtered.filter(notification => notification.type === this.typeFilter);
      }

      return filtered;
    },
    totalPages() {
      return Math.ceil(this.totalCount / this.perPage);
    },
    startIndex() {
      return (this.currentPage - 1) * this.perPage;
    },
    endIndex() {
      return Math.min(this.startIndex + this.perPage, this.totalCount);
    },
    readCount() {
      return this.notifications.filter(n => n.read_at).length;
    },
    todayCount() {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return this.notifications.filter(n => {
        const created = new Date(n.created_at);
        return created >= today;
      }).length;
    }
  },
  methods: {
    async fetchUnreadCount() {
      try {
        const response = await axios.get('/api/notifications/unread-count');
        this.unreadCount = response.data.count || response.data.data?.count || 0;
      } catch (error) {
        console.error('Error fetching unread count:', error);
      }
    },
    async fetchNotifications() {
      try {
        this.isLoading = true;
        const response = await axios.get('/api/notifications', {
          params: {
            page: this.currentPage,
            per_page: this.perPage
          }
        });
        
        this.notifications = response.data.notifications || response.data.data || response.data;
        this.totalCount = response.data.total || this.notifications.length;
        await this.fetchUnreadCount();
      } catch (error) {
        console.error('Error fetching notifications:', error);
      } finally {
        this.isLoading = false;
      }
    },

    async refreshNotifications() {
      this.currentPage = 1;
      await this.fetchNotifications();
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
        this.totalCount = Math.max(0, this.totalCount - 1);
        await this.fetchUnreadCount();
      } catch (error) {
        console.error('Error deleting notification:', error);
      }
    },

    async clearAllNotifications() {
      try {
        await axios.delete('/api/notifications');
        this.notifications = [];
        this.totalCount = 0;
        await this.fetchUnreadCount();
      } catch (error) {
        console.error('Error clearing all notifications:', error);
      }
    },

    async fetchPreferences() {
      try {
        const response = await axios.get('/api/notifications/preferences');
        if (response.data.success && response.data.data) {
          this.preferences = {
            email: response.data.data.email ?? true,
            push: response.data.data.push ?? true,
            sound: response.data.data.sound ?? false
          };
        }
      } catch (error) {
        console.error('Error fetching preferences:', error);
      }
    },

    async updatePreferences() {
      try {
        await axios.post('/api/notifications/preferences', this.preferences);
      } catch (error) {
        console.error('Error updating preferences:', error);
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

    clearFilters() {
      this.searchQuery = '';
      this.statusFilter = '';
      this.typeFilter = '';
    },

    previousPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
        this.fetchNotifications();
      }
    },

    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
        this.fetchNotifications();
      }
    },
    async fetchUser() {
      try {
        const response = await axios.get('/api/user');
        this.user = response.data;
      } catch (error) {
        console.error('Error fetching user:', error);
      }
    }
  },
  
  mounted() {
    this.fetchNotifications();
    this.fetchPreferences();
    this.fetchUnreadCount();
    this.fetchUser();
    // Poll unread count every 30 seconds
    this.unreadCountInterval = setInterval(this.fetchUnreadCount, 30000);
  },
  
  watch: {
    searchQuery() {
      // Debounce search
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1;
        this.fetchNotifications();
      }, 300);
    },
    statusFilter() {
      this.currentPage = 1;
      this.fetchNotifications();
    },
    typeFilter() {
      this.currentPage = 1;
      this.fetchNotifications();
    }
  },
  beforeUnmount() {
    if (this.unreadCountInterval) {
      clearInterval(this.unreadCountInterval);
    }
  }
};
</script>

<style scoped>
.list-group-item:hover {
  background-color: rgba(0, 0, 0, 0.02) !important;
}

.card {
  border: 1px solid rgba(0, 0, 0, 0.125);
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
  background-color: rgba(0, 0, 0, 0.03);
  border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.form-switch .form-check-input {
  width: 2.5em;
  height: 1.25em;
}

.form-switch .form-check-input:focus {
  border-color: rgba(0, 0, 0, 0.25);
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
