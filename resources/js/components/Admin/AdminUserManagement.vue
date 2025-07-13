<template>
  <div class="container py-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold mb-1">
          <i class="bi bi-people me-2"></i> User Management
        </h2>
        <p class="text-muted mb-0">View, search, and manage all users on the platform.</p>
      </div>
      <router-link to="/admin-dashboard" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Admin Dashboard
      </router-link>
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
  </div>
</template>

<script>
export default {
  name: 'AdminUserManagement',
  data() {
    return {
      search: '',
      roleFilter: 'all',
      users: [],
      loading: false,
      error: null,
      currentPage: 1,
      perPage: 15,
      total: 0,
      totalPages: 0,
      searchTimeout: null
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

        const response = await fetch(`/api/admin/users?${params}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          if (response.status === 403) {
            throw new Error('Access denied. Admin privileges required.');
          }
          throw new Error('Failed to fetch users');
        }

        const data = await response.json();
        this.users = data.data;
        this.currentPage = data.current_page;
        this.perPage = data.per_page;
        this.total = data.total;
        this.totalPages = data.last_page;
      } catch (error) {
        console.error('Error fetching users:', error);
        this.error = error.message;
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
      // TODO: Implement user detail view
      console.log('View user:', user);
    },
    
    editUser(user) {
      // TODO: Implement user edit
      console.log('Edit user:', user);
    },
    
    deactivateUser(user) {
      // TODO: Implement user deactivation
      console.log('Deactivate user:', user);
    }
  }
};
</script>

<style scoped>
.table td, .table th {
  vertical-align: middle;
}
</style> 