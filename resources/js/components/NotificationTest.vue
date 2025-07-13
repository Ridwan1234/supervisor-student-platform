<template>
  <div class="container py-4">
    <div class="card">
      <div class="card-header">
        <h2 class="card-title mb-0">Notification Test Panel</h2>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <!-- Notification Type -->
          <div class="col-12">
            <label class="form-label">Notification Type</label>
            <select v-model="notificationType" class="form-select">
              <option value="message">Message</option>
              <option value="task">Task</option>
              <option value="project">Project</option>
              <option value="file">File</option>
              <option value="deadline">Deadline</option>
              <option value="progress">Progress</option>
              <option value="group">Group</option>
              <option value="custom">Custom</option>
            </select>
          </div>

          <!-- Title -->
          <div class="col-12">
            <label class="form-label">Title</label>
            <input 
              v-model="title" 
              type="text" 
              class="form-control"
              placeholder="Enter notification title"
            />
          </div>

          <!-- Message -->
          <div class="col-12">
            <label class="form-label">Message</label>
            <textarea 
              v-model="message" 
              rows="3"
              class="form-control"
              placeholder="Enter notification message"
            ></textarea>
          </div>

          <!-- Send Button -->
          <div class="col-12">
            <button 
              @click="sendTestNotification"
              :disabled="!title || !message || isSending"
              class="btn btn-primary w-100"
            >
              {{ isSending ? 'Sending...' : 'Send Test Notification' }}
            </button>
          </div>

          <!-- Quick Test Buttons -->
          <div class="col-12">
            <hr>
            <h5 class="mb-3">Quick Tests</h5>
            <div class="row g-2">
              <div class="col-md-6">
                <button 
                  @click="sendQuickTest('message', 'New Message', 'You have received a new message from your supervisor.')"
                  class="btn btn-outline-primary w-100"
                >
                  Test Message
                </button>
              </div>
              <div class="col-md-6">
                <button 
                  @click="sendQuickTest('task', 'New Task Assigned', 'A new task has been assigned to you.')"
                  class="btn btn-outline-success w-100"
                >
                  Test Task
                </button>
              </div>
              <div class="col-md-6">
                <button 
                  @click="sendQuickTest('project', 'Project Update', 'Your project status has been updated.')"
                  class="btn btn-outline-info w-100"
                >
                  Test Project
                </button>
              </div>
              <div class="col-md-6">
                <button 
                  @click="sendQuickTest('file', 'File Shared', 'A new file has been shared with you.')"
                  class="btn btn-outline-warning w-100"
                >
                  Test File
                </button>
              </div>
              <div class="col-md-6">
                <button 
                  @click="sendQuickTest('deadline', 'Deadline Reminder', 'You have a deadline approaching soon.')"
                  class="btn btn-outline-danger w-100"
                >
                  Test Deadline
                </button>
              </div>
              <div class="col-md-6">
                <button 
                  @click="sendQuickTest('progress', 'Progress Update', 'Your progress has been updated.')"
                  class="btn btn-outline-secondary w-100"
                >
                  Test Progress
                </button>
              </div>
            </div>
          </div>

          <!-- Status Messages -->
          <div v-if="statusMessage" class="col-12">
            <div class="alert" :class="statusClass" role="alert">
              {{ statusMessage }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'NotificationTest',
  data() {
    return {
      notificationType: 'message',
      title: '',
      message: '',
      isSending: false,
      statusMessage: '',
      statusClass: ''
    };
  },
  methods: {
    async sendTestNotification() {
      if (!this.title || !this.message) {
        this.showStatus('Please fill in all fields', 'alert-danger');
        return;
      }

      this.isSending = true;
      this.statusMessage = '';

      try {
        const response = await axios.post('/api/notifications/test');
        console.log('Test notification response:', response.data);
        this.showStatus('Test notification sent successfully!', 'alert-success');
        this.title = '';
        this.message = '';
      } catch (error) {
        console.error('Error sending test notification:', error);
        this.showStatus(`Error sending test notification: ${error.response?.data?.message || error.message}`, 'alert-danger');
      } finally {
        this.isSending = false;
      }
    },

    sendQuickTest(type, title, message) {
      this.notificationType = type;
      this.title = title;
      this.message = message;
      this.sendTestNotification();
    },

    showStatus(message, type) {
      this.statusMessage = message;
      this.statusClass = type;
      
      // Clear status after 3 seconds
      setTimeout(() => {
        this.statusMessage = '';
        this.statusClass = '';
      }, 3000);
    }
  }
};
</script> 