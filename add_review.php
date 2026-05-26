<?php
session_start();
require 'config.php'; // ensures $conn is defined

$error = '';
$success = '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'] ?? '';
    $comment = $_POST['comment'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Validate input
    if (empty($rating) || $rating < 1 || $rating > 5) {
        $error = 'Please select a valid rating between 1 and 5 stars.';
    } else {
        try {
            // Insert review into database
            $stmt = $conn->prepare("INSERT INTO website_reviews (user_id, rating, comment) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $rating, $comment]);
            $success = 'Thank you for your review!';
        } catch (PDOException $e) {
            $error = 'Error submitting your review. Please try again later.';
            // Uncomment the line below for debugging
            // $error .= ' Debug: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <?php require 'Partials/head.php'; ?>
  <title>Add Your Review - NutriCare</title>
  <style>
    .review-container {
      max-width: 600px;
      margin: 2rem auto;
      padding: 2rem;
      background-color: white;
      border-radius: 0.75rem;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .rating-stars {
      display: flex;
      justify-content: center;
      margin: 1rem 0;
      font-size: 2rem;
      direction: rtl; /* This makes the stars fill from left when clicked */
    }

    .rating-stars input {
      display: none;
    }

    .rating-stars label {
      color: #ccc;
      cursor: pointer;
      transition: color 0.2s;
    }

    .rating-stars input:checked ~ label,
    .rating-stars label:hover,
    .rating-stars label:hover ~ label {
      color: #f8d64e;
    }

    textarea {
      width: 100%;
      min-height: 150px;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 0.5rem;
      resize: vertical;
      margin-bottom: 1rem;
    }

    .btn-submit {
      background-color: #4CAF50;
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 0.5rem;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.3s;
      width: 100%;
    }

    .btn-submit:hover {
      background-color: #388e3c;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .error {
      color: #e74c3c;
      margin-bottom: 1rem;
    }

    .success {
      color: #2e7d32;
      margin-bottom: 1.5rem;
      text-align: center;
      font-size: 1.2rem;
      font-weight: 600;
    }

    .back-btn {
      display: inline-block;
      margin-bottom: 1rem;
      padding: 0.75rem 1.5rem;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 0.5rem;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s;
      text-align: center;
    }

    .back-btn:hover {
      background-color: #388e3c;
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .btn-container {
      margin-top: 1.5rem;
    }

    .success-container {
      text-align: center;
      padding: 2rem 0;
    }

    .form-title {
      margin-bottom: 1.5rem;
      text-align: center;
      color: #2c3e50;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #2c3e50;
    }
  </style>
</head>
<body>
  <?php require 'Partials/nav.php'; ?>

  <main>
    <div class="review-container">
      <?php if (!$success): ?>
        <a href="home.php" class="back-btn">← Back to Home</a>
      <?php endif; ?>
      
      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="success-container">
          <div class="success"><?php echo htmlspecialchars($success); ?></div>
          <div class="btn-container">
            <a href="home.php" class="btn-submit">Back to Home</a>
          </div>
        </div>
      <?php else: ?>
        <h1 class="form-title">Add Your Review</h1>
        <form method="POST">
          <div class="form-group">
            <label for="rating">Your Rating:</label>
            <div class="rating-stars">
              <input type="radio" id="star5" name="rating" value="5" />
              <label for="star5">★</label>
              <input type="radio" id="star4" name="rating" value="4" />
              <label for="star4">★</label>
              <input type="radio" id="star3" name="rating" value="3" />
              <label for="star3">★</label>
              <input type="radio" id="star2" name="rating" value="2" />
              <label for="star2">★</label>
              <input type="radio" id="star1" name="rating" value="1" />
              <label for="star1">★</label>
            </div>
          </div>

          <div class="form-group">
            <label for="comment">Your Review:</label>
            <textarea id="comment" name="comment" placeholder="Share your experience with NutriCare..."></textarea>
          </div>

          <div class="btn-container">
            <button type="submit" class="btn-submit">Submit Review</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </main>

  <?php require 'Partials/footer.php'; ?>

  <script>
    // Enhanced star rating interaction
    document.addEventListener('DOMContentLoaded', () => {
      const stars = document.querySelectorAll('.rating-stars input');
      const labels = document.querySelectorAll('.rating-stars label');
      
      // Highlight stars on hover
      labels.forEach(label => {
        label.addEventListener('mouseover', () => {
          const hoverValue = label.getAttribute('for').replace('star', '');
          highlightStars(hoverValue);
        });
        
        label.addEventListener('mouseout', () => {
          const checkedInput = document.querySelector('.rating-stars input:checked');
          if (checkedInput) {
            highlightStars(checkedInput.value);
          } else {
            resetStars();
          }
        });
      });
      
      // Highlight stars when one is selected
      stars.forEach(star => {
        star.addEventListener('change', () => {
          highlightStars(star.value);
        });
      });
      
      function highlightStars(value) {
        labels.forEach(label => {
          const starValue = label.getAttribute('for').replace('star', '');
          if (starValue <= value) {
            label.style.color = '#f8d64e';
          } else {
            label.style.color = '#ccc';
          }
        });
      }
      
      function resetStars() {
        labels.forEach(label => {
          label.style.color = '#ccc';
        });
      }
    });
  </script>
</body>
</html>
