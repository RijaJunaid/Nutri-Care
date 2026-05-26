<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NutriCare - Your Health, Your Diet, Your Way</title>
  <link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap">
  <!--  Font Awesome for the exchange icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://unpkg.com/feather-icons"></script> <!-- Feather Icons -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&display=swap" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <style>
    /* Base Styles */
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

    /* Reset and Base Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: var(--font-sans);
      color: var(--gray-800);
      background-color: hsl(120, 40%, 98%);
      line-height: 1.5;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    ul,
    ol {
      list-style: none;
    }

    button {
      background: none;
      border: none;
      cursor: pointer;
      font-family: inherit;
      font-size: inherit;
    }

    img {
      max-width: 100%;
      height: auto;
      display: block;
    }

    /* Container */
    .container {
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 1rem;
    }

    /* Typography */
    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
      font-family: var(--font-display);
      font-weight: 600;
    }

    .section-title {
      font-size: 1.875rem;
      margin-bottom: 1rem;
      color: var(--gray-800);
    }

    .section-description {
      font-size: 1.125rem;
      color: var(--gray-600);
      max-width: 42rem;
      margin: 0 auto 2rem;
    }

    .accent {
      color: var(--green-dark);
    }

    /* Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.625rem 1rem;
      border-radius: var(--border-radius);
      font-weight: 500;
      transition: all 0.2s ease;
      font-size: 0.875rem;
    }

    .btn-primary {
      background-color: var(--green);
      color: white;
      border: 1px solid var(--green);
    }

    .btn-primary:hover {
      background-color: var(--green-dark);
      border-color: var(--green-dark);
    }

    .btn-outline {
      background-color: transparent;
      color: var(--green);
      border: 1px solid var(--green);
    }

    .btn-outline:hover {
      background-color: var(--green-light);
    }

    .btn-full {
      width: 100%;
    }

    .footer {
      background-color: white;
      border-top: 1px solid var(--green-light);
      padding-top: 3rem;
    }

    .footer-content {
      display: grid;
      grid-template-columns: 1fr;
      gap: 2rem;
      padding-bottom: 3rem;
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
    }

    .footer-description {
      color: var(--gray-600);
      margin-bottom: 1rem;
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
      gap: 0.5rem;
    }

    .footer-list li a {
      color: var(--gray-600);
      transition: color 0.2s ease;
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
    }

    .contact-item a:hover {
      color: var(--green);
    }

    .footer-bottom {
      border-top: 1px solid rgba(95, 182, 90, 0.15);
      padding: 1.5rem 0;
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
    }

    .legal-links a:hover {
      color: var(--green);
    }

    /* Responsive adjustments */
    @media (min-width: 640px) {
      .footer-content {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (min-width: 768px) {
      .footer-content {
        grid-template-columns: repeat(3, 1fr);
      }

      .footer-bottom {
        flex-direction: row;
        justify-content: space-between;
      }
    }

    @media (min-width: 1024px) {
      .footer-content {
        grid-template-columns: 1fr 1fr 1fr 1fr;
      }
    }
  </style>
</head>

<!-- Footer -->

<body>
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
          <p class="footer-description">Your personalized nutrition and health companion. We make healthy eating easy
            and accessible for everyone.</p>
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
              <a href="tel:+11234567890">+1 (123) 456-7890</a>
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
          <a href="Login.php">User Login</a>
          
        </div>
      </div>
    </div>
  </footer>
  <script src="https://unpkg.com/feather-icons"></script>
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

      //  animation class to elements as they come into view
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


      window.addEventListener('load', animateOnScroll);
      window.addEventListener('scroll', animateOnScroll);
    });
  </script>


</body>


</html>
