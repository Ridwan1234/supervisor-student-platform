<template>
    <div class="auth-container">
      <div class="auth-card">
        <h2>Register</h2>
        <form @submit.prevent="handleRegister">
          <!-- Name -->
          <div class="form-group">
            <label for="name">Name</label>
            <input
              type="text"
              id="name"
              v-model="name"
              placeholder="Enter your name"
              required
            />
          </div>

          <!-- Email -->
          <div class="form-group">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              v-model="email"
              placeholder="Enter your email"
              required
            />
          </div>

          <!-- Password -->
          <div class="form-group">
            <label for="password">Password</label>
            <input
              type="password"
              id="password"
              v-model="password"
              placeholder="Enter your password"
              required
            />
          </div>

          <!-- Password Confirmation -->
          <div class="form-group">
            <label for="password_confirmation">Confirm Password</label>
            <input
              type="password"
              id="password_confirmation"
              v-model="passwordConfirmation"
              placeholder="Confirm your password"
              required
            />
          </div>

          <!-- Role Dropdown -->
          <div class="form-group">
            <label for="role">Role</label>
            <select id="role" v-model="role" required>
              <option value="" disabled>Select Role</option>
              <option value="student">Student</option>
              <option value="supervisor">Supervisor</option>
            </select>
          </div>

          <!-- Register Button -->
          <button type="submit" class="btn-register">Register</button>
        </form>

        <!-- Links -->
        <div class="auth-links">
          <p>
            Already have an account? <router-link to="/login">Login</router-link>
          </p>
        </div>
      </div>
    </div>
  </template>

  <script>
  import axios from "axios";
  import { useToast } from "vue-toastification";

  export default {
    data() {
      return {
        name: "",
        email: "",
        password: "",
        passwordConfirmation: "", // Add password confirmation field
        role: "",
      };
    },
    methods: {
      handleRegister() {
        const toast = useToast();

        // Check if password and password confirmation match
        if (this.password !== this.passwordConfirmation) {
          toast.error("Password and confirmation do not match.");
          return;
        }

        const formData = {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.passwordConfirmation, // Send the confirmation field to the backend
          role: this.role,
        };

        axios
          .post("http://127.0.0.1:8000/api/register", formData)
          .then((response) => {
            toast.success(response.data.message || "Registration successful!");
            this.$router.push("/login");
          })
          .catch((error) => {
            if (error.response && error.response.data.errors) {
              for (const key in error.response.data.errors) {
                toast.error(error.response.data.errors[key][0]);
              }
            } else {
              toast.error("An error occurred during registration.");
            }
          });
      },
    },
  };
  </script>
  
<style scoped>
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f8f9fa;
}

.auth-card {
  background: white;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  text-align: center;
  width: 350px;
}

h2 {
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 15px;
  text-align: left;
}

label {
  display: block;
  margin-bottom: 5px;
  font-size: 14px;
  font-weight: bold;
}

input,
select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.btn-login {
  width: 100%;
  padding: 10px;
  background-color: #212529;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
}

.btn-login:hover {
  background-color: #343a40;
}
.btn-login {
  width: 100%;
  padding: 10px;
  background-color: #212529;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 10px;
}

.btn-login:hover {
  background-color: #343a40;
}

.auth-links {
  margin-top: 10px;
  font-size: 14px;
}

.auth-links a {
  color: #007bff;
  text-decoration: none;
}

.auth-links a:hover {
  text-decoration: underline;
}
</style>
