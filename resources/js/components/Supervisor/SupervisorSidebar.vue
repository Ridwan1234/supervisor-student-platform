<template>
    <aside class="sidebar">
      <div class="sidebar-header">Dashboard</div>
      <nav class="sidebar-menu">
        <router-link :to="{ name: 'supervisor-dashboard' }" class="menu-item">Home</router-link>
        <router-link to="#" class="menu-item">Projects</router-link>
        <router-link to="#" class="menu-item">Messages</router-link>
        <!-- <router-link to="/tasks" class="menu-item">Tasks</router-link> -->
        <router-link :to="{ name: 'tasks' }" class="menu-item">Tasks</router-link>
        <router-link to="#" class="menu-item">Settings</router-link>
        <router-link :to="{ name: 'logout' }" class="menu-item">Logout</router-link>

      </nav>
    </aside>
  </template>

  <script>
  export default {
    name: 'Sidebar',
  };
  </script>

  <style scoped>
  .sidebar {
    width: 250px;
    background-color: #343a40;
    color: #fff;
    display: flex;
    flex-direction: column;
  }

  .sidebar-header {
    padding: 20px;
    font-size: 18px;
    font-weight: bold;
    background-color: #212529;
  }

  .sidebar-menu {
    display: flex;
    flex-direction: column;
  }

  .menu-item {
    padding: 15px 20px;
    color: #ddd;
    text-decoration: none;
  }

  .menu-item:hover {
    background-color: #495057;
    color: #fff;
  }
  </style>
