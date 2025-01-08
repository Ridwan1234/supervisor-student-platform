<template>
    <div id="app">
      <!-- Navbar (Optional) -->
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
          <router-link class="navbar-brand" to="/">Home</router-link>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
              <li class="nav-item">
                <router-link class="nav-link" to="/login">Login</router-link>
              </li>
              <li class="nav-item">
                <router-link class="nav-link" to="/register">Register</router-link>
              </li>
            </ul>
          </div>
        </div>
      </nav>

      <!-- Router View (For Rendering Different Components) -->
      <router-view />
    </div>
  </template>

  <script>
  // You can add global logic or imports here if needed.
  export default {
    name: 'App',
  };
  </script>

  <style>
  /* Global Styles */
  #app {
    font-family: 'Arial', sans-serif;
    background-color: #f8f9fa;
    min-height: 100vh;
  }

  .navbar {
    margin-bottom: 20px;
  }
  </style>
