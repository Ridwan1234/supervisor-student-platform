<template>
    
  <div class="container mt-5">
    <div class="row">
      <!-- Assign Tasks Section -->
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-body">
            <h5 class="card-title">Assign Tasks</h5>
            <form @submit.prevent="assignTask">
              <div class="mb-3">
                <label for="student" class="form-label">Student</label>
                <select
                  id="student"
                  v-model="task.student"
                  class="form-select"
                  required
                >
                  <option disabled value="">Select a student</option>
                  <option
                    v-for="student in students"
                    :key="student.id"
                    :value="student.id"
                  >
                    {{ student.name }}
                  </option>
                </select>
              </div>
              <div class="mb-3">
                <label for="taskTitle" class="form-label">Task Title</label>
                <input
                  type="text"
                  id="taskTitle"
                  v-model="task.title"
                  class="form-control"
                  placeholder="Enter task title"
                  required
                />
              </div>
              <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea
                  id="description"
                  v-model="task.description"
                  class="form-control"
                  rows="3"
                  placeholder="Enter task description"
                  required
                ></textarea>
              </div>
              <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input
                  type="date"
                  id="deadline"
                  v-model="task.deadline"
                  class="form-control"
                  required
                />
              </div>
              <button
                type="submit"
                class="btn btn-primary w-100"
                :disabled="isLoading"
              >
                <span
                  v-if="isLoading"
                  class="spinner-border spinner-border-sm"
                  role="status"
                  aria-hidden="true"
                ></span>
                <span v-if="!isLoading">Assign Task</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Track Progress Section -->
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-body">
            <h5 class="card-title">Track Progress</h5>
            <ul class="list-group">
              <li
                class="list-group-item d-flex justify-content-between align-items-center"
                :class="{
                  'text-danger': new Date(progress.deadline) < new Date(),
                }"
                v-for="progress in taskProgress"
                :key="progress.id"
              >
                <div>
                  <strong>{{ progress.title }}</strong>
                  <p class="mb-0 small text-muted">
                    Due: {{ progress.deadline }}
                  </p>
                  <p class="mb-0 small">{{ progress.description }}</p>
                </div>
                <select
                  @change="updateStatus(progress)"
                  v-model="progress.status"
                  class="form-select w-auto"
                >
                  <option value="Pending">Pending</option>
                  <option value="In Progress">In Progress</option>
                  <option value="Completed">Completed</option>
                </select>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Task History Section -->
    <div class="row mt-4">
      <div class="col">
        <div class="card shadow">
          <div class="card-body">
            <h5 class="card-title">Task History</h5>
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Task</th>
                  <th>Assigned To</th>
                  <th>Completed On</th>
                  <th>Feedback</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="history in taskHistory" :key="history.id">
                  <td>{{ history.title }}</td>
                  <td>{{ history.assignedTo }}</td>
                  <td>{{ history.completedOn }}</td>
                  <td>{{ history.feedback }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

  <script>
  import SupervisorSidebarVue from './SupervisorSidebar.vue';
import axios from "axios";
import { useToast } from "vue-toastification";
// import SupervisorSidebarVue from "./Supervior/SupervisorSidebar.vue";

export default {
  components: {
    SupervisorSidebarVue,
  },
  data() {
    return {
      task: {
        student: "",
        title: "",
        description: "",
        deadline: "",
      },
      students: [],
      taskProgress: [],
      taskHistory: [],
      currentUserId: null,
      isLoading: false, // Loading state for buttons
    };
  },
  methods: {
    async fetchCurrentUser() {
      try {
        const response = await axios.get("/api/user", {
          withCredentials: true,
        });
        this.currentUserId = response.data.id;
      } catch (error) {
        console.error(
          "Failed to fetch current user:",
          error.response?.data || error.message
        );
        this.$toast.error("Failed to authenticate user. Please log in.");
      }
    },

    async fetchStudents() {
      try {
        const response = await axios.get("/api/students");
        this.students = response.data;
      } catch (error) {
        console.error(
          "Failed to fetch students:",
          error.response?.data || error.message
        );
      }
    },

    async fetchTasks() {
      try {
        const response = await axios.get("/api/tasks");
        this.taskProgress = response.data;
      } catch (error) {
        console.error(
          "Failed to fetch tasks:",
          error.response?.data || error.message
        );
      }
    },

    async fetchTaskHistory() {
      try {
        const response = await axios.get("/api/task-history");
        this.taskHistory = response.data;
      } catch (error) {
        // Improved error handling: checks for undefined response and message
        const errorMessage =
          error.response?.data?.message ||
          error.message ||
          "An unknown error occurred while fetching task history.";
        console.error("Failed to fetch task history:", errorMessage);
        this.$toast.error(`Failed to fetch task history: ${errorMessage}`);
      }
    },

    async assignTask() {
      if (!this.currentUserId) {
        this.$toast.error("User not authenticated");
        return;
      }

      if (
        !this.task.student ||
        !this.task.title ||
        !this.task.description ||
        !this.task.deadline
      ) {
        this.$toast.error("Please fill all fields before assigning a task.");
        return;
      }

      this.isLoading = true;
      try {
        await axios.post("/api/tasks", {
          assigned_to: this.task.student,
          assigned_by: this.currentUserId,
          title: this.task.title,
          description: this.task.description,
          deadline: this.task.deadline,
        });

        this.$toast.success("Task assigned successfully!");
        await this.fetchTasks(); // Refresh task list
        this.task = { student: "", title: "", description: "", deadline: "" }; // Reset form
      } catch (error) {
        this.$toast.error("Failed to assign task.");
        console.error(
          "Failed to assign task:",
          error.response?.data || error.message
        );
      } finally {
        this.isLoading = false;
      }
    },

    async updateStatus(task) {
      try {
        await axios.put(`/api/tasks/${task.id}`, { status: task.status });
        this.$toast.success("Task status updated successfully!");
      } catch (error) {
        this.$toast.error("Failed to update task status.");
        console.error(
          "Failed to update task status:",
          error.response?.data || error.message
        );
      }
    },
  },
  async created() {
    try {
      await Promise.all([
        this.fetchCurrentUser(),
        this.fetchStudents(),
        this.fetchTasks(),
        this.fetchTaskHistory(),
      ]);
    } catch (error) {
      console.error("Initialization error:", error);
    }
  },
};
</script>

  <style>
.card {
  border-radius: 8px;
}

.card-title {
  font-size: 1.25rem;
  font-weight: bold;
}

.btn {
  border-radius: 4px;
}
</style>
