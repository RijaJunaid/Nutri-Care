<!DOCTYPE html>
<html lang="en">
<head>
  <?php require 'Partials/head.php';?>
  <?php require 'Partials/nav.php';?>
  <style>
:root {
  --green-light: #E3F4E1;
  --green: #5FB65A;
  --green-dark: #3C8D37;
  --purple-light: #E5DEFF;
  --blue-light: #D3E4FD;
  --peach: #FDE1D3;
  --gray-100: #F9FAFB;
  --gray-200: #F1F0FB;
  --gray-300: #E5E7EB;
  --gray-400: #D1D5DB;
  --gray-500: #9CA3AF;
  --gray-600: #6B7280;
  --gray-700: #4B5563;
  --gray-800: #1F2937;
  --gray-900: #111827;
  
  --border-radius: 0.75rem;
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  
  --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --font-display: 'Montserrat', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Base Styles */
.container {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
  position: relative;
  z-index: 1;
}

/* Hero Section */
.hero-section {
  position: relative;
  background: linear-gradient(to bottom, #d8f3dc, #ffffff);
  padding: 60px 0;
  overflow: hidden;
}

.hero-bg {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
  z-index: 0;
  opacity: 0.35;
}

.carousel-item img {
  object-fit: cover;
  height: 60vh;
  width: 100%;
}

.hero-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 30px;
}

.hero-text {
  width: 100%;
}

.hero-title {
  font-size: 2rem;
  font-weight: bold;
  color: #2f2f2f;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  line-height: 1.2;
  margin-bottom: 15px;
}

.highlight {
  color: #2e7d32;
}

.hero-description {
  font-size: 1.125rem;
  color: #4a4a4a;
  margin: 0 0 20px;
  line-height: 1.6;
}

.hero-buttons {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 20px;
  align-items: center;
}

.btn {
  padding: 12px 24px;
  font-size: 1rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  text-align: center;
}

.btn-primary {
  background-color: #4CAF50;
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: #388e3c;
}

.btn-outline {
  background-color: transparent;
  border: 2px solid #4CAF50;
  color: #4CAF50;
}

.btn-outline:hover {
  background-color: #e8f5e9;
}

.icon-arrow {
  width: 20px;
  height: 20px;
  margin-left: 8px;
}

.users-count {
  margin-top: 20px;
  font-size: 0.9rem;
  color: #777;
}

/* Features Section */
.features-section {
  background-color: rgba(227, 244, 225, 0.3);
  padding: 4rem 0;
}

.section-header {
  text-align: center;
  margin-bottom: 3rem;
  padding: 0 15px;
}

.section-title {
  font-size: 2rem;
  margin-bottom: 1rem;
  color: var(--gray-800);
}

.section-description {
  color: var(--gray-600);
  max-width: 700px;
  margin: 0 auto;
}

.features-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  padding: 0 15px;
}

.feature-card {
  background-color: white;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid rgba(95, 182, 90, 0.15);
  transform: scale(1);
  transition: all 0.3s ease;
  opacity: 0;
  z-index: 0;
}


.feature-card:hover {
  background-color: #2e7d32; 
  color: white; 
  transform: scale(1.05);
  z-index: 1; 
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 
              0 4px 6px -2px rgba(0, 0, 0, 0.05);
}
    
    .feature-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background-color: rgba(74, 222, 128, 0.1); 
      color: #15803d; 
      padding: 0.75rem;
      border-radius: 9999px;
      margin-bottom: 1rem;
      transition: all 0.3s ease;
    }
    
    .feature-card:hover .feature-icon {
      background-color: white; 
      color:  #2e7d32;}
    
    .feature-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 0.5rem;
      color: #1f2937; 
      transition: color 0.3s ease;
    }
    
    .feature-card:hover .feature-title {
      color: white; 
    }
    
    .feature-description {
      color: #6b7280; 
      transition: color 0.3s ease;
    }
    
    .feature-card:hover .feature-description {
      color: rgba(255, 255, 255, 0.9); 
    }

/* Add this to your existing CSS */
.feature-card {
  display: block;
  text-decoration: none;
  color: inherit;
}

.feature-card:hover {
  text-decoration: none;
}

