<template>
  <div class="row g-3">
    <div v-if="loading" class="col-12 text-center py-4">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
      <p class="mt-3 text-muted">Loading overview cards...</p>
    </div>
    
    <div v-else-if="error" class="col-12">
      <div class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        {{ error }}
        <button class="btn btn-outline-danger btn-sm ms-3" @click="fetchCards">
          <i class="bi bi-arrow-clockwise me-1"></i>
          Retry
        </button>
      </div>
    </div>
    
    <div v-else class="col-md-4" v-for="(item, index) in cards" :key="index">
      <div class="card hover-lift h-100" @click="navigateToCard(item)" style="cursor: pointer;">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <h6 class="card-title text-muted mb-1">{{ item.title }}</h6>
              <h3 class="fw-bold mb-0">{{ item.value }}</h3>
              <small class="text-success">
                <i class="bi bi-arrow-up me-1"></i>
                {{ item.change }}% from last month
              </small>
            </div>
            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
              <i :class="item.icon" class="text-primary fs-4"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'OverviewCards',
  data() {
    return {
      cards: [],
      loading: true,
      error: null
    };
  },
  async mounted() {
    await this.fetchCards();
  },
  methods: {
    async fetchCards() {
      try {
        this.loading = true;
        this.error = null;
        
        const response = await fetch('/api/dashboard/overview-cards', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (!response.ok) {
          throw new Error('Failed to fetch overview cards');
        }
        
        const data = await response.json();
        this.cards = data.data;
        
      } catch (error) {
        console.error('Error fetching overview cards:', error);
        this.error = 'Failed to load overview cards. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    navigateToCard(item) {
      if (item.route) {
        this.$router.push({ name: item.route });
      }
    }
  },
};
</script>

<style scoped>
.card {
  border: none;
  border-radius: 0.5rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  transition: all 0.15s ease-in-out;
}

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.bg-opacity-10 {
  background-color: rgba(13, 110, 253, 0.1) !important;
}
</style>
