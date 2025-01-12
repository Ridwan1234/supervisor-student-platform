<template>
    <div class="auth-container">
      <div class="auth-card">
        <h2>Login</h2>
        <form @submit.prevent="handleLogin">
          <!-- Email -->
          <div class="form-group">
            <label for="email">Email</label>
            <input
              type="email"
              id="email"
              v-model="form.email"
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
              v-model="form.password"
              placeholder="Enter your password"
              required
            />
          </div>

          <!-- Login Button -->
          <button type="submit" class="btn-login">Login</button>
        </form>

        <!-- Links -->
        <div class="auth-links">
          <a href="#">Forgot Password?</a>
          <p>
            Dont have an account? <router-link to="/register">Sign Up</router-link>
          </p>
        </div>
      </div>
    </div>
  </template>

  <script>
  import axios from 'axios';
  import { useRouter } from 'vue-router';
  import { useToast } from 'vue-toastification'; // Import Toastification

//   export default {
//     data() {
//       return {
//         form: {
//           email: '',
//           password: '',
//         },
//       };
//     },
//     methods: {
//       async handleLogin() {
//         const toast = useToast(); // Initialize the toast instance

//         try {
//           // Send POST request to login API
//           const response = await axios.post('http://127.0.0.1:8000/api/login', this.form);

//           // Save the received token and user data to localStorage
//           localStorage.setItem('token', response.data.token);
//           localStorage.setItem('user', JSON.stringify(response.data.user));
//           this.currentUserId = response.data.user.id;
//           // Redirect based on the user's role
//           const userRole = response.data.role;
//           if (userRole === 'supervisor') {
//             this.$router.push({ name: 'supervisor-dashboard' }); // Redirect to supervisor dashboard
//           } else if (userRole === 'student') {
//             this.$router.push({ name: 'student-dashboard' }); // Redirect to student dashboard
//           } else {
//             this.$router.push({ name: 'dashboard' }); // Fallback route if role is undefined
//           }

//           toast.success('Login successful!'); // Success toast
//         } catch (error) {
//           // Handle error if login fails
//           if (error.response) {
//             // Custom error handling based on backend response
//             toast.error(error.response.data.message || 'Login failed! Please check your credentials.');
//           } else {
//             // Handle unexpected error (e.g., network issue)
//             toast.error('An unexpected error occurred.');
//           }
//         }
//       },
//     },
//   };


export default {
  data() {
    return {
      form: {
        email: '',
        password: '',
      },
      currentUserId: null,
    };
  },
  methods: {
    async handleLogin() {
      axios.defaults.baseURL = 'http://supervisor-student-platform.test';
      const toast = useToast(); // Initialize the toast instance

      try {
        // Send POST request to login API
        const response = await axios.post("/api/login", this.form);

        // Save the received token and user data to localStorage
        const token = response.data.token;
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(response.data.user));
        this.currentUserId = response.data.user.id;

        // Set the token in Axios Authorization header
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        // Redirect based on the user's role
        const userRole = response.data.role;
        if (userRole === 'supervisor') {
          this.$router.push({ name: 'supervisor-dashboard' }); // Redirect to supervisor dashboard
        } else if (userRole === 'student') {
          this.$router.push({ name: 'student-dashboard' }); // Redirect to student dashboard
        } else {
          this.$router.push({ name: 'dashboard' }); // Fallback route if role is undefined
        }

        toast.success('Login successful!'); // Success toast
      } catch (error) {
        // Handle error if login fails
        if (error.response) {
          // Custom error handling based on backend response
          toast.error(error.response.data.message || 'Login failed! Please check your credentials.');
        } else {
          // Handle unexpected error (e.g., network issue)
          toast.error('An unexpected error occurred.');
        }
      }
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

input, select {
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
