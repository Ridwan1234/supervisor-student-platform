<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-overlay" @click="closeModal">
      <div class="modal-container" @click.stop>
        <div class="modal-dialog modal-xl">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">
                <i class="bi bi-eye me-2"></i>
                {{ file?.name }}
              </h5>
              <div class="d-flex gap-2">
                <button @click="downloadFile" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-download me-1"></i>Download
                </button>
                <button @click="closeModal" type="button" class="btn-close"></button>
              </div>
            </div>
            
            <div class="modal-body p-0">
              <div class="row g-0">
                <!-- File Preview Area -->
                <div class="col-md-8">
                  <div class="file-preview-area">
                    <!-- Loading State -->
                    <div v-if="loading" class="text-center py-5">
                      <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <p class="mt-3">Loading file preview...</p>
                    </div>

                    <!-- Image Preview -->
                    <div v-else-if="isImage" class="image-preview">
                      <img :src="previewUrl" :alt="file?.name" class="img-fluid">
                    </div>

                    <!-- PDF Preview -->
                    <div v-else-if="isPdf" class="pdf-preview">
                      <iframe :src="previewUrl" width="100%" height="600"></iframe>
                    </div>

                    <!-- Text Preview -->
                    <div v-else-if="isText" class="text-preview">
                      <pre class="text-content">{{ textContent }}</pre>
                    </div>

                    <!-- Video Preview -->
                    <div v-else-if="isVideo" class="video-preview">
                      <video controls width="100%">
                        <source :src="previewUrl" :type="file?.mime_type">
                        Your browser does not support the video tag.
                      </video>
                    </div>

                    <!-- Audio Preview -->
                    <div v-else-if="isAudio" class="audio-preview">
                      <audio controls width="100%">
                        <source :src="previewUrl" :type="file?.mime_type">
                        Your browser does not support the audio tag.
                      </audio>
                    </div>

                    <!-- Unsupported File Type -->
                    <div v-else class="unsupported-file">
                      <div class="text-center py-5">
                        <i class="bi bi-file-earmark display-1 text-muted"></i>
                        <h5 class="mt-3">Preview Not Available</h5>
                        <p class="text-muted">This file type cannot be previewed in the browser.</p>
                        <button @click="downloadFile" class="btn btn-primary">
                          <i class="bi bi-download me-2"></i>Download to View
                        </button>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Comments Sidebar -->
                <div class="col-md-4">
                  <div class="comments-sidebar">
                    <div class="comments-header">
                      <h6 class="mb-3">
                        <i class="bi bi-chat-dots me-2"></i>
                        Comments ({{ comments.length }})
                      </h6>
                    </div>

                    <!-- Add Comment -->
                    <div class="add-comment mb-3">
                      <div class="mb-2">
                        <textarea 
                          v-model="newComment" 
                          class="form-control" 
                          rows="3" 
                          placeholder="Add a comment..."
                          @keydown.ctrl.enter="addComment"
                        ></textarea>
                      </div>
                      <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Press Ctrl+Enter to submit</small>
                        <button @click="addComment" class="btn btn-primary btn-sm" :disabled="!newComment.trim()">
                          <i class="bi bi-send me-1"></i>Comment
                        </button>
                      </div>
                    </div>

                    <!-- Comments List -->
                    <div class="comments-list">
                      <div v-if="loadingComments" class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                          <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 small">Loading comments...</p>
                      </div>

                      <div v-else-if="comments.length === 0" class="text-center py-4">
                        <i class="bi bi-chat-dots text-muted"></i>
                        <p class="mt-2 text-muted small">No comments yet</p>
                      </div>

                      <div v-else class="comment-items">
                        <div 
                          v-for="comment in comments" 
                          :key="comment.id"
                          class="comment-item"
                        >
                          <div class="comment-header">
                            <div class="d-flex align-items-center">
                              <div class="comment-avatar">
                                <i class="bi bi-person-circle"></i>
                              </div>
                              <div class="comment-meta">
                                <strong>{{ comment.user?.name }}</strong>
                                <small class="text-muted">{{ formatDate(comment.created_at) }}</small>
                              </div>
                            </div>
                            <div class="comment-actions">
                              <button @click="replyToComment(comment)" class="btn btn-sm btn-link p-0">
                                <i class="bi bi-reply"></i>
                              </button>
                              <button 
                                v-if="canDeleteComment(comment)"
                                @click="deleteComment(comment)" 
                                class="btn btn-sm btn-link p-0 text-danger"
                              >
                                <i class="bi bi-trash"></i>
                              </button>
                            </div>
                          </div>
                          <div class="comment-content">
                            <p class="mb-2">{{ comment.content }}</p>
                          </div>

                          <!-- Reply Form -->
                          <div v-if="replyingTo === comment.id" class="reply-form">
                            <div class="mb-2">
                              <textarea 
                                v-model="replyContent" 
                                class="form-control" 
                                rows="2" 
                                placeholder="Write a reply..."
                                @keydown.ctrl.enter="submitReply"
                              ></textarea>
                            </div>
                            <div class="d-flex gap-2">
                              <button @click="submitReply" class="btn btn-primary btn-sm" :disabled="!replyContent.trim()">
                                Reply
                              </button>
                              <button @click="cancelReply" class="btn btn-secondary btn-sm">
                                Cancel
                              </button>
                            </div>
                          </div>

                          <!-- Replies -->
                          <div v-if="comment.replies && comment.replies.length > 0" class="replies">
                            <div 
                              v-for="reply in comment.replies" 
                              :key="reply.id"
                              class="reply-item"
                            >
                              <div class="reply-header">
                                <div class="d-flex align-items-center">
                                  <div class="reply-avatar">
                                    <i class="bi bi-person-circle"></i>
                                  </div>
                                  <div class="reply-meta">
                                    <strong>{{ reply.user?.name }}</strong>
                                    <small class="text-muted">{{ formatDate(reply.created_at) }}</small>
                                  </div>
                                </div>
                                <div class="reply-actions">
                                  <button 
                                    v-if="canDeleteComment(reply)"
                                    @click="deleteComment(reply)" 
                                    class="btn btn-sm btn-link p-0 text-danger"
                                  >
                                    <i class="bi bi-trash"></i>
                                  </button>
                                </div>
                              </div>
                              <div class="reply-content">
                                <p class="mb-0">{{ reply.content }}</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
