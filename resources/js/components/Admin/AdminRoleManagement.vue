<template>
  <div class="admin-role-management">
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
                <i class="bi bi-person-badge me-2"></i>Role Management
              </h4>
              <p class="text-muted mb-0">Manage user roles and permissions</p>
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

      <!-- Main Content -->
      <div class="container-fluid py-4">
        <!-- Role Overview -->
        <div class="row mb-4">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="bi bi-shield-check me-2"></i>Role Overview
                </h5>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4 mb-3">
                    <div class="role-card bg-primary text-white p-3 rounded">
                      <h6 class="mb-2">Admin</h6>
                      <p class="mb-1">Full system access and control</p>
                      <small>Users: {{ roleStats.admin }}</small>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <div class="role-card bg-success text-white p-3 rounded">
                      <h6 class="mb-2">Supervisor</h6>
                      <p class="mb-1">Project and student management</p>
                      <small>Users: {{ roleStats.supervisor }}</small>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3">
                    <div class="role-card bg-info text-white p-3 rounded">
                      <h6 class="mb-2">Student</h6>
                      <p class="mb-1">Project participation and task completion</p>
                      <small>Users: {{ roleStats.student }}</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Role Permissions -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h5 class="card-title mb-0">
                  <i class="bi bi-key me-2"></i>Role Permissions
                </h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Permission</th>
                        <th class="text-center">Admin</th>
                        <th class="text-center">Supervisor</th>
                        <th class="text-center">Student</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="permission in permissions" :key="permission.name">
                        <td>{{ permission.name }}</td>
                        <td class="text-center">
                          <i v-if="permission.admin" class="bi bi-check-circle-fill text-success"></i>
                          <i v-else class="bi bi-x-circle-fill text-muted"></i>
                        </td>
                        <td class="text-center">
                          <i v-if="permission.supervisor" class="bi bi-check-circle-fill text-success"></i>
                          <i v-else class="bi bi-x-circle-fill text-muted"></i>
                        </td>
                        <td class="text-center">
                          <i v-if="permission.student" class="bi bi-check-circle-fill text-success"></i>
                          <i v-else class="bi bi-x-circle-fill text-muted"></i>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import AdminSidebar from './AdminSidebar.vue';
import NotificationBell from '../NotificationBell.vue';
import { auth } from '../../utils/auth';

export default {
  name: 'AdminRoleManagement',
  components: {
    AdminSidebar,
    NotificationBell
  },
  data() {
    return {
      currentUser: null,
      roleStats: {
        admin: 0,
        supervisor: 0,
        student: 0
      },
      permissions: [
        {
          name: 'User Management',
          admin: true,
          supervisor: false,
          student: false
        },
        {
          name: 'Project Creation',
          admin: true,
          supervisor: true,
          student: false
        },
        {
          name: 'Task Assignment',
          admin: true,
          supervisor: true,
          student: false
        },
        {
          name: 'File Management',
          admin: true,
          supervisor: true,
          student: true
        },
        {
          name: 'Messaging',
          admin: true,
          supervisor: true,
          student: true
        },
        {
          name: 'System Settings',
          admin: true,
          supervisor: false,
          student: false
        },
        {
          name: 'Reports & Analytics',
          admin: true,
          supervisor: true,
          student: false
        }
      ]
    };
  },
  mounted() {
    this.currentUser = auth.getUser();
    this.fetchRoleStats();
  },
  methods: {
    async fetchRoleStats() {
      try {
        const response = await fetch('/api/admin/role-stats', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          this.roleStats = data;
        }
      } catch (error) {
        console.error('Error fetching role stats:', error);
      }
    },
    
    logout() {
      auth.logout();
      this.$router.push('/login');
    }
  }
};
</script>

<style scoped>
.admin-role-management {
  display: flex;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  background-color: #f8f9fa;
  margin-left: 280px;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 0;
  }
}

.role-card {
  transition: transform 0.2s;
}

.role-card:hover {
  transform: translateY(-2px);
}

.table td {
  vertical-align: middle;
}
</style>
