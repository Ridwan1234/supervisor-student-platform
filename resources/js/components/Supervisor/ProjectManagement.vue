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
            <h4 class="mb-0 fw-bold text-primary">Project Management</h4>
          </div>

          <div class="d-flex align-items-center gap-3">
            <!-- Search Bar -->
            <div class="position-relative">
              <input 
                type="text" 
                class="form-control form-control-sm" 
                placeholder="Search projects..." 
                v-model="searchQuery"
              />
              <i class="bi bi-search position-absolute top-50 end-0 translate-middle-y me-2 text-muted"></i>
            </div>

            <!-- Add New Project Button -->
            <button class="btn btn-primary" @click="openCreateModal">
              <i class="bi bi-plus me-2"></i>New Project
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
          <p class="mt-3 text-muted">Loading projects...</p>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="alert alert-danger" role="alert">
          <i class="bi bi-exclamation-triangle me-2"></i>
          {{ error }}
          <button class="btn btn-outline-danger btn-sm ms-3" @click="fetchProjects">
            <i class="bi bi-arrow-clockwise me-1"></i>
            Retry
          </button>
        </div>

        <!-- Stats Cards -->
        <div v-else class="row mb-4">
          <div class="col-md-4" v-for="(stat, index) in stats" :key="index">
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

        <!-- Projects Table -->
        <div class="card">
          <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold">All Projects</h5>
            <div class="d-flex gap-2">
              <select class="form-select form-select-sm" v-model="statusFilter" style="width: auto;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="on-hold">On Hold</option>
              </select>
              <button class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-funnel"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Project Name</th>
                    <th>Students</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="project in filteredProjects" :key="project.id">
                    <td>
                      <div class="d-flex align-items-center">
                     
                        <div>
                          <h6 class="mb-0">{{ project.title }}</h6>
                          <small class="text-muted">{{ project.description }}</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <div class="align-items-center">
                        <p v-for="student in project.students.slice(0, 3)" :key="student.id">{{ student.name }} </p>
                        <span v-if="project.students.length > 3" class="badge bg-secondary">
                          +{{ project.students.length - 3 }}
                        </span>
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="progress me-2" style="width: 100px; height: 6px;">
                          <div class="progress-bar" :class="getProgressClass(project.progress)" 
                               :style="{ width: project.progress + '%' }"></div>
                        </div>
                        <small>{{ project.progress }}%</small>
                      </div>
                    </td>
                    <td>
                      <span class="badge" :class="getStatusClass(project.status)">
                        {{ project.status }}
                      </span>
                    </td>
                    <td>
                      <small class="text-muted">{{ formatDate(project.dueDate) }}</small>
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        <button class="btn btn-outline-primary btn-sm" @click="viewProject(project)">
                          <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" @click="editProject(project)">
                          <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-outline-danger btn-sm" @click="deleteProject(project)">
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

    <!-- Create Project Modal -->
    <div class="modal fade" id="createProjectModal" tabindex="-1" v-show="showCreateModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Create New Project</h5>
            <button type="button" class="btn-close" @click="closeCreateModal"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="createProject">
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-3">
                    <label class="form-label">Project Name</label>
                    <input type="text" class="form-control" v-model="newProject.name" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" v-model="newProject.status" required>
                      <option value="active">Active</option>
                      <option value="planning">Planning</option>
                      <option value="on-hold">On Hold</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" rows="3" v-model="newProject.description" required></textarea>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" v-model="newProject.startDate" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" class="form-control" v-model="newProject.dueDate" required>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Assign Students</label>
                
                <!-- Current Students (Drag to remove) -->
                <div class="mb-3">
                  <h6 class="text-muted mb-2">Selected Students</h6>
                  <div class="assigned-students-container" 
                       @dragover.prevent 
                       @drop="handleDropCreate($event, 'remove')">
                    <div v-for="student in newProject.students" :key="student.id" 
                         class="student-tag" 
                         draggable="true"
                         @dragstart="handleDragStart($event, student, 'assigned')"
                         @click="removeStudentCreate(student)">
                      <div class="d-flex align-items-center">
                        <img :src="student.avatar || '/images/default-avatar.png'" 
                             :alt="student.name" 
                             class="rounded-circle me-2" 
                             style="width: 25px; height: 25px;">
                        <span>{{ student.name }}</span>
                        <button type="button" class="btn-close btn-close-sm ms-2" 
                                @click.stop="removeStudentCreate(student)"></button>
                      </div>
                    </div>
                    <div v-if="newProject.students.length === 0" 
                         class="text-muted text-center py-3 border-dashed">
                      <i class="bi bi-people me-2"></i>
                      No students selected
                    </div>
                  </div>
                </div>

                <!-- Available Students (Drag to add) -->
                <div class="mb-3">
                  <h6 class="text-muted mb-2">Available Students</h6>
                  <div class="available-students-container" 
                       @dragover.prevent 
                       @drop="handleDropCreate($event, 'add')"
                       @dragenter="handleDragEnterCreate($event, 'add')"
                       @dragleave="handleDragLeaveCreate($event, 'add')">
                    <div v-for="student in availableStudents.filter(s => !newProject.students.find(assigned => assigned.id === s.id))" 
                         :key="student.id" 
                         class="student-tag available" 
                         draggable="true"
                         @dragstart="handleDragStart($event, student, 'available')"
                         @click="addStudentCreate(student)">
                      <div class="d-flex align-items-center">
                        <img :src="student.avatar || '/images/default-avatar.png'" 
                             :alt="student.name" 
                             class="rounded-circle me-2" 
                             style="width: 25px; height: 25px;">
                        <span>{{ student.name }}</span>
                        <small class="text-muted ms-2">{{ student.email }}</small>
                      </div>
                    </div>
                    <div v-if="availableStudents.filter(s => !newProject.students.find(assigned => assigned.id === s.id)).length === 0" 
                         class="text-muted text-center py-3 border-dashed">
                      <i class="bi bi-check-circle me-2"></i>
                      All students are selected
                    </div>
                  </div>
                </div>

                <!-- Quick Add Multi-Select -->
                <div class="mb-3">
                  <label class="form-label">Quick Add Multiple Students</label>
                  <div class="position-relative">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Search students..." 
                           v-model="newStudentSearch"
                           @focus="showNewStudentDropdown = true"
                           @blur="setTimeout(() => showNewStudentDropdown = false, 200)">
                    
                    <!-- Dropdown for quick selection -->
                    <div v-if="showNewStudentDropdown && filteredNewAvailableStudents.length > 0" 
                         class="position-absolute w-100 bg-white border rounded shadow-sm" 
                         style="top: 100%; z-index: 1000; max-height: 200px; overflow-y: auto;">
                      <div v-for="student in filteredNewAvailableStudents" 
                           :key="student.id" 
                           class="dropdown-item py-2 px-3 cursor-pointer"
                           @click="addStudentCreate(student)">
                        <div class="d-flex align-items-center">
                          <img :src="student.avatar || '/images/default-avatar.png'" 
                               :alt="student.name" 
                               class="rounded-circle me-2" 
                               style="width: 25px; height: 25px;">
                          <div>
                            <div class="fw-medium">{{ student.name }}</div>
                            <small class="text-muted">{{ student.email }}</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeCreateModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="createProject">Create Project</button>
          </div>
        </div>
      </div>
    </div>

    <!-- View Project Modal -->
    <div class="modal fade" id="viewProjectModal" tabindex="-1" v-show="showViewModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Project Details</h5>
            <button type="button" class="btn-close" @click="closeViewModal"></button>
          </div>
          <div class="modal-body" v-if="selectedProject">
            <div class="row">
              <div class="col-md-8">
                <h4>{{ selectedProject.title }}</h4>
                <p class="text-muted">{{ selectedProject.description }}</p>
              </div>
              <div class="col-md-4">
                <span class="badge" :class="getStatusClass(selectedProject.status)">
                  {{ selectedProject.status }}
                </span>
              </div>
            </div>
            
            <div class="row mt-3">
              <div class="col-md-6">
                <h6>Progress</h6>
                <div class="progress mb-2">
                  <div class="progress-bar" :class="getProgressClass(selectedProject.progress)" 
                       :style="{ width: selectedProject.progress + '%' }"></div>
                </div>
                <small>{{ selectedProject.progress }}% Complete</small>
              </div>
              <div class="col-md-6">
                <h6>Timeline</h6>
                <p><strong>Start:</strong> {{ formatDate(selectedProject.startDate) }}</p>
                <p><strong>Due:</strong> {{ formatDate(selectedProject.dueDate) }}</p>
              </div>
            </div>
            
            <div class="mt-3">
              <h6>Assigned Students</h6>
              <div class="d-flex flex-wrap gap-2">
                <div v-for="student in selectedProject.students" :key="student.id" class="d-flex align-items-center bg-light rounded p-2">
                  <span>{{ student.name }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeViewModal">Close</button>
            <button type="button" class="btn btn-primary" @click="editProject(selectedProject)">Edit Project</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Project Modal -->
    <div class="modal fade" id="editProjectModal" tabindex="-1" v-show="showEditModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Edit Project</h5>
            <button type="button" class="btn-close" @click="closeEditModal"></button>
          </div>
          <div class="modal-body" v-if="editingProject">
            <form @submit.prevent="updateProject">
              <div class="row">
                <div class="col-md-8">
                  <div class="mb-3">
                    <label class="form-label">Project Name</label>
                    <input type="text" class="form-control" v-model="editingProject.name" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" v-model="editingProject.status" required>
                      <option value="not_started">Not Started</option>
                      <option value="in_progress">In Progress</option>
                      <option value="completed">Completed</option>
                      <option value="on_hold">On Hold</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" rows="3" v-model="editingProject.description" required></textarea>
              </div>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" v-model="editingProject.startDate" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-3">
                    <label class="form-label">Due Date</label>
                    <input type="date" class="form-control" v-model="editingProject.dueDate" required>
                  </div>
                </div>
              </div>
              
              <div class="mb-3">
                <label class="form-label">Assign Students</label>
                
                <!-- Current Students (Drag to remove) -->
                <div class="mb-3">
                  <h6 class="text-muted mb-2">Current Students</h6>
                  <div class="assigned-students-container" 
                       @dragover.prevent 
                       @drop="handleDrop($event, 'remove')"
                       @dragenter="handleDragEnter($event, 'remove')"
                       @dragleave="handleDragLeave($event, 'remove')">
                    <div v-for="student in editingProject.students" :key="student.id" 
                         class="student-tag" 
                         draggable="true"
                         @dragstart="handleDragStart($event, student, 'assigned')"
                         @click="removeStudent(student)">
                      <div class="d-flex align-items-center">
                        <span>{{ student.name }}</span>
                        <button type="button" class="btn-close btn-close-sm ms-2" @click.stop="removeStudent(student)"></button>
                      </div>
                    </div>
                    <div v-if="editingProject.students.length === 0" 
                         class="text-muted text-center py-3 border-dashed">
                      <i class="bi bi-people me-2"></i>
                      No students assigned
                    </div>
                  </div>
                </div>

                <!-- Available Students (Drag to add) -->
                <div class="mb-3">
                  <h6 class="text-muted mb-2">Available Students</h6>
                  <div class="available-students-container" 
                       @dragover.prevent 
                       @drop="handleDrop($event, 'add')"
                       @dragenter="handleDragEnter($event, 'add')"
                       @dragleave="handleDragLeave($event, 'add')">
                    <div v-for="student in availableStudents.filter(s => !editingProject.students.find(assigned => assigned.id === s.id))" 
                         :key="student.id" 
                         class="student-tag available" 
                         draggable="true"
                         @dragstart="handleDragStart($event, student, 'available')"
                         @click="addStudent(student)">
                      <div class="d-flex align-items-center">
                        <img :src="student.avatar || '/images/default-avatar.png'" 
                             :alt="student.name" 
                             class="rounded-circle me-2" 
                             style="width: 25px; height: 25px;">
                        <span>{{ student.name }}</span>
                        <small class="text-muted ms-2">{{ student.email }}</small>
                      </div>
                    </div>
                    <div v-if="availableStudents.filter(s => !editingProject.students.find(assigned => assigned.id === s.id)).length === 0" 
                         class="text-muted text-center py-3 border-dashed">
                      <i class="bi bi-check-circle me-2"></i>
                      All students are assigned
                    </div>
                  </div>
                </div>

                <!-- Quick Add Multi-Select -->
                <div class="mb-3">
                  <label class="form-label">Quick Add Multiple Students</label>
                  <div class="position-relative">
                    <input type="text" 
                           class="form-control" 
                           placeholder="Search students..." 
                           v-model="studentSearch"
                           @focus="showStudentDropdown = true"
                           @blur="setTimeout(() => showStudentDropdown = false, 200)">
                    
                    <!-- Dropdown for quick selection -->
                    <div v-if="showStudentDropdown && filteredAvailableStudents.length > 0" 
                         class="position-absolute w-100 bg-white border rounded shadow-sm" 
                         style="top: 100%; z-index: 1000; max-height: 200px; overflow-y: auto;">
                      <div v-for="student in filteredAvailableStudents" 
                           :key="student.id" 
                           class="dropdown-item py-2 px-3 cursor-pointer"
                           @click="addStudent(student)">
                        <div class="d-flex align-items-center">
                          <img :src="student.avatar || '/images/default-avatar.png'" 
                               :alt="student.name" 
                               class="rounded-circle me-2" 
                               style="width: 25px; height: 25px;">
                          <div>
                            <div class="fw-medium">{{ student.name }}</div>
                            <small class="text-muted">{{ student.email }}</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeEditModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="updateProject">Update Project</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import SupervisorSidebar from './SupervisorSidebar.vue';

export default {
  name: 'ProjectManagement',
  components: {
    SupervisorSidebar
  },
  data() {
    return {
      searchQuery: '',
      statusFilter: '',
      showCreateModal: false,
      showViewModal: false,
      showEditModal: false,
      projects: [],
      availableStudents: [],
      selectedProject: null,
      editingProject: null,
      loading: true,
      error: null,
      stats: [
        { label: 'Total Projects', value: 0, change: 12, icon: 'bi bi-folder' },
        { label: 'Active Projects', value: 0, change: 8, icon: 'bi bi-play-circle' },
        { label: 'Completed', value: 0, change: 15, icon: 'bi bi-check-circle' },
      ],
      newProject: {
        name: '',
        description: '',
        status: 'active',
        startDate: '',
        dueDate: '',
        students: []
      },
      studentSearch: '',
      showStudentDropdown: false,
      filteredAvailableStudents: [],
      newStudentSearch: '',
      showNewStudentDropdown: false,
      filteredNewAvailableStudents: []
    };
  },
  computed: {
    filteredProjects() {
      return this.projects.filter(project => {
        const matchesSearch = project.title.toLowerCase().includes(this.searchQuery.toLowerCase());
        const matchesStatus = !this.statusFilter || project.status === this.statusFilter;
        return matchesSearch && matchesStatus;
      });
    },
    filteredAvailableStudents() {
      return this.availableStudents.filter(student => 
        student.name.toLowerCase().includes(this.studentSearch.toLowerCase()) ||
        student.email.toLowerCase().includes(this.studentSearch.toLowerCase())
      );
    },
    filteredNewAvailableStudents() {
      return this.availableStudents.filter(student => 
        student.name.toLowerCase().includes(this.newStudentSearch.toLowerCase()) ||
        student.email.toLowerCase().includes(this.newStudentSearch.toLowerCase())
      );
    }
  },
  async mounted() {
    await this.fetchProjects();
    await this.fetchAvailableStudents();
  },
  methods: {
    async fetchProjects() {
      try {
        this.loading = true;
        this.error = null;
        
        const response = await fetch('/api/supervisor/projects', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (!response.ok) {
          throw new Error('Failed to fetch projects');
        }
        
        const data = await response.json();
        this.projects = data.data;
        
        // Update stats
        this.updateStats();
        
      } catch (error) {
        console.error('Error fetching projects:', error);
        this.error = 'Failed to load projects. Please try again.';
      } finally {
        this.loading = false;
      }
    },
    
    async fetchAvailableStudents() {
      try {
        const response = await fetch('/api/supervisor/available-students', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        });

        if (!response.ok) {
          throw new Error('Failed to fetch available students');
        }

        const data = await response.json();
        this.availableStudents = data.data;
      } catch (error) {
        console.error('Error fetching available students:', error);
        this.showToast('Failed to load available students', 'error');
      }
    },
    
    updateStats() {
      const totalProjects = this.projects.length;
      const activeProjects = this.projects.filter(p => p.status === 'in_progress').length;
      const completedProjects = this.projects.filter(p => p.status === 'completed').length;
      
      this.stats = [
        { label: 'Total Projects', value: totalProjects, change: 12, icon: 'bi bi-folder' },
        { label: 'Active Projects', value: activeProjects, change: 8, icon: 'bi bi-play-circle' },
        { label: 'Completed', value: completedProjects, change: 15, icon: 'bi bi-check-circle' },
      ];
    },
    
    async createProject() {
      try {
        const response = await fetch('/api/supervisor/projects', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            title: this.newProject.name,
            description: this.newProject.description,
            status: this.newProject.status,
            start_date: this.newProject.startDate,
            due_date: this.newProject.dueDate,
            student_ids: this.newProject.students.map(s => s.id) // Extract IDs from student objects
          })
        });
        
        if (!response.ok) {
          throw new Error('Failed to create project');
        }
        
        const data = await response.json();
        
        // Add new project to local array
        this.projects.push({
          id: data.data.id,
          title: this.newProject.name,
          description: this.newProject.description,
          status: this.newProject.status,
          progress: 0,
          students: [],
          startDate: this.newProject.startDate,
          dueDate: this.newProject.dueDate
        });
        
        this.closeCreateModal();
        this.updateStats();
        this.showToast('Project created successfully', 'success');
        
      } catch (error) {
        console.error('Error creating project:', error);
        this.showToast('Failed to create project', 'error');
      }
    },
    
    resetNewProject() {
      this.newProject = {
        name: '',
        description: '',
        status: 'active',
        startDate: '',
        dueDate: '',
        students: []
      };
      this.newStudentSearch = '';
      this.showNewStudentDropdown = false;
    },
    
    getProgressClass(progress) {
      if (progress >= 80) return 'bg-success';
      if (progress >= 50) return 'bg-warning';
      return 'bg-info';
    },
    
    getStatusClass(status) {
      const classes = {
        'not_started': 'bg-secondary',
        'in_progress': 'bg-primary',
        'completed': 'bg-success',
        'on_hold': 'bg-warning'
      };
      return classes[status] || 'bg-secondary';
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString();
    },
    
    viewProject(project) {
      // Navigate to project details page or show modal
      this.selectedProject = project;
      this.showViewModal = true;
      this.$nextTick(() => {
        const modal = document.getElementById('viewProjectModal');
        if (modal && window.bootstrap) {
          try {
            const bootstrapModal = new window.bootstrap.Modal(modal);
            bootstrapModal.show();
          } catch (error) {
            console.error('Error opening view modal:', error);
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
          }
        }
      });
    },
    
    editProject(project) {
      this.selectedProject = project;
      this.editingProject = {
        id: project.id,
        name: project.title,
        description: project.description,
        status: project.status,
        startDate: project.startDate,
        dueDate: project.dueDate,
        students: project.students || [] // Keep the full student objects
      };
      this.studentSearch = '';
      this.showStudentDropdown = false;
      this.showEditModal = true;
      this.$nextTick(() => {
        const modal = document.getElementById('editProjectModal');
        if (modal && window.bootstrap) {
          try {
            const bootstrapModal = new window.bootstrap.Modal(modal);
            bootstrapModal.show();
          } catch (error) {
            console.error('Error opening edit modal:', error);
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
          }
        }
      });
    },
    
    async deleteProject(project) {
      if (confirm(`Are you sure you want to delete "${project.title}"? This action cannot be undone.`)) {
        try {
          const response = await fetch(`/api/projects/${project.id}`, {
            method: 'DELETE',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
              'Content-Type': 'application/json'
            }
          });
          
          if (!response.ok) {
            throw new Error('Failed to delete project');
          }
          
          // Remove project from local array
          const index = this.projects.findIndex(p => p.id === project.id);
          if (index !== -1) {
            this.projects.splice(index, 1);
          }
          
          this.updateStats();
          this.showToast('Project deleted successfully', 'success');
          
        } catch (error) {
          console.error('Error deleting project:', error);
          this.showToast('Failed to delete project', 'error');
        }
      }
    },
    
    toggleSidebar() {
      document.querySelector('.sidebar').classList.toggle('show');
    },
    
    showToast(message, type = 'info') {
      console.log(`${type.toUpperCase()}: ${message}`);
    },
    
    openCreateModal() {
      this.showCreateModal = true;
      this.$nextTick(() => {
        const modal = document.getElementById('createProjectModal');
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
    
    closeCreateModal() {
      const modal = document.getElementById('createProjectModal');
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
      this.resetNewProject();
    },
    
    closeViewModal() {
      const modal = document.getElementById('viewProjectModal');
      if (modal && window.bootstrap) {
        try {
          const bootstrapModal = window.bootstrap.Modal.getInstance(modal);
          if (bootstrapModal) {
            bootstrapModal.hide();
          }
        } catch (error) {
          console.error('Error closing view modal:', error);
          modal.style.display = 'none';
          modal.classList.remove('show');
          document.body.classList.remove('modal-open');
          const backdrop = document.querySelector('.modal-backdrop');
          if (backdrop) {
            backdrop.remove();
          }
        }
      }
      this.showViewModal = false;
      this.selectedProject = null;
    },
    
    closeEditModal() {
      const modal = document.getElementById('editProjectModal');
      if (modal && window.bootstrap) {
        try {
          const bootstrapModal = window.bootstrap.Modal.getInstance(modal);
          if (bootstrapModal) {
            bootstrapModal.hide();
          }
        } catch (error) {
          console.error('Error closing edit modal:', error);
          modal.style.display = 'none';
          modal.classList.remove('show');
          document.body.classList.remove('modal-open');
          const backdrop = document.querySelector('.modal-backdrop');
          if (backdrop) {
            backdrop.remove();
          }
        }
      }
      this.showEditModal = false;
      this.editingProject = null;
      this.studentSearch = '';
      this.showStudentDropdown = false;
      this.filteredAvailableStudents = [];
    },
    
    async updateProject() {
      try {
        const response = await fetch(`/api/projects/${this.editingProject.id}`, {
          method: 'PUT',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            title: this.editingProject.name,
            description: this.editingProject.description,
            status: this.editingProject.status,
            start_date: this.editingProject.startDate,
            due_date: this.editingProject.dueDate,
            student_ids: this.editingProject.students.map(s => s.id) // Extract IDs from student objects
          })
        });
        
        if (!response.ok) {
          throw new Error('Failed to update project');
        }
        
        const data = await response.json();
        
        // Update project in local array
        const index = this.projects.findIndex(p => p.id === this.editingProject.id);
        if (index !== -1) {
          this.projects[index] = data.data;
        }
        
        this.closeEditModal();
        this.updateStats();
        this.showToast('Project updated successfully', 'success');
        
      } catch (error) {
        console.error('Error updating project:', error);
        this.showToast('Failed to update project', 'error');
      }
    },

    handleDragStart(event, item, source) {
      event.dataTransfer.setData('text/plain', JSON.stringify({ item, source }));
    },

    handleDrop(event, target) {
      event.preventDefault();
      const data = JSON.parse(event.dataTransfer.getData('text/plain'));
      const item = data.item;
      const source = data.source;

      if (target === 'remove') {
        this.removeStudent(item);
      } else if (target === 'add') {
        this.addStudent(item);
      }
    },

    removeStudent(student) {
      const index = this.editingProject.students.findIndex(s => s.id === student.id);
      if (index !== -1) {
        this.editingProject.students.splice(index, 1);
      }
    },

    addStudent(student) {
      if (!this.editingProject.students.find(s => s.id === student.id)) {
        this.editingProject.students.push(student);
      }
      this.studentSearch = ''; // Clear search input
      this.showStudentDropdown = false; // Hide dropdown
    },

    handleDropCreate(event, target) {
      event.preventDefault();
      const data = JSON.parse(event.dataTransfer.getData('text/plain'));
      const item = data.item;
      const source = data.source;

      if (target === 'remove') {
        this.removeStudentCreate(item);
      } else if (target === 'add') {
        this.addStudentCreate(item);
      }
    },

    removeStudentCreate(student) {
      const index = this.newProject.students.findIndex(s => s.id === student.id);
      if (index !== -1) {
        this.newProject.students.splice(index, 1);
      }
    },

    addStudentCreate(student) {
      if (!this.newProject.students.find(s => s.id === student.id)) {
        this.newProject.students.push(student);
      }
      this.newStudentSearch = ''; // Clear search input
      this.showNewStudentDropdown = false; // Hide dropdown
    },

    handleDragEnterCreate(event, target) {
      event.preventDefault();
      const container = event.currentTarget.closest('.assigned-students-container');
      if (container) {
        container.classList.add('drag-over');
      }
    },

    handleDragLeaveCreate(event, target) {
      event.preventDefault();
      const container = event.currentTarget.closest('.assigned-students-container');
      if (container) {
        container.classList.remove('drag-over');
      }
    },

    handleDragEnter(event, target) {
      event.preventDefault();
      const container = event.currentTarget.closest('.assigned-students-container');
      if (container) {
        container.classList.add('drag-over');
      }
    },

    handleDragLeave(event, target) {
      event.preventDefault();
      const container = event.currentTarget.closest('.assigned-students-container');
      if (container) {
        container.classList.remove('drag-over');
      }
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

.avatar-group img {
  border: 2px solid white;
}

.student-tag {
  display: flex;
  align-items: center;
  padding: 0.375rem 0.75rem;
  margin-bottom: 0.5rem;
  border-radius: 0.375rem;
  background-color: #e9ecef;
  cursor: grab;
  transition: background-color 0.2s ease;
}

.student-tag:hover {
  background-color: #dee2e6;
}

.student-tag .btn-close {
  background-color: rgba(0, 0, 0, 0.1);
  border: none;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  margin-left: 0.5rem;
}

.student-tag .btn-close:hover {
  background-color: rgba(0, 0, 0, 0.2);
}

.available {
  background-color: #f8f9fa;
  border: 1px dashed #ced4da;
}

.available:hover {
  background-color: #e9ecef;
}

.assigned-students-container {
  min-height: 50px; /* Ensure container has a minimum height */
  max-height: 200px; /* Limit height for scrolling */
  overflow-y: auto;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.75rem;
  margin-bottom: 1rem;
}

.available-students-container {
  min-height: 50px; /* Ensure container has a minimum height */
  max-height: 200px; /* Limit height for scrolling */
  overflow-y: auto;
  border: 1px solid #ced4da;
  border-radius: 0.375rem;
  padding: 0.75rem;
  margin-bottom: 1rem;
}

.dropdown-item {
  cursor: pointer;
  padding: 0.5rem 0.75rem;
}

.dropdown-item:hover {
  background-color: #f8f9fa;
}

.dropdown-item .d-flex {
  align-items: center;
}

.dropdown-item .fw-medium {
  font-weight: 500;
}

.dropdown-item .text-muted {
  font-size: 0.875rem;
}

.border-dashed {
  border: 2px dashed #ced4da;
  border-radius: 0.375rem;
}

.student-tag.dragging {
  opacity: 0.5;
  transform: rotate(5deg);
}

.assigned-students-container.drag-over,
.available-students-container.drag-over {
  background-color: #e3f2fd;
  border-color: #2196f3;
}

.cursor-pointer {
  cursor: pointer;
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
