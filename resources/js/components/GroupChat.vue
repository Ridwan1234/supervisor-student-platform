<template>
    <div>
        <h2>{{ group.name }}</h2>
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
import axios from "axios";

export default {
    props: {
        groupId: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            messages: [],
            newMessage: "",
            group: {},
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
