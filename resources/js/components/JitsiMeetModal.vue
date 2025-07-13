<template>
  <div v-if="visible" class="jitsi-modal-overlay">
    <div class="jitsi-modal-content">
      <button class="btn-close position-absolute top-0 end-0 m-3" @click="$emit('close')" aria-label="Close"></button>
      
      <!-- Browser compatibility check -->
      <div v-if="!isWebRTCSupported" class="alert alert-warning m-3">
        <h6><i class="bi bi-exclamation-triangle me-2"></i>Browser Compatibility Issue</h6>
        <p class="mb-2">Your browser may not support video calls. Please try:</p>
        <ul class="mb-2">
          <li>Using Chrome, Firefox, or Safari (latest versions)</li>
          <li>Allowing camera and microphone permissions</li>
          <li>Using HTTPS (required for camera access)</li>
        </ul>
        <button class="btn btn-primary btn-sm" @click="retryConnection">Try Again</button>
      </div>
      
      <iframe
        v-else
        :src="jitsiUrl"
        allow="camera; microphone; fullscreen; display-capture; autoplay"
        style="width: 100%; height: 600px; border: 0; border-radius: 8px;"
        allowfullscreen
        @load="onIframeLoad"
        @error="onIframeError"
      ></iframe>
    </div>
  </div>
</template>

<script>
export default {
  name: 'JitsiMeetModal',
  props: {
    roomName: { type: String, required: true },
    visible: { type: Boolean, default: false }
  },
  data() {
    return {
      isWebRTCSupported: true,
      retryCount: 0,
      maxRetries: 3,
      useAlternativeServer: false
    };
  },
  computed: {
    jitsiUrl() {
      // Use alternative server for local development if needed
      const baseUrl = this.useAlternativeServer ? 'https://jitsi.riot.im' : 'https://meet.jit.si'
      const roomName = encodeURIComponent(this.roomName)
      
      // Configuration optimized for local development
      const config = [
        'config.prejoinPageEnabled=false',
        'config.disableAudioLevels=true',
        'config.disableSimulcast=true',
        'config.enableClosePage=true',
        'config.enableWelcomePage=false',
        'config.enableLobbyChat=false',
        'config.enablePrejoinPage=false',
        'config.startWithAudioMuted=true',
        'config.startWithVideoMuted=true',
        'config.requireDisplayName=false',
        'config.enableNoAudioDetection=false',
        'config.enableNoisyMicDetection=false',
        'config.enableRemb=true',
        'config.enableTcc=true',
        'config.openBridgeChannel=websocket',
        'config.p2p.enabled=true',
        'config.p2p.enableUnifiedOnChrome=true',
        'config.websocket=wss://meet.jit.si/xmpp-websocket',
        'config.websocketKeepAlive=30',
        'config.websocketKeepAliveUrl=https://meet.jit.si/ping',
        // Add HTTP-specific settings
        'config.allowHttp=true',
        'config.allowInsecureConnections=true',
        'config.websocketKeepAliveUrl=http://meet.jit.si/ping'
      ].join('&')
      
      return `${baseUrl}/${roomName}#${config}`
    }
  },
  methods: {
    onIframeLoad() {
      console.log(this.jitsiUrl);
      console.log('Jitsi iframe loaded successfully.');
      this.retryCount = 0; // Reset retry count on successful load
    },
    onIframeError(event) {
      console.error('Jitsi iframe error:', event);
      this.retryCount++;
      if (this.retryCount < this.maxRetries) {
        console.log(`Retrying connection (${this.retryCount}/${this.maxRetries})...`);
        this.retryConnection();
      } else if (!this.useAlternativeServer) {
        // Try alternative server
        console.log('Trying alternative Jitsi server...');
        this.useAlternativeServer = true;
        this.retryCount = 0;
        this.retryConnection();
      } else {
        console.error('Max retries reached. Jitsi connection failed.');
        alert('Failed to connect to Jitsi Meet. Please try again later.');
        this.$emit('close');
      }
    },
    retryConnection() {
      const iframe = document.querySelector('iframe');
      if (iframe) {
        iframe.src = this.jitsiUrl; // Reload the iframe with the same URL
      }
    }
  },
  mounted() {
    // Check if WebRTC is supported
    if (typeof RTCPeerConnection === 'undefined' || typeof MediaStream === 'undefined') {
      this.isWebRTCSupported = false;
      console.warn('WebRTC or MediaStream not supported in your browser. Jitsi Meet functionality may be limited.');
    }
  }
};
</script>

<style scoped>
.jitsi-modal-overlay {
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

.jitsi-modal-content {
  background: white;
  border-radius: 12px;
  padding: 20px;
  max-width: 90vw;
  max-height: 90vh;
  width: 800px;
  position: relative;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.btn-close {
  background: rgba(255, 255, 255, 0.9);
  border: none;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  z-index: 10;
  transition: all 0.2s;
}

.btn-close:hover {
  background: rgba(255, 255, 255, 1);
  transform: scale(1.1);
}

.alert {
  border-radius: 8px;
  border: none;
}

.alert ul {
  margin-bottom: 0;
  padding-left: 1.2rem;
}

.alert li {
  margin-bottom: 0.25rem;
}

@media (max-width: 768px) {
  .jitsi-modal-content {
    width: 95vw;
    padding: 15px;
  }
  
  iframe {
    height: 400px !important;
  }
}
</style> 