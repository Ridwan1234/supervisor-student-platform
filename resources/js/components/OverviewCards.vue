<template>
    <div class="overview-cards">
      <div class="card" v-for="(item, index) in cards" :key="index">
        <h3>{{ item.title }}</h3>
        <p>{{ item.value }}</p>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: 'OverviewCards',
    data() {
      return {
        cards: [
          { title: 'Students Connected', value: 24 },
          { title: 'Tasks Pending', value: 7 },
          { title: 'Projects Active', value: 3 },
        ],
      };
    },
  };
  </script>

  <style scoped>
  .overview-cards {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
  }

  .card {
    background-color: #f1f3f5;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    flex: 1;
    text-align: center;
  }

  .card h3 {
    margin: 0 0 10px;
  }

  .card p {
    font-size: 24px;
    font-weight: bold;
  }
  </style>
