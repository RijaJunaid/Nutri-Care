<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Check if food ID is provided
if (!isset($_GET['id'])) {
    header('Location: admin_foods.php');
    exit;
}

$food_id = (int)$_GET['id'];

// Fetch food data
$food = $conn->query("SELECT * FROM foods WHERE food_id = $food_id")->fetch();

if (!$food) {
    $_SESSION['error'] = "Food item not found";
    header('Location: admin_foods.php');
    exit;
}

// Get all medical conditions
$conditions = $conn->query("SELECT * FROM medical_conditions ORDER BY name")->fetchAll();

// Get existing recommendations for this food
$recommendations = $conn->query("
    SELECT fr.*, mc.name as condition_name 
    FROM food_recommendations fr
    JOIN medical_conditions mc ON fr.condition_id = mc.condition_id
    WHERE fr.food_id = $food_id
    ORDER BY mc.name
")->fetchAll();

// Handle recommendation deletion
if (isset($_GET['delete_recommendation'])) {
    $recommendation_id = (int)$_GET['delete_recommendation'];
    try {
        $conn->prepare("DELETE FROM food_recommendations WHERE recommendation_id = ?")->execute([$recommendation_id]);
        $_SESSION['message'] = "Recommendation deleted successfully";
        header("Location: admin_food_recommendations.php?id=$food_id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting recommendation: " . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $condition_id = (int)$_POST['condition_id'];
        $recommendation_type = $_POST['recommendation_type'];
        $reason = trim($_POST['reason']);
        $scientific_evidence = trim($_POST['scientific_evidence']);

        // Check if recommendation already exists for this food and condition
        $stmt = $conn->prepare("SELECT recommendation_id FROM food_recommendations WHERE food_id = ? AND condition_id = ?");
        $stmt->execute([$food_id, $condition_id]);
        
        if ($stmt->fetch()) {
            $_SESSION['error'] = "A recommendation already exists for this food and condition";
        } else {
            // Insert new recommendation
            $stmt = $conn->prepare("INSERT INTO food_recommendations (food_id, condition_id, recommendation_type, reason, scientific_evidence) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$food_id, $condition_id, $recommendation_type, $reason, $scientific_evidence]);
            $_SESSION['message'] = "Recommendation added successfully";
        }
        
        header("Location: admin_food_recommendations.php?id=$food_id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding recommendation: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Food Recommendations</title>
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
        
        .badge-recommended {
            background-color: #28a745;
        }
        .badge-avoid {
            background-color: #dc3545;
        }
        .badge-moderation {
            background-color: #ffc107;
            color: #212529;
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
            <h2>Food Recommendations for: <?php echo htmlspecialchars($food['name']); ?></h2>
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
            <!-- Add Recommendation Form -->
            <div class="col-md-5 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Add New Recommendation</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="condition_id" class="form-label">Medical Condition</label>
                                <select class="form-select" id="condition_id" name="condition_id" required>
                                    <option value="">-- Select Condition --</option>
                                    <?php foreach ($conditions as $condition): ?>
                                        <option value="<?php echo $condition['condition_id']; ?>"><?php echo htmlspecialchars($condition['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="recommendation_type" class="form-label">Recommendation Type</label>
                                <select class="form-select" id="recommendation_type" name="recommendation_type" required>
                                    <option value="Recommended">Recommended</option>
                                    <option value="Avoid">Avoid</option>
                                    <option value="Moderation">Moderation</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reason" class="form-label">Reason</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="scientific_evidence" class="form-label">Scientific Evidence</label>
                                <textarea class="form-control" id="scientific_evidence" name="scientific_evidence" rows="3"></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Recommendation
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Existing Recommendations -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h5>Current Recommendations</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recommendations)): ?>
                            <p class="text-muted">No recommendations found for this food</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Condition</th>
                                            <th>Type</th>
                                            <th>Reason</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recommendations as $rec): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($rec['condition_name']); ?></td>
                                                <td>
                                                    <span class="badge <?php 
                                                        echo 'badge-' . strtolower($rec['recommendation_type']);
                                                    ?>">
                                                        <?php echo $rec['recommendation_type']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($rec['reason']); ?></td>
                                                <td>
                                                    <a href="admin_food_recommendations.php?id=<?php echo $food_id; ?>&delete_recommendation=<?php echo $rec['recommendation_id']; ?>" 
                                                       class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this recommendation?')">
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