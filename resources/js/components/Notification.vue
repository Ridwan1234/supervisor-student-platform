<template>
    <div>
        <h2>Notifications</h2>
        <ul>
            <li v-for="notification in notifications" :key="notification.id">
                <a :href="notification.data.url">{{
                    notification.data.title
                }}</a>
                <p>{{ notification.data.body }}</p>
            </li>
        </ul>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            notifications: [],
        };
    },
    methods: {
        fetchNotifications() {
            axios.get("/api/notifications").then((response) => {
                this.notifications = response.data;
            });
        },
    },
    mounted() {
        axios.defaults.baseURL = "http://supervisor-student-platform.test";

        this.fetchNotifications();

        window.Echo.private(`App.Models.User.${userId}`).notification(
            (notification) => {
                this.notifications.unshift(notification);
                alert(`${notification.title}: ${notification.body}`);
            }
        );
    },
};
</script>
