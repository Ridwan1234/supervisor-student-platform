<template>
  <div class="admin-user-management">
    <!-- Sidebar -->
    <AdminSidebar />
    
    <!-- Main Content -->
    <div class="main-content">
      <!-- Header -->
      <div class="header bg-white shadow-sm border-bottom">
        <div class="container-fluid">
          <div class="d-flex justify-content-between align-items-center py-3">
            <div>
              <h4 class="mb-0 fw-bold text-primary">
                <i class="bi bi-people me-2"></i>User Management
              </h4>
              <p class="text-muted mb-0">View, search, and manage all users on the platform.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
              <NotificationBell />
              <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                  <i class="bi bi-person-circle me-1"></i>
                  {{ currentUser?.name || 'Admin' }}
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#" @click="logout">Logout</a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>

              <div class="mb-4 d-flex justify-content-between align-items-center">
          <div>
            <h2 class="fw-bold mb-1">
              <i class="bi bi-people me-2"></i> User Management
            </h2>
            <p class="text-muted mb-0">View, search, and manage all users on the platform.</p>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-primary" @click="showCreateUserModal = true">
              <i class="bi bi-person-plus me-1"></i> Add User
            </button>
          </div>
        </div>
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <input v-model="search" type="text" class="form-control" placeholder="Search by name or email..." @input="debounceSearch" />
          </div>
          <div class="col-md-3">
            <select v-model="roleFilter" class="form-select" @change="fetchUsers">
              <option value="all">All Roles</option>
              <option value="admin">Admin</option>
              <option value="supervisor">Supervisor</option>
              <option value="student">Student</option>
            </select>
          </div>
          <div class="col-md-3 text-end">
            <button class="btn btn-primary" @click="fetchUsers" :disabled="loading">
              <i class="bi bi-search me-1"></i> Search
            </button>
          </div>
        </div>
        
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2 text-muted">Loading users...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          {{ error }}
        </div>

        <!-- Users Table -->
        <div v-else class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(user, idx) in users" :key="user.id">
                <td>{{ (currentPage - 1) * perPage + idx + 1 }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="badge" :class="roleBadgeClass(user.role)">{{ user.role }}</span>
                </td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td>
                  <button class="btn btn-outline-secondary btn-sm me-1" title="View" @click="viewUser(user)">
                    <i class="bi bi-eye"></i>
                  </button>
                  <button class="btn btn-outline-primary btn-sm me-1" title="Edit" @click="editUser(user)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm" title="Deactivate" @click="deactivateUser(user)">
                    <i class="bi bi-x-circle"></i>
                  </button>
                </td>
              </tr>
              <tr v-if="users.length === 0">
                <td colspan="6" class="text-center text-muted">No users found.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <nav v-if="totalPages > 1" aria-label="User pagination">
          <ul class="pagination justify-content-center">
            <li class="page-item" :class="{ disabled: currentPage === 1 }">
              <a class="page-link" href="#" @click.prevent="changePage(currentPage - 1)">Previous</a>
            </li>
            <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === currentPage }">
              <a class="page-link" href="#" @click.prevent="changePage(page)">{{ page }}</a>
            </li>
            <li class="page-item" :class="{ disabled: currentPage === totalPages }">
              <a class="page-link" href="#" @click.prevent="changePage(currentPage + 1)">Next</a>
            </li>
          </ul>
        </nav>

        <!-- Pagination Info -->
        <div v-if="total > 0" class="text-center text-muted mt-3">
          Showing {{ (currentPage - 1) * perPage + 1 }} to {{ Math.min(currentPage * perPage, total) }} of {{ total }} users
        </div>
              </div>
      </div>

      <!-- Create User Modal -->
      <AdminCreateUser 
        :visible="showCreateUserModal"
        @close="showCreateUserModal = false"
        @user-created="onUserCreated"
      />

      <!-- User Detail Modal -->
      <AdminUserDetailModal
        :visible="showUserDetailModal"
        :user-id="selectedUserId"
        @close="showUserDetailModal = false"
        @edit-user="editUser"
      />

      <!-- User Edit Modal -->
      <AdminUserEditModal
        :visible="showUserEditModal"
        :user="selectedUser"
        @close="showUserEditModal = false"
        @user-updated="onUserUpdated"
      />
    </div>
  </div>
