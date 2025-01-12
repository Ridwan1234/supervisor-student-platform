<template>
    <div>
      <h2>Manage Expertise Areas</h2>
      <form @submit.prevent="addExpertise">
        <input v-model="expertiseArea" placeholder="Add Expertise Area" required />
        <button type="submit">Add</button>
      </form>
  
      <ul>
        <li v-for="item in expertise" :key="item.id">
          {{ item.expertise_area }}
          <button @click="removeExpertise(item.id)">Remove</button>
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        expertiseArea: '',
        expertise: [],
      };
    },
    methods: {
      fetchExpertise() {
        axios.get('/api/expertise').then(response => {
          this.expertise = response.data;
        });
      },
      addExpertise() {
        axios
          .post('/api/expertise', { expertise_area: this.expertiseArea })
          .then(() => {
            this.expertiseArea = '';
            this.fetchExpertise();
          });
      },
      removeExpertise(id) {
        axios.delete(`/api/expertise/${id}`).then(() => {
          this.fetchExpertise();
        });
      },
    },
    mounted() {
      axios.defaults.baseURL = 'http://supervisor-student-platform.test';

      this.fetchExpertise();
    },
  };
  </script>
  
  <style>
  /* Add any styles here */
  </style>
  