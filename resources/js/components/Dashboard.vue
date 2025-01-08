<template>
    <div class="dashboard-container">
      <!-- Sidebar -->
      <Sidebar />

      <!-- Main Content -->
      <main class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
          <input type="text" placeholder="Search..." class="search-bar" />
          <div class="profile-icon">
            <img src="https://via.placeholder.com/30" alt="Profile" />
          </div>
        </div>

        <!-- Overview Cards -->
        <OverviewCards />

        <!-- Active Projects -->
        <section class="projects-section">
          <h2>Active Projects</h2>
          <ProjectsList />
        </section>
      </main>
    </div>
  </template>

  <script>
  import Sidebar from './Sidebar.vue';
  import OverviewCards from './OverviewCards.vue';
  import ProjectsList from './ProjectsList.vue';

  export default {
    name: 'Dashboard',
    components: {
      Sidebar,
      OverviewCards,
      ProjectsList,
    },
  };
  </script>

  <style scoped>
  .dashboard-container {
    display: flex;
    height: 100vh;
    background-color: #fff;
  }

  .main-content {
    flex: 1;
    padding: 20px;
  }

  .top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .search-bar {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 250px;
  }

  .profile-icon img {
    border-radius: 50%;
  }

  .projects-section h2 {
    margin-bottom: 10px;
  }
  </style>
