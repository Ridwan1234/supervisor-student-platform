<template>
    <div>
      <h3>Uploaded Files</h3>
      <ul>
        <li v-for="file in files" :key="file.id">
          <a :href="`/api/files/download/${file.id}`">{{ file.name }}</a>
          <button @click="deleteFile(file.id)">Delete</button>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    data() {
      return {
        files: [],
      };
    },
    async created() {
        axios.defaults.baseURL = 'http://supervisor-student-platform.test';
        this.fetchFiles();
    },
    methods: {
        async fetchFiles() {
        try {
          const response = await axios.get("/api/files");
          this.files = response.data;
        } catch (error) {
          console.error("Failed to fetch files.");
        }
      },
      async deleteFile(fileId) {
        try {
          await axios.delete(`/api/files/${fileId}`);
          this.files = this.files.filter((file) => file.id !== fileId);
        } catch (error) {
          console.error("Failed to delete file.");
        }
      },
    },
  };
  </script>
  