<?php
// Start session and database connection
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Initialize variables
$errors = [];
$success = false;
$userData = [];

// Fetch user data if exists
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch();

// Fetch medical conditions and allergens
$conditions = $conn->query("SELECT * FROM medical_conditions")->fetchAll();
$allergens = $conn->query("SELECT * FROM allergens")->fetchAll();

// Fetch user's existing conditions and allergens
$userConditions = [];
$userAllergens = [];

if ($userData) {
    $stmt = $conn->prepare("SELECT condition_id FROM user_conditions WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userConditions = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    $stmt = $conn->prepare("SELECT allergen_id FROM user_allergens WHERE user_id = ?");
    $stmt->execute([$userId]);
    $userAllergens = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = trim($_POST['name'] ?? '');
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 120]]);
    $gender = $_POST['gender'] ?? '';
    $weight = filter_input(INPUT_POST, 'weight', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 1, 'max_range' => 300]]);
    $height = filter_input(INPUT_POST, 'height', FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 50, 'max_range' => 250]]);
    $dietPreference = $_POST['diet'] ?? '';
    $childMode = isset($_POST['child_mode']) ? 1 : 0;
    $childAgeGroups = $_POST['child-age'] ?? [];
    
    // Validate required fields
    if (empty($name)) $errors[] = 'Full name is required';
    if (!$age) $errors[] = 'Valid age is required (1-120)';
    if (!in_array($gender, ['Male', 'Female', 'Other', 'Prefer not to say'])) $errors[] = 'Please select a valid gender';
    if (!$weight) $errors[] = 'Valid weight is required (1-300 kg)';
    if (!$height) $errors[] = 'Valid height is required (50-250 cm)';
    if (!in_array($dietPreference, ['Vegetarian', 'Non-Vegetarian'])) $errors[] = 'Please select a diet preference';
    
    // Process medical conditions
    $selectedConditions = $_POST['conditions'] ?? [];
    $conditionIds = [];
    foreach ($selectedConditions as $condition) {
        if ($condition === 'none') {
            $conditionIds = [];
            break;
        }
        $conditionIds[] = (int)$condition;
    }
    
    // Process allergens
    $selectedAllergens = $_POST['allergies'] ?? [];
    $allergenIds = [];
    foreach ($selectedAllergens as $allergen) {
        if ($allergen === 'none') {
            $allergenIds = [];
            break;
        }
        $allergenIds[] = (int)$allergen;
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            // Update user table
            $stmt = $conn->prepare("UPDATE users SET 
                name = ?, age = ?, gender = ?, weight = ?, height = ?, 
                diet_preference = ?, child_mode = ?, profile_completed = 1 
                WHERE user_id = ?");
            $stmt->execute([
                $name, $age, $gender, $weight, $height, 
                $dietPreference, $childMode, $userId
            ]);
            
            // Update user conditions
            $conn->prepare("DELETE FROM user_conditions WHERE user_id = ?")->execute([$userId]);
            foreach ($conditionIds as $conditionId) {
                $conn->prepare("INSERT INTO user_conditions (user_id, condition_id) VALUES (?, ?)")
                    ->execute([$userId, $conditionId]);
            }
            
            // Update user allergens
            $conn->prepare("DELETE FROM user_allergens WHERE user_id = ?")->execute([$userId]);
            foreach ($allergenIds as $allergenId) {
                $conn->prepare("INSERT INTO user_allergens (user_id, allergen_id) VALUES (?, ?)")
                    ->execute([$userId, $allergenId]);
            }
            
            // Store child age groups in session
            $_SESSION['child_age_groups'] = $childAgeGroups;
            
            $conn->commit();
            $success = true;
            
            // Update session data
            $_SESSION['user_name'] = $name;
            
            // Refresh to show updated data
            header('Location: profileform.php?success=1');
            exit;
            
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <?php require 'Partials/head.php'; ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    :root {
      --green-light: #E3F4E1;
      --green: #5FB65A;
      --green-dark: #3C8D37;
      --gray-100: #F9FAFB;
      --gray-600: #6B7280;
      --gray-700: #4B5563;
      --border-radius: 0.75rem;
    }

    .profile-container {
      max-width: 1000px;
      margin: 0 auto;
      padding: 20px;
      display: grid;
      grid-template-columns: 250px 1fr;
      gap: 30px;
    }

    @media (max-width: 768px) {
      .profile-container {
        grid-template-columns: 1fr;
        gap: 20px;
      }
    }

    .profile-sidebar {
      background: white;
      border-radius: var(--border-radius);
      padding: 20px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .profile-avatar {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      background-color: var(--green-light);
      margin: 0 auto 15px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .profile-name {
      text-align: center;
      font-size: 1.2rem;
      margin-bottom: 5px;
    }

    .profile-email {
      text-align: center;
      color: var(--gray-600);
      margin-bottom: 20px;
    }

    .profile-nav {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .profile-nav-item {
      padding: 10px;
      border-radius: var(--border-radius);
      color: var(--gray-700);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .profile-nav-item i {
      width: 20px;
      text-align: center;
    }

    .profile-nav-item.active {
      background-color: var(--green-light);
      color: var(--green-dark);
    }

    .profile-content {
      background: white;
      border-radius: var(--border-radius);
      padding: 30px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    @media (max-width: 600px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }

    .required-field::after {
      content: " *";
      color: red;
    }

    .form-control {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: var(--border-radius);
      box-sizing: border-box;
    }

    .checkbox-group {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 10px;
      margin-top: 10px;
    }

    @media (max-width: 480px) {
      .checkbox-group {
        grid-template-columns: 1fr;
      }
    }

    .checkbox-item {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .checkbox-item input[type="checkbox"],
    .checkbox-item input[type="radio"] {
      width: 1.25rem;
      height: 1.25rem;
      min-width: 1.25rem;
    }

    .full-width {
      grid-column: 1 / -1;
    }

    /* Toggle switch styles */
    .toggle-switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 30px;
    }
    
    .toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    
    .toggle-slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
      border-radius: 34px;
    }
    
    .toggle-slider:before {
      position: absolute;
      content: "";
      height: 22px;
      width: 22px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    
    input:checked + .toggle-slider {
      background-color: var(--green);
    }
    
    input:checked + .toggle-slider:before {
      transform: translateX(30px);
    }
    
    .child-nutrition-fields {
      display: none;
      margin-top: 15px;
      padding: 15px;
      background-color: var(--green-light);
      border-radius: 8px;
    }
    
    .child-nutrition-fields.active {
      display: block;
    }

    .form-actions {
      margin-top: 30px;
      text-align: right;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: var(--border-radius);
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-info {
      background-color: #d1ecf1;
      color: #0c5460;
      border: 1px solid #bee5eb;
    }

    .btn-primary {
      background-color: var(--green);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-weight: 500;
      transition: background-color 0.3s;
    }

    .btn-primary:hover {
      background-color: var(--green-dark);
    }

    @media (max-width: 480px) {
      .profile-content {
        padding: 20px 15px;
      }
      
      .form-actions {
        text-align: center;
      }
      
      .btn-primary {
        width: 100%;
        padding: 12px;
      }
    }
  </style>
</head>
<body>
  <?php require 'Partials/nav.php'; ?>

  <main class="profile-container">
    <!-- Sidebar - Now appears first on mobile -->
    <aside class="profile-sidebar">
      <div class="profile-avatar">
        <i class="fas fa-user" style="font-size: 2.5rem; color: var(--green);"></i>
      </div>
      <h2 class="profile-name"><?= htmlspecialchars($userData['name'] ?? 'No name set') ?></h2>
      <p class="profile-email"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></p>

      <?php if (!$userData || !$userData['profile_completed']): ?>
        <div class="alert alert-info" style="margin-bottom: 20px;">
          <i class="fas fa-info-circle"></i> Please complete all required fields to get personalized recommendations.
        </div>
      <?php endif; ?>

      <nav class="profile-nav">
        <a href="#" class="profile-nav-item active">
          <i class="fas fa-user-edit"></i> Profile Setup
        </a>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="profile-content">
      <h1 style="margin-bottom: 20px; color: var(--green-dark);">Complete Your Profile</h1>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
          <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
          Profile saved successfully!
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label required-field" for="name">Full Name</label>
            <input type="text" id="name" name="name" class="form-control" 
                   value="<?= htmlspecialchars($userData['name'] ?? '') ?>" 
                   placeholder="Enter your full name" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" class="form-control" 
                   value="<?= htmlspecialchars($_SESSION['user_email'] ?? '') ?>" disabled>
          </div>

          <div class="form-group">
            <label class="form-label required-field" for="age">Age</label>
            <input type="number" id="age" name="age" class="form-control" 
                   value="<?= htmlspecialchars($userData['age'] ?? '') ?>" 
                   placeholder="Enter your age" min="1" max="120" required>
          </div>

          <div class="form-group">
            <label class="form-label required-field" for="gender">Gender</label>
            <select id="gender" name="gender" class="form-control" required>
              <option value="" disabled <?= empty($userData['gender']) ? 'selected' : '' ?>>Select your gender</option>
              <option value="Male" <?= ($userData['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
              <option value="Female" <?= ($userData['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
              <option value="Other" <?= ($userData['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
              <option value="Prefer not to say" <?= ($userData['gender'] ?? '') === 'Prefer not to say' ? 'selected' : '' ?>>Prefer not to say</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label required-field" for="weight">Weight (kg)</label>
            <input type="number" id="weight" name="weight" class="form-control" 
                   value="<?= htmlspecialchars($userData['weight'] ?? '') ?>" 
                   placeholder="Enter your weight" min="1" max="300" step="0.1" required>
          </div>

          <div class="form-group">
            <label class="form-label required-field" for="height">Height (cm)</label>
            <input type="number" id="height" name="height" class="form-control" 
                   value="<?= htmlspecialchars($userData['height'] ?? '') ?>" 
                   placeholder="Enter your height" min="50" max="250" required>
          </div>
        </div>

        <div class="form-group full-width">
          <label class="form-label">Medical Conditions (select all that apply)</label>
          <div class="checkbox-group">
            <?php foreach ($conditions as $condition): ?>
              <label class="checkbox-item">
                <input type="checkbox" name="conditions[]" 
                       value="<?= $condition['condition_id'] ?>" 
                       <?= in_array($condition['condition_id'], $userConditions ?? []) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars($condition['name']) ?></span>
              </label>
            <?php endforeach; ?>
            <label class="checkbox-item">
              <input type="checkbox" name="conditions[]" value="none"
                     <?= empty($userConditions) ? 'checked' : '' ?>>
              <span>None</span>
            </label>
          </div>
        </div>

        <div class="form-group full-width">
          <label class="form-label">Food Allergies (select all that apply)</label>
          <div class="checkbox-group">
            <?php foreach ($allergens as $allergen): ?>
              <label class="checkbox-item">
                <input type="checkbox" name="allergies[]" 
                       value="<?= $allergen['allergen_id'] ?>" 
                       <?= in_array($allergen['allergen_id'], $userAllergens ?? []) ? 'checked' : '' ?>>
                <span><?= htmlspecialchars($allergen['name']) ?></span>
              </label>
            <?php endforeach; ?>
            <label class="checkbox-item">
              <input type="checkbox" name="allergies[]" value="none"
                     <?= empty($userAllergens) ? 'checked' : '' ?>>
              <span>None</span>
            </label>
          </div>
        </div>

        <div class="form-group full-width">
          <label class="form-label required-field">Diet Preference</label>
          <div class="checkbox-group">
            <label class="checkbox-item">
              <input type="radio" name="diet" value="Vegetarian" required
                     <?= ($userData['diet_preference'] ?? '') === 'Vegetarian' ? 'checked' : '' ?>>
              <span>Vegetarian</span>
            </label>
            <label class="checkbox-item">
              <input type="radio" name="diet" value="Non-Vegetarian"
                     <?= ($userData['diet_preference'] ?? '') === 'Non-Vegetarian' ? 'checked' : '' ?>>
              <span>Non-Vegetarian</span>
            </label>
          </div>
        </div>

        <div class="form-group full-width">
          <label class="form-label">Child Nutrition Mode</label>
          <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <label class="toggle-switch">
              <input type="checkbox" id="child-mode" name="child_mode" 
                     <?= ($userData['child_mode'] ?? 0) ? 'checked' : '' ?>>
              <span class="toggle-slider"></span>
            </label>
            <span id="child-mode-status"><?= ($userData['child_mode'] ?? 0) ? 'Enabled' : 'Disabled' ?></span>
          </div>
          
          <div class="child-nutrition-fields <?= ($userData['child_mode'] ?? 0) ? 'active' : '' ?>" id="child-fields">
            <p style="margin-bottom: 15px; color: var(--gray-600);">Select all age groups that apply:</p>
            
            <div class="checkbox-group">
              <?php 
              $childAgeGroups = $_SESSION['child_age_groups'] ?? [];
              $ageGroups = [
                '0-3' => 'Infants & Toddlers (0-3 years)',
                '4-8' => 'Preschoolers (4-8 years)',
                '9-13' => 'Children (9-13 years)',
                '14-18' => 'Teenagers (14-18 years)'
              ];
              ?>
              
              <?php foreach ($ageGroups as $value => $label): ?>
                <label class="checkbox-item">
                  <input type="radio" name="child-age[]" 
                         value="<?= $value ?>" 
                         <?= in_array($value, $childAgeGroups) ? 'checked' : '' ?>
                         <?= !($userData['child_mode'] ?? 0) ? 'disabled' : '' ?>>
                  <span><?= $label ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Profile
          </button>
        </div>
      </form>
    </div>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Handle "None" checkbox behavior for conditions
      const conditionsNone = document.querySelector('input[name="conditions[]"][value="none"]');
      const otherConditions = document.querySelectorAll('input[name="conditions[]"]:not([value="none"])');
      
      if (conditionsNone) {
        conditionsNone.addEventListener('change', function() {
          if (this.checked) {
            otherConditions.forEach(cb => cb.checked = false);
          }
        });
        
        otherConditions.forEach(cb => {
          cb.addEventListener('change', function() {
            if (this.checked) {
              conditionsNone.checked = false;
            }
          });
        });
      }

      // Handle "None" checkbox behavior for allergens
      const allergensNone = document.querySelector('input[name="allergies[]"][value="none"]');
      const otherAllergens = document.querySelectorAll('input[name="allergies[]"]:not([value="none"])');
      
      if (allergensNone) {
        allergensNone.addEventListener('change', function() {
          if (this.checked) {
            otherAllergens.forEach(cb => cb.checked = false);
          }
        });
        
        otherAllergens.forEach(cb => {
          cb.addEventListener('change', function() {
            if (this.checked) {
              allergensNone.checked = false;
            }
          });
        });
      }
      
      // Child mode toggle functionality
      const childModeToggle = document.getElementById('child-mode');
      const childFields = document.getElementById('child-fields');
      const childModeStatus = document.getElementById('child-mode-status');
      const ageCheckboxes = document.querySelectorAll('input[name="child-age[]"]');
      
      if (childModeToggle) {
        childModeToggle.addEventListener('change', function() {
          if (this.checked) {
            childFields.classList.add('active');
            childModeStatus.textContent = 'Enabled';
            ageCheckboxes.forEach(cb => cb.disabled = false);
          } else {
            childFields.classList.remove('active');
            childModeStatus.textContent = 'Disabled';
            ageCheckboxes.forEach(cb => {
              cb.disabled = true;
              cb.checked = false;
            });
          }
        });
      }
    });
  </script>

  <?php require 'Partials/footer.php'; ?>
</body>
</html>
