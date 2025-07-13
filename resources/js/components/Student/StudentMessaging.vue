<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <StudentSidebar />

    <!-- Main Content -->
    <main class="main-content">
      <!-- Messaging Content -->
      <div class="messaging-wrapper">
        <Messaging 
          ref="messaging"
          @conversation-started="handleConversationStarted"
          @message-sent="handleMessageSent"
        />
      </div>
    </main>
  </div>
</template>

<script>
import StudentSidebar from './StudentSidebar.vue';
import Messaging from '../Messaging.vue';

export default {
  name: 'StudentMessaging',
  components: {
    StudentSidebar,
    Messaging
  },
  data() {
    return {
      // Component data
    };
  },
  methods: {
    toggleSidebar() {
      // Toggle sidebar on mobile
      const sidebar = document.querySelector('.sidebar');
      if (sidebar) {
        sidebar.classList.toggle('show');
      }
    },
    handleConversationStarted() {
      // Handle new conversation started
      this.$refs.messaging?.fetchConversations();
    },
    handleMessageSent() {
      // Handle message sent
      this.$refs.messaging?.refreshMessages();
    }
  }
};
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
  background-color: #f8f9fa;
}

.main-content {
  flex: 1;
  padding: 1.5rem;
  margin-left: 20px;
  transition: margin-left 0.3s ease;
}

.messaging-wrapper {
  background: white;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  overflow: hidden;
  height: calc(100vh - 100px);
}

/* Override messaging component height when used as child */
.messaging-wrapper :deep(.messaging-container) {
  height: 100% !important;
}

/* Ensure chat area takes full height */
.messaging-wrapper :deep(.chat-area) {
  display: flex !important;
  flex-direction: column !important;
  height: 100% !important;
}

/* Ensure message input is visible */
.messaging-wrapper :deep(.message-input) {
  flex-shrink: 0 !important;
  position: relative !important;
  z-index: 10 !important;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
  
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

/* Ensure proper z-index for modals */
:deep(.modal) {
  z-index: 1050;
}

:deep(.modal-backdrop) {
  z-index: 1040;
}
</style> 