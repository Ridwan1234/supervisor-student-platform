<template>
  <div v-if="visible" class="video-chat-modal-overlay">
    <div class="video-chat-modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="bi bi-camera-video me-2"></i>
          Video Chat - {{ roomName }}
        </h5>
        <button class="btn-close" @click="$emit('close')" aria-label="Close"></button>
      </div>
      
      <div class="modal-body">
        <!-- Connection Status -->
        <div class="alert alert-info mb-3" v-if="!isConnected">
          <i class="bi bi-info-circle me-2"></i>
          <strong>Getting ready...</strong> Please allow camera and microphone access when prompted.
        </div>
        
        <div class="alert alert-success mb-3" v-if="isConnected">
          <i class="bi bi-check-circle me-2"></i>
          <strong>Connected!</strong> You can now see and hear other participants.
        </div>
        
        <!-- Video Streams -->
        <div class="video-container">
          <!-- Local Video -->
          <div class="video-wrapper local-video">
            <video 
              ref="localVideo" 
              autoplay 
              muted 
              playsinline
              class="video-stream"
            ></video>
            <div class="video-label">You</div>
          </div>
          
          <!-- Remote Video -->
          <div class="video-wrapper remote-video" v-if="remoteStream">
            <video 
              ref="remoteVideo" 
              autoplay 
              playsinline
              class="video-stream"
            ></video>
            <div class="video-label">Remote</div>
          </div>
          
          <!-- No Remote Stream Message -->
          <div class="no-remote" v-if="!remoteStream && isConnected">
            <i class="bi bi-person-x"></i>
            <p>Waiting for other participants to join...</p>
            <small class="text-muted">Share this room name: <code>{{ roomName }}</code></small>
          </div>
        </div>
        
        <!-- Controls -->
        <div class="video-controls">
          <button 
            class="btn btn-outline-secondary" 
            @click="toggleMute"
            :class="{ 'btn-danger': isMuted }"
          >
            <i :class="isMuted ? 'bi bi-mic-mute' : 'bi bi-mic'"></i>
            {{ isMuted ? 'Unmute' : 'Mute' }}
          </button>
          
          <button 
            class="btn btn-outline-secondary" 
            @click="toggleVideo"
            :class="{ 'btn-danger': isVideoOff }"
          >
            <i :class="isVideoOff ? 'bi bi-camera-video-off' : 'bi bi-camera-video'"></i>
            {{ isVideoOff ? 'Turn On Video' : 'Turn Off Video' }}
          </button>
          
          <button class="btn btn-outline-danger" @click="$emit('close')">
            <i class="bi bi-telephone-x"></i>
            End Call
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SimpleVideoChat',
  props: {
    roomName: { type: String, required: true },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      localStream: null,
      remoteStream: null,
      peerConnection: null,
      isConnected: false,
      isMuted: false,
      isVideoOff: false,
      localVideo: null,
      remoteVideo: null
    }
  },
  watch: {
    visible(newVal) {
      if (newVal) {
        this.initializeVideoChat()
      } else {
        this.cleanup()
      }
    }
  },
  methods: {
    async initializeVideoChat() {
      try {
        // Get user media
        this.localStream = await navigator.mediaDevices.getUserMedia({
          video: true,
          audio: true
        })
        
        // Display local video
        this.$nextTick(() => {
          if (this.$refs.localVideo) {
            this.$refs.localVideo.srcObject = this.localStream
          }
        })
        
        this.isConnected = true
        console.log('Video chat initialized successfully')
        
      } catch (error) {
        console.error('Error initializing video chat:', error)
        alert('Could not access camera/microphone. Please check your browser permissions.')
        this.$emit('close')
      }
    },
    
    toggleMute() {
      if (this.localStream) {
        const audioTrack = this.localStream.getAudioTracks()[0]
        if (audioTrack) {
          audioTrack.enabled = !audioTrack.enabled
          this.isMuted = !audioTrack.enabled
        }
      }
    },
    
    toggleVideo() {
      if (this.localStream) {
        const videoTrack = this.localStream.getVideoTracks()[0]
        if (videoTrack) {
          videoTrack.enabled = !videoTrack.enabled
          this.isVideoOff = !videoTrack.enabled
        }
      }
    },
    
    cleanup() {
      if (this.localStream) {
        this.localStream.getTracks().forEach(track => track.stop())
        this.localStream = null
      }
      
      if (this.peerConnection) {
        this.peerConnection.close()
        this.peerConnection = null
      }
      
      this.isConnected = false
      this.remoteStream = null
    }
  },
  
  beforeUnmount() {
    this.cleanup()
  }
}
</script>

<style scoped>
.video-chat-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.video-chat-modal-content {
  background: white;
  border-radius: 12px;
  max-width: 90vw;
  max-height: 90vh;
  width: 800px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #dee2e6;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.modal-body {
  padding: 1.5rem;
}

.video-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
  min-height: 300px;
}

.video-wrapper {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  background: #000;
  aspect-ratio: 16/9;
}

.video-stream {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.video-label {
  position: absolute;
  bottom: 10px;
  left: 10px;
  background: rgba(0, 0, 0, 0.7);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.875rem;
}

.no-remote {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #f8f9fa;
  border: 2px dashed #dee2e6;
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
}

.no-remote i {
  font-size: 3rem;
  color: #6c757d;
  margin-bottom: 1rem;
}

.video-controls {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  padding-top: 1rem;
  border-top: 1px solid #dee2e6;
}

@media (max-width: 768px) {
  .video-container {
    grid-template-columns: 1fr;
  }
  
  .video-chat-modal-content {
    width: 95vw;
    margin: 1rem;
  }
  
  .video-controls {
    flex-direction: column;
  }
}
</style> 