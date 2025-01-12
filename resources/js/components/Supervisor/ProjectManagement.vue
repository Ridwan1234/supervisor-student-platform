<template>
  <div>
    <h2>Manage Projects</h2>
    <form @submit.prevent="createProject">
      <input v-model="title" placeholder="Project Title" required />
      <textarea v-model="description" placeholder="Project Description"></textarea>
      <button type="submit">Create Project</button>
    </form>

    <ul>
      <li v-for="project in projects" :key="project.id">
        <h3>{{ project.title }}</h3>
        <p>{{ project.description }}</p>
        <h4>Assigned Students:</h4>
        <ul>
          <li v-for="student in project.students" :key="student.id">{{ student.fullname }}</li>
        </ul>

        <div>
          <h5>Assign a Student:</h5>
          <select v-model="selectedStudent">
            <option value="" disabled>Select Student</option>
            <option v-for="student in availableStudents" :key="student.id" :value="student.id">
              {{ student.fullname }}
            </option>
          </select>
          <button @click="assignStudent(project.id)">Assign</button>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      title: '',
      description: '',
      projects: [],
      availableStudents: [],
      selectedStudent: '',
    };
  },
  methods: {
    fetchProjects() {
      axios.get('/api/projects').then(response => {
        this.projects = response.data;
      });
    },
    fetchAvailableStudents() {
      axios.get('/api/students').then(response => {
        this.availableStudents = response.data;
      });
    },
    createProject() {
      axios
        .post('/api/projects', { title: this.title, description: this.description })
        .then(() => {
          this.title = '';
          this.description = '';
          this.fetchProjects();
        });
    },
    assignStudent(projectId) {
      if (!this.selectedStudent) {
        alert('Please select a student.');
        return;
      }

      axios
        .post('/api/assign-student', { project_id: projectId, student_id: this.selectedStudent })
        .then(() => {
          this.selectedStudent = '';
          this.fetchProjects(); // Refresh project data to include the newly assigned student
        });
    },
  },
  mounted() {
    axios.defaults.baseURL = 'http://supervisor-student-platform.test';

    this.fetchProjects();
    this.fetchAvailableStudents();
  },
};
</script>
