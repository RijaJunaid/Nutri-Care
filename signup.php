<?php
include 'config.php';
session_start();

$message = []; // Initialize message array

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

try {
    // Handle Signup
    if (isset($_POST['signup'])) {
        $name = filter_var($_POST['signup-name'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['signup-email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['signup-password'];
        $confirm_password = $_POST['signup-confirm'];

        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            $message[] = 'Please fill in all fields!';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message[] = 'Invalid email format!';
        } elseif ($password !== $confirm_password) {
            $message[] = 'Passwords do not match!';
        } elseif (strlen($password) < 6) {
            $message[] = 'Password must be at least 6 characters!';
        } else {
            $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
            $check_email->execute([$email]);

            if ($check_email->rowCount() > 0) {
                $message[] = 'Email already exists!';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_user = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $insert_success = $insert_user->execute([$name, $email, $hashed_password]);

                if ($insert_success) {
                    $user_id = $conn->lastInsertId();
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    // Redirect to profile completion
                    header('Location: profileform.php');
                    exit;
                } else {
                    $message[] = 'Registration failed. Please try again!';
                }
            }
        }
    }
} catch (PDOException $e) {
    $message[] = 'Database error: ' . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php require 'Partials/nav.php';?>
<?php require 'Partials/head.php';?>

  <style>
    /* Reuse your NutriCare variables */
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

    /* Signup Specific Styles */
    .auth-section {
      background-color: var(--green-light);
      min-height: calc(100vh - 8rem);
      padding: 4rem 0;
    }

    .auth-container {
      max-width: 500px;
      margin: 0 auto;
      background-color: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-md);
      overflow: hidden;
    }

    .auth-content {
      padding: 2rem;
    }

    .auth-title {
      font-size: 1.5rem;
      color: var(--green-dark);
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
      color: var(--gray-700);
      font-weight: 500;
    }

    .form-control {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 1px solid var(--gray-300);
      border-radius: var(--border-radius);
      font-family: var(--font-sans);
      font-size: 1rem;
      transition: border-color 0.2s ease;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(95, 182, 90, 0.1);
    }

    .auth-footer {
      text-align: center;
      margin-top: 1.5rem;
      color: var(--gray-600);
    }

    .auth-link {
      color: var(--green);
      font-weight: 500;
    }

    .auth-link:hover {
      color: var(--green-dark);
      text-decoration: underline;
    }

    .btn-auth {
      width: 100%;
      padding: 0.75rem;
      font-size: 1rem;
      font-weight: 600;
    }

    .divider {
      display: flex;
      align-items: center;
      margin: 1.5rem 0;
      color: var(--gray-500);
    }

    .divider::before,
    .divider::after {
      content: "";
      flex: 1;
      border-bottom: 1px solid var(--gray-300);
    }

    .divider-text {
      padding: 0 1rem;
    }

    .social-login {
      display: flex;
      justify-content: center;
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .social-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 3rem;
      height: 3rem;
      border-radius: 50%;
      background-color: white;
      border: 1px solid var(--gray-300);
      color: var(--gray-700);
      transition: all 0.2s ease;
    }

    .social-btn:hover {
      background-color: var(--gray-100);
      border-color: var(--gray-400);
    }
  </style>
</head>
<body>
  <!-- Auth Section -->
  <section class="auth-section">
    <div class="container">
      <div class="auth-container">
        <div class="auth-content">
          <!-- Display messages -->
          <?php if (!empty($message)): ?>
            <div class="error-message">
              <?php foreach ($message as $msg): ?>
                <p><?php echo htmlspecialchars($msg); ?></p>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Signup Form -->
          <form class="auth-form" id="signup-form" method="POST" action="">
            <h3 class="auth-title">Create Your Account</h3>
            
            <div class="form-group">
              <label class="form-label" for="signup-name">Full Name</label>
              <input type="text" id="signup-name" name="signup-name" class="form-control" placeholder="Enter your full name" required>
            </div>
            
            <div class="form-group">
              <label class="form-label" for="signup-email">Email</label>
              <input type="email" id="signup-email" name="signup-email" class="form-control" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
              <label class="form-label" for="signup-password">Password</label>
              <input type="password" id="signup-password" name="signup-password" class="form-control" placeholder="Create a password" required>
            </div>
            
            <div class="form-group">
              <label class="form-label" for="signup-confirm">Confirm Password</label>
              <input type="password" id="signup-confirm" name="signup-confirm" class="form-control" placeholder="Confirm your password" required>
            </div>
            
            <div class="form-group">
              <button type="submit" name="signup" class="btn btn-primary btn-auth">Create Account</button>
            </div>
            
            <div class="divider">
              <span class="divider-text">or</span>
            </div>
            
            <div class="social-login">
              <button type="button" class="social-btn">
                <i class="fab fa-google"></i>
              </button>
              <button type="button" class="social-btn">
                <i class="fab fa-facebook-f"></i>
              </button>
              <button type="button" class="social-btn">
                <i class="fab fa-apple"></i>
              </button>
            </div>
            
            <div class="auth-footer">
              Already have an account? <a href="login.php" class="auth-link">Log in</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      feather.replace();
      
      // Set current year in footer
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
    });
  </script>
 
</body>

<?php require 'Partials/footer.php';?>
</html>