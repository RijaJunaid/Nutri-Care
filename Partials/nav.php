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
   
    :root {
      --green-light: #E3F4E1;
      --green: #5FB65A;
      --green-dark: #3C8D37;
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

    ul, ol {
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
    h1, h2, h3, h4, h5, h6 {
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

    /* Navbar (from WEB.html) */
    .custom-navbar {
      background-color: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(5px);
      position: sticky;
      top: 0;
      z-index: 50;
      box-shadow: var(--shadow-sm);
      border-bottom: 1px solid var(--green-light);
    }

    .navbar-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 4rem;
      max-width: 1280px;
      margin: 0 auto;
      padding: 0 1rem;
    }

    .navbar-logo {
      display: flex;
      align-items: center;
    }

    .logo-link {
      display: flex;
      align-items: center;
    }

    .logo-icon {
      background-color: var(--green);
      color: white;
      padding: 0.25rem;
      border-radius: 0.25rem;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 0.5rem;
    }

    @keyframes textclip {
      to {
        background-position: 200% center;
      }
    }

    .logo-text.poppins {
      font-family: 'Poppins', sans-serif;
      font-size: 2rem;
      font-weight: 600;
      background: linear-gradient(to right, #1a472a 10%, #3c8c4a 50%, #75d69c 60%);
      background-size: 200% auto;
      background-clip: text;
      -webkit-background-clip: text;
      color: transparent;
      -webkit-text-fill-color: transparent;
      animation: textclip 1.5s linear infinite;
    }

    .desktop-nav {
      display: none;
    }

    @media (min-width: 1024px) {
      .desktop-nav {
        display: flex;
        align-items: center;
        gap: 1rem;
      }
    }

    /* User Profile Button */
    .user-profile-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 2.5rem;
      height: 2.5rem;
      border-radius: 50%;
      background-color: var(--green-light);
      color: var(--green-dark);
      transition: all 0.2s ease;
      margin-left: 0.75rem;
    }

    .user-profile-btn:hover {
      background-color: var(--green);
      color: white;
    }


    /* Dropdown Menu */
    .dropdown {
      position: relative;
      display: inline-block;
    }

    .dropdown-button {
      display: flex;
      align-items: center;
      padding: 0.5rem 1rem;
      color: var(--green-dark);
      font-weight: 500;
    }

    .dropdown-content {
      display: none;
      position: absolute;
      top: 100%;
      left: 0;
      min-width: 14rem;
      background-color: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-md);
      z-index: 1;
      padding: 0.5rem 0;
    }

    .dropdown-item {
      display: flex;
      align-items: center;
      padding: 0.75rem 1rem;
      color: var(--gray-700);
    }

    .dropdown-item i {
      margin-right: 0.5rem;
      color: var(--green);
    }

    .dropdown-item:hover {
      background-color: var(--green-light);
    }

    /* Login Dropdown Specific Styles */
    .login-dropdown .dropdown-content {
      min-width: 10rem;
      right: 0;
      left: auto;
    }

    /* Mobile Menu */
    .mobile-menu-btn {
      display: flex;
    }

    @media (min-width: 1024px) {
      .mobile-menu-btn {
        display: none;
      }
    }

    .mobile-menu {
      display: none;
      background-color: white;
      position: absolute;
      width: 100%;
      left: 0;
      top: 4rem;
      box-shadow: var(--shadow-md);
      z-index: 1000;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .mobile-menu.active {
      display: block;
      max-height: 1000px;
      border-bottom: 1px solid var(--green-light);
    }

    .mobile-menu-container {
      padding: 1rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    .mobile-menu-section {
      padding: 0.75rem 0;
      border-bottom: 1px solid var(--green-light);
    }

    .mobile-menu-section:last-child {
      border-bottom: none;
    }

    .mobile-menu-heading {
      font-size: 0.875rem;
      font-weight: 500;
      color: var(--green-dark);
      margin-bottom: 0.5rem;
      padding: 0 0.5rem;
    }

    .mobile-menu-item {
      display: flex;
      align-items: center;
      padding: 0.75rem 0.5rem;
      color: var(--gray-700);
      border-radius: var(--border-radius);
    }

    .mobile-menu-item:hover {
      background-color: var(--green-light);
    }

    .mobile-menu-item i {
      margin-right: 0.5rem;
    }

    .nav-link {
      padding: 0.5rem 1rem;
      color: var(--green-dark);
      font-weight: 500;
    }

    .nav-link:hover {
      color: var(--green);
    }

    /* Mobile nav buttons */
    .navbar-buttons-mobile {
      display: flex;
      gap: 0.5rem;
    }

    .navbar-buttons-mobile .btn {
      padding: 0.4rem 0.75rem;
      font-size: 0.875rem;
    }

    @media (min-width: 1024px) {
      .navbar-buttons-mobile {
        display: none;
      }
    }

    @media (max-width: 1023px) {
      .mobile-menu-section:last-child {
        display: block;
      }
    }
    </style>
    </head>
<body>
  <!-- Navbar (from WEB.html) -->
  <nav class="custom-navbar">
    <div class="navbar-container">
      <div class="navbar-logo">
        <a href="home.php" class="logo-link">
          <div class="logo-icon">
            <i data-feather="home"></i>
          </div>
          <span class="logo-text poppins">NutriCare</span>
        </a>
      </div>

      <!-- Desktop Navigation -->
      <div class="desktop-nav">
        <div class="dropdown">
          <button class="dropdown-button">
            Features
          </button>
          <div class="dropdown-content">
            <a id="foodRecommendationsLink" href="foodrecommendations.php" class="dropdown-item"><i class="fas fa-exchange-alt"></i> Food Recommendations</a>
           <a id="foodSwapLink" href="foodswap.php" class="dropdown-item"><i class="fas fa-sync-alt"></i> Food Swaps</a>
            <a id="childNutritionLink" href="childnutrition.php" class="dropdown-item"><i class="fas fa-baby"></i> Child Nutrition</a>
           <a id="exerciseLink" href="exercise.php" class="dropdown-item"><i class="fas fa-running"></i> Exercise Tips</a>
            <a href="add_review.php" class="dropdown-item"><i class="fas fa-calendar-alt"></i> Reviews</a>
         <a id="nutritionalBreakdownLink" href="nutritionalbreakdown.php" class="dropdown-item"><i class="fas fa-chart-pie"></i> Nutritional Breakdown</a>
          <a id="premiumLink" href="premium.php" class="dropdown-item"><i class="fas fa-user-plus"></i> Premium Consult</a>
          </div>
        </div>

        <a href="contact.php" class="nav-link">About</a>
     <a id="contactLink" href="contact.php" class="nav-link">Contact</a>
      <a id="helpLink" href="contact.php" class="nav-link">Help</a>

        <!-- Login Dropdown -->
        <div class="dropdown login-dropdown">
          <button class="btn btn-outline" id="loginButtonNav">Log In</button>
          <div class="dropdown-content">
            <a href="login.php" class="dropdown-item"><i class="fas fa-user"></i> User Login</a>
            <a href="admin_login.php" class="dropdown-item"><i class="fas fa-user-shield"></i> Admin Login</a>
          </div>
        </div>
        
       <button class="btn btn-primary" id="signupButtonNav">Sign Up</button>
        <a href="profileform.php" class="user-profile-btn"><i data-feather="user"></i></a>
      </div>

      <!-- Mobile menu button -->
      <div class="mobile-menu-btn">
        <button id="menu-toggle">
          <i data-feather="menu" id="menu-icon"></i>
          <i data-feather="x" id="close-icon" style="display: none;"></i>
        </button>
      </div>

      <!-- Mobile login/signup buttons -->
      <div class="navbar-buttons-mobile">
        <div class="dropdown">
          <button id="loginButtonMobile" class="btn btn-outline">Log In</button>
          <div class="dropdown-content">
            <a href="login.php" class="dropdown-item"><i class="fas fa-user"></i> User Login</a>
            <a href="admin_login.php" class="dropdown-item"><i class="fas fa-user-shield"></i> Admin Login</a>
          </div>
        </div>
        <button class="btn btn-primary" id="signupButtonMobile">Sign Up</button>
        <a href="profileform.php" class="user-profile-btn">
          <i data-feather="user"></i>
        </a>
      </div>
    </div>

    <!-- Mobile menu -->
    <div class="mobile-menu" id="mobile-menu">
      <div class="mobile-menu-container">
        <div class="mobile-menu-section">
          <p class="mobile-menu-heading">Features</p>
          <a id="foodRecommendationsLink" href="foodrecommendations.php" class="mobile-menu-item"><i class="fas fa-exchange-alt"></i> Food Recommendations</a>
         <a id="foodSwapLink" href="foodswap.php" class="mobile-menu-item"><i class="fas fa-sync-alt"></i> Food Swaps</a>
          <a id="childNutritionLink" href="childnutrition.php" class="mobile-menu-item"><i class="fas fa-baby"></i> Child Nutrition</a>
         <a id="exerciseLink" href="exercise.php" class="mobile-menu-item"><i class="fas fa-running"></i> Exercise Tips</a>
          <a href="add_review.php" class="mobile-menu-item"><i class="fas fa-calendar-alt"></i> Reviews </a>
          <a id="nutritionalBreakdownLink" href="nutritionalbreakdown.php" class="mobile-menu-item"><i class="fas fa-chart-pie"></i> Nutritional Breakdown</a>
         <a id="premiumLink" href="premium.php" class="mobile-menu-item"><i class="fas fa-user-plus"></i> Premium Consult</a>
        </div>
        
        <div class="mobile-menu-section">
          <a href="contact.php" class="mobile-menu-item"><i data-feather="info"></i> About</a>
        <a id="contactLink" href="contact.php" class="mobile-menu-item"><i data-feather="mail"></i> Contact</a>
          <a href="contact.php" class="mobile-menu-item"><i data-feather="help-circle"></i> Help</a>
        </div>

        <div class="mobile-menu-section">
          <a href="login.php" class="mobile-menu-item"><i class="fas fa-user"></i> User Login</a>
          <a href="admin_login.php" class="mobile-menu-item"><i class="fas fa-user-shield"></i> Admin Login</a>
          <a href="profileform.php" class="mobile-menu-item"><i data-feather="user"></i> Profile</a>
        </div>
      </div>
    </div>
  </nav>
  </body>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      feather.replace();
      
      // Mobile menu toggle
      const menuToggle = document.getElementById('menu-toggle');
      const mobileMenu = document.getElementById('mobile-menu');
      const menuIcon = document.getElementById('menu-icon');
      const closeIcon = document.getElementById('close-icon');
      
      menuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const isActive = mobileMenu.classList.contains('active');
        
        if (isActive) {
          mobileMenu.classList.remove('active');
          menuIcon.style.display = 'block';
          closeIcon.style.display = 'none';
        } else {
          mobileMenu.classList.add('active');
          menuIcon.style.display = 'none';
          closeIcon.style.display = 'block';
        }
      });
      
      // Close mobile menu when clicking outside
      document.addEventListener('click', (e) => {
        if (!mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) {
          mobileMenu.classList.remove('active');
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

      // Mobile login dropdown
      const mobileLoginBtn = document.getElementById('loginButtonMobile');
      const mobileLoginDropdown = mobileLoginBtn.nextElementSibling;
      
      mobileLoginBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        mobileLoginDropdown.style.display = mobileLoginDropdown.style.display === 'block' ? 'none' : 'block';
      });
      
      // Close mobile dropdown when clicking elsewhere
      document.addEventListener('click', () => {
        if (mobileLoginDropdown) {
          mobileLoginDropdown.style.display = 'none';
        }
      });
    });
    
    // Navigation links
    document.getElementById("foodRecommendationsLink").onclick = () => window.location.href = "foodrecommendations.php";
    document.getElementById("childNutritionLink").onclick = () => window.location.href = "childnutrition.php";
    document.getElementById("contactLink").onclick = () => window.location.href = "contact.php";
    document.getElementById("exerciseLink").onclick = () => window.location.href = "exercise.php";
    document.getElementById("foodSwapLink").onclick = () => window.location.href = "foodswap.php";
    document.getElementById("helpLink").onclick = () => window.location.href = "contact.php";
    document.getElementById("nutritionalBreakdownLink").onclick = () => window.location.href = "nutritionalbreakdown.php";
    document.getElementById("premiumLink").onclick = () => window.location.href = "premium.php";
    document.getElementById("signupButtonNav").onclick = () => window.location.href = "signup.php";
    document.getElementById("signupButtonMobile").onclick = () => window.location.href = "signup.php";

    // Update navigation based on login status
    document.addEventListener('DOMContentLoaded', () => {
        // Check if user is logged in (you might want to use PHP session instead)
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        
        if (isLoggedIn) {
            // Replace login/signup buttons with profile and logout
            document.querySelectorAll('.login-dropdown').forEach(el => el.style.display = 'none');
            document.getElementById('signupButtonNav').style.display = 'none';
            
            // Add logout button
            const logoutBtn = document.createElement('button');
            logoutBtn.className = 'btn btn-outline';
            logoutBtn.id = 'logoutButtonNav';
            logoutBtn.innerHTML = 'Logout';
            logoutBtn.onclick = () => window.location.href = 'logout.php';
            
            const desktopNav = document.querySelector('.desktop-nav');
            desktopNav.appendChild(logoutBtn);
            
            // For mobile
            document.getElementById('loginButtonMobile').parentNode.style.display = 'none';
            document.getElementById('signupButtonMobile').style.display = 'none';
            
            const mobileLogoutBtn = document.createElement('button');
            mobileLogoutBtn.className = 'btn btn-outline';
            mobileLogoutBtn.id = 'logoutButtonMobile';
            mobileLogoutBtn.innerHTML = 'Logout';
            mobileLogoutBtn.onclick = () => window.location.href = 'logout.php';
            
            const mobileButtons = document.querySelector('.navbar-buttons-mobile');
            mobileButtons.appendChild(mobileLogoutBtn);
        }
    });
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

  </script>
</html>
