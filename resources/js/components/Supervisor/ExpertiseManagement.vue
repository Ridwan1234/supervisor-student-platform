<template>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <SupervisorSidebar />

    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Navigation Bar -->
      <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid">
          <button class="navbar-toggler d-lg-none" type="button" @click="toggleSidebar">
            <span class="navbar-toggler-icon"></span>
          </button>
          
          <div class="d-flex align-items-center">
            <h4 class="mb-0 fw-bold text-primary">Expertise Management</h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Search Bar -->
            <div class="position-relative">
              <input 
                type="text" 
                class="form-control form-control-sm" 
                placeholder="Search expertise..." 
                v-model="searchQuery"
              />
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
            </div>

            <!-- Add New Expertise Button -->
            <button class="btn btn-primary" @click="openCreateModal">
              <i class="bi bi-plus me-2"></i>Add Expertise
            </button>
          </div>
        </div>
      </nav>

      <!-- Dashboard Content -->
      <div>
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3">Loading expertise data...</p>
        </div>

        <!-- Stats Cards -->
        <div v-else class="row mb-4">
          <div class="col-md-3" v-for="(stat, index) in stats" :key="index">
            <div class="card hover-lift">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h6 class="text-muted mb-1">{{ stat.label }}</h6>
                    <h3 class="fw-bold mb-0">{{ stat.value }}</h3>
                    <small class="text-success">
                      <i class="bi bi-arrow-up me-1"></i>
                      {{ stat.change }}% from last month
                    </small>
                  </div>
                  <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i :class="stat.icon" class="text-primary"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Expertise Grid -->
        <div v-if="!loading" class="row g-4">
          <div class="col-md-6 col-lg-4" v-for="expertise in filteredExpertise" :key="expertise.id">
            <div class="card hover-lift h-100">
              <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-3">
                  <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i :class="expertise.icon || 'bi bi-award'" class="text-primary"></i>
                  </div>
                  <div class="dropdown">
                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li><a class="dropdown-item" href="#" @click="editExpertise(expertise)">
                        <i class="bi bi-pencil me-2"></i>Edit
                      </a></li>
                      <li><a class="dropdown-item" href="#" @click="viewDetails(expertise)">
                        <i class="bi bi-eye me-2"></i>View Details
                      </a></li>
                      <li><hr class="dropdown-divider"></li>
                      <li><a class="dropdown-item text-danger" href="#" @click="deleteExpertise(expertise)">
                        <i class="bi bi-trash me-2"></i>Delete
                      </a></li>
                    </ul>
                  </div>
                </div>
                
                <h5 class="card-title fw-bold mb-2">{{ expertise.name }}</h5>
                <p class="card-text text-muted mb-3">{{ expertise.description }}</p>
                
                <div class="mb-3">
                  <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted">Proficiency Level</small>
                    <small class="text-muted">{{ expertise.proficiency_level || 0 }}%</small>
                  </div>
                  <div class="progress" style="height: 6px;">
                    <div class="progress-bar" :class="getProficiencyClass(expertise.proficiency_level || 0)" 
                         :style="{ width: (expertise.proficiency_level || 0) + '%' }"></div>
                  </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <small class="text-muted">Years of Experience</small>
                    <div class="fw-bold">{{ expertise.years_experience || 0 }} years</div>
                  </div>
                  <div>
                    <small class="text-muted">Projects Completed</small>
                    <div class="fw-bold">{{ expertise.projects_completed || 0 }}</div>
                  </div>
                </div>
                
                <div class="mt-3">
                  <div class="d-flex flex-wrap gap-1">
                    <span v-for="tag in expertise.tags" :key="tag" class="badge bg-light text-dark">
                      {{ tag }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && filteredExpertise.length === 0" class="text-center py-5">
          <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
            <i class="bi bi-award text-muted" style="font-size: 2rem;"></i>
          </div>
          <h5 class="text-muted">No expertise found</h5>
          <p class="text-muted">Add your first expertise area to get started</p>
          <button class="btn btn-primary" @click="openCreateModal">
            <i class="bi bi-plus me-2"></i>Add Expertise
          </button>
        </div>
      </div>
    </main>

    <!-- Create/Edit Expertise Modal -->
    <div class="modal fade" id="expertiseModal" tabindex="-1" v-show="showCreateModal || editingExpertise">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ editingExpertise ? 'Edit Expertise' : 'Add New Expertise' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveExpertise">
              <div class="mb-3">
                <label class="form-label">Expertise Name</label>
                <input type="text" class="form-control" v-model="expertiseForm.name" required>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" rows="3" v-model="expertiseForm.description" required></textarea>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Proficiency Level (%)</label>
                    <input type="range" class="form-range" min="0" max="100" v-model="expertiseForm.proficiencyLevel">
                    <div class="d-flex justify-content-between">
                      <small class="text-muted">Beginner</small>
                      <small class="text-muted">{{ expertiseForm.proficiencyLevel }}%</small>
                      <small class="text-muted">Expert</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Years of Experience</label>
                    <input type="number" class="form-control" v-model="expertiseForm.yearsExperience" min="0" required>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Icon</label>
                <select class="form-select" v-model="expertiseForm.icon">
                  <option value="bi bi-code-slash">Code</option>
                  <option value="bi bi-database">Database</option>
                  <option value="bi bi-graph-up">Analytics</option>
                  <option value="bi bi-palette">Design</option>
                  <option value="bi bi-gear">Engineering</option>
                  <option value="bi bi-lightbulb">Innovation</option>
                  <option value="bi bi-cpu">Technology</option>
                  <option value="bi bi-graph-up-arrow">Business</option>
                </select>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Tags (comma separated)</label>
                <input type="text" class="form-control" v-model="expertiseForm.tagsString" 
                       placeholder="e.g., JavaScript, React, Node.js">
                <small class="text-muted">Add relevant skills and technologies</small>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="saveExpertise" :disabled="saving">
              <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
              {{ editingExpertise ? 'Update' : 'Create' }} Expertise
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SupervisorSidebar from './SupervisorSidebar.vue';
import axios from 'axios';

export default {
  name: 'ExpertiseManagement',
  components: {
    SupervisorSidebar
  },
  data() {
    return {
      loading: false,
      saving: false,
      searchQuery: '',
      showCreateModal: false,
      editingExpertise: null,
      expertise: [],
      stats: [
        { label: 'Total Expertise', value: 0, change: 0, icon: 'bi bi-award' },
        { label: 'High Proficiency', value: 0, change: 0, icon: 'bi bi-star' },
        { label: 'Projects Completed', value: 0, change: 0, icon: 'bi bi-check-circle' },
        { label: 'Years Experience', value: 0, change: 0, icon: 'bi bi-clock' }
      ],
      expertiseForm: {
        name: '',
        description: '',
        proficiencyLevel: 50,
        yearsExperience: 0,
        icon: 'bi bi-code-slash',
        tagsString: ''
      }
    };
  },
  computed: {
    filteredExpertise() {
      if (!this.searchQuery) return this.expertise;
      
      return this.expertise.filter(exp => 
        exp.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        exp.description.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
        (exp.tags && exp.tags.some(tag => tag.toLowerCase().includes(this.searchQuery.toLowerCase())))
      );
    }
  },
  async mounted() {
    await this.fetchExpertise();
    this.updateStats();
  },
  methods: {
    async fetchExpertise() {
      this.loading = true;
      try {
        const response = await axios.get('/api/supervisor/expertise');
        this.expertise = response.data.data || response.data;
        this.updateStats();
      } catch (error) {
        console.error('Error fetching expertise:', error);
        this.showToast('Error loading expertise data', 'error');
      } finally {
        this.loading = false;
      }
    },
    updateStats() {
      const totalExpertise = this.expertise.length;
      const highProficiency = this.expertise.filter(exp => (exp.proficiency_level || 0) >= 80).length;
      const totalProjects = this.expertise.reduce((sum, exp) => sum + (exp.projects_completed || 0), 0);
      const totalYears = this.expertise.reduce((sum, exp) => sum + (exp.years_experience || 0), 0);
      
      this.stats = [
        { label: 'Total Expertise', value: totalExpertise, change: 12, icon: 'bi bi-award' },
        { label: 'High Proficiency', value: highProficiency, change: 8, icon: 'bi bi-star' },
        { label: 'Projects Completed', value: totalProjects, change: 15, icon: 'bi bi-check-circle' },
        { label: 'Years Experience', value: totalYears, change: 5, icon: 'bi bi-clock' }
      ];
    },
    toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    },
    getProficiencyClass(level) {
      if (level >= 90) return 'bg-success';
      if (level >= 70) return 'bg-warning';
      return 'bg-info';
    },
    viewDetails(expertise) {
      this.showToast(`Viewing details for: ${expertise.name}`, 'info');
    },
    editExpertise(expertise) {
      this.editingExpertise = expertise;
      this.expertiseForm = {
        name: expertise.name,
        description: expertise.description,
        proficiencyLevel: expertise.proficiency_level || 50,
        yearsExperience: expertise.years_experience || 0,
        icon: expertise.icon || 'bi bi-award',
        tagsString: expertise.tags ? expertise.tags.join(', ') : ''
      };
      this.openCreateModal();
    },
    async deleteExpertise(expertise) {
      if (confirm(`Are you sure you want to delete "${expertise.name}"?`)) {
        try {
          await axios.delete(`/api/expertise/${expertise.id}`);
          await this.fetchExpertise();
          this.showToast('Expertise deleted successfully', 'success');
        } catch (error) {
          console.error('Error deleting expertise:', error);
          this.showToast('Error deleting expertise', 'error');
        }
      }
    },
    openCreateModal() {
      this.showCreateModal = true;
      this.$nextTick(() => {
        const modal = document.getElementById('expertiseModal');
        if (modal && window.bootstrap) {
          try {
            const bootstrapModal = new window.bootstrap.Modal(modal);
            bootstrapModal.show();
          } catch (error) {
            console.error('Error opening modal:', error);
            // Fallback: just show the modal with CSS
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
          }
        } else {
          console.warn('Modal or Bootstrap not available');
        }
      });
    },
    
    closeModal() {
      const modal = document.getElementById('expertiseModal');
      if (modal && window.bootstrap) {
        try {
          const bootstrapModal = window.bootstrap.Modal.getInstance(modal);
          if (bootstrapModal) {
            bootstrapModal.hide();
          }
        } catch (error) {
          console.error('Error closing modal:', error);
          // Fallback: hide the modal with CSS
          modal.style.display = 'none';
          modal.classList.remove('show');
          document.body.classList.remove('modal-open');
          const backdrop = document.querySelector('.modal-backdrop');
          if (backdrop) {
            backdrop.remove();
          }
        }
      }
      this.showCreateModal = false;
      this.editingExpertise = null;
      this.resetForm();
    },
    resetForm() {
      this.expertiseForm = {
        name: '',
        description: '',
        proficiencyLevel: 50,
        yearsExperience: 0,
        icon: 'bi bi-code-slash',
        tagsString: ''
      };
    },
    async saveExpertise() {
      if (!this.expertiseForm.name || !this.expertiseForm.description) {
        this.showToast('Please fill in all required fields', 'error');
        return;
      }
      
      this.saving = true;
      try {
        const expertiseData = {
          name: this.expertiseForm.name,
          description: this.expertiseForm.description,
          proficiency_level: this.expertiseForm.proficiencyLevel,
          years_experience: this.expertiseForm.yearsExperience,
          icon: this.expertiseForm.icon,
          tags: this.expertiseForm.tagsString.split(',').map(tag => tag.trim()).filter(tag => tag)
        };
        
        if (this.editingExpertise) {
          await axios.put(`/api/expertise/${this.editingExpertise.id}`, expertiseData);
          this.showToast('Expertise updated successfully', 'success');
        } else {
          await axios.post('/api/expertise', expertiseData);
          this.showToast('Expertise created successfully', 'success');
        }
        
        await this.fetchExpertise();
        this.closeModal();
      } catch (error) {
        console.error('Error saving expertise:', error);
        this.showToast('Error saving expertise', 'error');
      } finally {
        this.saving = false;
      }
    },
    showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
      toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.remove();
      }, 5000);
    }
  }
};
</script>

<style scoped>
.dashboard-container {
  min-height: 100vh;
  background-color: #f8f9fa;
  display: flex;
}

.main-content {
  flex: 1;
  padding: 0;
  margin-left: 20px;
}

.navbar {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

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

.progress {
  border-radius: 0.25rem;
  background-color: #e9ecef;
}

.badge {
  font-size: 0.75rem;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
  
  .sidebar {
    position: fixed;
    z-index: 1000;
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .sidebar.show {
    transform: translateX(0);
  }
}
</style>
  