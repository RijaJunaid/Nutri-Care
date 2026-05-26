<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Get counts for dashboard
try {
    $user_count = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $nutritionist_count = $conn->query("SELECT COUNT(*) FROM nutritionists")->fetchColumn();
    $food_count = $conn->query("SELECT COUNT(*) FROM foods")->fetchColumn();
    $reviews_count = $conn->query("SELECT COUNT(*) FROM website_reviews")->fetchColumn();
    $consultation_count = $conn->query("SELECT COUNT(*) FROM consultations")->fetchColumn();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Admin Dashboard</title>
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
        <h2>Dashboard Overview</h2>
        
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card-counter bg-primary text-white">
                    <i class="fas fa-users"></i>
                    <span class="count-numbers"><?php echo $user_count; ?></span>
                    <span class="count-name">Users</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card-counter bg-success text-white">
                    <i class="fas fa-user-md"></i>
                    <span class="count-numbers"><?php echo $nutritionist_count; ?></span>
                    <span class="count-name">Nutritionists</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card-counter bg-info text-white">
                    <i class="fas fa-utensils"></i>
                    <span class="count-numbers"><?php echo $food_count; ?></span>
                    <span class="count-name">Food Items</span>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card-counter bg-warning text-dark">
                    <i class="fas fa-calendar-check"></i>
                    <span class="count-numbers"><?php echo $consultation_count; ?></span>
                    <span class="count-name">Consultations</span>
                </div>
            </div>
            <div class="col-md-3">
                 <div class="card-counter bg-secondary text-white">
                   <i class="fas fa-star"></i>
                    <span class="count-numbers"><?php echo $reviews_count; ?></span>
                     <span class="count-name">Reviews</span>
               </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_users = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();
                        if ($recent_users): ?>
                            <ul class="list-group">
                                <?php foreach ($recent_users as $user): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($user['name']); ?>
                                        <span class="badge bg-secondary"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No users found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Consultations</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_consultations = $conn->query("
                            SELECT c.*, u.name as user_name, n.name as nutritionist_name 
                            FROM consultations c
                            JOIN users u ON c.user_id = u.user_id
                            JOIN nutritionists n ON c.nutritionist_id = n.nutritionist_id
                            ORDER BY c.scheduled_time DESC LIMIT 5
                        ")->fetchAll();
                        if ($recent_consultations): ?>
                            <ul class="list-group">
                                <?php foreach ($recent_consultations as $consult): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <span><?php echo htmlspecialchars($consult['user_name']); ?> → <?php echo htmlspecialchars($consult['nutritionist_name']); ?></span>
                                            <span class="badge bg-<?php echo $consult['status'] === 'Completed' ? 'success' : ($consult['status'] === 'Confirmed' ? 'primary' : 'warning'); ?>">
                                                <?php echo $consult['status']; ?>
                                            </span>
                                        </div>
                                        <small class="text-muted"><?php echo date('M d, Y H:i', strtotime($consult['scheduled_time'])); ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No consultations found.</p>
                        <?php endif; ?>
                    </div>
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