/* Stats Section */
.stats-section {
  background-color: white;
  padding: 4rem 0;
}

.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
  padding: 0 15px;
}

.stat-card {
  background-color: rgba(227, 244, 225, 0.2);
  border-radius: var(--border-radius);
  padding: 1.5rem;
  text-align: center;
  transform: scale(1);
  transition: transform 0.3s ease;
  border: 1px solid rgba(95, 182, 90, 0.15);
}

.stat-card:hover {
  transform: scale(1.03);
}

.stat-value {
  font-size: 1.875rem;
  font-family: var(--font-display);
  font-weight: 700;
  color: var(--green-dark);
  margin-bottom: 0.5rem;
}

.stat-label {
  color: var(--gray-700);
}

/* Testimonials Section */
.testimonials-section {
  padding: 4rem 0;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.5rem;
  padding: 0 15px;
}

.testimonial-card {
  background-color: white;
  border-radius: var(--border-radius);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid rgba(211, 228, 253, 0.4);
  opacity: 0;
  transform: translateY(10px);
}

.testimonial-rating {
  display: flex;
  margin-bottom: 1rem;
}

.testimonial-rating i {
  margin-right: 0.25rem;
}

.star-filled {
  color: var(--green);
  fill: var(--green);
}

.star-empty {
  color: var(--gray-300);
}

.testimonial-content {
  color: var(--gray-700);
  margin-bottom: 1rem;
  line-height: 1.6;
}

.testimonial-author {
  margin-top: 1rem;
}

.author-name {
  font-weight: 600;
  color: var(--gray-800);
}

.author-role {
  font-size: 0.875rem;
  color: var(--gray-500);
}

/* Footer */
.footer {
  background-color: white;
  border-top: 1px solid var(--green-light);
  padding-top: 3rem;
}

.footer-content {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
  padding: 0 15px 3rem;
}

