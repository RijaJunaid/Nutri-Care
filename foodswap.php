<?php
require 'config.php';

// Function to get food swaps from database
function getFoodSwaps($conn, $searchTerm = '') {
    try {
        if (!empty($searchTerm)) {
            // Search for foods matching the search term
            $stmt = $conn->prepare("
                SELECT fs.*, 
                       original.name AS original_name, original.image_url AS original_image,
                       better.name AS better_name, better.image_url AS better_image,
                       better.calories, better.protein, better.carbs, better.fat, better.fiber,
                       fs.reason, fs.benefit_description
                FROM food_swaps fs
                JOIN foods original ON fs.original_food_id = original.food_id
                JOIN foods better ON fs.better_food_id = better.food_id
                WHERE original.name LIKE ?
            ");
            $searchParam = "%" . $searchTerm . "%";
            $stmt->execute([$searchParam]);
            return $stmt->fetchAll();
        } else {
            // Get popular swaps (limit to 4)
            $stmt = $conn->prepare("
                SELECT fs.*, 
                       original.name AS original_name, original.image_url AS original_image,
                       better.name AS better_name, better.image_url AS better_image,
                       better.calories, better.protein, better.carbs, better.fat, better.fiber,
                       fs.reason, fs.benefit_description
                FROM food_swaps fs
                JOIN foods original ON fs.original_food_id = original.food_id
                JOIN foods better ON fs.better_food_id = better.food_id
                LIMIT 4
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Database error in getFoodSwaps: " . $e->getMessage());
        return [];
    }
}

// Get search term from URL
$searchTerm = $_GET['search'] ?? '';
$swapSuggestions = getFoodSwaps($conn, $searchTerm);
$popularSwaps = getFoodSwaps($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Food Swap | NutriCare</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .hero {
            background-color: #f7fafc;
            padding: 4rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .card-shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .transition-shadow {
            transition: box-shadow 0.3s ease;
        }
        .bg-green-50 {
            background-color: #f0fff4;
        }
        .border-green-200 {
            border-color: #9ae6b4;
        }
        .text-green-700 {
            color: #2f855a;
        }
        .bg-red-50 {
            background-color: #fff5f5;
        }
        .border-red-200 {
            border-color: #fed7d7;
        }
        .text-red-700 {
            color: #c53030;
        }
        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            height: 1.25rem;
            width: 1.25rem;
        }
        .food-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin: 0 auto;
        }
        .food-image-container {
            display: flex;
            justify-content: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <!-- Include your header/navigation -->
    <?php require 'Partials/head.php'; ?>
    <?php require 'Partials/nav.php'; ?>

    <!-- Hero Section -->
    <div class="hero">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800 mb-4">
                    Food Swap
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                    Make healthier choices by discovering better alternatives to your favorite foods. 
                    Enter a food below and we'll suggest a healthier swap!
                </p>
            </div>
        </div>
    </div>

    <!-- Search Section -->
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto mb-8 bg-white rounded-lg card-shadow">
            <div class="p-6">
                <h2 class="text-center text-2xl text-gray-800 font-bold mb-6">
                    Find Your Food Swap
                </h2>
                <form method="GET" action="foodswap.php" class="flex gap-3">
                    <div class="flex-1 relative">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input
                            type="text"
                            name="search"
                            placeholder="Enter a food (e.g., White Rice, Potato Chips)"
                            value="<?php echo htmlspecialchars($searchTerm); ?>"
                            class="w-full pl-10 pr-4 py-3 text-lg border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        >
                    </div>
                    <button 
                        type="submit"
                        class="px-8 py-3 text-lg bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors"
                    >
                        Find Swap
                    </button>
                </form>
            </div>
        </div>

        <!-- Results Section -->
        <?php if (!empty($searchTerm) && !empty($swapSuggestions)): ?>
            <div class="max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                    Healthier Alternative Found!
                </h2>
                <?php foreach ($swapSuggestions as $swap): 
                    // Calculate calories saved (example - you'd need to get original food calories)
                    $calories_saved = 0;
                    $originalStmt = $conn->prepare("SELECT calories FROM foods WHERE food_id = ?");
                    $originalStmt->execute([$swap['original_food_id']]);
                    $originalFood = $originalStmt->fetch();
                    if ($originalFood) {
                        $calories_saved = max(0, $originalFood['calories'] - $swap['calories']);
                    }
                ?>
                    <div class="mb-6 bg-white rounded-lg card-shadow-lg">
                        <div class="p-8">
                            <div class="flex flex-col md:flex-row items-center justify-between mb-6">
                                <div class="text-center flex-1 mb-4 md:mb-0">
                                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Current Choice</h3>
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                        <div class="food-image-container">
                                            <?php if (!empty($swap['original_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($swap['original_image']); ?>" alt="<?php echo htmlspecialchars($swap['original_name']); ?>" class="food-image">
                                            <?php else: ?>
                                                <div class="food-image bg-gray-200 flex items-center justify-center text-gray-500">
                                                    No Image
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-lg font-medium text-red-700"><?php echo htmlspecialchars($swap['original_name']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="flex-shrink-0 mx-0 md:mx-8 my-4 md:my-0">
                                    <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                                
                                <div class="text-center flex-1">
                                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Better Choice</h3>
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                        <div class="food-image-container">
                                            <?php if (!empty($swap['better_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($swap['better_image']); ?>" alt="<?php echo htmlspecialchars($swap['better_name']); ?>" class="food-image">
                                            <?php else: ?>
                                                <div class="food-image bg-gray-200 flex items-center justify-center text-gray-500">
                                                    No Image
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-lg font-medium text-green-700"><?php echo htmlspecialchars($swap['better_name']); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gray-50 rounded-lg p-6">
                                <div class="flex items-start gap-3 mb-4">
                                    <svg class="h-6 w-6 text-green-500 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-2">Why This Swap?</h4>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($swap['reason']); ?></p>
                                    </div>
                                </div>
                                
                                <?php if ($calories_saved > 0): ?>
                                    <div class="bg-white rounded-lg p-4 border border-green-200">
                                        <p class="text-sm font-medium text-green-700">
                                            💡 You could save approximately <span class="font-bold"><?php echo $calories_saved; ?> calories</span> per serving with this swap!
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- No Results -->
        <?php if (!empty($searchTerm) && empty($swapSuggestions)): ?>
            <div class="max-w-2xl mx-auto bg-white rounded-lg card-shadow">
                <div class="p-8 text-center">
                    <p class="text-gray-600 mb-4">
                        No swap suggestions found for "<?php echo htmlspecialchars($searchTerm); ?>". 
                    </p>
                    <p class="text-sm text-gray-500">
                        Try searching for common foods like "white rice", "potato chips", or "fried chicken".
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Popular Swaps Section -->
        <?php if (empty($searchTerm)): ?>
            <div class="max-w-4xl mx-auto">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                    Popular Food Swaps
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($popularSwaps as $swap): ?>
                        <div class="bg-white rounded-lg card-shadow transition-shadow hover:card-shadow-lg">
                            <div class="p-6">
                                <div class="flex flex-col md:flex-row items-center">
                                    <div class="flex-1 text-center mb-4 md:mb-0">
                                        <div class="food-image-container">
                                            <?php if (!empty($swap['original_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($swap['original_image']); ?>" alt="<?php echo htmlspecialchars($swap['original_name']); ?>" class="food-image">
                                            <?php else: ?>
                                                <div class="food-image bg-gray-200 flex items-center justify-center text-gray-500">
                                                    No Image
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-md font-medium text-red-700"><?php echo htmlspecialchars($swap['original_name']); ?></p>
                                    </div>
                                    
                                    <div class="mx-4 my-2">
                                        <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </div>
                                    
                                    <div class="flex-1 text-center">
                                        <div class="food-image-container">
                                            <?php if (!empty($swap['better_image'])): ?>
                                                <img src="<?php echo htmlspecialchars($swap['better_image']); ?>" alt="<?php echo htmlspecialchars($swap['better_name']); ?>" class="food-image">
                                            <?php else: ?>
                                                <div class="food-image bg-gray-200 flex items-center justify-center text-gray-500">
                                                    No Image
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-md font-medium text-green-700"><?php echo htmlspecialchars($swap['better_name']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="flex items-start gap-2">
                                        <svg class="h-5 w-5 text-green-500 flex-shrink-0 mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($swap['reason']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Include your footer -->
    <?php require 'Partials/footer.php'; ?>
</body>
</html>
