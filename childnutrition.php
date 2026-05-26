<?php
// Start session and database connection
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user data
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT child_mode FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

// Check if child mode is enabled
$childModeEnabled = $userData && $userData['child_mode'];

// Get selected age groups from session
$selectedAgeGroups = $_SESSION['child_age_groups'] ?? [];

// If only one age group is selected, use it; otherwise show the first one
$currentAgeGroup = count($selectedAgeGroups) === 1 ? $selectedAgeGroups[0] : 
                  (count($selectedAgeGroups) > 1 ? $selectedAgeGroups[0] : null);

// Map age group values to display text
$ageGroupMap = [
    '0-3' => '0-3 years',
    '4-8' => '4-8 years',
    '9-13' => '9-13 years',
    '14-18' => '14-18 years'
];

// Get display text for current age group
$currentAgeGroupText = isset($ageGroupMap[$currentAgeGroup]) ? 
                      $ageGroupMap[$currentAgeGroup] : '0-3 years';

// Fetch all data for the current age group
$recommendedFoods = [];
$avoidFoods = [];
$nutritionTips = [];
$ageGroupDescription = '';

if ($childModeEnabled && $currentAgeGroupText) {
    // Fetch recommended foods
    $stmt = $conn->prepare("
        SELECT f.*, cnr.recommendation, cnr.nutritional_benefits, cnr.serving_suggestion 
        FROM child_nutrition_recommendations cnr
        JOIN foods f ON cnr.food_id = f.food_id
        WHERE cnr.age_group = ?
    ");
    $stmt->execute([$currentAgeGroupText]);
    $recommendedFoods = $stmt->fetchAll();

    // Fetch foods to avoid
    $stmt = $conn->prepare("
        SELECT * FROM child_nutrition_avoid_foods
        WHERE age_group = ?
    ");
    $stmt->execute([$currentAgeGroupText]);
    $avoidFoods = $stmt->fetchAll();

    // Fetch nutrition tips
    $stmt = $conn->prepare("
        SELECT tip_text FROM child_nutrition_tips
        WHERE age_group = ?
        ORDER BY tip_id
    ");
    $stmt->execute([$currentAgeGroupText]);
    $nutritionTips = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Fetch age group description
    $stmt = $conn->prepare("
        SELECT description_text FROM child_nutrition_descriptions
        WHERE age_group = ?
        LIMIT 1
    ");
    $stmt->execute([$currentAgeGroupText]);
    $ageGroupDescription = $stmt->fetchColumn();
}
?>

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
    }

    /* Child Nutrition Specific Styles */
    .child-nutrition-section {
      padding: 4rem 1rem;
      background-color: rgba(227, 244, 225, 0.3);
    }

    .age-group-header {
      text-align: center;
      margin-bottom: 2rem;
    }

    .age-group-title {
      font-size: 2rem;
      color: var(--green-dark);
      margin-bottom: 0.5rem;
    }

    .age-group-description {
      color: var(--gray-600);
      max-width: 800px;
      margin: 0 auto;
    }

    .nutrition-categories {
      display: grid;
      grid-template-columns: 1fr;
      gap: 2rem;
      margin-bottom: 3rem;
    }

    @media (min-width: 768px) {
      .nutrition-categories {
        grid-template-columns: 1fr 1fr;
      }
    }

    .category-card {
      background-color: white;
      border-radius: var(--border-radius);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(95, 182, 90, 0.15);
    }

    .category-title {
      font-size: 1.25rem;
      color: var(--green-dark);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
    }

    .category-title i {
      margin-right: 0.5rem;
      color: var(--green);
    }

    .food-list {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1rem;
    }

    .food-card {
      display: flex;
      background-color: var(--gray-100);
      border-radius: calc(var(--border-radius) - 0.25rem);
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .food-card:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-md);
    }

    .food-image {
      width: 100px;
      height: 100px;
      object-fit: cover;
    }

    .food-info {
      flex: 1;
      padding: 0.75rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .food-name {
      font-weight: 600;
      color: var(--gray-800);
      margin-bottom: 0.25rem;
    }

    .food-description {
      font-size: 0.875rem;
      color: var(--gray-600);
      margin-bottom: 0.5rem;
    }

    .food-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .food-benefits {
      font-size: 0.75rem;
      color: var(--green-dark);
      background-color: var(--green-light);
      padding: 0.25rem 0.5rem;
      border-radius: 9999px;
    }

    .btn-favorite {
      background: none;
      border: none;
      color: var(--gray-500);
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-favorite:hover, .btn-favorite.active {
      color: var(--green);
    }

    .btn-favorite i {
      font-size: 1.25rem;
    }

    .nutrition-tips {
      background-color: white;
      border-radius: var(--border-radius);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
      border: 1px solid rgba(211, 228, 253, 0.4);
      margin-top: 2rem;
    }

    .tips-title {
      font-size: 1.25rem;
      color: var(--gray-800);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
    }

    .tips-title i {
      margin-right: 0.5rem;
      color: var(--green);
    }

    .tips-list {
      list-style-type: none;
      padding-left: 0;
    }

    .tips-list li {
      padding: 0.5rem 0;
      border-bottom: 1px solid var(--gray-200);
      display: flex;
    }

    .tips-list li:last-child {
      border-bottom: none;
    }

    .tips-list li i {
      color: var(--green);
      margin-right: 0.5rem;
      flex-shrink: 0;
      margin-top: 0.25rem;
    }

    .not-enabled-message {
      text-align: center;
      padding: 4rem 1rem;
      background-color: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow-sm);
      max-width: 800px;
      margin: 0 auto;
    }

    .not-enabled-message i {
      font-size: 3rem;
      color: var(--gray-400);
      margin-bottom: 1rem;
    }

    .not-enabled-message h2 {
      color: var(--gray-700);
      margin-bottom: 1rem;
    }

    .not-enabled-message p {
      color: var(--gray-600);
      margin-bottom: 1.5rem;
    }

    .btn-enable {
      background-color: var(--green);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: var(--border-radius);
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-enable:hover {
      background-color: var(--green-dark);
    }
  </style>
</head>
<body>
  <main>
    <!-- Child Nutrition Section -->
    <section class="child-nutrition-section">
      <div class="container">
        <?php if ($childModeEnabled && $currentAgeGroup): ?>
          <!-- Dynamic content based on selected age group -->
          <div class="age-group-header">
            <h1 class="age-group-title">Nutrition for Children (<?= htmlspecialchars($currentAgeGroupText) ?>)</h1>
            <p class="age-group-description">
              <?= htmlspecialchars($ageGroupDescription) ?>
            </p>
          </div>

          <!-- Nutrition Categories Grid -->
          <div class="nutrition-categories">
            <!-- Recommended Foods -->
            <div class="category-card">
              <h2 class="category-title">
                <i class="fas fa-thumbs-up"></i> Recommended Foods
              </h2>
              <div class="food-list">
                <?php foreach ($recommendedFoods as $food): ?>
                  <div class="food-card">
                    <img src="<?= htmlspecialchars($food['image_url']) ?>" 
                         alt="<?= htmlspecialchars($food['name']) ?>" class="food-image">
                    <div class="food-info">
                      <div>
                        <h3 class="food-name"><?= htmlspecialchars($food['name']) ?></h3>
                        <p class="food-description"><?= htmlspecialchars($food['recommendation']) ?></p>
                      </div>
                      <div class="food-actions">
                        <span class="food-benefits"><?= htmlspecialchars($food['nutritional_benefits']) ?></span>
                        <button class="btn-favorite" title="Add to favorites">
                          <i class="far fa-heart"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Foods to Avoid -->
            <div class="category-card">
              <h2 class="category-title">
                <i class="fas fa-ban"></i> Foods to Avoid
              </h2>
              <div class="food-list">
                <?php foreach ($avoidFoods as $food): ?>
                  <div class="food-card">
                    <img src="<?= htmlspecialchars($food['image_url']) ?>" 
                         alt="<?= htmlspecialchars($food['name']) ?>" class="food-image">
                    <div class="food-info">
                      <div>
                        <h3 class="food-name"><?= htmlspecialchars($food['name']) ?></h3>
                        <p class="food-description"><?= htmlspecialchars($food['reason']) ?></p>
                      </div>
                      <div class="food-actions">
                        <span class="food-benefits">Avoid</span>
                        <button class="btn-favorite" title="Add to avoid list">
                          <i class="far fa-heart"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Nutrition Tips -->
          <div class="nutrition-tips">
            <h2 class="tips-title">
              <i class="fas fa-lightbulb"></i> Important Nutrition Tips
            </h2>
            <ul class="tips-list">
              <?php foreach ($nutritionTips as $tip): ?>
                <li>
                  <i class="fas fa-check-circle"></i>
                  <span><?= htmlspecialchars($tip) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>

        <?php else: ?>
          <!-- Show message if child nutrition is not enabled -->
          <div class="not-enabled-message">
            <i class="fas fa-child"></i>
            <h2>Child Nutrition Not Enabled</h2>
            <p>To access child nutrition recommendations, please enable Child Nutrition Mode in your profile settings and select your child's age group.</p>
            <a href="profileform.php" class="btn-enable">Go to Profile Settings</a>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Favorite button functionality
      const favoriteButtons = document.querySelectorAll('.btn-favorite');
      
      favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
          this.classList.toggle('active');
          const icon = this.querySelector('i');
          
          if (this.classList.contains('active')) {
            icon.classList.remove('far', 'fa-heart');
            icon.classList.add('fas', 'fa-heart');
            // Here you would send an AJAX request to save to favorites
            console.log('Added to favorites');
          } else {
            icon.classList.remove('fas', 'fa-heart');
            icon.classList.add('far', 'fa-heart');
            // Here you would send an AJAX request to remove from favorites
            console.log('Removed from favorites');
          }
        });
      });
    });
  </script>

  <?php require 'Partials/footer.php'; ?>
</body>
</html>
