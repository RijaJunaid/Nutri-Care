<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_faq'])) {
        // Add new FAQ
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);
        $category = trim($_POST['category']);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        try {
            $stmt = $conn->prepare("INSERT INTO faqs (question, answer, category, is_featured) VALUES (?, ?, ?, ?)");
            $stmt->execute([$question, $answer, $category, $is_featured]);
            
            $_SESSION['message'] = "FAQ added successfully!";
            header('Location: admin_faqs.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error adding FAQ: " . $e->getMessage();
        }
    } 
    elseif (isset($_POST['update_faq'])) {
        // Update existing FAQ
        $faq_id = (int)$_POST['faq_id'];
        $question = trim($_POST['question']);
        $answer = trim($_POST['answer']);
        $category = trim($_POST['category']);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        
        try {
            $stmt = $conn->prepare("UPDATE faqs SET question = ?, answer = ?, category = ?, is_featured = ? WHERE faq_id = ?");
            $stmt->execute([$question, $answer, $category, $is_featured, $faq_id]);
            
            $_SESSION['message'] = "FAQ updated successfully!";
            header('Location: admin_faqs.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating FAQ: " . $e->getMessage();
        }
    } 
    elseif (isset($_POST['delete_faq'])) {
        // Delete FAQ
        $faq_id = (int)$_POST['faq_id'];
        
        try {
            $stmt = $conn->prepare("DELETE FROM faqs WHERE faq_id = ?");
            $stmt->execute([$faq_id]);
            
            $_SESSION['message'] = "FAQ deleted successfully!";
            header('Location: admin_faqs.php');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error deleting FAQ: " . $e->getMessage();
        }
    }
}

// Fetch all FAQs
$faqs = [];
try {
    $faqs = $conn->query("SELECT * FROM faqs ORDER BY is_featured DESC, created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching FAQs: " . $e->getMessage();
}

// Get unique categories for dropdown
$categories = [];
try {
    $categories = $conn->query("SELECT DISTINCT category FROM faqs WHERE category IS NOT NULL AND category != ''")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching categories: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Manage FAQs</title>
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
        
        .badge-featured {
            background-color: #FFC107;
            color: #212529;
        }
        
        .badge-category {
            background-color: #17A2B8;
            color: white;
        }
        
        .faq-question {
            font-weight: 600;
            color: var(--green-dark);
        }
        
        .faq-answer {
            color: #6B7280;
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
            <a href="admin_consultations.php"><i class="fas fa-calendar-check"></i> Consultations</a>
            <a href="admin_reviews.php"><i class="fas fa-star"></i> Website Reviews</a>
            <a href="admin_faqs.php" class="active"><i class="fas fa-question-circle"></i> FAQs</a>
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
            <h2>Manage FAQs</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                <i class="fas fa-plus"></i> Add FAQ
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
                                <th>Question</th>
                                <th>Category</th>
                                <th>Featured</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($faqs)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No FAQs found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($faqs as $faq): ?>
                                    <tr>
                                        <td><?php echo $faq['faq_id']; ?></td>
                                        <td>
                                            <div class="faq-question"><?php echo htmlspecialchars($faq['question']); ?></div>
                                            <div class="faq-answer" style="font-size: 0.875rem; margin-top: 0.25rem;">
                                                <?php echo nl2br(htmlspecialchars(substr($faq['answer'], 0, 100))); ?>...
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($faq['category'])): ?>
                                                <span class="badge badge-category"><?php echo htmlspecialchars($faq['category']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($faq['is_featured']): ?>
                                                <span class="badge badge-featured">Featured</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($faq['created_at'])); ?></td>
                                        <td class="action-btns">
                                            <button class="btn btn-sm btn-primary edit-btn" 
                                                    data-id="<?php echo $faq['faq_id']; ?>"
                                                    data-question="<?php echo htmlspecialchars($faq['question']); ?>"
                                                    data-answer="<?php echo htmlspecialchars($faq['answer']); ?>"
                                                    data-category="<?php echo htmlspecialchars($faq['category']); ?>"
                                                    data-featured="<?php echo $faq['is_featured']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                                                <input type="hidden" name="faq_id" value="<?php echo $faq['faq_id']; ?>">
                                                <button type="submit" name="delete_faq" class="btn btn-sm btn-danger">
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

    <!-- Add FAQ Modal -->
    <div class="modal fade" id="addFaqModal" tabindex="-1" aria-labelledby="addFaqModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addFaqModalLabel">Add New FAQ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="question" class="form-label">Question *</label>
                            <input type="text" class="form-control" id="question" name="question" required>
                        </div>
                        <div class="mb-3">
                            <label for="answer" class="form-label">Answer *</label>
                            <textarea class="form-control" id="answer" name="answer" rows="5" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="category" name="category" list="category-list">
                                    <datalist id="category-list">
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 form-check" style="padding-top: 2rem;">
                                    <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured">
                                    <label class="form-check-label" for="is_featured">Featured FAQ</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_faq" class="btn btn-success">Add FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit FAQ Modal -->
    <div class="modal fade" id="editFaqModal" tabindex="-1" aria-labelledby="editFaqModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="faq_id" id="edit_faq_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editFaqModalLabel">Edit FAQ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_question" class="form-label">Question *</label>
                            <input type="text" class="form-control" id="edit_question" name="question" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_answer" class="form-label">Answer *</label>
                            <textarea class="form-control" id="edit_answer" name="answer" rows="5" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="edit_category" name="category" list="category-list">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 form-check" style="padding-top: 2rem;">
                                    <input type="checkbox" class="form-check-input" id="edit_is_featured" name="is_featured">
                                    <label class="form-check-label" for="edit_is_featured">Featured FAQ</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_faq" class="btn btn-primary">Update FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const faqId = this.getAttribute('data-id');
                const faqQuestion = this.getAttribute('data-question');
                const faqAnswer = this.getAttribute('data-answer');
                const faqCategory = this.getAttribute('data-category');
                const faqFeatured = this.getAttribute('data-featured') === '1';
                
                document.getElementById('edit_faq_id').value = faqId;
                document.getElementById('edit_question').value = faqQuestion;
                document.getElementById('edit_answer').value = faqAnswer;
                document.getElementById('edit_category').value = faqCategory;
                document.getElementById('edit_is_featured').checked = faqFeatured;
                
                const editModal = new bootstrap.Modal(document.getElementById('editFaqModal'));
                editModal.show();
            });
        });
    </script>
</body>
</html>
