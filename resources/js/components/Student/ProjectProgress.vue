<template>
    <div>
      <h2>Project Progress</h2>
      <form @submit.prevent="addProgress">
        <input v-model="title" placeholder="Progress Title" required />
        <textarea v-model="description" placeholder="Progress Description"></textarea>
        <input type="number" v-model="completionPercentage" placeholder="Completion %" min="0" max="100" required />
        <button type="submit">Add Progress</button>
      </form>
  
      <ul>
        <li v-for="progress in progressList" :key="progress.id">
          <h3>{{ progress.title }} ({{ progress.completion_percentage }}%)</h3>
          <p>{{ progress.description }}</p>
          <small>Updated by: {{ progress.updated_by.name }}</small>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    props: {
      projectId: {
        type: Number,
        required: true,
      },
    },
    data() {
      return {
        title: '',
        description: '',
        completionPercentage: '',
        progressList: [],
      };
    },
    methods: {
      fetchProgress() {
        axios.get(`/api/projects/${this.projectId}/progress`).then(response => {
          this.progressList = response.data;
        });
      },
      addProgress() {
        axios
          .post('/api/projects/progress', {
            project_id: this.projectId,
            title: this.title,
            description: this.description,
            completion_percentage: this.completionPercentage,
          })
          .then(() => {
            this.title = '';
            this.description = '';
            this.completionPercentage = '';
            this.fetchProgress();
          });
      },
    },
    mounted() {
        axios.defaults.baseURL = 'http://supervisor-student-platform.test';

      this.fetchProgress();
    },
  };
  </script>
  