<?php
// Start session and database connection
session_start();
require 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user's medical conditions, allergens, and diet preferences
$userId = $_SESSION['user_id'];
$conditions = [];
$allergens = [];
$dietPreference = '';

// Get user's medical conditions
$stmt = $conn->prepare("SELECT mc.condition_id, mc.name 
                       FROM user_conditions uc
                       JOIN medical_conditions mc ON uc.condition_id = mc.condition_id
                       WHERE uc.user_id = ?");
$stmt->execute([$userId]);
$conditions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's allergens
$stmt = $conn->prepare("SELECT a.allergen_id, a.name 
                       FROM user_allergens ua
                       JOIN allergens a ON ua.allergen_id = a.allergen_id
                       WHERE ua.user_id = ?");
$stmt->execute([$userId]);
$allergens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's diet preference
$stmt = $conn->prepare("SELECT diet_preference FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$userData = $stmt->fetch(PDO::FETCH_ASSOC);
$dietPreference = $userData['diet_preference'] ?? '';

// Get recommended foods based on user's conditions, allergens, and diet preferences
$recommendedFoods = [];
$avoidFoods = [];
$moderationFoods = [];

if (!empty($conditions)) {
    $conditionIds = array_column($conditions, 'condition_id');
    $allergenNames = array_column($allergens, 'name');
    
    // Base query parts
    $select = "SELECT DISTINCT f.*, fr.reason, fr.scientific_evidence";
    $from = "FROM food_recommendations fr
             JOIN foods f ON fr.food_id = f.food_id
             JOIN food_categories fc ON f.category_id = fc.category_id";
    
    // Where clauses
    $whereConditions = "fr.condition_id IN (" . implode(',', array_fill(0, count($conditionIds), '?')) . ")";
    
    // Exclude foods that match user's allergens
    $whereAllergens = "";
    if (!empty($allergens)) {
        $allergenConditions = [];
        foreach ($allergenNames as $allergen) {
            $allergen = strtolower($allergen);
            $allergenConditions[] = "(f.is_common_allergen = TRUE OR LOWER(f.name) LIKE '%$allergen%' OR LOWER(f.description) LIKE '%$allergen%')";
        }
        $whereAllergens = "AND NOT (" . implode(" OR ", $allergenConditions) . ")";
    }
    
    // Filter by diet preference
    $whereDiet = "";
    if ($dietPreference === 'Vegetarian') {
        $whereDiet = "AND fc.name NOT IN ('Meat', 'Poultry', 'Fish', 'Seafood')";
    } elseif ($dietPreference === 'Non-Vegetarian') {
        $whereDiet = "AND fc.name NOT IN ('Vegetables', 'Fruits', 'Legumes')";
    }
    
    // Parameters for prepared statements
    $params = $conditionIds;
    
    // Get recommended foods
    $stmt = $conn->prepare("$select $from 
                           WHERE $whereConditions 
                           AND fr.recommendation_type = 'Recommended'
                           $whereAllergens
                           $whereDiet
                           ORDER BY f.name");
    $stmt->execute($params);
    $recommendedFoods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get foods to avoid
    $stmt = $conn->prepare("$select $from 
                           WHERE $whereConditions 
                           AND fr.recommendation_type = 'Avoid'
                           $whereAllergens
                           $whereDiet
                           ORDER BY f.name");
    $stmt->execute($params);
    $avoidFoods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get foods to eat in moderation
    $stmt = $conn->prepare("$select $from 
                           WHERE $whereConditions 
                           AND fr.recommendation_type = 'Moderation'
                           $whereAllergens
                           $whereDiet
                           ORDER BY f.name");
    $stmt->execute($params);
    $moderationFoods = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle favorites
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['favorite_action'])) {
    $foodId = $_POST['food_id'];
    $action = $_POST['favorite_action'];
    
    if ($action === 'add') {
        // Add to favorites
        $stmt = $conn->prepare("INSERT INTO user_favorites (user_id, food_id) VALUES (?, ?)");
        $stmt->execute([$userId, $foodId]);
    } elseif ($action === 'remove') {
        // Remove from favorites
        $stmt = $conn->prepare("DELETE FROM user_favorites WHERE user_id = ? AND food_id = ?");
        $stmt->execute([$userId, $foodId]);
    }
    
    // Refresh the page to update favorites
    header("Location: foodrecommendations.php");
    exit;
}

// Get user's favorite foods
$favoriteFoods = [];
$stmt = $conn->prepare("SELECT f.* 
                       FROM user_favorites uf
                       JOIN foods f ON uf.food_id = f.food_id
                       WHERE uf.user_id = ?");
$stmt->execute([$userId]);
$favoriteFoods = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php require 'Partials/head.php'; ?>
    <style>
    :root {
        --green-light: #E3F4E1;
        --green: #5FB65A;
        --green-dark: #3C8D37;
        --gray-100: #F9FAFB;
        --gray-600: #6B7280;
        --gray-700: #4B5563;
        --border-radius: 0.75rem;
        --red: #e76f51;
        --orange: #f4a261;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background-color: var(--gray-100);
        color: var(--gray-700);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    main {
        flex: 1;
        padding: 2rem 1rem;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 0;
        position: relative;
        overflow: hidden;
    }

    .header {
        margin-bottom: 0;
        text-align: center;
        position: relative;
        padding: 3rem 2rem;
        background: linear-gradient(90deg, var(--green), var(--green-dark));
        color: white;
    }

    .header h1 {
        color: white;
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        display: inline-block;
        letter-spacing: 1px;
    }

    .header h1::after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 4px;
        background: white;
        border-radius: 2px;
    }

    .header p {
        color: rgba(255,255,255,0.9);
        font-size: 1.1rem;
        max-width: 700px;
        margin: 1rem auto 0;
        line-height: 1.6;
    }

    /* Tabs styling */
    .tabs {
        display: flex;
        border-bottom: 1px solid #ddd;
        margin-bottom: 2.5rem;
        position: relative;
        flex-wrap: wrap;
        justify-content: center;
        padding: 0 2.5rem;
        padding-top: 1.5rem;
    }

    .tab {
        padding: 1rem 2rem;
        cursor: pointer;
        font-weight: 600;
        color: var(--gray-700);
        position: relative;
        transition: all 0.3s ease;
        white-space: nowrap;
        user-select: none;
        font-size: 1rem;
        letter-spacing: 0.5px;
    }

    .tab.active {
        color: var(--green-dark);
    }

    .tab.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100%;
        height: 3px;
        background-color: var(--green-dark);
    }

    .tab:hover:not(.active) {
        color: var(--green);
    }

    /* Tab content */
    .tab-content {
        display: none;
        animation: fadeIn 0.5s ease;
        padding: 0 2.5rem 2.5rem;
    }

    .tab-content.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Food cards grid */
    .foods-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }

    .food-card {
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        border: 1px solid rgba(95, 182, 90, 0.1);
    }

    .food-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: rgba(95, 182, 90, 0.3);
    }

    .food-image {
        height: 200px;
        width: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .food-card:hover .food-image {
        transform: scale(1.03);
    }

    .food-info {
        padding: 1.5rem;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .food-name {
        font-size: 1.3rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--gray-700);
    }

    .food-reason {
        color: var(--gray-700);
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
        line-height: 1.6;
        flex-grow: 1;
        opacity: 0.9;
    }

    .food-buttons {
        display: flex;
        justify-content: space-between;
        gap: 0.8rem;
    }

    .btn {
        padding: 0.8rem 1.2rem;
        border-radius: var(--border-radius);
        border: none;
        cursor: pointer;
        font-weight: 500;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        flex: 1;
        justify-content: center;
        user-select: none;
    }

    .btn-primary {
        background-color: var(--green);
        color: white;
    }

    .btn-primary:hover {
        background-color: var(--green-dark);
    }

    .btn-outline {
        background-color: transparent;
        border: 1px solid #ddd;
        color: var(--gray-700);
        flex: 1;
    }

    .btn-outline:hover {
        background-color: var(--green-light);
        border-color: var(--green);
        color: var(--green-dark);
    }

    /* Indicator colors for different tabs */
    .recommended-indicator {
        background-color: var(--green);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 30px;
        font-size: 0.8rem;
        display: inline-block;
        margin-bottom: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .avoid-indicator {
        background-color: var(--red);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 30px;
        font-size: 0.8rem;
        display: inline-block;
        margin-bottom: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .moderation-indicator {
        background-color: var(--orange);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 30px;
        font-size: 0.8rem;
        display: inline-block;
        margin-bottom: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Favorites section */
    #favorites-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
    }
    
    #no-favorites-message {
        grid-column: 1 / -1;
        text-align: center;
        color: var(--gray-700);
        font-size: 1.1rem;
        padding: 2rem;
        opacity: 0.7;
    }

    .no-recommendations {
        grid-column: 1 / -1;
        text-align: center;
        padding: 2rem;
        color: var(--gray-600);
    }

    /* Alert styling */
    .alert {
        margin-top: 1rem;
        background-color: rgba(255,255,255,0.2);
        color: white;
        padding: 1rem;
        border-radius: var(--border-radius);
        border: 1px solid rgba(255,255,255,0.3);
    }

    .alert a {
        color: white;
        text-decoration: underline;
        font-weight: 600;
    }

    /* Filter indicators */
    .filter-indicators {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        justify-content: center;
        padding: 0 2.5rem;
    }

    .filter-indicator {
        background-color: var(--green-light);
        color: var(--green-dark);
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-size: 0.85rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        main {
            padding: 1rem;
        }
        
        .container {
            padding: 0;
        }

        .header {
            padding: 2rem 1rem;
        }

        .header h1 {
            font-size: 2.2rem;
        }

        .header p {
            font-size: 1rem;
        }

        .tabs {
            padding: 0 1rem;
            padding-top: 1rem;
        }

        .tab-content {
            padding: 0 1rem 1.5rem;
        }

        .filter-indicators {
            padding: 0 1rem;
        }

        .foods-grid, #favorites-container {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .tab {
            padding: 0.8rem 1.2rem;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 480px) {
        .header h1 {
            font-size: 1.8rem;
        }

        .header p {
            font-size: 0.95rem;
        }

        .foods-grid, #favorites-container {
            grid-template-columns: 1fr;
            gap: 1.2rem;
        }

        .tabs {
            flex-wrap: wrap;
        }

        .tab {
            flex: 1 1 auto;
            text-align: center;
            padding: 0.8rem 0.5rem;
            font-size: 0.85rem;
        }

        .food-buttons {
            flex-direction: column;
            gap: 0.8rem;
        }

        .btn {
            width: 100%;
        }
    }
</style>
</head>
<body>
    <?php require 'Partials/nav.php'; ?>
    
    <main>
        <div class="container">
            <div class="header">
                <h1>Food Recommendations</h1>
                <p>Discover personalized food suggestions tailored to your health needs and preferences. Our recommendations are based on your health profile to help you make the best dietary choices.</p>
                
                <?php if (empty($conditions)): ?>
                    <div class="alert alert-info" style="margin-top: 1rem; background-color: var(--green-light); color: var(--green-dark); padding: 1rem; border-radius: var(--border-radius);">
                        <i class="fas fa-info-circle"></i> You haven't selected any medical conditions in your profile. 
                        <a href="profileform.php" style="color: var(--green-dark); text-decoration: underline;">Update your profile</a> to get personalized recommendations.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Display active filters -->
            <div class="filter-indicators">
                <?php if (!empty($conditions)): ?>
                    <div class="filter-indicator">
                        <i class="fas fa-heartbeat"></i> Medical Conditions: 
                        <?= implode(', ', array_column($conditions, 'name')) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($allergens)): ?>
                    <div class="filter-indicator">
                        <i class="fas fa-allergies"></i> Allergens: 
                        <?= implode(', ', array_column($allergens, 'name')) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($dietPreference)): ?>
                    <div class="filter-indicator">
                        <i class="fas fa-utensils"></i> Diet: <?= $dietPreference ?>
                    </div>
                <?php else: ?>
                    <div class="filter-indicator">
                        <i class="fas fa-utensils"></i> Diet: Not specified
                    </div>
                <?php endif; ?>
            </div>

            <div class="tabs" role="tablist" aria-label="Food recommendation sections">
                <div class="tab active" data-tab="recommended" role="tab" aria-selected="true" tabindex="0">Recommended</div>
                <div class="tab" data-tab="avoid" role="tab" aria-selected="false" tabindex="-1">To Avoid</div>
                <div class="tab" data-tab="moderation" role="tab" aria-selected="false" tabindex="-1">Moderation</div>
                <div class="tab favorites-tab" data-tab="favorites" role="tab" aria-selected="false" tabindex="-1">Your Favorites</div>
            </div>

            <!-- Recommended Foods Tab -->
            <div id="recommended" class="tab-content active" role="tabpanel" aria-hidden="false">
                <div class="foods-grid" id="recommended-grid">
                    <?php if (!empty($recommendedFoods)): ?>
                        <?php foreach ($recommendedFoods as $food): ?>
                            <div class="food-card" data-id="<?= $food['food_id'] ?>" data-name="<?= htmlspecialchars($food['name']) ?>" data-reason="<?= htmlspecialchars($food['reason']) ?>">
                                <img class="food-image" src="<?= $food['image_url'] ? htmlspecialchars($food['image_url']) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80' ?>" alt="<?= htmlspecialchars($food['name']) ?>" />
                                <div class="food-info">
                                    <span class="recommended-indicator">Recommended</span>
                                    <h3 class="food-name"><?= htmlspecialchars($food['name']) ?></h3>
                                    <p class="food-reason"><?= htmlspecialchars($food['reason']) ?></p>
                                    <div class="food-buttons">
                                        <form method="POST" style="width: 100%;">
                                            <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                                            <?php 
                                            $isFavorite = false;
                                            foreach ($favoriteFoods as $fav) {
                                                if ($fav['food_id'] == $food['food_id']) {
                                                    $isFavorite = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php if ($isFavorite): ?>
                                                <button type="submit" name="favorite_action" value="remove" class="btn" style="background-color: var(--red); color: white; border: none;">
                                                    ❌ Remove from Favorites
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="favorite_action" value="add" class="btn btn-outline">
                                                    ❤️ Add to Favorites
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        <button class="btn btn-primary btn-nutrition" onclick="showNutritionInfo('<?= htmlspecialchars($food['name']) ?>', '<?= htmlspecialchars($food['reason']) ?>')">
                                            ℹ️ Nutrition Info
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-recommendations">
                            <p>No recommended foods found based on your profile.</p>
                            <?php if (empty($conditions)): ?>
                                <p><a href="profileform.php" style="color: var(--green-dark);">Update your profile</a> with your medical conditions to get personalized recommendations.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Avoid Foods Tab -->
            <div id="avoid" class="tab-content" role="tabpanel" aria-hidden="true">
                <div class="foods-grid" id="avoid-grid">
                    <?php if (!empty($avoidFoods)): ?>
                        <?php foreach ($avoidFoods as $food): ?>
                            <div class="food-card" data-id="<?= $food['food_id'] ?>" data-name="<?= htmlspecialchars($food['name']) ?>" data-reason="<?= htmlspecialchars($food['reason']) ?>">
                                <img class="food-image" src="<?= $food['image_url'] ? htmlspecialchars($food['image_url']) : 'https://images.unsplash.com/photo-1555532530-6dd0a5ee1f50?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80' ?>" alt="<?= htmlspecialchars($food['name']) ?>" />
                                <div class="food-info">
                                    <span class="avoid-indicator">Avoid</span>
                                    <h3 class="food-name"><?= htmlspecialchars($food['name']) ?></h3>
                                    <p class="food-reason"><?= htmlspecialchars($food['reason']) ?></p>
                                    <div class="food-buttons">
                                        <form method="POST" style="width: 100%;">
                                            <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                                            <?php 
                                            $isFavorite = false;
                                            foreach ($favoriteFoods as $fav) {
                                                if ($fav['food_id'] == $food['food_id']) {
                                                    $isFavorite = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php if ($isFavorite): ?>
                                                <button type="submit" name="favorite_action" value="remove" class="btn" style="background-color: var(--red); color: white; border: none;">
                                                    ❌ Remove from Favorites
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="favorite_action" value="add" class="btn btn-outline">
                                                    ❤️ Add to Favorites
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        <button class="btn btn-primary btn-nutrition" onclick="showNutritionInfo('<?= htmlspecialchars($food['name']) ?>', '<?= htmlspecialchars($food['reason']) ?>')">
                                            ℹ️ Nutrition Info
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-recommendations">
                            <p>No foods to avoid found based on your profile.</p>
                            <?php if (empty($conditions)): ?>
                                <p><a href="profileform.php" style="color: var(--green-dark);">Update your profile</a> with your medical conditions to get personalized recommendations.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Moderation Foods Tab -->
            <div id="moderation" class="tab-content" role="tabpanel" aria-hidden="true">
                <div class="foods-grid" id="moderation-grid">
                    <?php if (!empty($moderationFoods)): ?>
                        <?php foreach ($moderationFoods as $food): ?>
                            <div class="food-card" data-id="<?= $food['food_id'] ?>" data-name="<?= htmlspecialchars($food['name']) ?>" data-reason="<?= htmlspecialchars($food['reason']) ?>">
                                <img class="food-image" src="<?= $food['image_url'] ? htmlspecialchars($food['image_url']) : 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80' ?>" alt="<?= htmlspecialchars($food['name']) ?>" />
                                <div class="food-info">
                                    <span class="moderation-indicator">Moderation</span>
                                    <h3 class="food-name"><?= htmlspecialchars($food['name']) ?></h3>
                                    <p class="food-reason"><?= htmlspecialchars($food['reason']) ?></p>
                                    <div class="food-buttons">
                                        <form method="POST" style="width: 100%;">
                                            <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                                            <?php 
                                            $isFavorite = false;
                                            foreach ($favoriteFoods as $fav) {
                                                if ($fav['food_id'] == $food['food_id']) {
                                                    $isFavorite = true;
                                                    break;
                                                }
                                            }
                                            ?>
                                            <?php if ($isFavorite): ?>
                                                <button type="submit" name="favorite_action" value="remove" class="btn" style="background-color: var(--red); color: white; border: none;">
                                                    ❌ Remove from Favorites
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" name="favorite_action" value="add" class="btn btn-outline">
                                                    ❤️ Add to Favorites
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        <button class="btn btn-primary btn-nutrition" onclick="showNutritionInfo('<?= htmlspecialchars($food['name']) ?>', '<?= htmlspecialchars($food['reason']) ?>')">
                                            ℹ️ Nutrition Info
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-recommendations">
                            <p>No foods to eat in moderation found based on your profile.</p>
                            <?php if (empty($conditions)): ?>
                                <p><a href="profileform.php" style="color: var(--green-dark);">Update your profile</a> with your medical conditions to get personalized recommendations.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Favorites Tab Content -->
            <div id="favorites" class="tab-content" role="tabpanel" aria-hidden="true">
                <div id="favorites-container" class="foods-grid" aria-live="polite" aria-atomic="true">
                    <?php if (!empty($favoriteFoods)): ?>
                        <?php foreach ($favoriteFoods as $food): ?>
                            <div class="food-card" data-id="<?= $food['food_id'] ?>" data-name="<?= htmlspecialchars($food['name']) ?>">
                                <img class="food-image" src="<?= $food['image_url'] ? htmlspecialchars($food['image_url']) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80' ?>" alt="<?= htmlspecialchars($food['name']) ?>" />
                                <div class="food-info">
                                    <h3 class="food-name"><?= htmlspecialchars($food['name']) ?></h3>
                                    <p class="food-reason">One of your favorite foods</p>
                                    <div class="food-buttons">
                                        <form method="POST" style="width: 100%;">
                                            <input type="hidden" name="food_id" value="<?= $food['food_id'] ?>">
                                            <button type="submit" name="favorite_action" value="remove" class="btn" style="background-color: var(--red); color: white; border: none;">
                                                ❌ Remove from Favorites
                                            </button>
                                        </form>
                                        <button class="btn btn-primary btn-nutrition" onclick="showNutritionInfo('<?= htmlspecialchars($food['name']) ?>', 'One of your favorite foods')">
                                            ℹ️ Nutrition Info
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p id="no-favorites-message">You have no favorite foods yet. Start adding some by clicking the ❤️ button on any food card!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php require 'Partials/footer.php'; ?>

    <script>
        function showNutritionInfo(name, reason) {
            alert(`Nutritional Breakdown for ${name}:\n\n${reason}`);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabId = tab.getAttribute('data-tab');
                    switchToTab(tabId);
                });

                tab.addEventListener('keydown', e => {
                    if(e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                        e.preventDefault();
                        const currentIndex = Array.from(tabs).indexOf(e.target);
                        let newIndex = e.key === 'ArrowRight' ? currentIndex + 1 : currentIndex -1;
                        if(newIndex < 0) newIndex = tabs.length - 1;
                        if(newIndex >= tabs.length) newIndex = 0;
                        tabs[newIndex].focus();
                    }
                    if(e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        const tabId = e.target.getAttribute('data-tab');
                        switchToTab(tabId);
                    }
                });
            });

            function switchToTab(tabId) {
                tabs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                    t.setAttribute('tabindex', '-1');
                });
                tabContents.forEach(c => {
                    c.classList.remove('active');
                    c.setAttribute('aria-hidden', 'true');
                });

                const activeTab = document.querySelector(`.tab[data-tab="${tabId}"]`);
                const activeContent = document.getElementById(tabId);

                if (activeTab && activeContent) {
                    activeTab.classList.add('active');
                    activeTab.setAttribute('aria-selected', 'true');
                    activeTab.setAttribute('tabindex', '0');
                    activeTab.focus();

                    activeContent.classList.add('active');
                    activeContent.setAttribute('aria-hidden', 'false');
                }
            }
        });
    </script>
</body>
</html>
