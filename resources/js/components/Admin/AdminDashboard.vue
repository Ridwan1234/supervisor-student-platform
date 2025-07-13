<template>
  <div class="container py-4">
    <div class="mb-4">
      <h1 class="display-5 fw-bold text-primary mb-2">
        <i class="bi bi-shield-lock me-2"></i> Admin Dashboard
      </h1>
      <p class="text-muted">Welcome, Admin! Manage users, roles, and platform settings here.</p>
    </div>
    <div class="row">
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body d-flex flex-column align-items-center justify-content-center">
            <i class="bi bi-people fs-1 text-primary mb-3"></i>
            <h5 class="card-title">User Management</h5>
            <p class="card-text text-muted">View, search, and manage all users.</p>
            <router-link to="/admin/users" class="btn btn-outline-primary btn-sm mt-2">Manage Users</router-link>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body d-flex flex-column align-items-center justify-content-center">
            <i class="bi bi-person-badge fs-1 text-success mb-3"></i>
            <h5 class="card-title">Role Management</h5>
            <p class="card-text text-muted">Assign and update user roles.</p>
            <router-link to="/admin/roles" class="btn btn-outline-success btn-sm mt-2">Manage Roles</router-link>
          </div>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm">
          <div class="card-body d-flex flex-column align-items-center justify-content-center">
            <i class="bi bi-gear fs-1 text-secondary mb-3"></i>
            <h5 class="card-title">Platform Settings</h5>
            <p class="card-text text-muted">Configure platform-wide settings.</p>
            <router-link to="/admin/settings" class="btn btn-outline-secondary btn-sm mt-2">Settings</router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminDashboard',
};
</script>

<style scoped>
.card {
  border: 1px solid rgba(0,0,0,0.08);
  border-radius: 0.5rem;
}
</style> 