import axios from 'axios';

export default {
  name: 'FilePreviewModal',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    file: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      loading: false,
      loadingComments: false,
      previewUrl: '',
      textContent: '',
      comments: [],
      newComment: '',
      replyingTo: null,
      replyContent: '',
      blobUrl: null
    }
  },
  computed: {
    isImage() {
      if (!this.file) return false;
      const imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
      return imageTypes.includes(this.file.mime_type);
    },
    isPdf() {
      return this.file?.mime_type === 'application/pdf';
    },
    isText() {
      if (!this.file) return false;
      const textTypes = ['text/plain', 'text/html', 'text/css', 'text/javascript', 'application/json'];
      return textTypes.includes(this.file.mime_type);
    },
    isVideo() {
      if (!this.file) return false;
      const videoTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/avi'];
      return videoTypes.includes(this.file.mime_type);
    },
    isAudio() {
      if (!this.file) return false;
      const audioTypes = ['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3'];
      return audioTypes.includes(this.file.mime_type);
    }
  },
  watch: {
    visible(newVal) {
      if (newVal && this.file) {
        this.loadPreview();
        this.loadComments();
      }
    }
  },
  methods: {
    async loadPreview() {
      if (!this.file) return;
      
      this.loading = true;
      try {
        // For images, videos, and audio, we need to create a blob URL
        if (this.isImage || this.isVideo || this.isAudio || this.isPdf) {
          const response = await axios.get(`/api/files/download/${this.file.id}`, {
            responseType: 'blob'
          });
          
          // Create a blob URL for the file
          const blob = new Blob([response.data], { type: this.file.mime_type });
          this.blobUrl = URL.createObjectURL(blob);
          this.previewUrl = this.blobUrl;
        }
        
        // For text files, get the content directly
        if (this.isText) {
          const response = await axios.get(`/api/files/download/${this.file.id}`);
          this.textContent = response.data;
        }
      } catch (error) {
        console.error('Error loading preview:', error);
        this.showToast('Error loading file preview', 'error');
      } finally {
        this.loading = false;
      }
    },

    async loadComments() {
      if (!this.file) return;
      
      this.loadingComments = true;
      try {
        const response = await axios.get(`/api/files/${this.file.id}/comments`);
        this.comments = response.data.data || [];
      } catch (error) {
        console.error('Error loading comments:', error);
        this.showToast('Error loading comments', 'error');
      } finally {
        this.loadingComments = false;
      }
    },

    async addComment() {
      if (!this.newComment.trim()) return;
      
      try {
        const response = await axios.post(`/api/files/${this.file.id}/comments`, {
          content: this.newComment
        });
        
        this.comments.unshift(response.data.data);
        this.newComment = '';
        this.showToast('Comment added successfully', 'success');
      } catch (error) {
        console.error('Error adding comment:', error);
        this.showToast('Error adding comment', 'error');
      }
    },

    replyToComment(comment) {
      this.replyingTo = comment.id;
      this.replyContent = '';
    },

    async submitReply() {
      if (!this.replyContent.trim()) return;
      
      try {
        const response = await axios.post(`/api/files/${this.file.id}/comments`, {
          content: this.replyContent,
          parent_id: this.replyingTo
        });
        
        // Find the parent comment and add the reply
        const parentComment = this.comments.find(c => c.id === this.replyingTo);
        if (parentComment) {
          if (!parentComment.replies) parentComment.replies = [];
          parentComment.replies.push(response.data.data);
        }
        
        this.replyContent = '';
        this.replyingTo = null;
        this.showToast('Reply added successfully', 'success');
      } catch (error) {
        console.error('Error adding reply:', error);
        this.showToast('Error adding reply', 'error');
      }
    },

    cancelReply() {
      this.replyingTo = null;
      this.replyContent = '';
    },

    async deleteComment(comment) {
      if (!confirm('Are you sure you want to delete this comment?')) return;
      
      try {
        await axios.delete(`/api/files/comments/${comment.id}`);
        
        // Remove from comments array
        const index = this.comments.findIndex(c => c.id === comment.id);
        if (index > -1) {
          this.comments.splice(index, 1);
        } else {
          // Remove from replies
          this.comments.forEach(c => {
            if (c.replies) {
              const replyIndex = c.replies.findIndex(r => r.id === comment.id);
              if (replyIndex > -1) {
                c.replies.splice(replyIndex, 1);
              }
            }
          });
        }
        
        this.showToast('Comment deleted successfully', 'success');
      } catch (error) {
        console.error('Error deleting comment:', error);
        this.showToast('Error deleting comment', 'error');
      }
    },

    canDeleteComment(comment) {
      // Check if current user can delete this comment
      // This would typically check against the authenticated user
      return true; // Simplified for now
    },

    async downloadFile() {
      try {
        const response = await axios.get(`/api/files/download/${this.file.id}`, {
          responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', this.file.name);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
        
        this.showToast('File downloaded successfully', 'success');
      } catch (error) {
        console.error('Error downloading file:', error);
        this.showToast('Error downloading file', 'error');
      }
    },

    closeModal() {
      // Clean up blob URL to prevent memory leaks
      if (this.blobUrl) {
        URL.revokeObjectURL(this.blobUrl);
        this.blobUrl = null;
      }
      
      this.newComment = '';
      this.replyingTo = null;
      this.replyContent = '';
      this.previewUrl = '';
      this.textContent = '';
      this.$emit('close');
    },

    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString();
    },

    showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
      toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.remove();
      }, 5000);
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.modal-container {
  width: 100%;
  max-width: 1200px;
  margin: 1rem;
  background-color: white;
  padding: 20px;
}

