<template>
  <div>
    <h2>Chat</h2>
    <ul>
      <li v-for="msg in messages" :key="msg.id">
        <strong>{{ msg.sender.name }}:</strong> {{ msg.message }}
      </li>
    </ul>

    <form @submit.prevent="sendMessage">
      <input v-model="newMessage" placeholder="Type a message..." />
      <button type="submit">Send</button>
    </form>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      messages: [],
      newMessage: '',
    };
  },
  methods: {
    fetchMessages(receiverId) {
      axios
        .post('/api/messages', { receiver_id: receiverId })
        .then(response => {
          this.messages = response.data;
        });
    },
    sendMessage() {
      if (!this.newMessage) return;

      axios
        .post('/api/send-message', { receiver_id: this.receiverId, message: this.newMessage })
        .then(response => {
          this.messages.push(response.data);
          this.newMessage = '';
        });
    },
  },
  mounted() {
    axios.defaults.baseURL = 'http://supervisor-student-platform.test';

    this.fetchMessages(this.receiverId);

    window.Echo.channel('messages').listen('MessageSent', event => {
      this.messages.push(event.message);
    });
  },
  props: {
    receiverId: {
      type: Number,
      required: true,
    },
  },
};
</script>
