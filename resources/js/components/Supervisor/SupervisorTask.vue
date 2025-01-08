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
                  <select id="student" v-model="task.assigned_to" class="form-select" required>
                    <option disabled value="">Select a student</option>
                    <option v-for="student in students" :key="student.id" :value="student.id">
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
                <button type="submit" class="btn btn-primary w-100">Assign Task</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  export default {
    data() {
      return {
        task: {
          assigned_to: "",
          assigned_by: "1", // Assume admin ID or logged-in user's ID
          title: "",
          description: "",
          deadline: "",
        },
        students: [],
      };
    },
    mounted() {
      this.fetchStudents();
    },
    methods: {
      async fetchStudents() {
        const response = await fetch("/api/students");
        const data = await response.json();
        this.students = data;
      },
      async assignTask() {
        const response = await fetch("/api/assign-task", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(this.task),
        });
        const result = await response.json();
        if (response.ok) {
          alert(result.message);
        } else {
          alert("Failed to assign task");
        }
      },
    },
  };
  </script>
