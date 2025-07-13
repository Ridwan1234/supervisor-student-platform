<template>
  <div class="admin-sidebar">
    <div class="sidebar-header">
      <div class="d-flex align-items-center">
        <i class="bi bi-shield-check text-primary fs-4 me-2"></i>
        <h5 class="mb-0 fw-bold">Admin Panel</h5>
      </div>
    </div>

    <nav class="sidebar-nav">
      <ul class="nav flex-column">
        <li class="nav-item">
          <router-link to="/admin-dashboard" class="nav-link" active-class="active">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard
          </router-link>
        </li>
        
        <li class="nav-item">
          <router-link to="/admin/users" class="nav-link" active-class="active">
            <i class="bi bi-people me-2"></i>
            User Management
          </router-link>
        </li>
        
        <li class="nav-item">
          <router-link to="/admin/roles" class="nav-link" active-class="active">
            <i class="bi bi-person-badge me-2"></i>
            Role Management
          </router-link>
        </li>
        
        <li class="nav-item">
          <router-link to="/admin/projects" class="nav-link" active-class="active">
            <i class="bi bi-folder me-2"></i>
            Project Overview
          </router-link>
        </li>
        
        <li class="nav-item">
          <router-link to="/admin/tasks" class="nav-link" active-class="active">
            <i class="bi bi-list-check me-2"></i>
            Task Overview
          </router-link>
        </li>
        
        <li class="nav-item">
          <router-link to="/admin/files" class="nav-link" active-class="active">
            <i class="bi bi-file-earmark me-2"></i>
            File Management
          </router-link>
        </li>
        
        <li class="nav-item">
          <router-link to="/admin/logs" class="nav-link" active-class="active">
            <i class="bi bi-journal-text me-2"></i>
            System Logs
          </router-link>
        </li>
        
        <li class="nav-item">
          <router-link to="/admin/backups" class="nav-link" active-class="active">
            <i class="bi bi-download me-2"></i>
            Backups
          </router-link>
        </li>
        
        <li class="nav-item">
          <router-link to="/admin/settings" class="nav-link" active-class="active">
            <i class="bi bi-gear me-2"></i>
            System Settings
          </router-link>
        </li>
        
        <li class="nav-item">
          <router-link to="/admin/reports" class="nav-link" active-class="active">
            <i class="bi bi-graph-up me-2"></i>
            Reports
          </router-link>
        </li>
      </ul>
    </nav>

    <div class="sidebar-footer">
      <div class="text-center">
        <small class="text-muted">Admin Panel v1.0</small>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminSidebar'
}
</script>

<style scoped>
.admin-sidebar {
  width: 280px;
  background: white;
  border-right: 1px solid #dee2e6;
  display: flex;
  flex-direction: column;
  height: 100vh;
  position: fixed;
  left: 0;
  top: 0;
  z-index: 1000;
}

.sidebar-header {
  padding: 1.5rem;
  border-bottom: 1px solid #dee2e6;
  background: #f8f9fa;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0;
}

.nav-link {
  color: #6c757d;
  padding: 0.75rem 1.5rem;
  border: none;
  background: transparent;
  transition: all 0.2s;
  text-decoration: none;
  display: flex;
  align-items: center;
}

.nav-link:hover {
  color: #0d6efd;
  background-color: #f8f9fa;
  text-decoration: none;
}

.nav-link.active {
  color: #0d6efd;
  background-color: #e3f2fd;
  border-right: 3px solid #0d6efd;
  font-weight: 500;
}

.nav-link i {
  width: 20px;
  text-align: center;
}

.sidebar-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid #dee2e6;
  background: #f8f9fa;
}

@media (max-width: 768px) {
  .admin-sidebar {
    transform: translateX(-100%);
    transition: transform 0.3s ease;
  }
  
  .admin-sidebar.show {
    transform: translateX(0);
  }
}
</style> 