</template>

<script>
import AdminCreateUser from './AdminCreateUser.vue';
import AdminUserDetailModal from './AdminUserDetailModal.vue';
import AdminUserEditModal from './AdminUserEditModal.vue';
import AdminSidebar from './AdminSidebar.vue';
import NotificationBell from '../NotificationBell.vue';
import { auth } from '../../utils/auth';
import { apiGet, apiPatch, handleApiError } from '../../utils/api';

export default {
  name: 'AdminUserManagement',
  components: {
    AdminCreateUser,
    AdminUserDetailModal,
    AdminUserEditModal,
    AdminSidebar,
    NotificationBell
  },
  data() {
    return {
      currentUser: null,
      search: '',
      roleFilter: 'all',
      users: [],
      loading: false,
      error: null,
      currentPage: 1,
      perPage: 15,
      total: 0,
      totalPages: 0,
      searchTimeout: null,
      showCreateUserModal: false,
      showUserDetailModal: false,
      showUserEditModal: false,
      selectedUserId: null,
      selectedUser: null
    };
  },
  computed: {
    visiblePages() {
      const pages = [];
      const start = Math.max(1, this.currentPage - 2);
      const end = Math.min(this.totalPages, this.currentPage + 2);
      
      for (let i = start; i <= end; i++) {
        pages.push(i);
      }
      return pages;
    }
  },
  mounted() {
    this.currentUser = auth.getUser();
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      this.loading = true;
      this.error = null;
      
      try {
        const params = new URLSearchParams({
          page: this.currentPage,
          per_page: this.perPage
        });
        
        if (this.search) {
          params.append('search', this.search);
        }
        
        if (this.roleFilter && this.roleFilter !== 'all') {
          params.append('role', this.roleFilter);
        }

        const data = await apiGet(`/api/admin/users?${params}`);
        this.users = data.data;
        this.currentPage = data.current_page;
        this.perPage = data.per_page;
        this.total = data.total;
        this.totalPages = data.last_page;
      } catch (error) {
        console.error('Error fetching users:', error);
        this.error = handleApiError(error, 'fetching users');
      } finally {
        this.loading = false;
      }
    },
    
    debounceSearch() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.currentPage = 1;
        this.fetchUsers();
      }, 500);
    },
    
    changePage(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.currentPage = page;
        this.fetchUsers();
      }
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString();
    },
    
    roleBadgeClass(role) {
      if (role === 'admin') return 'bg-dark text-white';
      if (role === 'supervisor') return 'bg-primary';
      if (role === 'student') return 'bg-success';
      return 'bg-secondary';
    },
    
    viewUser(user) {
      this.selectedUserId = user.id;
      this.showUserDetailModal = true;
    },
    
    editUser(user) {
      this.selectedUser = user;
      this.showUserEditModal = true;
    },
    
    async deactivateUser(user) {
      if (!confirm(`Are you sure you want to ${user.status === 'active' ? 'deactivate' : 'activate'} ${user.name}?`)) {
        return;
      }
      
      try {
        await apiPatch(`/api/admin/users/${user.id}/toggle-status`, {});
        // Refresh the user list
        this.fetchUsers();
        alert(`User ${user.name} has been ${user.status === 'active' ? 'deactivated' : 'activated'} successfully.`);
      } catch (error) {
        console.error('Error updating user status:', error);
        alert(handleApiError(error, 'updating user status'));
      }
    },

    onUserUpdated() {
      this.showUserEditModal = false;
      this.fetchUsers(); // Refresh the user list
      alert('User updated successfully!');
    },

    logout() {
      auth.logout();
      this.$router.push('/login');
    }
  },
  
  onUserCreated() {
    this.showCreateUserModal = false;
    this.fetchUsers(); // Refresh the user list
  }
};
</script>

<style scoped>
.admin-user-management {
  display: flex;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  background-color: #f8f9fa;
  margin-left: 280px;
}

.table td, .table th {
  vertical-align: middle;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
}
</style> 