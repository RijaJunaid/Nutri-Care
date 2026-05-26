<!DOCTYPE html>
<html lang="en">
<head>
  <?php require 'Partials/nav.php';?>
<?php require 'Partials/head.php';?>
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

   

    /* Profile Page Specific Styles */
    .profile-section {
      padding: 3rem 0;
    }

    .profile-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 2rem;
    }

    .profile-title {
      font-size: 2rem;
      color: var(--green-dark);
    }

    .profile-container {
      display: grid;
      grid-template-columns: 1fr;
      gap: 2rem;
    }

    @media (min-width: 1024px) {
      .profile-container {
        grid-template-columns: 300px 1fr;
      }
    }
    .info-page {
      padding: 4rem 0;
      background-color: var(--green-light);
    }
    
    .info-section {
      background-color: white;
      border-radius: var(--border-radius);
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-sm);
    }
    
    .info-nav {
      position: sticky;
      top: 6rem;
      background-color: white;
      border-radius: var(--border-radius);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
      margin-bottom: 2rem;
    }
    
    .info-nav-list {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }
    
    .info-nav-link {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      padding: 0.75rem 1rem;
      color: var(--gray-700);
      border-radius: var(--border-radius);
      transition: all 0.2s ease;
    }
    
    .info-nav-link:hover, .info-nav-link.active {
      background-color: var(--green-light);
      color: var(--green-dark);
    }
    
    .info-nav-link i {
      width: 1.25rem;
      text-align: center;
    }
    
    .contact-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }
    
    .contact-card {
      background-color: var(--green-light);
      padding: 1.5rem;
      border-radius: var(--border-radius);
      display: flex;
      align-items: flex-start;
      gap: 1rem;
    }
    
    .contact-icon {
      background-color: var(--green);
      color: white;
      width: 3rem;
      height: 3rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    
    .faq-item {
      margin-bottom: 1.5rem;
      border-bottom: 1px solid var(--gray-200);
      padding-bottom: 1.5rem;
    }
    
    .faq-question {
      font-weight: 600;
      color: var(--green-dark);
      margin-bottom: 0.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
    }
    
    .faq-answer {
      color: var(--gray-700);
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
    }
    
    .faq-item.active .faq-answer {
      max-height: 500px;
    }
    
    @media (min-width: 768px) {
      .info-container {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
      }
      
      .info-nav {
        margin-bottom: 0;
      }
      
      .contact-grid {
        grid-template-columns: 1fr 1fr;
      }
    }
    
    @media (min-width: 1024px) {
      .contact-grid {
        grid-template-columns: 1fr 1fr 1fr;
      }
    }
    
  </style>
</head>
<body>
  
<?php
// Start session (if not already started in nav.php)
//if (session_status() === PHP_SESSION_NONE) {
  //  session_start();
//}

// Include database configuration
require 'config.php';

// Initialize variables
$name = $email = $message = '';
$name_err = $email_err = $message_err = '';
$success_msg = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email address.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $email = trim($_POST["email"]);
    }
        
    // Validate message
    if (empty(trim($_POST["message"]))) {
        $message_err = "Please enter your message.";
    } else {
        $message = trim($_POST["message"]);
    }
    
    // Check for errors before inserting
    if (empty($name_err) && empty($email_err) && empty($message_err)) {
        try {
            // Prepare SQL statement
            $sql = "INSERT INTO contact_messages (user_id, name, email, subject, message) 
                    VALUES (:user_id, :name, :email, :subject, :message)";
            
            // Get user_id if logged in
            $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : NULL;
            $subject = "Contact Form Submission";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':subject', $subject, PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $success_msg = "Your message has been sent successfully! We'll get back to you soon.";
                // Clear form fields
                $name = $email = $message = '';
            }
        } catch (PDOException $e) {
          $message_err = "Oops! Something went wrong. Please try again later. Error: " . $e->getMessage();
        }
    }
}
?>

  <main class="info-page">
    <div class="container">
      <div class="info-container">
        <!-- Navigation Sidebar -->
        <aside class="info-nav">
          <h3 class="section-title" style="margin-bottom: 1.5rem;">Quick Links</h3>
          <ul class="info-nav-list">
            <li>
              <a href="#about" class="info-nav-link active">
                <i data-feather="info"></i>
                About NutriCare
              </a>
            </li>
            <li>
              <a href="#contact" class="info-nav-link">
                <i data-feather="mail"></i>
                Contact Us
              </a>
            </li>
            <li>
              <a href="#help" class="info-nav-link">
                <i data-feather="help-circle"></i>
                Help & FAQ
              </a>
            </li>
          </ul>
        </aside>

        <!-- Main Content -->
        <div class="info-content">
          <!-- About Section -->
          <section id="about" class="info-section">
            <h2 class="section-title">
              <i data-feather="info" class="accent"></i>
              About NutriCare
            </h2>
            <div class="section-description">
              <p>NutriCare is revolutionizing personalized nutrition by combining medical science with artificial intelligence to deliver tailored dietary recommendations.</p>
              
              <h3 style="margin: 1.5rem 0 1rem; color: var(--green-dark);">Our Mission</h3>
              <p>To empower individuals to take control of their health through scientifically-backed, personalized nutrition plans that adapt to their unique needs and lifestyle.</p>
              
              <h3 style="margin: 1.5rem 0 1rem; color: var(--green-dark);">The Science Behind NutriCare</h3>
              <p>Our algorithms analyze over 50 health factors including medical conditions, allergies, metabolism, and lifestyle to create optimal nutrition plans. We collaborate with nutritionists and dietitians to ensure all recommendations meet clinical standards.</p>
              
              <div style="margin-top: 2rem; background-color: var(--green-light); padding: 1.5rem; border-radius: var(--border-radius);">
                <h3 style="margin-bottom: 1rem; color: var(--green-dark);">Key Features</h3>
                <ul style="list-style-type: disc; padding-left: 1.5rem; display: grid; gap: 0.75rem;">
                  <li>Condition-specific food recommendations</li>
                  <li>Smart food substitution system</li>
                  <li>Child nutrition tracking</li>
                  <li>Exercise synchronization</li>
                  <li>Nutritionist consultation portal</li>
                </ul>
              </div>
            </div>
          </section>

          <!-- Contact Section -->
          <section id="contact" class="info-section">
            <h2 class="section-title">
              <i data-feather="mail" class="accent"></i>
              Contact Us
            </h2>
            <div class="section-description">
              <p>Have questions or feedback? We'd love to hear from you.</p>
              
              <div class="contact-grid" style="margin-top: 2rem;">
                <!-- [Contact cards remain unchanged] -->
              </div>
              <div style="margin-top: 2rem;">
                <h3 style="margin-bottom: 1rem; color: var(--green-dark);">Send Us a Message</h3>
                
                <?php if (!empty($success_msg)): ?>
                  <div style="background-color: var(--green-light); color: var(--green-dark); padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
                    <?php echo $success_msg; ?>
                  </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>#contact" method="post" style="display: grid; gap: 1rem;">
                  <div class="form-group">
                    <label class="form-label">Your Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name" value="<?php echo htmlspecialchars($name); ?>">
                    <?php if (!empty($name_err)): ?>
                      <span style="color: #dc3545; font-size: 0.875rem;"><?php echo $name_err; ?></span>
                    <?php endif; ?>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" value="<?php echo htmlspecialchars($email); ?>">
                    <?php if (!empty($email_err)): ?>
                      <span style="color: #dc3545; font-size: 0.875rem;"><?php echo $email_err; ?></span>
                      <?php endif; ?>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="4" placeholder="Your message here"><?php echo htmlspecialchars($message); ?></textarea>
                    <?php if (!empty($message_err)): ?>
                      <span style="color: #dc3545; font-size: 0.875rem;"><?php echo $message_err; ?></span>
                    <?php endif; ?>
                  </div>
                  <button type="submit" class="btn btn-primary" style="justify-self: start;">Send Message</button>
                </form>
              </div>
            </div>
          </section>

          <!-- Help & FAQ Section -->
          <!-- Help & FAQ Section -->
