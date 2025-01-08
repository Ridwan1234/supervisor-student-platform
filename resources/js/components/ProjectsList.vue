<template>
    <div class="projects-list">
      <div class="project" v-for="(project, index) in projects" :key="index">
        <div class="project-info">
          <h4>{{ project.name }}</h4>
          <p>Due: {{ project.due }}</p>
        </div>
        <span class="status" :class="project.status.toLowerCase()">
          {{ project.status }}
        </span>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: 'ProjectsList',
    data() {
      return {
        projects: [
          { name: 'Project Alpha', due: 'July 15, 2025', status: 'In Progress' },
          { name: 'Project Beta', due: 'August 1, 2025', status: 'Completed' },
        ],
      };
    },
  };
  </script>

  <style scoped>
  .projects-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .project {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
  }

  .project-info h4 {
    margin: 0;
    font-size: 16px;
  }

  .status {
    padding: 5px 10px;
    border-radius: 4px;
    font-weight: bold;
    text-transform: capitalize;
  }

  .status.in-progress {
    background-color: #ffec99;
    color: #856404;
  }

  .status.completed {
    background-color: #d4edda;
    color: #155724;
  }
  </style>
