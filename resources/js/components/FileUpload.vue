<template>
    <div>
      <h3>Upload File</h3>
      <input type="file" @change="onFileChange" />
      <button @click="uploadFile" :disabled="!file">Upload</button>
      <p v-if="message">{{ message }}</p>
    </div>
  </template>
  
  <script>
  import axios from "axios";
  
  export default {
    data() {
      return {
        file: null,
        message: "",
      };
    },
    methods: {
      onFileChange(event) {
        this.file = event.target.files[0];
      },
      async uploadFile() {
        if (!this.file) return;
  
        const formData = new FormData();
        formData.append("file", this.file);
        formData.append("project_id", this.$route.params.projectId || 1);
        formData.append("task_id", this.$route.params.taskId || 1);
  
        try {
          const response = await axios.post("/api/files/upload", formData, {
            headers: {
              "Content-Type": "multipart/form-data",
            },
          });
          this.message = response.data.message;
        } catch (error) {
          this.message = "Failed to upload file.";
        }
      },
    },
  };
  </script>
  