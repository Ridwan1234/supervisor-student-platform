<template>
    <div class="home-container">
      <!-- Hero Section -->
      <div class="hero-section">
        <div class="hero-content">
          <h1 class="title">Cloud-Based Collaborative Platform</h1>
          <p class="subtitle">
            Seamlessly connect supervisors and students to collaborate in real-time and boost productivity.
          </p>
          <div class="cta-buttons">
            <router-link to="/login">
              <button class="btn-primary">Login</button>
            </router-link>
            <router-link to="/register">
              <button class="btn-secondary">Sign Up</button>
            </router-link>
          </div>
        </div>
        <div class="hero-image">
          <img src="https://via.placeholder.com/500x300.png?text=Cloud+Based" alt="Collaboration illustration" />
        </div>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: "HomePage",
  };
  </script>

  <style scoped>

  body {
    margin: 0;
    font-family: 'Roboto', sans-serif;
    background-color: #f8f9fa;
    color: #2c3e50;
  }
  .home-container {
    display: flex;
    margin-top: 200px;
    flex-direction: column;
    align-items: center;
    text-align: center;
  }

  /* Hero Section */
  .hero-section {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 40px 20px;
    background: linear-gradient(135deg, #2980b9, #6dd5fa);
    color: #fff;
  }

  .hero-content {
    flex: 1;
    max-width: 500px;
  }

  .hero-image img {
    max-width: 500px;
    border-radius: 10px;
  }

  .title {
    font-size: 2.8rem;
    margin-bottom: 20px;
  }

  .subtitle {
    font-size: 1.2rem;
    margin-bottom: 30px;
  }

  .cta-buttons {
    display: flex;
    gap: 15px;
  }

  .btn-primary {
    background-color: #1abc9c;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s;
  }

  .btn-primary:hover {
    background-color: #16a085;
  }

  .btn-secondary {
    background-color: #fff;
    color: #2980b9;
    border: 2px solid #fff;
    border-radius: 5px;
    padding: 10px 20px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
  }

  .btn-secondary:hover {
    background-color: #fff;
    color: #16a085;
  }

  /* Features Section */
  .features-section {
    padding: 60px 20px;
    background-color: #fff;
  }

  .features-section h2 {
    font-size: 2rem;
    margin-bottom: 30px;
    color: #2c3e50;
  }

  .features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
  }

  .feature-card {
    background: #f4f6f9;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  }

  .feature-card i {
    font-size: 2.5rem;
    color: #2980b9;
    margin-bottom: 10px;
  }

  .feature-card h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
  }

  .feature-card p {
    font-size: 0.9rem;
    color: #7f8c8d;
  }

  /* Footer */
  .footer {
    padding: 20px;
    background-color: #2c3e50;
    color: #fff;
    font-size: 0.9rem;
  }
  </style>
