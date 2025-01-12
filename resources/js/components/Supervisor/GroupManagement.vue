<template>
  <div>
    <h2>Create Group</h2>
    <form @submit.prevent="createGroup">
      <input v-model="groupName" placeholder="Group Name" required />
      <select v-model="selectedMembers" multiple>
        <option v-for="user in users" :key="user.id" :value="user.id">
          {{ user.name }}
        </option>
      </select>
      <button type="submit">Create Group</button>
    </form>

    <h2>Your Groups</h2>
    <ul>
      <li v-for="group in groups" :key="group.id">
        {{ group.name }}
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      groupName: '',
      selectedMembers: [],
      users: [], // Fetch this from an API
      groups: [],
    };
  },
  methods: {
    fetchUsers() {
      axios.get('/api/users').then(response => {
        this.users = response.data;
      });
    },
    fetchGroups() {
      axios.get('/api/groups').then(response => {
        this.groups = response.data;
      });
    },
    createGroup() {
      axios
        .post('/api/groups', {
          name: this.groupName,
          members: this.selectedMembers,
        })
        .then(() => {
          this.groupName = '';
          this.selectedMembers = [];
          this.fetchGroups();
        });
    },
  },
  mounted() {
    axios.defaults.baseURL = 'http://supervisor-student-platform.test';

    this.fetchUsers();
    this.fetchGroups();
  },
};
</script>