.footer-about {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.footer-logo {
  display: flex;
  align-items: center;
  margin-bottom: 1rem;
  gap: 0.5rem;
}

.logo-text {
  font-weight: 600;
  font-size: 1.25rem;
}

.footer-description {
  color: var(--gray-600);
  margin-bottom: 1rem;
  line-height: 1.6;
}

.social-links {
  display: flex;
  gap: 1rem;
}

.social-link {
  color: var(--green);
  transition: color 0.2s ease;
}

.social-link:hover {
  color: var(--green-dark);
}

.footer-heading {
  font-size: 1.125rem;
  color: var(--gray-800);
  margin-bottom: 1rem;
}

.footer-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.footer-list li a {
  color: var(--gray-600);
  transition: color 0.2s ease;
  text-decoration: none;
}

.footer-list li a:hover {
  color: var(--green);
}

.footer-contact-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.contact-item {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
}

.contact-item i {
  color: var(--green);
  flex-shrink: 0;
  margin-top: 0.25rem;
}

.contact-item a {
  color: var(--gray-600);
  transition: color 0.2s ease;
  text-decoration: none;
}

.contact-item a:hover {
  color: var(--green);
}

.footer-bottom {
  border-top: 1px solid rgba(95, 182, 90, 0.15);
  padding: 1.5rem 15px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  align-items: center;
}

.copyright {
  color: var(--gray-600);
  text-align: center;
}

.legal-links {
  display: flex;
  gap: 1.5rem;
  flex-wrap: wrap;
  justify-content: center;
}

.legal-links a {
  font-size: 0.875rem;
  color: var(--gray-600);
  transition: color 0.2s ease;
  text-decoration: none;
}

.legal-links a:hover {
  color: var(--green);
}

/* Animations */
@keyframes fade-in {
  0% {
    opacity: 0;
    transform: translateY(10px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.5s ease-out forwards;
}

/* Media Queries */
@media (min-width: 576px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-buttons {
    flex-direction: row;
    justify-content: center;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .testimonials-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (min-width: 768px) {
  .hero-section {
    padding: 80px 0;
  }
  
  .hero-content {
    flex-direction: row;
    text-align: left;
    align-items: center;
    gap: 50px;
  }
  
  .hero-text {
    width: 50%;
    align-items: flex-start;
  }
  
  .hero-buttons {
    justify-content: flex-start;
  }
  
  .hero-title {
    font-size: 3rem;
  }
  
  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .footer-content {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .footer-bottom {
    flex-direction: row;
    justify-content: space-between;
  }
}

@media (min-width: 992px) {
  .hero-title {
    font-size: 3.5rem;
  }
  
  .features-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .stats-grid {
    grid-template-columns: repeat(4, 1fr);
  }
  
  .testimonials-grid {
    grid-template-columns: repeat(3, 1fr);
  }
  
  .footer-content {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (min-width: 1200px) {
  .carousel-item img {
    height: 80vh;
  }
}
  </style>
</head>
<body>
  
  <main>
   <!-- Hero Section  -->
<section class="hero-section position-relative">
  <div id="heroCarousel" class="carousel slide hero-bg" data-ride="carousel" data-interval="4000">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="https://images.unsplash.com/photo-1512621776951-a57141f2eefd?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80" class="d-block w-100" alt="Healthy Food">
      </div>
      <div class="carousel-item">
        <img src="Images/salad.jpg" class="d-block w-100" alt="Fruits and Veggies">
      </div>
    </div>
  </div>

  <!-- Hero Content -->
  <div class="container">
    <div class="hero-content">
      <div class="hero-text">
        <h1 class="hero-title">
          Your Health, Your Diet, <span class="highlight">Your Way</span>
        </h1>
        <p class="hero-description">
          Personalized nutrition guidance tailored to your unique health needs and goals.
        </p>
        <div class="hero-buttons">
          <button class="btn btn-primary">
            Get Started
            <svg class="icon-arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
          </button>
          <button class="btn btn-outline" id="explore-features-btn">
            Explore Features
          </button>
        </div>
        <p class="users-count">Helping 10,000+ users eat better every day</p>
      </div>
    </div>
  </div>
</section>
     <!-- Features Section -->
     
<section class="features-section">
  <div class="container">
    <div class="section-header" id="features-section">
      <h2 class="section-title">Our Features</h2>
      <p class="section-description">Explore our comprehensive set of tools designed to help you achieve your health and nutrition goals.</p>
    </div>
    
    <div class="features-grid">
      <a href="foodrecommendations.php" class="feature-card" style="animation-delay: 0s;">
        <div class="feature-icon">
          <i class="fas fa-exchange-alt"></i>
        </div>
        <h3 class="feature-title">Food Recommendations</h3>
        <p class="feature-description">Food suggestions based on your health conditions and nutritional needs.</p>
      </a>
      
      <a href="foodswap.php" class="feature-card" style="animation-delay: 0.1s;">
        <div class="feature-icon">
          <i class="fas fa-sync-alt"></i>
        </div>
        <h3 class="feature-title">Food Swaps</h3>
        <p class="feature-description">Discover healthier alternatives to your favorite foods without sacrificing taste.</p>
      </a>
      
      <a href="childnutrition.php" class="feature-card" style="animation-delay: 0.2s;">
        <div class="feature-icon">
          <i class="fas fa-baby"></i>
        </div>
        <h3 class="feature-title">Child Nutrition</h3>
        <p class="feature-description">Ensure your children receive proper nutrition for their growth and development.</p>
      </a>
      
      <a href="exercise.php" class="feature-card" style="animation-delay: 0.3s;">
        <div class="feature-icon">
          <i class="fas fa-running"></i>
        </div>
        <h3 class="feature-title">Exercise Tips</h3>
        <p class="feature-description">Complement your diet with appropriate exercise routines for optimal results.</p>
      </a>
      
      <a href="add_review.php" class="feature-card" style="animation-delay: 0.4s;">
        <div class="feature-icon">
          <i class="fas fa-calendar-alt"></i>
        </div>
        <h3 class="feature-title">Reviews</h3>
        <p class="feature-description">Add your reviews regarding our website and keep us informed for future improvements</p>
      </a>
      
      <a href="nutritionalbreakdown.php" class="feature-card" style="animation-delay: 0.5s;">
        <div class="feature-icon">
          <i class="fas fa-chart-pie"></i>
        </div>
        <h3 class="feature-title">Nutritional Breakdown</h3>
        <p class="feature-description">Visualize and understand the nutritional content of every meal</p>
      </a>
      
      <a href="premium.php" class="feature-card" style="animation-delay: 0.6s;">
        <div class="feature-icon">
          <i class="fas fa-user-plus"></i>
        </div>
        <h3 class="feature-title">Premium Consult</h3>
        <p class="feature-description">Get personalized advice from certified nutritionists and dietitians.</p>
      </a>
    </div>
  </div>
</section>

    <!-- Stats Section -->
    <section class="stats-section">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-value">10,000+</div>
            <div class="stat-label">Active Users</div>
          </div>

          <div class="stat-card">
            <div class="stat-value">500+</div>
            <div class="stat-label">Nutrition Experts</div>
          </div>

          <div class="stat-card">
            <div class="stat-value">50,000+</div>
            <div class="stat-label">Food Items Analyzed</div>
          </div>

          <div class="stat-card">
            <div class="stat-value">95%</div>
            <div class="stat-label">User Satisfaction</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
<section class="testimonials-section">
  <div class="container">
    <div class="section-header">
      <h2 class="section-title">What Our Users Say</h2>
      <p class="section-description">Join thousands of satisfied users who have improved their health with NutriCare.</p>
      <button class="btn btn-outline" onclick="window.location.href='add_review.php'">
        Add Your Review
        <svg class="icon-arrow" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
        </svg>
      </button>
    </div>
    
    <div class="testimonials-grid">
      <?php
      require 'config.php';
      
      try {
          $stmt = $conn->query("
              SELECT website_reviews.*, users.name 
              FROM website_reviews 
              JOIN users ON website_reviews.user_id = users.user_id 
              ORDER BY created_at DESC 
              LIMIT 3
          ");
          $reviews = $stmt->fetchAll();
          
          if (empty($reviews)) {
              echo '<p>No reviews yet. Be the first to review!</p>';
          } else {
              foreach ($reviews as $review) {
                  echo '
                  <div class="testimonial-card">
                    <div class="testimonial-rating">';
                      
                      // Display filled stars
                      for ($i = 0; $i < $review['rating']; $i++) {
                          echo '<i data-feather="star" class="star-filled"></i>';
                      }
                      
                      // Display empty stars
                      for ($i = $review['rating']; $i < 5; $i++) {
                          echo '<i data-feather="star" class="star-empty"></i>';
                      }
                      
                  echo '</div>
                    <p class="testimonial-content">' . htmlspecialchars($review['comment']) . '</p>
                    <div class="testimonial-author">
                      <p class="author-name">' . htmlspecialchars($review['name']) . '</p>
                      <p class="author-role">NutriCare User</p>
                    </div>
                  </div>';
              }
          }
      } catch (PDOException $e) {
          echo '<p>Error loading reviews. Please try again later.</p>';
      }
      ?>
    </div>
  </div>
</section>

  </main>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="footer-content">
        <div class="footer-about">
          <div class="footer-logo">
            <div class="logo-icon">
              <i data-feather="heart"></i>
            </div>
            <span class="logo-text">NutriCare</span>
          </div>
          <p class="footer-description">Your personalized nutrition and health companion. We make healthy eating easy and accessible for everyone.</p>
          <div class="social-links">
            <a href="#" class="social-link"><i data-feather="facebook"></i></a>
            <a href="#" class="social-link"><i data-feather="twitter"></i></a>
            <a href="#" class="social-link"><i data-feather="instagram"></i></a>
            <a href="#" class="social-link"><i data-feather="youtube"></i></a>
          </div>
        </div>

        <div class="footer-links">
          <h3 class="footer-heading">Features</h3>
          <ul class="footer-list">
            <li><a href="foodrecommendations.php">Food Recommendations</a></li>
            <li><a href="foodswap.php">Food Swaps</a></li>
            <li><a href="childnutrition.php">Child Nutrition</a></li>
            <li><a href="exercise.php">Exercise Tips</a></li>
            <li><a href="add_review.php">Reviews</a></li>
            <li><a href="nutritionalbreakdown.php">Nutritional Breakdown</a></li>
            <li><a href="premium.php">Premium Consult</a></li>
          </ul>
        </div>

        <div class="footer-links">
          <h3 class="footer-heading">Company</h3>
          <ul class="footer-list">
            <li><a href="contact.php">About Us</a></li>
           
          </ul>
        </div>

        <div class="footer-contact">
          <h3 class="footer-heading">Contact</h3>
          <ul class="footer-contact-list">
            <li class="contact-item">
              <i data-feather="map-pin"></i>
              <span>123 Nutrition Street, Healthy City, HC 12345</span>
            </li>
            <li class="contact-item">
              <i data-feather="mail"></i>
              <a href="mailto:info@nutricare.com">info@nutricare.com</a>
            </li>
            <li class="contact-item">
              <i data-feather="phone-call"></i>
              <a href="tel:+11234567890">(042) 05368198</a>
            </li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <div class="copyright">
          <p>© <span id="current-year"></span> NutriCare. All rights reserved.</p>
        </div>
        <div class="legal-links">
          <a href="admin_login.php">Admin Login</a>
          <a href="login.php">User Login</a>
        </div>
      </div>
    </div>
  </footer>

  
  <script src="https://cdn.gpteng.co/gptengineer.js" type="module"></script>
  
  <script>
   
    document.addEventListener('DOMContentLoaded', () => {  //  Feather Icons
      feather.replace();
      
      
      document.getElementById('current-year').textContent = new Date().getFullYear();
      
      // Mobile menu toggle
      const menuToggle = document.getElementById('menu-toggle');
      const mobileMenu = document.getElementById('mobile-menu');
      const menuIcon = document.getElementById('menu-icon');
      const closeIcon = document.getElementById('close-icon');
      
      menuToggle.addEventListener('click', () => {
        mobileMenu.classList.toggle('active');
        
        if (mobileMenu.classList.contains('active')) {
          menuIcon.style.display = 'none';
          closeIcon.style.display = 'block';
        } else {
          menuIcon.style.display = 'block';
          closeIcon.style.display = 'none';
        }
      });
      
      // Dropdown handling for desktop
      const dropdowns = document.querySelectorAll('.dropdown');
      
      dropdowns.forEach(dropdown => {
        dropdown.addEventListener('mouseenter', () => {
          const dropdownContent = dropdown.querySelector('.dropdown-content');
          dropdownContent.style.display = 'block';
        });
        
        dropdown.addEventListener('mouseleave', () => {
          const dropdownContent = dropdown.querySelector('.dropdown-content');
          dropdownContent.style.display = 'none';
        });
      });
      
      // Explore Features button scroll to section
      const exploreFeaturesBtn = document.getElementById('explore-features-btn');
      if (exploreFeaturesBtn) {
        exploreFeaturesBtn.addEventListener('click', (e) => {
          e.preventDefault();
          const featuresSection = document.getElementById('features-section');
          if (featuresSection) {
            featuresSection.scrollIntoView({ 
              behavior: 'smooth' 
            });
          }
        });
      }
      
      // Animation on scroll
      const animateOnScroll = () => {
        const elements = document.querySelectorAll('.feature-card, .testimonial-card');
        
        elements.forEach(element => {
          const elementTop = element.getBoundingClientRect().top;
          const elementBottom = element.getBoundingClientRect().bottom;
          const isVisible = (elementTop < window.innerHeight) && (elementBottom > 0);
          
          if (isVisible) {
            element.classList.add('animate-fade-in');
          }
        });
      };
      
      // Initialize animations
      window.addEventListener('load', animateOnScroll);
      window.addEventListener('scroll', animateOnScroll);
      
      // Signup button handling
      const signupButtons = document.querySelectorAll('.btn-primary:not(.btn-auth)');
      
      signupButtons.forEach(button => {
        button.addEventListener('click', (e) => {
          
          if (button.tagName === 'BUTTON') {
            e.preventDefault();
          }
          window.location.href = 'signup.php';
        });
      });

      const getStartedBtn = document.querySelector('.hero-buttons .btn-primary');
      if (getStartedBtn) {
        getStartedBtn.addEventListener('click', () => {
          window.location.href = 'signup.php';
        });
      }
    });

    
  </script>
</body>
</html>
