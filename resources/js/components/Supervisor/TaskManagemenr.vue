<template>
  <div>
    <h2>Task Management</h2>
    <form @submit.prevent="createTask">
      <input v-model="title" placeholder="Task Title" required />
      <textarea v-model="description" placeholder="Task Description"></textarea>
      <select v-model="assignee">
        <option value="">Assign to (optional)</option>
        <option v-for="user in users" :key="user.id" :value="user.id">
          {{ user.name }}
        </option>
      </select>
      <select v-model="group">
        <option value="">Assign to Group (optional)</option>
        <option v-for="grp in groups" :key="grp.id" :value="grp.id">
          {{ grp.name }}
        </option>
      </select>
      <input type="date" v-model="dueDate" />
      <button type="submit">Create Task</button>
    </form>

    <h3>Your Tasks</h3>
    <ul>
      <li v-for="task in tasks" :key="task.id">
        {{ task.title }} - {{ task.status }}
        <button @click="updateStatus(task.id, 'in_progress')">In Progress</button>
        <button @click="updateStatus(task.id, 'completed')">Complete</button>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  data() {
    return {
      title: '',
      description: '',
      assignee: '',
      group: '',
      dueDate: '',
      tasks: [],
      users: [],
      groups: [],
    };
  },
  methods: {
    fetchUsers() {
      axios.get('/api/users').then((response) => {
        this.users = response.data;
      });
    },
    fetchGroups() {
      axios.get('/api/groups').then((response) => {
        this.groups = response.data;
      });
    },
    fetchTasks() {
      axios.get('/api/tasks').then((response) => {
        this.tasks = response.data;
      });
    },
    createTask() {
      axios
        .post('/api/tasks', {
          title: this.title,
          description: this.description,
          assigned_to: this.assignee,
          group_id: this.group,
          due_date: this.dueDate,
        })
        .then(() => {
          this.title = '';
          this.description = '';
          this.assignee = '';
          this.group = '';
          this.dueDate = '';
          this.fetchTasks();
        });
    },
    updateStatus(taskId, status) {
      axios.patch(`/api/tasks/${taskId}/status`, { status }).then(() => {
        this.fetchTasks();
      });
    },
  },
  mounted() {
    axios.defaults.baseURL = 'http://supervisor-student-platform.test';

    this.fetchUsers();
    this.fetchGroups();
    this.fetchTasks();
  },
};
</script>
