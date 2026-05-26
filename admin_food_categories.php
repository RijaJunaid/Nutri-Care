<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle category deletion
if (isset($_GET['delete'])) {
    $category_id = (int)$_GET['delete'];
    try {
        // Check if category is in use
        $stmt = $conn->prepare("SELECT COUNT(*) FROM foods WHERE category_id = ?");
        $stmt->execute([$category_id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error'] = "Cannot delete category - it is being used by food items";
        } else {
            $conn->prepare("DELETE FROM food_categories WHERE category_id = ?")->execute([$category_id]);
            $_SESSION['message'] = "Category deleted successfully";
        }
        
        header('Location: admin_food_categories.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting category: " . $e->getMessage();
    }
}

// Handle form submission for adding/editing categories
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (isset($_POST['category_id']) && !empty($_POST['category_id'])) {
            // Update existing category
            $category_id = (int)$_POST['category_id'];
            $stmt = $conn->prepare("UPDATE food_categories SET name = ?, description = ? WHERE category_id = ?");
            $stmt->execute([$name, $description, $category_id]);
            $_SESSION['message'] = "Category updated successfully";
        } else {
            // Add new category
            $stmt = $conn->prepare("INSERT INTO food_categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $_SESSION['message'] = "Category added successfully";
        }
        
        header('Location: admin_food_categories.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error saving category: " . $e->getMessage();
    }
}

// Get all categories
$categories = $conn->query("SELECT * FROM food_categories ORDER BY name")->fetchAll();

// Get category to edit if specified
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_category = $conn->query("SELECT * FROM food_categories WHERE category_id = " . (int)$_GET['edit'])->fetch();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Manage Food Categories</title>
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
            <h2>Manage Food Categories</h2>
            <a href="admin_foods.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Foods
            </a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Add/Edit Category Form -->
            <div class="col-md-5 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><?php echo $edit_category ? 'Edit Category' : 'Add New Category'; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php if ($edit_category): ?>
                                <input type="hidden" name="category_id" value="<?php echo $edit_category['category_id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                    value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                    echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; 
                                ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> <?php echo $edit_category ? 'Update' : 'Save'; ?> Category
                            </button>
                            
                            <?php if ($edit_category): ?>
                                <a href="admin_food_categories.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Categories List -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h5>Existing Categories</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <p class="text-muted">No categories found</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo $category['category_id']; ?></td>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                                <td>
                                                    <a href="admin_food_categories.php?edit=<?php echo $category['category_id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="admin_food_categories.php?delete=<?php echo $category['category_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
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