<section id="help" class="info-section">
    <h2 class="section-title">
        <i data-feather="help-circle" class="accent"></i>
        Help & FAQ
    </h2>
    <div class="section-description">
        <p>Find answers to common questions about using NutriCare.</p>
        
        <div style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: var(--green-dark);">Frequently Asked Questions</h3>
            
            <?php
            // Fetch FAQs from database
            try {
                $stmt = $conn->query("SELECT * FROM faqs ORDER BY created_at DESC");
                $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($faqs)) {
                    echo '<p>No FAQs found.</p>';
                } else {
                    foreach ($faqs as $faq) {
                        echo '
                        <div class="faq-item">
                            <div class="faq-question">
                                <span>'.htmlspecialchars($faq['question']).'</span>
                                <i data-feather="chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>'.htmlspecialchars($faq['answer']).'</p>
                            </div>
                        </div>';
                    }
                }
            } catch (PDOException $e) {
                echo '<div style="background-color: var(--peach); color: #dc3545; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
                    Error loading FAQs: '.htmlspecialchars($e->getMessage()).'
                </div>';
            }
            ?>
        </div>
        
        <div style="margin-top: 3rem; background-color: var(--green-light); padding: 1.5rem; border-radius: var(--border-radius);">
            <h3 style="margin-bottom: 1rem; color: var(--green-dark);">Still need help?</h3>
            <p>Our support team is available 24/7 to assist you with any questions or technical issues.</p>
            <button class="btn btn-outline" style="margin-top: 1rem;">
                <i data-feather="mail"></i>
                Contact Support
            </button>
        </div>
    </div>
</section>
        </div>
      </div>
    </div>
  </main>

  
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
      
      
      // FAQ accordion functionality
      const faqQuestions = document.querySelectorAll('.faq-question');
      faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
          const faqItem = question.parentElement;
          faqItem.classList.toggle('active');
          
          // Rotate chevron icon
          const icon = question.querySelector('i');
          if (faqItem.classList.contains('active')) {
            icon.style.transform = 'rotate(180deg)';
          } else {
            icon.style.transform = 'rotate(0deg)';
          }
        });
      });
      
      // Smooth scrolling for nav links
      const navLinks = document.querySelectorAll('.info-nav-link');
      navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          const targetId = link.getAttribute('href');
          const targetSection = document.querySelector(targetId);
          
          // Update active nav link
          navLinks.forEach(navLink => navLink.classList.remove('active'));
          link.classList.add('active');
          
          // Scroll to section
          targetSection.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        });
      });
      
      // Update active nav link on scroll
      const sections = document.querySelectorAll('.info-section');
      window.addEventListener('scroll', () => {
        let current = '';
        
        sections.forEach(section => {
          const sectionTop = section.offsetTop;
          const sectionHeight = section.clientHeight;
          
          if (pageYOffset >= (sectionTop - 100)) {
            current = section.getAttribute('id');
          }
        });
        
        navLinks.forEach(link => {
          link.classList.remove('active');
          if (link.getAttribute('href') === `#${current}`) {
            link.classList.add('active');
          }
        });
      });
    });
  </script>
</body>
<?php require 'Partials/footer.php';?>
</html>