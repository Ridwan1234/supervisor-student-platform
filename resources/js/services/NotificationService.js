import axios from 'axios';

class NotificationService {
  constructor() {
    this.unreadCount = 0;
    this.listeners = [];
  }

  // Fetch notifications
  async fetchNotifications(page = 1, perPage = 20) {
    try {
      const response = await axios.get('/api/notifications', {
        params: { page, per_page: perPage }
      });
      return response.data;
    } catch (error) {
      console.error('Error fetching notifications:', error);
      throw error;
    }
  }

  // Fetch unread count
  async fetchUnreadCount() {
    try {
      const response = await axios.get('/api/notifications/unread-count');
      this.unreadCount = response.data.count || 0;
      this.notifyListeners();
      return this.unreadCount;
    } catch (error) {
      console.error('Error fetching unread count:', error);
      return 0;
    }
  }

  // Mark notification as read
  async markAsRead(notificationId) {
    try {
      await axios.post('/api/notifications/mark-read', { notification_id: notificationId });
      this.unreadCount = Math.max(0, this.unreadCount - 1);
      this.notifyListeners();
    } catch (error) {
      console.error('Error marking notification as read:', error);
      throw error;
    }
  }

  // Mark all notifications as read
  async markAllAsRead() {
    try {
      await axios.post('/api/notifications/mark-all-read');
      this.unreadCount = 0;
      this.notifyListeners();
    } catch (error) {
      console.error('Error marking all notifications as read:', error);
      throw error;
    }
  }

  // Delete notification
  async deleteNotification(notificationId) {
    try {
      await axios.delete(`/api/notifications/${notificationId}`);
    } catch (error) {
      console.error('Error deleting notification:', error);
      throw error;
    }
  }

  // Clear all notifications
  async clearAllNotifications() {
    try {
      await axios.delete('/api/notifications');
      this.unreadCount = 0;
      this.notifyListeners();
    } catch (error) {
      console.error('Error clearing all notifications:', error);
      throw error;
    }
  }

  // Send notification (for testing)
  async sendNotification(data) {
    try {
      const response = await axios.post('/api/notifications/send', data);
      return response.data;
    } catch (error) {
      console.error('Error sending notification:', error);
      throw error;
    }
  }

  // Subscribe to unread count changes
  subscribe(callback) {
    this.listeners.push(callback);
    // Immediately call with current value
    callback(this.unreadCount);
  }

  // Unsubscribe from unread count changes
  unsubscribe(callback) {
    const index = this.listeners.indexOf(callback);
    if (index > -1) {
      this.listeners.splice(index, 1);
    }
  }

  // Notify all listeners
  notifyListeners() {
    this.listeners.forEach(callback => callback(this.unreadCount));
  }

  // Get current unread count
  getUnreadCount() {
    return this.unreadCount;
  }

  // Setup real-time notifications
  setupRealtimeNotifications() {
    if (window.Echo) {
      // Listen for new notifications
      window.Echo.private(`App.Models.User.${this.getUserId()}`)
        .notification((notification) => {
          this.unreadCount++;
          this.notifyListeners();
          this.showToast(notification.title, notification.message);
        });
    }
  }

  // Get user ID from auth
  getUserId() {
    // This should be implemented based on your auth system
    const user = JSON.parse(localStorage.getItem('user'));
    return user ? user.id : null;
  }

  // Show toast notification
  showToast(title, message) {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50 max-w-sm transform transition-all duration-300 translate-x-full';
    toast.innerHTML = `
      <div class="flex items-start space-x-3">
        <div class="flex-shrink-0">
          <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
            </svg>
          </div>
        </div>
        <div class="flex-1">
          <h4 class="text-sm font-medium text-gray-900">${title}</h4>
          <p class="text-sm text-gray-600 mt-1">${message}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          </svg>
        </button>
      </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
      toast.classList.remove('translate-x-full');
    }, 100);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
      if (toast.parentElement) {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
          if (toast.parentElement) {
            toast.remove();
          }
        }, 300);
      }
    }, 5000);
  }

  // Initialize the service
  async init() {
    await this.fetchUnreadCount();
    this.setupRealtimeNotifications();
  }
}

// Create singleton instance
const notificationService = new NotificationService();

export default notificationService; 