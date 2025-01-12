<template>
    <div>
      <h4>Notifications</h4>
      <ul>
        <li v-for="notification in notifications" :key="notification.id">
          {{ notification.data.message }}
        </li>
      </ul>
    </div>
  </template>
  
  <script>
import axios from 'axios';
  export default {
    data() {
      return {
        notifications: [],
      };
    },
    methods: {
      fetchNotifications() {
        axios.defaults.baseURL = 'http://supervisor-student-platform.test';
        axios.get('/api/notifications').then(response => {
          this.notifications = response.data;
        });
      },
    },
    mounted() {
      this.fetchNotifications();
  
    //   window.Echo.private(`App.Models.User.${this.$user.id}`)
    //     .notification(notification => {
    //       this.notifications.push(notification);
    //     });
    },
  };
  </script>
  