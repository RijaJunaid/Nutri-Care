<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle review deletion
if (isset($_GET['delete'])) {
    $review_id = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM website_reviews WHERE review_id = ?");
        $stmt->execute([$review_id]);
        $_SESSION['success'] = "Review deleted successfully";
        header("Location: admin_reviews.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting review: " . $e->getMessage();
    }
}

// Get all reviews
try {
    $reviews = $conn->query("
        SELECT wr.*, u.name as user_name, u.email as user_email 
        FROM website_reviews wr
        LEFT JOIN users u ON wr.user_id = u.user_id
        ORDER BY wr.created_at DESC
    ")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Manage Reviews</title>
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
        
        .card-counter {
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 5px;
            background-color: white;
        }
        
        .card-counter i {
            font-size: 2.5rem;
            opacity: 0.5;
        }
        
        .card-counter .count-numbers {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .card-counter .count-name {
            opacity: 0.8;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        
        .navbar-admin {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 20px;
            margin-left: var(--sidebar-width);
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
        
        .star-rating {
            color: #f8d64e;
        }
        
        .review-card {
            border-left: 4px solid #5FB65A;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Sidebar (same as admin_dashboard.php) -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>NutriCare Admin</h3>
        </div>
        
        <div class="sidebar-menu">
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin_nutritionists.php"><i class="fas fa-user-md"></i> Nutritionists</a>
            <a href="admin_foods.php"><i class="fas fa-utensils"></i> Foods</a>
            <a href="admin_conditions.php"><i class="fas fa-heartbeat"></i> Medical Conditions</a>
            <a href="admin_allergens.php"><i class="fas fa-allergies"></i> Allergens</a>
            <a href="admin_consultations.php"><i class="fas fa-calendar-check"></i> Consultations</a>
            <a href="admin_reviews.php" class="active"><i class="fas fa-star"></i> Website Reviews</a>
            <a href="admin_faqs.php"><i class="fas fa-question-circle"></i> FAQs</a>
            <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <h2>Website Reviews</h2>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <?php if (empty($reviews)): ?>
                    <div class="alert alert-info">No reviews found.</div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="card review-card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($review['user_name'] ?? 'Anonymous'); ?>
                                            <small class="text-muted"><?php echo htmlspecialchars($review['user_email'] ?? ''); ?></small>
                                        </h5>
                                        <div class="star-rating mb-2">
                                            <?php 
                                            $filled = (int)$review['rating'];
                                            $empty = 5 - $filled;
                                            for ($i = 0; $i < $filled; $i++) {
                                                echo '<i class="fas fa-star"></i>';
                                            }
                                            for ($i = 0; $i < $empty; $i++) {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                            ?>
                                        </div>
                                        <p class="card-text"><?php echo htmlspecialchars($review['comment']); ?></p>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($review['created_at'])); ?></small>
                                        <div class="mt-2">
                                            <a href="admin_reviews.php?delete=<?php echo $review['review_id']; ?>" 
                                               class="btn btn-sm btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this review?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>