.modal-dialog {
  margin: 0;
  max-width: 100%;
}

.file-preview-area {
  height: 600px;
  overflow: auto;
  background: #f8f9fa;
}

.image-preview {
  text-align: center;
  padding: 1rem;
}

.image-preview img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
}

.pdf-preview iframe {
  border: none;
}

.text-preview {
  padding: 1rem;
  background: white;
  height: 100%;
  overflow: auto;
}

.text-content {
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  line-height: 1.5;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.video-preview,
.audio-preview {
  padding: 1rem;
  text-align: center;
}

.unsupported-file {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.comments-sidebar {
  height: 600px;
  border-left: 1px solid #dee2e6;
  display: flex;
  flex-direction: column;
}

.comments-header {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.add-comment {
  padding: 1rem;
  border-bottom: 1px solid #dee2e6;
}

.comments-list {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.comment-item {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f3f4;
}

.comment-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

.comment-avatar,
.reply-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #e9ecef;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 0.5rem;
  font-size: 1.2rem;
  color: #6c757d;
}

.comment-meta,
.reply-meta {
  flex: 1;
}

.comment-meta strong,
.reply-meta strong {
  display: block;
  font-size: 0.875rem;
}

.comment-meta small,
.reply-meta small {
  font-size: 0.75rem;
}

.comment-actions,
.reply-actions {
  display: flex;
  gap: 0.5rem;
}

.comment-content,
.reply-content {
  margin-left: 2.5rem;
}

.comment-content p,
.reply-content p {
  margin: 0;
  font-size: 0.875rem;
  line-height: 1.4;
}

.reply-form {
  margin-left: 2.5rem;
  margin-top: 1rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 0.375rem;
}

.replies {
  margin-left: 2.5rem;
  margin-top: 1rem;
}

.reply-item {
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f1f3f4;
}

.reply-item:last-child {
  border-bottom: none;
}

.reply-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
  .modal-container {
    margin: 0.5rem;
  }
  
  .row {
    flex-direction: column;
  }
  
  .col-md-8,
  .col-md-4 {
    width: 100%;
  }
  
  .comments-sidebar {
    height: 300px;
    border-left: none;
    border-top: 1px solid #dee2e6;
  }
  
  .file-preview-area {
    height: 400px;
  }
}
</style> 