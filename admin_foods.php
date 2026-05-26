<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle food deletion
if (isset($_GET['delete'])) {
    $food_id = (int)$_GET['delete'];
    try {
        $conn->beginTransaction();
        
        // Delete related records first
        $conn->prepare("DELETE FROM food_recommendations WHERE food_id = ?")->execute([$food_id]);
        $conn->prepare("DELETE FROM food_swaps WHERE original_food_id = ? OR better_food_id = ?")->execute([$food_id, $food_id]);
        $conn->prepare("DELETE FROM user_favorites WHERE food_id = ?")->execute([$food_id]);
        $conn->prepare("DELETE FROM child_nutrition_recommendations WHERE food_id = ?")->execute([$food_id]);
        
        // Then delete the food
        $conn->prepare("DELETE FROM foods WHERE food_id = ?")->execute([$food_id]);
        
        $conn->commit();
        $_SESSION['message'] = "Food item deleted successfully";
        header('Location: admin_foods.php');
        exit;
    } catch (PDOException $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error deleting food item: " . $e->getMessage();
    }
}

// Get all foods with category names
$foods = $conn->query("
    SELECT f.*, fc.name as category_name 
    FROM foods f
    LEFT JOIN food_categories fc ON f.category_id = fc.category_id
    ORDER BY f.name
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Manage Foods</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
       :root {
            --green-light: #E3F4E1;
            --green: #5FB65A;
            --green-dark: #3C8D37;
            --sidebar-width: 250px;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: var(--green-dark);
            color: white;
            padding: 20px 0;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 10px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }
         @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content, .navbar-admin {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>NutriCare Admin</h3>
        </div>
        
        <div class="sidebar-menu">
            <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin_nutritionists.php"><i class="fas fa-user-md"></i> Nutritionists</a>
            <a href="admin_foods.php"><i class="fas fa-utensils"></i> Foods</a>
            <a href="admin_conditions.php"><i class="fas fa-heartbeat"></i> Medical Conditions</a>
            <a href="admin_allergens.php"><i class="fas fa-allergies"></i> Allergens</a>
            <a href="admin_consultations.php"><i class="fas fa-calendar-check"></i> Consultations</a>
            <a href="admin_reviews.php"><i class="fas fa-star"></i> Website Reviews</a>
            <a href="admin_faqs.php"><i class="fas fa-question-circle"></i> FAQs</a>
            <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <!-- Navbar -->
    <nav class="navbar-admin">
        <div class="d-flex justify-content-between w-100">
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div>
                <span class="text-muted">Welcome, Admin</span>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Foods</h2>
            <div>
                <a href="admin_food_add.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Add Food
                </a>
                <a href="admin_food_categories.php" class="btn btn-info">
                    <i class="fas fa-list"></i> Manage Categories
                </a>
            </div>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Calories</th>
                                <th>Protein</th>
                                <th>Carbs</th>
                                <th>Fat</th>
                                <th>Allergen</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($foods as $food): ?>
                                <tr>
                                    <td><?php echo $food['food_id']; ?></td>
                                    <td><?php echo htmlspecialchars($food['name']); ?></td>
                                    <td><?php echo $food['category_name'] ?: '-'; ?></td>
                                    <td><?php echo $food['calories']; ?></td>
                                    <td><?php echo $food['protein']; ?>g</td>
                                    <td><?php echo $food['carbs']; ?>g</td>
                                    <td><?php echo $food['fat']; ?>g</td>
                                    <td>
                                        <?php if ($food['is_common_allergen']): ?>
                                            <span class="badge bg-danger">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="admin_food_edit.php?id=<?php echo $food['food_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="admin_foods.php?delete=<?php echo $food['food_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this food item?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <a href="admin_food_recommendations.php?id=<?php echo $food['food_id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-heartbeat"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
