<?php
session_start();
require_once 'config.php';

// Check if admin is logged in - using your existing admin login system
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_allergen'])) {
        // Add new allergen
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (!empty($name)) {
            try {
                $stmt = $conn->prepare("INSERT INTO allergens (name, description) VALUES (?, ?)");
                $stmt->execute([$name, $description]);
                
                $_SESSION['message'] = "Allergen added successfully!";
                header('Location: admin_allergens.php');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error adding allergen: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Allergen name cannot be empty!";
        }
    } 
    elseif (isset($_POST['update_allergen'])) {
        // Update existing allergen
        $allergen_id = (int)$_POST['allergen_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (!empty($name)) {
            try {
                $stmt = $conn->prepare("UPDATE allergens SET name = ?, description = ? WHERE allergen_id = ?");
                $stmt->execute([$name, $description, $allergen_id]);
                
                $_SESSION['message'] = "Allergen updated successfully!";
                header('Location: admin_allergens.php');
                exit;
            } catch (PDOException $e) {
                $_SESSION['error'] = "Error updating allergen: " . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = "Allergen name cannot be empty!";
        }
    } 
    elseif (isset($_POST['delete_allergen'])) {
        // Delete allergen
        $allergen_id = (int)$_POST['allergen_id'];
        
        try {
            // First check if allergen is in use
            $stmt = $conn->prepare("SELECT COUNT(*) FROM user_allergens WHERE allergen_id = ?");
            $stmt->execute([$allergen_id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $_SESSION['error'] = "Cannot delete allergen - it's currently assigned to users!";
            } else {
                $stmt = $conn->prepare("DELETE FROM allergens WHERE allergen_id = ?");
                $stmt->execute([$allergen_id]);
                
                $_SESSION['message'] = "Allergen deleted successfully!";
            }
            
            header('Location: admin_allergens.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting allergen: " . $e->getMessage();
            header('Location: admin_allergens.php');
            exit;
        }
    }
}

// Fetch all allergens
$allergens = [];
try {
    $allergens = $conn->query("SELECT * FROM allergens ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching allergens: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Manage Allergens</title>
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
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .action-btns .btn {
            margin-right: 5px;
        }
        
        .badge-allergen {
            background-color: #6f42c1;
            color: white;
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
            <a href="admin_allergens.php" class="active"><i class="fas fa-allergies"></i> Allergens</a>
            <a href="admin_consultations.php"><i class="fas fa-calendar-check"></i> Consultations</a>
            <a href="admin_reviews.php"><i class="fas fa-star"></i> Website Reviews</a>
            <a href="admin_faqs.php"><i class="fas fa-question-circle"></i> FAQs</a>
            <a href="admin_logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Allergens</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAllergenModal">
                <i class="fas fa-plus"></i> Add Allergen
            </button>
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
                                <th>Description</th>
                                <th>Common</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allergens)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No allergens found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allergens as $allergen): ?>
                                    <tr>
                                        <td><?php echo $allergen['allergen_id']; ?></td>
                                        <td><?php echo htmlspecialchars($allergen['name']); ?></td>
                                        <td><?php echo htmlspecialchars($allergen['description']); ?></td>
                                        <td>
                                            <?php if (in_array($allergen['name'], ['Nuts', 'Dairy', 'Gluten', 'Shellfish', 'Eggs', 'Soy', 'Fish', 'Wheat'])): ?>
                                                <span class="badge badge-allergen">Common</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($allergen['created_at'])); ?></td>
                                        <td class="action-btns">
                                            <button class="btn btn-sm btn-primary edit-btn" 
                                                    data-id="<?php echo $allergen['allergen_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($allergen['name']); ?>"
                                                    data-desc="<?php echo htmlspecialchars($allergen['description']); ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this allergen?');">
                                                <input type="hidden" name="allergen_id" value="<?php echo $allergen['allergen_id']; ?>">
                                                <button type="submit" name="delete_allergen" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Allergen Modal -->
    <div class="modal fade" id="addAllergenModal" tabindex="-1" aria-labelledby="addAllergenModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAllergenModalLabel">Add New Allergen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Allergen Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_allergen" class="btn btn-success">Add Allergen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Allergen Modal -->
    <div class="modal fade" id="editAllergenModal" tabindex="-1" aria-labelledby="editAllergenModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="allergen_id" id="edit_allergen_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAllergenModalLabel">Edit Allergen</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Allergen Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_allergen" class="btn btn-primary">Update Allergen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const allergenId = this.getAttribute('data-id');
                const allergenName = this.getAttribute('data-name');
                const allergenDesc = this.getAttribute('data-desc');
                
                document.getElementById('edit_allergen_id').value = allergenId;
                document.getElementById('edit_name').value = allergenName;
                document.getElementById('edit_description').value = allergenDesc;
                
                const editModal = new bootstrap.Modal(document.getElementById('editAllergenModal'));
                editModal.show();
            });
        });
    </script>
</body>
</html>
