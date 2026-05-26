<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle consultation status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $consultation_id = (int)$_POST['consultation_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $conn->prepare("UPDATE consultations SET status = ? WHERE consultation_id = ?");
        $stmt->execute([$new_status, $consultation_id]);
        
        $_SESSION['message'] = "Consultation status updated successfully";
        header('Location: admin_consultations.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating consultation: " . $e->getMessage();
    }
}

// Handle consultation deletion
if (isset($_GET['delete'])) {
    $consultation_id = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM consultations WHERE consultation_id = ?");
        $stmt->execute([$consultation_id]);
        
        $_SESSION['message'] = "Consultation deleted successfully";
        header('Location: admin_consultations.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting consultation: " . $e->getMessage();
    }
}

// Fetch all consultations with user and nutritionist details
$consultations = $conn->query("
    SELECT c.*, 
           u.name as user_name, u.email as user_email,
           n.name as nutritionist_name, n.email as nutritionist_email,
           n.hourly_rate
    FROM consultations c
    JOIN users u ON c.user_id = u.user_id
    JOIN nutritionists n ON c.nutritionist_id = n.nutritionist_id
    ORDER BY c.scheduled_time DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Manage Consultations</title>
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
        
        .badge-pending {
            background-color: #FFC107;
            color: #000;
        }
        
        .badge-confirmed {
            background-color: #17A2B8;
            color: #FFF;
        }
        
        .badge-completed {
            background-color: #28A745;
            color: #FFF;
        }
        
        .badge-cancelled {
            background-color: #DC3545;
            color: #FFF;
        }
        
        .badge-paid {
            background-color: #28A745;
            color: #FFF;
        }
        
        .badge-pending-payment {
            background-color: #FFC107;
            color: #000;
        }
        
        .badge-refunded {
            background-color: #6C757D;
            color: #FFF;
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
            <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin_nutritionists.php"><i class="fas fa-user-md"></i> Nutritionists</a>
            <a href="admin_foods.php"><i class="fas fa-utensils"></i> Foods</a>
            <a href="admin_conditions.php"><i class="fas fa-heartbeat"></i> Medical Conditions</a>
            <a href="admin_allergens.php"><i class="fas fa-allergies"></i> Allergens</a>
            <a href="admin_consultations.php" class="active"><i class="fas fa-calendar-check"></i> Consultations</a>
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
            <h2>Manage Consultations</h2>
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
                                <th>User</th>
                                <th>Nutritionist</th>
                                <th>Scheduled Time</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th>Amount</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($consultations as $consultation): ?>
                                <tr>
                                    <td><?php echo $consultation['consultation_id']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($consultation['user_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($consultation['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($consultation['nutritionist_name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($consultation['nutritionist_email']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('M d, Y h:i A', strtotime($consultation['scheduled_time'])); ?>
                                    </td>
                                    <td><?php echo $consultation['duration_minutes']; ?> mins</td>
                                    <td>
                                        <?php 
                                        $status_class = '';
                                        switch($consultation['status']) {
                                            case 'Pending': $status_class = 'badge-pending'; break;
                                            case 'Confirmed': $status_class = 'badge-confirmed'; break;
                                            case 'Completed': $status_class = 'badge-completed'; break;
                                            case 'Cancelled': $status_class = 'badge-cancelled'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>"><?php echo $consultation['status']; ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $payment_class = '';
                                        switch($consultation['payment_status']) {
                                            case 'Paid': $payment_class = 'badge-paid'; break;
                                            case 'Pending': $payment_class = 'badge-pending-payment'; break;
                                            case 'Refunded': $payment_class = 'badge-refunded'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $payment_class; ?>"><?php echo $consultation['payment_status']; ?></span>
                                    </td>
                                    <td>
                                        Rs.<?php echo number_format($consultation['amount'] ?? $consultation['hourly_rate'] * ($consultation['duration_minutes'] / 60), 2); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $consultation['consultation_id']; ?>">
                                            <i class="fas fa-edit"></i> Status
                                        </button>
                                        <a href="admin_consultations.php?delete=<?php echo $consultation['consultation_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this consultation?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <a href="<?php echo htmlspecialchars($consultation['meeting_link']); ?>" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-video"></i>
                                        </a>
                                    </td>
                                </tr>
                                
                                <!-- Status Update Modal -->
                                <div class="modal fade" id="statusModal<?php echo $consultation['consultation_id']; ?>" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST">
                                                <input type="hidden" name="consultation_id" value="<?php echo $consultation['consultation_id']; ?>">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="statusModalLabel">Update Consultation Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Current Status: <span class="badge <?php echo $status_class; ?>"><?php echo $consultation['status']; ?></span></label>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="status" class="form-label">New Status</label>
                                                        <select class="form-select" id="status" name="status" required>
                                                            <option value="Pending" <?php echo $consultation['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                                            <option value="Confirmed" <?php echo $consultation['status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                            <option value="Completed" <?php echo $consultation['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                            <option value="Cancelled" <?php echo $consultation['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                        </select>
                                                    </div>
                                                    <?php if($consultation['status'] === 'Completed' || $consultation['status'] === 'Cancelled'): ?>
                                                        <div class="alert alert-warning">
                                                            Changing status from <?php echo $consultation['status']; ?> may require additional actions.
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
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
