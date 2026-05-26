<?php
require 'config.php';
session_start();

// Initialize variables
$foodData = null;
$error = '';
$searchQuery = '';

// Process search if form submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $searchQuery = trim($_POST['search']);
    if (!empty($searchQuery)) {
        try {
            // Search for food in database
            $stmt = $conn->prepare("
                SELECT f.*, fc.name as category_name 
                FROM foods f
                LEFT JOIN food_categories fc ON f.category_id = fc.category_id
                WHERE f.name LIKE :query
                LIMIT 1
            ");
            $stmt->bindValue(':query', '%' . $searchQuery . '%');
            $stmt->execute();
            
            $foodData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$foodData) {
                $error = 'Food not found. Try another item.';
            }
            } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please enter a food item';
    }
}

// Get popular food suggestions
$suggestions = [];
try {
    $stmt = $conn->query("SELECT name FROM foods ORDER BY RAND() LIMIT 4");
    $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // If error, use default suggestions
    $suggestions = ['Banana', 'Salmon', 'Brown Rice', 'Spinach'];
}
?>

<!DOCTYPE html>
<html lang="en">
 <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>NutriCare | Nutritional Breakdown</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php require 'Partials/nav.php';?>
<?php require 'Partials/head.php';?>
 
  <style>
    :root {
      --green-light: #E3F4E1;
      --green: #5FB65A;
      --green-dark: #3C8D37;
      --gray-100: #F9FAFB;
      --gray-200: #F1F0FB;
      --gray-600: #6B7280;
      --gray-800: #1F2937;
      --border-radius: 0.75rem;
      --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
      --font-sans: 'Inter', sans-serif;
      --font-display: 'Montserrat', sans-serif;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: var(--font-sans);
      color: var(--gray-800);
      background-color: hsl(120, 40%, 98%);
      line-height: 1.5;
      min-height: 100vh;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 1rem;
    }

    /* Search Section */
    .search-section {
      background-color: white;
      border-radius: var(--border-radius);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
      margin-bottom: 1.5rem;
      border: 1px solid var(--green-light);
    }

    .search-title {
      font-family: var(--font-display);
      font-size: 1.25rem;
      margin-bottom: 1rem;
      color: var(--green-dark);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .search-bar {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      margin-bottom: 1rem;
    }

    @media (min-width: 640px) {
      .search-bar {
        flex-direction: row;
      }
    }

    .search-input {
      flex: 1;
      padding: 0.75rem 1rem;
      border: 1px solid var(--gray-200);
      border-radius: var(--border-radius);
      font-family: inherit;
      font-size: 1rem;
    }

    .search-btn {
      background-color: var(--green);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: var(--border-radius);
      font-weight: 500;
      cursor: pointer;
      font-size: 1rem;
      white-space: nowrap;
    }

    .search-btn:hover {
      background-color: var(--green-dark);
    }

    .suggestions {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .suggestion-tag {
      background-color: var(--green-light);
      color: var(--green-dark);
      padding: 0.5rem 1rem;
      border-radius: 1rem;
      font-size: 0.875rem;
      cursor: pointer;
      transition: all 0.2s;
    }

    .suggestion-tag:hover {
      background-color: var(--green);
      color: white;
    }

    /* Nutrition Data Section */
    .nutrition-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.5rem;
    }

    @media (min-width: 768px) {
      .nutrition-grid {
        grid-template-columns: 1fr 1fr;
      }
    }

    .nutrition-card {
      background-color: white;
      border-radius: var(--border-radius);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--green-light);
    }

    .card-title {
      font-family: var(--font-display);
      font-size: 1.125rem;
      margin-bottom: 1rem;
      color: var(--green-dark);
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .card-title i {
      color: var(--green);
      font-size: 1.25rem;
    }

    /* Nutrition Table */
    .nutrition-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9375rem;
    }

    .nutrition-table th, 
    .nutrition-table td {
      padding: 0.75rem 0.5rem;
      text-align: left;
      border-bottom: 1px solid var(--gray-200);
    }

    .nutrition-table th {
      font-weight: 600;
      color: var(--gray-800);
    }

    .nutrition-table tr:last-child td {
      border-bottom: none;
    }

    .highlight {
      font-weight: 600;
      color: var(--green-dark);
    }

    /* Visualization */
    .chart-container {
      position: relative;
      height: 200px;
      margin-top: 1rem;
    }

    @media (min-width: 640px) {
      .chart-container {
        height: 250px;
      }
    }

    /* Daily Intake */
    .daily-intake {
      margin-top: 1rem;
    }

    .daily-intake-item {
      margin-bottom: 1rem;
    }

    .progress-container {
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .progress-label {
      min-width: 100px;
      font-size: 0.875rem;
    }

    .progress-bar {
      flex: 1;
      height: 8px;
      background-color: var(--gray-200);
      border-radius: 4px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background-color: var(--green);
      border-radius: 4px;
    }

    .progress-value {
      min-width: 40px;
      text-align: right;
      font-size: 0.875rem;
      color: var(--gray-600);
    }

    /* Custom Input */
    .custom-input {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
      margin-top: 1rem;
    }

    @media (min-width: 480px) {
      .custom-input {
        flex-direction: row;
        align-items: center;
      }
    }

    .custom-input input {
      padding: 0.75rem;
      border: 1px solid var(--gray-200);
      border-radius: var(--border-radius);
      font-family: inherit;
      font-size: 1rem;
      flex: 1;
    }

    .custom-input select {
      padding: 0.75rem;
      border: 1px solid var(--gray-200);
      border-radius: var(--border-radius);
      font-family: inherit;
      font-size: 1rem;
      background-color: white;
    }

    /* Smart Suggestions */
    .suggestion-card {
      background-color: var(--green-light);
      padding: 1rem;
      border-radius: var(--border-radius);
      margin-top: 1rem;
    }

    .suggestion-card h4 {
      margin-top: 0;
      margin-bottom: 0.5rem;
      color: var(--green-dark);
      font-size: 1rem;
    }

    .suggestion-list {
      padding-left: 1.25rem;
    }

    .suggestion-list li {
      margin-bottom: 0.5rem;
      font-size: 0.9375rem;
    }

    /* Save Button */
    .save-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      background-color: var(--green);
      color: white;
      padding: 0.75rem 1.5rem;
      border-radius: var(--border-radius);
      border: none;
      margin-top: 1rem;
      cursor: pointer;
      font-size: 1rem;
      width: 100%;
    }

    @media (min-width: 480px) {
      .save-btn {
        width: auto;
      }
    }

    .save-btn:hover {
      background-color: var(--green-dark);
    }

    /* Loading State */
    .loading {
      display: none;
      text-align: center;
      padding: 2rem;
    }

    .loading-spinner {
      border: 3px solid var(--green-light);
      border-top: 3px solid var(--green);
      border-radius: 50%;
      width: 30px;
      height: 30px;
      animation: spin 1s linear infinite;
      margin: 0 auto 1rem;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Error State */
    .error-message {
      display: none;
      color: #dc2626;
      background-color: #fee2e2;
      padding: 1rem;
      border-radius: var(--border-radius);
      margin-top: 1rem;
    }
  </style>
</head>
<body>
  

  <div class="container">
    <!-- Search Section -->
    <section class="search-section">
      <h2 class="search-title"><i class="fas fa-search"></i> Search Food Items</h2>
      <form method="POST" action="">
        <div class="search-bar">
          <input type="text" name="search" class="search-input" placeholder="e.g., Apple, Chicken Breast, Pasta..." 
                 value="<?php echo htmlspecialchars($searchQuery); ?>" id="foodSearch">
          <button type="submit" class="search-btn" id="searchButton">Analyze</button>
        </div>
      </form>
      <div class="suggestions" id="suggestions">
        <?php foreach ($suggestions as $suggestion): ?>
          <span class="suggestion-tag"><?php echo htmlspecialchars($suggestion); ?></span>
        <?php endforeach; ?>
      </div>
      <?php if (!empty($error)): ?>
        <div class="error-message" id="errorMessage">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
    </section>

    <?php if ($foodData): ?>
    <!-- Nutrition Data Grid -->
    <div class="nutrition-grid" id="nutritionData">
      <!-- Macronutrients -->
      <div class="nutrition-card">
        <h3 class="card-title"><i class="fas fa-chart-pie"></i> Macronutrients <span id="servingSize">(per 100g)</span></h3>
        <table class="nutrition-table">
          <tr>
            <td>Calories</td>
            <td class="highlight" id="calories"><?php echo $foodData['calories']; ?> kcal</td>
          </tr>
          <tr>
            <td>Protein</td>
            <td class="highlight" id="protein"><?php echo $foodData['protein']; ?>g</td>
          </tr>
          <tr>
            <td>Carbohydrates</td>
            <td class="highlight" id="carbs"><?php echo $foodData['carbs']; ?>g</td>
          </tr>
          <tr>
            <td>- Fiber</td>
            <td id="fiber"><?php echo $foodData['fiber']; ?>g</td>
          </tr>
          <tr>
            <td>- Sugars</td>
            <td id="sugars"><?php echo $foodData['sugar']; ?>g</td>
          </tr>
          <tr>
            <td>Fats</td>
            <td class="highlight" id="fats"><?php echo $foodData['fat']; ?>g</td>
          </tr>
          <tr>
            <td>- Saturated</td>
            <td id="saturatedFat"><?php echo round($foodData['fat'] * 0.3, 1); ?>g</td>
          </tr>
        </table>

        <div class="chart-container">
          <canvas id="macronutrientChart"></canvas>
        </div>
      </div>

      <!-- Micronutrients -->
      <div class="nutrition-card">
        <h3 class="card-title"><i class="fas fa-vitamins"></i> Micronutrients</h3>
        <table class="nutrition-table">
          <tr>
            <td>Sodium</td>
            <td class="highlight" id="sodium"><?php echo $foodData['sodium']; ?>mg</td>
          </tr>
          <tr>
            <td>Glycemic Index</td>
            <td class="highlight" id="glycemicIndex"><?php echo $foodData['glycemic_index'] ?? 'N/A'; ?></td>
          </tr>
        </table>

        <h4 class="card-title"><i class="fas fa-chart-bar"></i> Daily Value (%)</h4>
        <div class="daily-intake">
          <div class="daily-intake-item">
            <div class="progress-container">
              <span class="progress-label">Protein</span>
              <div class="progress-bar">
                <div class="progress-fill" id="proteinProgress" 
                     style="width: <?php echo min(100, ($foodData['protein'] / 50) * 100); ?>%"></div>
              </div>
              <span class="progress-value"><?php echo round(($foodData['protein'] / 50) * 100); ?>%</span>
            </div>
          </div>
          <div class="daily-intake-item">
            <div class="progress-container">
              <span class="progress-label">Fiber</span>
              <div class="progress-bar">
                <div class="progress-fill" id="fiberProgress" 
                     style="width: <?php echo min(100, ($foodData['fiber'] / 25) * 100); ?>%"></div>
              </div>
              <span class="progress-value"><?php echo round(($foodData['fiber'] / 25) * 100); ?>%</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Custom Input -->
      <div class="nutrition-card">
        <h3 class="card-title"><i class="fas fa-calculator"></i> Adjust Quantity</h3>
        <div class="custom-input">
          <input type="number" id="quantityInput" value="100" min="1">
          <select id="unitSelect">
            <option value="g">grams (g)</option>
            <option value="oz">ounces (oz)</option>
            <option value="cups">cups</option>
          </select>
          <button class="search-btn" id="updateQuantity">Update</button>
        </div>
        <p>Nutritional values will adjust automatically.</p>
      </div>

      <!-- Smart Suggestions -->
      <div class="nutrition-card">
        <h3 class="card-title"><i class="fas fa-lightbulb"></i> Smart Suggestions</h3>
        <div class="suggestion-card">
          <h4>Healthier Alternatives</h4>
          <ul class="suggestion-list" id="alternativesList">
            <?php
            // Get food swaps from database
            $swaps = [];
            try {
                $stmt = $conn->prepare("
                    SELECT fs.*, f2.name as better_food_name 
                    FROM food_swaps fs
                    JOIN foods f2 ON fs.better_food_id = f2.food_id
                    WHERE fs.original_food_id = :food_id
                    LIMIT 2
                ");
                $stmt->bindValue(':food_id', $foodData['food_id']);
                $stmt->execute();
                $swaps = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // If error, use default suggestions
                $swaps = [];
            }
            
            if (!empty($swaps)) {
                foreach ($swaps as $swap) {
                    echo '<li>Try ' . htmlspecialchars($swap['better_food_name']) . ' - ' . 
                         htmlspecialchars($swap['reason']) . '</li>';
                }
            } else {
                echo '<li>No specific alternatives found. Consider similar foods with lower calories or higher protein.</li>';
            }
            ?>
          </ul>
        </div>
        <div class="suggestion-card">
          <h4>Expert Tip</h4>
          <p id="expertTip">
            <?php
            // Generate expert tip based on food properties
            if ($foodData['protein'] > 15) {
                echo "This high-protein food is great for muscle building and recovery.";
            } elseif ($foodData['carbs'] > 30) {
                echo "This carbohydrate-rich food provides good energy, but watch portion sizes.";
            } elseif ($foodData['fat'] > 10) {
                echo "Contains healthy fats that support brain function and nutrient absorption.";
            } else {
                echo "This food is a nutritious addition to a balanced diet.";
            }
            ?>
          </p>
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
          
        <?php else: ?>
          <a href="login.php" class="save-btn">
            <i class="fas fa-sign-in-alt"></i> Login to Save
          </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <script>
    // DOM Elements
    const foodSearch = document.getElementById('foodSearch');
    const suggestions = document.getElementById('suggestions');
    const saveButton = document.getElementById('saveButton');

    // Initialize the page
    document.addEventListener('DOMContentLoaded', () => {
      // Suggestion tags click handler
      suggestions.querySelectorAll('.suggestion-tag').forEach(tag => {
        tag.addEventListener('click', () => {
          foodSearch.value = tag.textContent;
          foodSearch.form.submit();
        });
      });

      // Quantity update handler
      document.getElementById('updateQuantity')?.addEventListener('click', updateQuantity);

      // Initialize chart if food data exists
      <?php if ($foodData): ?>
        updateChart(
          <?php echo $foodData['protein']; ?>,
          <?php echo $foodData['carbs']; ?>,
          <?php echo $foodData['fat']; ?>
        );
      <?php endif; ?>
    });

    // Update the chart
    function updateChart(protein, carbs, fats) {
      const ctx = document.getElementById('macronutrientChart').getContext('2d');
      
      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Protein', 'Carbs', 'Fats'],
          datasets: [{
            data: [protein, carbs, fats],
            backgroundColor: [
              '#5FB65A', // Green
              '#3C8D37', // Dark Green
              '#E3F4E1'  // Light Green
            ],
            borderWidth: 0
          }]
        },
        options: {
          cutout: '70%',
          plugins: {
            legend: {
              position: 'bottom',
              labels: {
                boxWidth: 12,
                padding: 20
              }
            }
          },
          maintainAspectRatio: false
        }
      });
    }

    // Update quantity
    function updateQuantity() {
      const quantity = parseFloat(document.getElementById('quantityInput').value);
      const unit = document.getElementById('unitSelect').value;
      
      if (isNaN(quantity) || quantity <= 0) {
        alert('Please enter a valid quantity');
        return;
      }
      
      // Calculate adjusted values
      const factor = quantity / 100;
      document.getElementById('calories').textContent = Math.round(<?php echo $foodData['calories']; ?> * factor) + ' kcal';
      document.getElementById('protein').textContent = (<?php echo $foodData['protein']; ?> * factor).toFixed(1) + 'g';
      document.getElementById('carbs').textContent = (<?php echo $foodData['carbs']; ?> * factor).toFixed(1) + 'g';
      document.getElementById('fiber').textContent = (<?php echo $foodData['fiber']; ?> * factor).toFixed(1) + 'g';
      document.getElementById('sugars').textContent = (<?php echo $foodData['sugar']; ?> * factor).toFixed(1) + 'g';
      document.getElementById('fats').textContent = (<?php echo $foodData['fat']; ?> * factor).toFixed(1) + 'g';
      document.getElementById('saturatedFat').textContent = (<?php echo $foodData['fat'] * 0.3; ?> * factor).toFixed(1) + 'g';
      document.getElementById('sodium').textContent = Math.round(<?php echo $foodData['sodium']; ?> * factor) + 'mg';
      
      // Update progress bars
      const proteinPercent = Math.min(100, (<?php echo $foodData['protein']; ?> * factor / 50) * 100);
      const fiberPercent = Math.min(100, (<?php echo $foodData['fiber']; ?> * factor / 25) * 100);
      
      document.getElementById('proteinProgress').style.width = proteinPercent + '%';
      document.getElementById('fiberProgress').style.width = fiberPercent + '%';
      document.querySelectorAll('.progress-value')[0].textContent = Math.round(proteinPercent) + '%';
      document.querySelectorAll('.progress-value')[1].textContent = Math.round(fiberPercent) + '%';
      
      // Update chart
      updateChart(
        <?php echo $foodData['protein']; ?> * factor,
        <?php echo $foodData['carbs']; ?> * factor,
        <?php echo $foodData['fat']; ?> * factor
      );
    }

    // Save to favorites
    function saveToFavorites(foodId) {
      fetch('save_favorite.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'food_id=' + foodId
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('Saved to your favorites!');
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => {
        alert('Error saving favorite: ' + error);
      });
    }
  </script>
</body>

<?php require 'Partials/footer.php';?>
</html>
