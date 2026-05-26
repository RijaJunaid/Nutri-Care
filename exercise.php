<?php
// Start session and database connection
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user's conditions for filtering
$userConditions = [];
$stmt = $conn->prepare("SELECT c.name FROM user_conditions uc 
                       JOIN medical_conditions c ON uc.condition_id = c.condition_id 
                       WHERE uc.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$userConditions = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Get all exercises and their recommendations
$exercises = [];
$conditionFilter = '';

// Check if a specific condition filter is applied
if (isset($_GET['condition'])) {
    $conditionFilter = $_GET['condition'];
    $stmt = $conn->prepare("SELECT e.* FROM exercise_types e
                           JOIN exercise_recommendations er ON e.exercise_id = er.exercise_id
                           JOIN medical_conditions c ON er.condition_id = c.condition_id
                           WHERE c.name = ?");
    $stmt->execute([$conditionFilter]);
    $exercises = $stmt->fetchAll();
} else {
    // Get all exercises if no filter
    $exercises = $conn->query("SELECT * FROM exercise_types")->fetchAll();
}

// Get all conditions for filter buttons
$conditions = $conn->query("SELECT * FROM medical_conditions")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <?php require 'Partials/nav.php';?>
<?php require 'Partials/head.php';?>
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

    .exercise-section {
      padding: 4rem 0;
      background-color: var(--green-light);
    }
    
    .exercise-header {
      margin-bottom: 2rem;
    }
    
    .exercise-filters {
      display: flex;
      gap: 1rem;
      margin-bottom: 2rem;
      flex-wrap: wrap;
    }
    
    .filter-btn {
      padding: 0.5rem 1rem;
      background-color: white;
      border: 1px solid var(--gray-300);
      border-radius: var(--border-radius);
      cursor: pointer;
      transition: all 0.2s ease;
    }
    
    .filter-btn.active, .filter-btn:hover {
      background-color: var(--green);
      color: white;
      border-color: var(--green);
    }
    
    .exercise-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }
    
    .exercise-card {
      background-color: white;
      border-radius: var(--border-radius);
      overflow: hidden;
      box-shadow: var(--shadow-sm);
      transition: transform 0.3s ease;
    }
    
    .exercise-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
    }
    
    .exercise-image {
      height: 200px;
      background-color: var(--gray-200);
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--gray-500);
      position: relative;
      overflow: hidden;
    }
    
    .exercise-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .exercise-image i {
      position: absolute;
      font-size: 3rem;
    }
    
    .exercise-content {
      padding: 1.5rem;
    }
    
    .exercise-tags {
      display: flex;
      gap: 0.5rem;
      margin-bottom: 0.75rem;
      flex-wrap: wrap;
    }
    
    .exercise-tag {
      background-color: var(--green-light);
      color: var(--green-dark);
      padding: 0.25rem 0.75rem;
      border-radius: 1rem;
      font-size: 0.75rem;
      font-weight: 500;
    }
    
    .exercise-tag.low {
      background-color: var(--blue-light);
      color: #1E40AF;
    }
    
    .exercise-tag.high {
      background-color: var(--peach);
      color: #9A3412;
    }
    
    .exercise-duration {
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--gray-600);
      margin-top: 1rem;
    }
    
    .exercise-benefits {
      margin-top: 1rem;
      padding: 1rem;
      background-color: var(--gray-100);
      border-radius: var(--border-radius);
    }
    
    .exercise-benefits h4 {
      margin-bottom: 0.5rem;
      color: var(--green-dark);
    }
    
    .user-conditions {
      margin-bottom: 1.5rem;
    }
    
    .user-condition-tag {
      display: inline-block;
      background-color: var(--purple-light);
      color: #5B21B6;
      padding: 0.25rem 0.75rem;
      border-radius: 1rem;
      font-size: 0.875rem;
      margin-right: 0.5rem;
      margin-bottom: 0.5rem;
    }
    
    @media (min-width: 768px) {
      .exercise-grid {
        grid-template-columns: 1fr 1fr;
      }
    }
    
    @media (min-width: 1024px) {
      .exercise-grid {
        grid-template-columns: 1fr 1fr 1fr;
      }
    }
  </style>
