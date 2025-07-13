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
            <h4 class="mb-0 fw-bold text-primary">
              <i class="bi bi-people-fill me-2"></i>
              Group Management
            </h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Search Bar -->
            <div class="position-relative">
              <input 
                type="text" 
                class="form-control form-control-sm" 
                placeholder="Search groups..." 
                v-model="searchQuery"
              />
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
            </div>

            <!-- Create New Group Button -->
            <button class="btn btn-primary" @click="showCreateModal = true">
              <i class="bi bi-plus me-2"></i>New Group
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
          <p class="mt-3 text-muted">Loading groups...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          {{ error }}
          <button class="btn btn-outline-danger btn-sm ms-3" @click="fetchGroups">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Retry
          </button>
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
                  </div>
                  <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i :class="stat.icon" class="text-primary"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Groups Table -->
        <div class="card">
          <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">All Groups</h5>
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm" v-model="statusFilter" style="width: auto;">
                <option value="">All Groups</option>
                <option value="my-groups">My Groups</option>
                <option value="member">Member</option>
              </select>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Group Name</th>
                    <th>Members</th>
                    <th>Messages</th>
                    <th>Created By</th>
                    <th>Created Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="group in filteredGroups" :key="group.id">
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                          <i class="bi bi-people-fill text-primary"></i>
                        </div>
                        <div>
                          <h6 class="mb-0">{{ group.name }}</h6>
                          <small class="text-muted">{{ group.description || 'No description' }}</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="member-avatars me-2">
                          <span v-for="member in group.members.slice(0, 3)" :key="member.id" class="avatar">
                            {{ member.name.charAt(0).toUpperCase() }}
                          </span>
                        </div>
                        <span class="badge bg-secondary">
                          {{ group.members.length }} members
                        </span>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-info">
                        {{ group.messages_count || 0 }} messages
                      </span>
                    </td>
                    <td>
                      <small class="text-muted">{{ group.creator?.name }}</small>
                    </td>
                    <td>
                      <small class="text-muted">{{ formatDate(group.created_at) }}</small>
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary btn-sm" @click="viewGroup(group)">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button v-if="group.created_by === currentUser.id" class="btn btn-outline-secondary btn-sm" @click="editGroup(group)">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-success btn-sm" @click="addMembers(group)">
                          <i class="bi bi-person-plus"></i>
                        </button>
                        <button v-if="group.created_by === currentUser.id" class="btn btn-outline-danger btn-sm" @click="deleteGroup(group)">
                          <i class="bi bi-trash"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Create Group Modal -->
    <div class="modal fade" :class="{ show: showCreateModal }" :style="{ display: showCreateModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Create New Group</h5>
            <button type="button" class="btn-close" @click="closeCreateModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="createGroup">
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-3">
                    <label class="form-label">Group Name</label>
                    <input type="text" class="form-control" v-model="newGroup.name" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Group Type</label>
                    <select class="form-select" v-model="newGroup.type">
                      <option value="project">Project Team</option>
                      <option value="study">Study Group</option>
                      <option value="general">General Discussion</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" rows="3" v-model="newGroup.description" required></textarea>
              </div>

              <div class="mb-3">
                <label class="form-label">Add Members</label>
                <div class="member-selection">
                  <div v-for="user in availableUsers" :key="user.id" class="form-check">
                    <input 
                      class="form-check-input" 
                      type="checkbox" 
                      :value="user.id" 
                      :id="'user-' + user.id"
                      v-model="newGroup.members"
                    >
                    <label class="form-check-label" :for="'user-' + user.id">
                      {{ user.name }} ({{ user.role }})
                    </label>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeCreateModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="createGroup" :disabled="creating">
              <span v-if="creating" class="spinner-border spinner-border-sm me-2"></span>
              {{ creating ? 'Creating...' : 'Create Group' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Members Modal -->
    <div class="modal fade" :class="{ show: showAddMembersModal }" :style="{ display: showAddMembersModal ? 'block' : 'none' }" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Add Members to {{ selectedGroup?.name }}</h5>
            <button type="button" class="btn-close" @click="closeAddMembersModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Select Members</label>
              <div class="member-selection">
                <div v-for="user in availableUsersForGroup" :key="user.id" class="form-check">
                  <input 
                    class="form-check-input" 
                    type="checkbox" 
                    :value="user.id" 
                    :id="'add-user-' + user.id"
                    v-model="selectedMembers"
                    :disabled="selectedGroup?.members.some(m => m.id === user.id)"
                  >
                  <label class="form-check-label" :for="'add-user-' + user.id">
                    {{ user.name }} ({{ user.role }})
                    <span v-if="selectedGroup?.members.some(m => m.id === user.id)" class="badge bg-secondary ms-2">Already member</span>
                  </label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeAddMembersModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="addMembersToGroup" :disabled="addingMembers">
              <span v-if="addingMembers" class="spinner-border spinner-border-sm me-2"></span>
              {{ addingMembers ? 'Adding...' : 'Add Members' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Backdrop -->
    <div v-if="showCreateModal || showAddMembersModal" class="modal-backdrop fade show"></div>
  </div>
</template>

<script>
import SupervisorSidebar from './SupervisorSidebar.vue';
import axios from 'axios';

export default {
  name: 'GroupManagement',
  components: {
    SupervisorSidebar
  },
  data() {
    return {
      currentUser: {},
      groups: [],
      availableUsers: [],
      availableUsersForGroup: [],
      loading: false,
      creating: false,
      addingMembers: false,
      error: null,
      searchQuery: '',
      statusFilter: '',
      showCreateModal: false,
      showAddMembersModal: false,
      selectedGroup: null,
      selectedMembers: [],
      newGroup: {
        name: '',
        description: '',
        type: 'project',
        members: []
      },
      stats: [
        { label: 'Total Groups', value: 0, icon: 'bi bi-people-fill' },
        { label: 'My Groups', value: 0, icon: 'bi bi-person-check' },
        { label: 'Total Members', value: 0, icon: 'bi bi-person-badge' },
        { label: 'Active Groups', value: 0, icon: 'bi bi-activity' }
      ]
    };
  },
  computed: {
    filteredGroups() {
      let filtered = this.groups;
      
      if (this.searchQuery) {
        filtered = filtered.filter(group => 
          group.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
          group.description?.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
      }
      
      if (this.statusFilter === 'my-groups') {
        filtered = filtered.filter(group => group.created_by === this.currentUser.id);
      }
      
      return filtered;
    }
  },
  methods: {
    async fetchCurrentUser() {
      try {
        const response = await axios.get('/api/user');
        this.currentUser = response.data;
      } catch (error) {
        console.error('Error fetching current user:', error);
      }
    },
    
    async fetchGroups() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get('/api/groups');
        this.groups = response.data.data || [];
        this.updateStats();
      } catch (error) {
        console.error('Error fetching groups:', error);
        this.error = 'Failed to load groups. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    async fetchAvailableUsers() {
      try {
        const response = await axios.get('/api/available-users');
        this.availableUsers = response.data.data || [];
      } catch (error) {
        console.error('Error fetching available users:', error);
      }
    },
    
    async createGroup() {
      if (!this.newGroup.name.trim() || !this.newGroup.description.trim()) {
        return;
      }
      
      this.creating = true;
      
      try {
        const response = await axios.post('/api/groups', {
          name: this.newGroup.name,
          description: this.newGroup.description,
          members: this.newGroup.members
        });
        
        this.groups.unshift(response.data.data);
        this.updateStats();
        this.closeCreateModal();
        this.showToast('Group created successfully!', 'success');
      } catch (error) {
        console.error('Error creating group:', error);
        this.showToast('Failed to create group. Please try again.', 'error');
      } finally {
        this.creating = false;
      }
    },
    
    async addMembers(group) {
      this.selectedGroup = group;
      this.selectedMembers = [];
      this.availableUsersForGroup = this.availableUsers.filter(user => 
        !group.members.some(member => member.id === user.id)
      );
      this.showAddMembersModal = true;
    },
    
    async addMembersToGroup() {
      if (this.selectedMembers.length === 0) {
        return;
      }
      
      this.addingMembers = true;
      
      try {
        const response = await axios.post(`/api/groups/${this.selectedGroup.id}/members`, {
          members: this.selectedMembers
        });
        
        // Update the group in the list
        const index = this.groups.findIndex(g => g.id === this.selectedGroup.id);
        if (index !== -1) {
          this.groups[index] = response.data.data;
        }
        
        this.closeAddMembersModal();
        this.showToast('Members added successfully!', 'success');
      } catch (error) {
        console.error('Error adding members:', error);
        this.showToast('Failed to add members. Please try again.', 'error');
      } finally {
        this.addingMembers = false;
      }
    },
    
    viewGroup(group) {
      // Navigate to group chat
      this.$router.push({ name: 'messaging', query: { group: group.id } });
    },
    
    editGroup(group) {
      // TODO: Implement edit group functionality
      this.showToast('Edit group functionality coming soon!', 'info');
    },
    
    async deleteGroup(group) {
      if (!confirm(`Are you sure you want to delete the group "${group.name}"?`)) {
        return;
      }
      
      try {
        await axios.delete(`/api/groups/${group.id}`);
        
        const index = this.groups.findIndex(g => g.id === group.id);
        if (index !== -1) {
          this.groups.splice(index, 1);
        }
        
        this.updateStats();
        this.showToast('Group deleted successfully!', 'success');
      } catch (error) {
        console.error('Error deleting group:', error);
        this.showToast('Failed to delete group. Please try again.', 'error');
      }
    },
    
    updateStats() {
      this.stats[0].value = this.groups.length;
      this.stats[1].value = this.groups.filter(g => g.created_by === this.currentUser.id).length;
      this.stats[2].value = this.groups.reduce((total, group) => total + group.members.length, 0);
      this.stats[3].value = this.groups.filter(g => g.messages_count > 0).length;
    },
    
    closeCreateModal() {
      this.showCreateModal = false;
      this.newGroup = {
        name: '',
        description: '',
        type: 'project',
        members: []
      };
    },
    
    closeAddMembersModal() {
      this.showAddMembersModal = false;
      this.selectedGroup = null;
      this.selectedMembers = [];
    },
    
    toggleSidebar() {
      const sidebar = document.querySelector('.sidebar');
      if (sidebar) {
        sidebar.classList.toggle('show');
      }
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString();
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
  },
  
  async mounted() {
    await this.fetchCurrentUser();
    await this.fetchGroups();
    await this.fetchAvailableUsers();
  }
};
</script>

<style scoped>
.dashboard-container {
  display: flex;
  min-height: 100vh;
  background-color: #f8f9fa;
}

.main-content {
  flex: 1;
  padding: 1.5rem;
  margin-left: 20px;
  transition: margin-left 0.3s ease;
}

.member-avatars {
  display: flex;
  align-items: center;
}

.avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-color: var(--bs-primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
  margin-right: -5px;
  border: 2px solid white;
}

.member-selection {
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
  padding: 1rem;
}

.form-check {
  margin-bottom: 0.5rem;
}

.hover-lift {
  transition: transform 0.2s ease-in-out;
}

.hover-lift:hover {
  transform: translateY(-2px);
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

/* Ensure proper z-index for modals */
:deep(.modal) {
  z-index: 1050;
}

:deep(.modal-backdrop) {
  z-index: 1040;
}
</style>
