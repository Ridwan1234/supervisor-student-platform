<template>
  <div class="admin-settings">
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
                <i class="bi bi-gear me-2"></i>System Settings
              </h4>
              <p class="text-muted mb-0">Configure system preferences and options</p>
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
        <!-- Settings Tabs -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                      <i class="bi bi-gear me-1"></i>General
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab">
                      <i class="bi bi-shield-check me-1"></i>Security
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">
                      <i class="bi bi-envelope me-1"></i>Email
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                      <i class="bi bi-download me-1"></i>Backup
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notifications" type="button" role="tab">
                      <i class="bi bi-bell me-1"></i>Notifications
                    </button>
                  </li>
                </ul>
              </div>
              <div class="card-body">
                <div class="tab-content">
                  <!-- General Settings -->
                  <div class="tab-pane fade show active" id="general" role="tabpanel">
                    <form @submit.prevent="saveGeneralSettings">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Site Name</label>
                          <input v-model="settings.general.siteName" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Site URL</label>
                          <input v-model="settings.general.siteUrl" type="url" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Contact Email</label>
                          <input v-model="settings.general.contactEmail" type="email" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Timezone</label>
                          <select v-model="settings.general.timezone" class="form-select">
                            <option value="UTC">UTC</option>
                            <option value="America/New_York">Eastern Time</option>
                            <option value="America/Chicago">Central Time</option>
                            <option value="America/Denver">Mountain Time</option>
                            <option value="America/Los_Angeles">Pacific Time</option>
                          </select>
                        </div>
                        <div class="col-12 mb-3">
                          <label class="form-label">Site Description</label>
                          <textarea v-model="settings.general.description" class="form-control" rows="3"></textarea>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary">Save General Settings</button>
                    </form>
                  </div>

                  <!-- Security Settings -->
                  <div class="tab-pane fade" id="security" role="tabpanel">
                    <form @submit.prevent="saveSecuritySettings">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Session Timeout (minutes)</label>
                          <input v-model="settings.security.sessionTimeout" type="number" class="form-control" min="5" max="1440" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Password Minimum Length</label>
                          <input v-model="settings.security.passwordMinLength" type="number" class="form-control" min="6" max="20" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="form-check">
                            <input v-model="settings.security.requirePasswordComplexity" type="checkbox" class="form-check-input" id="requireComplexity" />
                            <label class="form-check-label" for="requireComplexity">
                              Require Password Complexity
                            </label>
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="form-check">
                            <input v-model="settings.security.enableTwoFactor" type="checkbox" class="form-check-input" id="enableTwoFactor" />
                            <label class="form-check-label" for="enableTwoFactor">
                              Enable Two-Factor Authentication
                            </label>
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="form-check">
                            <input v-model="settings.security.enableLoginAttempts" type="checkbox" class="form-check-input" id="enableLoginAttempts" />
                            <label class="form-check-label" for="enableLoginAttempts">
                              Limit Login Attempts
                            </label>
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Max Login Attempts</label>
                          <input v-model="settings.security.maxLoginAttempts" type="number" class="form-control" min="3" max="10" />
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary">Save Security Settings</button>
                    </form>
                  </div>

                  <!-- Email Settings -->
                  <div class="tab-pane fade" id="email" role="tabpanel">
                    <form @submit.prevent="saveEmailSettings">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">SMTP Host</label>
                          <input v-model="settings.email.smtpHost" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">SMTP Port</label>
                          <input v-model="settings.email.smtpPort" type="number" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">SMTP Username</label>
                          <input v-model="settings.email.smtpUsername" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">SMTP Password</label>
                          <input v-model="settings.email.smtpPassword" type="password" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">From Email</label>
                          <input v-model="settings.email.fromEmail" type="email" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">From Name</label>
                          <input v-model="settings.email.fromName" type="text" class="form-control" />
                        </div>
                        <div class="col-12 mb-3">
                          <div class="form-check">
                            <input v-model="settings.email.enableEncryption" type="checkbox" class="form-check-input" id="enableEncryption" />
                            <label class="form-check-label" for="enableEncryption">
                              Enable SSL/TLS Encryption
                            </label>
                          </div>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary">Save Email Settings</button>
                    </form>
                  </div>

                  <!-- Backup Settings -->
                  <div class="tab-pane fade" id="backup" role="tabpanel">
                    <form @submit.prevent="saveBackupSettings">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Backup Frequency</label>
                          <select v-model="settings.backup.frequency" class="form-select">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                          </select>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Retention Period (days)</label>
                          <input v-model="settings.backup.retentionPeriod" type="number" class="form-control" min="1" max="365" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Backup Storage Path</label>
                          <input v-model="settings.backup.storagePath" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Max Backup Size (MB)</label>
                          <input v-model="settings.backup.maxSize" type="number" class="form-control" min="10" max="10000" />
                        </div>
                        <div class="col-12 mb-3">
                          <div class="form-check">
                            <input v-model="settings.backup.enableCompression" type="checkbox" class="form-check-input" id="enableCompression" />
                            <label class="form-check-label" for="enableCompression">
                              Enable Backup Compression
                            </label>
                          </div>
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary">Save Backup Settings</button>
                    </form>
                  </div>

                  <!-- Notification Settings -->
                  <div class="tab-pane fade" id="notifications" role="tabpanel">
                    <form @submit.prevent="saveNotificationSettings">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <div class="form-check">
                            <input v-model="settings.notifications.emailNotifications" type="checkbox" class="form-check-input" id="emailNotifications" />
                            <label class="form-check-label" for="emailNotifications">
                              Enable Email Notifications
                            </label>
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="form-check">
                            <input v-model="settings.notifications.pushNotifications" type="checkbox" class="form-check-input" id="pushNotifications" />
                            <label class="form-check-label" for="pushNotifications">
                              Enable Push Notifications
                            </label>
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="form-check">
                            <input v-model="settings.notifications.userRegistration" type="checkbox" class="form-check-input" id="userRegistration" />
                            <label class="form-check-label" for="userRegistration">
                              Notify on User Registration
                            </label>
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="form-check">
                            <input v-model="settings.notifications.projectUpdates" type="checkbox" class="form-check-input" id="projectUpdates" />
                            <label class="form-check-label" for="projectUpdates">
                              Notify on Project Updates
                            </label>
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <div class="form-check">
                            <input v-model="settings.notifications.systemAlerts" type="checkbox" class="form-check-input" id="systemAlerts" />
                            <label class="form-check-label" for="systemAlerts">
                              Enable System Alerts
                            </label>
                          </div>
                        </div>
                        <div class="col-md-6 mb-3">
                          <label class="form-label">Alert Email Recipients</label>
                          <input v-model="settings.notifications.alertRecipients" type="text" class="form-control" placeholder="email1@example.com, email2@example.com" />
                        </div>
                      </div>
                      <button type="submit" class="btn btn-primary">Save Notification Settings</button>
                    </form>
                  </div>
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
  name: 'AdminSettings',
  components: {
    AdminSidebar,
    NotificationBell
  },
  data() {
    return {
      currentUser: null,
      settings: {
        general: {
          siteName: 'Supervisor Student Platform',
          siteUrl: 'http://localhost:8000',
          contactEmail: 'admin@example.com',
          timezone: 'UTC',
          description: 'A platform for managing student-supervisor relationships and projects.'
        },
        security: {
          sessionTimeout: 120,
          passwordMinLength: 8,
          requirePasswordComplexity: true,
          enableTwoFactor: false,
          enableLoginAttempts: true,
          maxLoginAttempts: 5
        },
        email: {
          smtpHost: 'smtp.gmail.com',
          smtpPort: 587,
          smtpUsername: '',
          smtpPassword: '',
          fromEmail: 'noreply@example.com',
          fromName: 'System Admin',
          enableEncryption: true
        },
        backup: {
          frequency: 'daily',
          retentionPeriod: 30,
          storagePath: '/storage/backups',
          maxSize: 1000,
          enableCompression: true
        },
        notifications: {
          emailNotifications: true,
          pushNotifications: false,
          userRegistration: true,
          projectUpdates: true,
          systemAlerts: true,
          alertRecipients: ''
        }
      }
    };
  },
  mounted() {
    this.currentUser = auth.getUser();
    this.loadSettings();
  },
  methods: {
    async loadSettings() {
      try {
        const response = await fetch('/api/admin/settings', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          this.settings = { ...this.settings, ...data };
        }
      } catch (error) {
        console.error('Error loading settings:', error);
      }
    },

    async saveGeneralSettings() {
      try {
        const response = await fetch('/api/admin/settings/general', {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.settings.general)
        });

        if (response.ok) {
          alert('General settings saved successfully!');
        } else {
          throw new Error('Failed to save general settings');
        }
      } catch (error) {
        console.error('Error saving general settings:', error);
        alert('Failed to save general settings');
      }
    },

    async saveSecuritySettings() {
      try {
        const response = await fetch('/api/admin/settings/security', {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.settings.security)
        });

        if (response.ok) {
          alert('Security settings saved successfully!');
        } else {
          throw new Error('Failed to save security settings');
        }
      } catch (error) {
        console.error('Error saving security settings:', error);
        alert('Failed to save security settings');
      }
    },

    async saveEmailSettings() {
      try {
        const response = await fetch('/api/admin/settings/email', {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.settings.email)
        });

        if (response.ok) {
          alert('Email settings saved successfully!');
        } else {
          throw new Error('Failed to save email settings');
        }
      } catch (error) {
        console.error('Error saving email settings:', error);
        alert('Failed to save email settings');
      }
    },

    async saveBackupSettings() {
      try {
        const response = await fetch('/api/admin/settings/backup', {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.settings.backup)
        });

        if (response.ok) {
          alert('Backup settings saved successfully!');
        } else {
          throw new Error('Failed to save backup settings');
        }
      } catch (error) {
        console.error('Error saving backup settings:', error);
        alert('Failed to save backup settings');
      }
    },

    async saveNotificationSettings() {
      try {
        const response = await fetch('/api/admin/settings/notifications', {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(this.settings.notifications)
        });

        if (response.ok) {
          alert('Notification settings saved successfully!');
        } else {
          throw new Error('Failed to save notification settings');
        }
      } catch (error) {
        console.error('Error saving notification settings:', error);
        alert('Failed to save notification settings');
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
.admin-settings {
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

.nav-tabs .nav-link {
  color: #6c757d;
}

.nav-tabs .nav-link.active {
  color: #0d6efd;
  font-weight: 500;
}
</style>
