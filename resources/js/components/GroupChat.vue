<template>
    <div>
        <h2>{{ group.name }}</h2>
        <button class="btn btn-outline-primary mb-3" @click="showVideoChat = true">
            <i class="bi bi-camera-video me-1"></i> Start Video Call
        </button>
        <ul>
            <li v-for="msg in messages" :key="msg.id">
                <strong>{{ msg.sender.name }}:</strong> {{ msg.message }}
            </li>
        </ul>

        <form @submit.prevent="sendMessage">
            <input v-model="newMessage" placeholder="Type a message..." />
            <button type="submit">Send</button>
        </form>

        <SimpleVideoChat :visible="showVideoChat" :roomName="`group-${groupId}`" @close="showVideoChat = false" />
    </div>
</template>

<script>
import axios from "axios";
import SimpleVideoChat from "./SimpleVideoChat.vue";

export default {
  name: "GroupChat",
  components: {
    SimpleVideoChat,
  },
  props: {
    groupId: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      group: {},
      messages: [],
      newMessage: "",
      showVideoChat: false,
    };
  },
    methods: {
        fetchGroupMessages() {
            axios
                .post(`/api/groups/${this.groupId}/messages`)
                .then((response) => {
                    this.messages = response.data;
                });
        },
        sendMessage() {
            if (!this.newMessage) return;

            axios
                .post(`/api/groups/${this.groupId}/send-message`, {
                    message: this.newMessage,
                })
                .then((response) => {
                    this.messages.push(response.data);
                    this.newMessage = "";
                });
        },
    },
    mounted() {
        axios.defaults.baseURL = "http://supervisor-student-platform.test";

        this.fetchGroupMessages();

        window.Echo.private(`group.${this.groupId}`).listen(
            "MessageSent",
            (event) => {
                this.messages.push(event.message);
            }
        );
    },
};
</script>