</head>
<body>
  <main class="exercise-section">
    <div class="container">
      <div class="exercise-header">
        <h1 class="section-title">Exercise Suggestions</h1>
        <p class="section-description">
          Personalized workout recommendations based on your health condition.
        </p>
        
        <?php if (!empty($userConditions)): ?>
          <div class="user-conditions">
            <p>Your profile conditions:</p>
            <?php foreach ($userConditions as $condition): ?>
              <span class="user-condition-tag"><?= htmlspecialchars($condition) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
      
      <!-- Condition Filters -->
      <div class="exercise-filters">
        <a href="exercise.php" class="filter-btn <?= empty($conditionFilter) ? 'active' : '' ?>">All Exercises</a>
        <?php foreach ($conditions as $condition): ?>
          <a href="exercise.php?condition=<?= urlencode($condition['name']) ?>" 
             class="filter-btn <?= $conditionFilter === $condition['name'] ? 'active' : '' ?>">
            <?= htmlspecialchars($condition['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>
      
      <!-- Exercise Cards Grid -->
      <div class="exercise-grid">
        <?php foreach ($exercises as $exercise): 
          // Get recommendations for this exercise
          $stmt = $conn->prepare("SELECT c.name, er.frequency, er.benefits 
                                 FROM exercise_recommendations er
                                 JOIN medical_conditions c ON er.condition_id = c.condition_id
                                 WHERE er.exercise_id = ?");
          $stmt->execute([$exercise['exercise_id']]);
          $recommendations = $stmt->fetchAll();
          
          // Determine intensity tag class
          $intensityClass = strtolower($exercise['intensity']);
        ?>
          <div class="exercise-card">
            <div class="exercise-image">
              <?php if ($exercise['image_url']): ?>
                <img src="<?= htmlspecialchars($exercise['image_url']) ?>" alt="<?= htmlspecialchars($exercise['name']) ?>">
              <?php else: ?>
                <i class="fas fa-<?= 
                  $exercise['name'] === 'Yoga' ? 'hands' : 
                  ($exercise['name'] === 'Swimming' ? 'water' : 
                  ($exercise['name'] === 'Resistance Training' ? 'dumbbell' : 
                  ($exercise['name'] === 'Cycling' ? 'bicycle' : 'walking'))) ?>"></i>
              <?php endif; ?>
            </div>
            <div class="exercise-content">
              <div class="exercise-tags">
                <span class="exercise-tag <?= $intensityClass ?>"><?= htmlspecialchars($exercise['intensity']) ?> Intensity</span>
                <span class="exercise-tag"><?= htmlspecialchars($exercise['duration_minutes']) ?> min</span>
              </div>
              <h3><?= htmlspecialchars($exercise['name']) ?></h3>
              <p><?= htmlspecialchars($exercise['description']) ?></p>
              
              <?php if (!empty($recommendations)): ?>
                <div class="exercise-benefits">
                  <h4>Recommended For:</h4>
                  <ul>
                    <?php foreach ($recommendations as $rec): ?>
                      <li><strong><?= htmlspecialchars($rec['name']) ?>:</strong> <?= htmlspecialchars($rec['frequency']) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
              
              <div class="exercise-duration">
                <i data-feather="clock"></i>
                <span><?= htmlspecialchars($exercise['duration_minutes']) ?> minutes (<?= htmlspecialchars($exercise['calories_burned']) ?> calories)</span>
              </div>
              
              <?php if ($exercise['video_url']): ?>
                <a href="<?= htmlspecialchars($exercise['video_url']) ?>" target="_blank" class="btn btn-outline" style="margin-top: 1rem;">
                  <i data-feather="play"></i>
                  Watch Video
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
        
        <?php if (empty($exercises)): ?>
          <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
            <p>No exercises found for the selected filter. Try a different condition.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      feather.replace();
      
      // Set current year in footer
      document.getElementById('current-year').textContent = new Date().getFullYear();
      
      // Mobile menu toggle
      
    });
  </script>
</body>
<?php require 'Partials/footer.php';?>
</html>
