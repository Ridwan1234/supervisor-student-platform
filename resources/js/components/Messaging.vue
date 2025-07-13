<template>
  <div class="messaging-container">
    <!-- Sidebar -->
    <div class="messaging-sidebar">
      <div class="sidebar-header">
        <h5 class="mb-0">
          <i class="bi bi-chat-dots me-2"></i>
          Conversations
        </h5>
      </div>
      
      <div class="sidebar-content">
        <!-- New Conversation Button -->
        <div class="mb-3">
          <button @click="showNewConversationModal = true" class="btn btn-primary w-100">
            <i class="bi bi-plus-circle me-2"></i>
            New Conversation
          </button>
        </div>

        <!-- Search -->
        <div class="search-box mb-3">
          <div class="input-group">
            <span class="input-group-text">
              <i class="bi bi-search"></i>
            </span>
            <input 
              v-model="searchQuery" 
              type="text" 
              class="form-control" 
              placeholder="Search conversations..."
            >
          </div>
        </div>

        <!-- Conversations List -->
        <div class="conversations-list">
          <!-- Individual Conversations -->
          <div v-if="individualConversations.length > 0" class="conversation-section">
            <h6 class="section-title">Individual Chats</h6>
            <div 
              v-for="user in filteredIndividualConversations" 
              :key="user.id"
              @click="selectConversation(user, 'individual')"
              :class="['conversation-item', { active: selectedConversation?.id === user.id && selectedType === 'individual' }]"
            >
              <div class="conversation-avatar">
                <i class="bi bi-person-circle"></i>
              </div>
              <div class="conversation-info">
                <div class="conversation-name">{{ user.name }}</div>
                <div class="conversation-preview">
                  {{ user.lastMessage?.message || 'No messages yet' }}
                </div>
              </div>
              <div class="conversation-meta">
                <div class="conversation-time">
                  {{ formatTime(user.lastMessage?.created_at) }}
                </div>
                <div v-if="user.unreadCount > 0" class="unread-badge">
                  {{ user.unreadCount }}
                </div>
              </div>
            </div>
          </div>

          <!-- Group Conversations -->
          <div v-if="groupConversations.length > 0" class="conversation-section">
            <h6 class="section-title">Group Chats</h6>
            <div 
              v-for="group in filteredGroupConversations" 
              :key="group.id"
              @click="selectConversation(group, 'group')"
              :class="['conversation-item', { active: selectedConversation?.id === group.id && selectedType === 'group' }]"
            >
              <div class="conversation-avatar group-avatar">
                <i class="bi bi-people-fill"></i>
              </div>
              <div class="conversation-info">
                <div class="conversation-name">{{ group.name }}</div>
                <div class="conversation-preview">
                  {{ group.lastMessage?.message || 'No messages yet' }}
                </div>
              </div>
              <div class="conversation-meta">
                <div class="conversation-time">
                  {{ formatTime(group.lastMessage?.created_at) }}
                </div>
                <div v-if="group.unreadCount > 0" class="unread-badge">
                  {{ group.unreadCount }}
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="individualConversations.length === 0 && groupConversations.length === 0" class="empty-state">
            <i class="bi bi-chat-dots display-4 text-muted"></i>
            <p class="text-muted">No conversations yet</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-area">
      <!-- Chat Header -->
      <div v-if="selectedConversation" class="chat-header">
        <div class="chat-info">
          <div class="chat-avatar">
            <i :class="selectedType === 'group' ? 'bi bi-people-fill' : 'bi bi-person-circle'"></i>
          </div>
          <div class="chat-details">
            <h6 class="chat-name">{{ selectedConversation.name }}</h6>
            <small class="text-muted">
              {{ selectedType === 'group' ? `${selectedConversation.members?.length || 0} members` : 'Online' }}
            </small>
          </div>
        </div>
        <div class="chat-actions">
          <button class="btn btn-sm btn-outline-primary me-2" @click="openJitsiCall">
            <i class="bi bi-camera-video"></i> Video Call
          </button>
          <button class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-telephone"></i>
          </button>
          <button class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-three-dots-vertical"></i>
          </button>
        </div>
      </div>

      <!-- Messages Area -->
      <div class="messages-container" ref="messagesContainer">
        <div v-if="!selectedConversation" class="welcome-screen">
          <i class="bi bi-chat-dots display-1 text-muted"></i>
          <h4 class="mt-3">Welcome to Messages</h4>
          <p class="text-muted">Select a conversation to start messaging</p>
        </div>

        <div v-else-if="loading" class="loading-messages">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3">Loading messages...</p>
        </div>

        <div v-else class="messages-list">
          <div 
            v-for="message in messages" 
            :key="message.id"
            :class="['message-item', { 'message-own': message.sender_id === currentUser.id }]"
          >
            <div class="message-content">
              <div class="message-header">
                <span class="message-sender">{{ message.sender?.name }}</span>
                <span class="message-time">{{ formatTime(message.created_at) }}</span>
              </div>
              
              <div class="message-text">
                {{ message.message }}
              </div>

              <!-- File Attachments -->
              <div v-if="message.attachments && message.attachments.length > 0" class="message-attachments">
                <div 
                  v-for="attachment in message.attachments" 
                  :key="attachment.id"
                  class="attachment-item"
                >
                  <div class="attachment-icon">
                    <i :class="getFileIcon(attachment.name)"></i>
                  </div>
                  <div class="attachment-info">
                    <div class="attachment-name">{{ attachment.name }}</div>
                    <div class="attachment-size">{{ formatFileSize(attachment.size) }}</div>
                  </div>
                  <button @click="downloadAttachment(attachment)" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Message Input -->
      <div class="message-input">
        <div v-if="!selectedConversation" class="alert alert-info mb-2">
          Please select a conversation to start messaging
        </div>
        <div class="input-group">
          <button @click="showAttachmentModal = true" class="btn btn-outline-secondary" type="button">
            <i class="bi bi-paperclip"></i>
          </button>
          <input 
            v-model="newMessage" 
            type="text" 
            class="form-control" 
            placeholder="Type your message..."
            @keyup.enter="sendMessage"
            :disabled="!selectedConversation"
          >
          <button @click="sendMessage" class="btn btn-primary" type="button" :disabled="!newMessage.trim() || !selectedConversation">
            <i class="bi bi-send"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- New Conversation Modal -->
    <div class="modal fade" :class="{ show: showNewConversationModal }" :style="{ display: showNewConversationModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-chat-dots me-2"></i>
              Start New Conversation
            </h5>
            <button @click="showNewConversationModal = false" type="button" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Select User</label>
              <select v-model="selectedNewUser" class="form-select">
                <option value="">Choose a user...</option>
                <option v-for="user in availableUsers" :key="user.id" :value="user.id">
                  {{ user.name }} ({{ user.role }})
                </option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Initial Message</label>
              <textarea 
                v-model="newConversationMessage" 
                class="form-control" 
                rows="3" 
                placeholder="Type your first message..."
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showNewConversationModal = false" type="button" class="btn btn-secondary">Cancel</button>
            <button @click="startNewConversation" type="button" class="btn btn-primary" :disabled="!selectedNewUser || !newConversationMessage.trim()">
              Start Conversation
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Attachment Modal -->
    <div class="modal fade" :class="{ show: showAttachmentModal }" :style="{ display: showAttachmentModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="bi bi-paperclip me-2"></i>
              Attach Files
            </h5>
            <button @click="showAttachmentModal = false" type="button" class="btn-close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Select Files</label>
              <input 
                ref="attachmentInput"
                type="file" 
                multiple 
                class="form-control"
                @change="handleAttachmentSelect"
                accept="*/*"
              >
              <div class="form-text">Maximum file size: 5MB per file</div>
            </div>

            <div v-if="selectedAttachments.length > 0" class="selected-attachments">
              <label class="form-label">Selected Files</label>
              <div class="attachment-list">
                <div 
                  v-for="(file, index) in selectedAttachments" 
                  :key="index"
                  class="attachment-item"
                >
                  <i :class="getFileIcon(file.name)"></i>
                  <span>{{ file.name }}</span>
                  <small class="text-muted">({{ formatFileSize(file.size) }})</small>
                  <button @click="removeAttachment(index)" type="button" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-x"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="showAttachmentModal = false" type="button" class="btn btn-secondary">Cancel</button>
            <button @click="sendMessageWithAttachments" type="button" class="btn btn-primary" :disabled="sending">
              <span v-if="sending" class="spinner-border spinner-border-sm me-2"></span>
              {{ sending ? 'Sending...' : 'Send Message' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Backdrop -->
    <div v-if="showAttachmentModal" class="modal-backdrop fade show"></div>

    <SimpleVideoChat 
      :visible="showJitsi" 
      :roomName="jitsiRoomName" 
      @close="showJitsi = false" 
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import axios from 'axios'
import SimpleVideoChat from './SimpleVideoChat.vue';

export default {
  name: 'Messaging',
  emits: ['conversation-started', 'message-sent'],
  components: {
    SimpleVideoChat,
  },
  setup(props, { emit }) {
    const currentUser = ref({})
    const individualConversations = ref([])
    const groupConversations = ref([])
    const selectedConversation = ref(null)
    const selectedType = ref('')
    const messages = ref([])
    const newMessage = ref('')
    const searchQuery = ref('')
    const loading = ref(false)
    const sending = ref(false)
    const showAttachmentModal = ref(false)
    const selectedAttachments = ref([])
    const messagesContainer = ref(null)
    const showNewConversationModal = ref(false)
    const selectedNewUser = ref('')
    const newConversationMessage = ref('')
    const availableUsers = ref([])
    const showJitsi = ref(false)
    const jitsiRoomName = ref('')

    // Computed properties
    const filteredIndividualConversations = computed(() => {
      if (!searchQuery.value) return individualConversations.value
      return individualConversations.value.filter(user => 
        user.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      )
    })

    const filteredGroupConversations = computed(() => {
      if (!searchQuery.value) return groupConversations.value
      return groupConversations.value.filter(group => 
        group.name.toLowerCase().includes(searchQuery.value.toLowerCase())
      )
    })

    // Methods
    const fetchCurrentUser = async () => {
      try {
        const response = await axios.get('/api/user')
        currentUser.value = response.data
      } catch (error) {
        console.error('Error fetching current user:', error)
      }
    }

    const fetchConversations = async () => {
      try {
        const response = await axios.get('/api/conversations')
        individualConversations.value = response.data.data.individual_conversations || []
        groupConversations.value = response.data.data.group_conversations || []
      } catch (error) {
        console.error('Error fetching conversations:', error)
      }
    }

    const fetchAvailableUsers = async () => {
      try {
        const response = await axios.get('/api/available-users')
        availableUsers.value = response.data.data || []
      } catch (error) {
        console.error('Error fetching available users:', error)
      }
    }

    const startNewConversation = async () => {
      if (!selectedNewUser.value || !newConversationMessage.value.trim()) return
      
      sending.value = true
      try {
        const response = await axios.post('/api/send-message', {
          receiver_id: selectedNewUser.value,
          message: newConversationMessage.value
        })
        
        // Add the new conversation to the list
        const newUser = availableUsers.value.find(u => u.id == selectedNewUser.value)
        if (newUser) {
          newUser.lastMessage = response.data.data
          newUser.unreadCount = 0
          individualConversations.value.unshift(newUser)
        }
        
        // Select the new conversation
        await selectConversation(newUser, 'individual')
        
        // Reset modal
        showNewConversationModal.value = false
        selectedNewUser.value = ''
        newConversationMessage.value = ''
        
        showToast('Conversation started successfully', 'success')
        
        // Emit event for wrapper components
        emit('conversation-started')
      } catch (error) {
        console.error('Error starting conversation:', error)
        showToast('Error starting conversation', 'error')
      } finally {
        sending.value = false
      }
    }

    const selectConversation = async (conversation, type) => {
      selectedConversation.value = conversation
      selectedType.value = type
      messages.value = []
      await fetchMessages()
      scrollToBottom()
    }

    const fetchMessages = async () => {
      if (!selectedConversation.value) return
      
      loading.value = true
      try {
        let response
        if (selectedType.value === 'individual') {
          response = await axios.post('/api/messages', {
            receiver_id: selectedConversation.value.id
          })
        } else {
          response = await axios.get(`/api/groups/${selectedConversation.value.id}/messages`)
        }
        
        if (selectedType.value === 'individual') {
          messages.value = response.data.data || response.data
        } else {
          messages.value = response.data.data.messages || response.data.messages
        }
        markMessagesAsRead()
      } catch (error) {
        console.error('Error fetching messages:', error)
        showToast('Error loading messages', 'error')
      } finally {
        loading.value = false
      }
    }

    const sendMessage = async () => {
      if (!newMessage.value.trim() || !selectedConversation.value) return
      
      sending.value = true
      try {
        let response
        if (selectedType.value === 'individual') {
          response = await axios.post('/api/send-message', {
            receiver_id: selectedConversation.value.id,
            message: newMessage.value
          })
        } else {
          response = await axios.post(`/api/groups/${selectedConversation.value.id}/send-message`, {
            message: newMessage.value
          })
        }
        
        messages.value.push(response.data.data || response.data)
        newMessage.value = ''
        scrollToBottom()
        showToast('Message sent successfully', 'success')
        
        // Emit event for wrapper components
        emit('message-sent')
      } catch (error) {
        console.error('Error sending message:', error)
        showToast('Error sending message', 'error')
      } finally {
        sending.value = false
      }
    }

    const sendMessageWithAttachments = async () => {
      if (!newMessage.value.trim() && selectedAttachments.value.length === 0) return
      
      sending.value = true
      const formData = new FormData()
      
      if (newMessage.value.trim()) {
        formData.append('message', newMessage.value)
      }
      
      selectedAttachments.value.forEach(file => {
        formData.append('attachments[]', file)
      })
      
      try {
        let response
        if (selectedType.value === 'individual') {
          formData.append('receiver_id', selectedConversation.value.id)
          response = await axios.post('/api/send-message', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
        } else {
          response = await axios.post(`/api/groups/${selectedConversation.value.id}/send-message`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
          })
        }
        
        messages.value.push(response.data.data || response.data)
        newMessage.value = ''
        selectedAttachments.value = []
        showAttachmentModal.value = false
        scrollToBottom()
        showToast('Message sent successfully', 'success')
      } catch (error) {
        console.error('Error sending message with attachments:', error)
        showToast('Error sending message', 'error')
      } finally {
        sending.value = false
      }
    }

    const handleAttachmentSelect = (event) => {
      selectedAttachments.value = Array.from(event.target.files)
    }

    const removeAttachment = (index) => {
      selectedAttachments.value.splice(index, 1)
    }

    const downloadAttachment = async (attachment) => {
      try {
        const response = await axios.get(`/api/messages/attachments/${attachment.id}/download`, {
          responseType: 'blob'
        })
        
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', attachment.name)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
        
        showToast('File downloaded successfully', 'success')
      } catch (error) {
        console.error('Error downloading attachment:', error)
        showToast('Error downloading file', 'error')
      }
    }

    const markMessagesAsRead = async () => {
      const unreadMessages = messages.value.filter(msg => 
        msg.receiver_id === currentUser.value.id && !msg.read_at
      )
      
      if (unreadMessages.length > 0) {
        try {
          await axios.post('/api/messages/mark-read', {
            message_ids: unreadMessages.map(msg => msg.id)
          })
        } catch (error) {
          console.error('Error marking messages as read:', error)
        }
      }
    }

    const scrollToBottom = () => {
      nextTick(() => {
        if (messagesContainer.value) {
          messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
      })
    }

    const formatTime = (date) => {
      if (!date) return ''
      const messageDate = new Date(date)
      const now = new Date()
      const diffInHours = (now - messageDate) / (1000 * 60 * 60)
      
      if (diffInHours < 24) {
        return messageDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      } else if (diffInHours < 48) {
        return 'Yesterday'
      } else {
        return messageDate.toLocaleDateString()
      }
    }

    const formatFileSize = (bytes) => {
      if (!bytes) return '0 B'
      const units = ['B', 'KB', 'MB', 'GB']
      let size = bytes
      let unitIndex = 0
      
      while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024
        unitIndex++
      }
      
      return `${size.toFixed(1)} ${units[unitIndex]}`
    }

    const getFileIcon = (filename) => {
      const ext = filename.split('.').pop().toLowerCase()
      const icons = {
        pdf: 'bi bi-file-pdf',
        doc: 'bi bi-file-word',
        docx: 'bi bi-file-word',
        xls: 'bi bi-file-excel',
        xlsx: 'bi bi-file-excel',
        ppt: 'bi bi-file-ppt',
        pptx: 'bi bi-file-ppt',
        txt: 'bi bi-file-text',
        jpg: 'bi bi-file-image',
        jpeg: 'bi bi-file-image',
        png: 'bi bi-file-image',
        gif: 'bi bi-file-image',
        zip: 'bi bi-file-zip',
        rar: 'bi bi-file-zip',
        mp4: 'bi bi-file-play',
        avi: 'bi bi-file-play',
        mp3: 'bi bi-file-music',
        wav: 'bi bi-file-music'
      }
      return icons[ext] || 'bi bi-file-earmark'
    }

    const showToast = (message, type = 'info') => {
      const toast = document.createElement('div')
      toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`
      toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;'
      toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `
      document.body.appendChild(toast)
      
      setTimeout(() => {
        toast.remove()
      }, 5000)
    }

    const openJitsiCall = () => {
      if (!selectedConversation.value) return;
      if (selectedType.value === 'group') {
        jitsiRoomName.value = `group-${selectedConversation.value.id}`;
      } else {
        // 1:1 chat: use sorted user IDs for uniqueness
        const ids = [currentUser.value.id, selectedConversation.value.id].sort((a, b) => a - b);
        jitsiRoomName.value = `user-${ids[0]}-${ids[1]}`;
      }
      showJitsi.value = true;
    }

    // Lifecycle
    onMounted(() => {
      fetchCurrentUser()
      fetchConversations()
      fetchAvailableUsers()
    })

    // Watchers
    watch(messages, () => {
      scrollToBottom()
    })

    return {
      currentUser,
      individualConversations,
      groupConversations,
      selectedConversation,
      selectedType,
      messages,
      newMessage,
      searchQuery,
      loading,
      sending,
      showAttachmentModal,
      selectedAttachments,
      messagesContainer,
      filteredIndividualConversations,
      filteredGroupConversations,
      selectConversation,
      sendMessage,
      sendMessageWithAttachments,
      handleAttachmentSelect,
      removeAttachment,
      downloadAttachment,
      formatTime,
      formatFileSize,
      getFileIcon,
      showToast,
      showNewConversationModal,
      selectedNewUser,
      newConversationMessage,
      availableUsers,
      startNewConversation,
      fetchConversations,
      refreshMessages: fetchMessages,
      showJitsi,
      jitsiRoomName,
      openJitsiCall,
    }
  }
}
</script>

<style scoped>
.messaging-container {
  display: flex;
  height: 100vh;
  background-color: #f8f9fa;
}

.messaging-sidebar {
  width: 320px;
  background: white;
  border-right: 1px solid #dee2e6;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
  background: #f8f9fa;
}

.sidebar-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.search-box {
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
}

.conversation-section {
  margin-bottom: 2rem;
}

.section-title {
  font-size: 0.875rem;
  font-weight: 600;
  color: #6c757d;
  margin-bottom: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.conversation-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
  margin-bottom: 0.25rem;
}

.conversation-item:hover {
  background-color: #f8f9fa;
}

.conversation-item.active {
  background-color: #e3f2fd;
  border-left: 3px solid #2196f3;
}

.conversation-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.75rem;
  flex-shrink: 0;
}

.conversation-avatar i {
  font-size: 1.25rem;
  color: #6c757d;
}

.group-avatar {
  background: #fff3cd;
}

.group-avatar i {
  color: #856404;
}

.conversation-info {
  flex: 1;
  min-width: 0;
}

.conversation-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.conversation-preview {
  font-size: 0.875rem;
  color: #6c757d;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.conversation-meta {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  margin-left: 0.5rem;
}

.conversation-time {
  font-size: 0.75rem;
  color: #6c757d;
  margin-bottom: 0.25rem;
}

.unread-badge {
  background: #dc3545;
  color: white;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #6c757d;
}

.chat-area {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.chat-header {
  padding: 1rem 1.5rem;
  background: white;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.chat-info {
  display: flex;
  align-items: center;
}

.chat-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.75rem;
}

.chat-avatar i {
  font-size: 1.25rem;
  color: #6c757d;
}

.chat-name {
  margin-bottom: 0;
  font-weight: 600;
}

.chat-actions {
  display: flex;
  gap: 0.5rem;
}

.messages-container {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
  background: #f8f9fa;
}

.welcome-screen, .loading-messages {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #6c757d;
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message-item {
  display: flex;
  margin-bottom: 1rem;
}

.message-item.message-own {
  justify-content: flex-end;
}

.message-content {
  max-width: 70%;
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  background: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.message-own .message-content {
  background: #007bff;
  color: white;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  font-size: 0.75rem;
}

.message-sender {
  font-weight: 600;
}

.message-time {
  opacity: 0.7;
}

.message-text {
  line-height: 1.4;
  word-wrap: break-word;
}

.message-attachments {
  margin-top: 0.75rem;
  border-top: 1px solid rgba(0, 0, 0, 0.1);
  padding-top: 0.75rem;
}

.attachment-item {
  display: flex;
  align-items: center;
  padding: 0.5rem;
  background: rgba(0, 0, 0, 0.05);
  border-radius: 0.375rem;
  margin-bottom: 0.5rem;
  gap: 0.5rem;
}

.attachment-item:last-child {
  margin-bottom: 0;
}

.attachment-icon {
  color: #6c757d;
}

.attachment-info {
  flex: 1;
  min-width: 0;
}

.attachment-name {
  font-size: 0.875rem;
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.attachment-size {
  font-size: 0.75rem;
  color: #6c757d;
}

.message-input {
  padding: 1rem 1.5rem;
  background: white;
  border-top: 1px solid #dee2e6;
}

.attachment-list {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  padding: 0.5rem;
}

.attachment-list .attachment-item {
  background: transparent;
  border-bottom: 1px solid #f1f3f4;
  margin-bottom: 0.25rem;
}

.attachment-list .attachment-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

@media (max-width: 768px) {
  .messaging-container {
    flex-direction: column;
  }
  
  .messaging-sidebar {
    width: 100%;
    height: 300px;
    border-right: none;
    border-bottom: 1px solid #dee2e6;
  }
  
  .chat-area {
    flex: 1;
  }
  
  .message-content {
    max-width: 85%;
  }
}
</style>
