<template>
    <div class="dashboard-container">
      <h1>Student Dashboard</h1>

      <!-- Notifications -->
      <div class="notifications">
        <h2>Notifications</h2>
        <ul>
          <li v-for="notification in notifications" :key="notification.id">
            {{ notification.message }}
          </li>
        </ul>
      </div>

      <!-- Upcoming Meetings -->
      <div class="meetings">
        <h2>Upcoming Meetings</h2>
        <ul>
          <li v-for="meeting in meetings" :key="meeting.id">
            {{ meeting.date }} - {{ meeting.topic }}
          </li>
        </ul>
      </div>

      <!-- Submitted Work -->
      <div class="submitted-work">
        <h2>Submitted Work</h2>
        <ul>
          <li v-for="task in submittedTasks" :key="task.id">
            {{ task.title }} - Status: {{ task.status }}
          </li>
        </ul>
      </div>

      <!-- Task Submission -->
      <div class="task-submission">
        <h2>Submit New Work</h2>
        <form @submit.prevent="submitTask">
          <div class="form-group">
            <label for="title">Task Title</label>
            <input type="text" id="title" v-model="task.title" required />
          </div>
          <div class="form-group">
            <label for="file">Upload File</label>
            <input type="file" id="file" @change="handleFileUpload" required />
          </div>
          <button type="submit" class="btn-submit">Submit</button>
        </form>
      </div>
    </div>
  </template>

  <script>
  export default {
    data() {
      return {
        notifications: [],
        meetings: [],
        submittedTasks: [],
        task: {
          title: '',
          file: null,
        },
      };
    },
    methods: {
      fetchData() {
        // Fetch data from API
        // this.notifications = [...]; // Example: Fetch from backend
        // this.meetings = [...];
        // this.submittedTasks = [...];
      },
      handleFileUpload(event) {
        this.task.file = event.target.files[0];
      },
      submitTask() {
        const formData = new FormData();
        formData.append('title', this.task.title);
        formData.append('file', this.task.file);

        // Submit to API
        // axios.post('/api/submit-task', formData).then(response => {
        //   alert('Task submitted successfully!');
        // });
      },
    },
    mounted() {
      this.fetchData();
    },
  };
  </script>
