<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_login.php');
    exit;
}

// Check if nutritionist ID is provided
if (!isset($_GET['id'])) {
    header('Location: admin_nutritionists.php');
    exit;
}

$nutritionist_id = (int)$_GET['id'];

// Fetch nutritionist data
$nutritionist = $conn->query("SELECT * FROM nutritionists WHERE nutritionist_id = $nutritionist_id")->fetch();

if (!$nutritionist) {
    $_SESSION['error'] = "Nutritionist not found";
    header('Location: admin_nutritionists.php');
    exit;
}

// Handle availability form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_availability'])) {
    try {
        $day_of_week = $_POST['day_of_week'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];

        $stmt = $conn->prepare("INSERT INTO nutritionist_availability (nutritionist_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nutritionist_id, $day_of_week, $start_time, $end_time]);
        
        $_SESSION['message'] = "Availability added successfully";
        header("Location: admin_nutritionist_schedule.php?id=$nutritionist_id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding availability: " . $e->getMessage();
    }
}

// Handle unavailable date form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_unavailable_date'])) {
    try {
        $date = $_POST['date'];
        $reason = trim($_POST['reason']);

        $stmt = $conn->prepare("INSERT INTO nutritionist_unavailable_dates (nutritionist_id, date, reason) VALUES (?, ?, ?)");
        $stmt->execute([$nutritionist_id, $date, $reason]);
        
        $_SESSION['message'] = "Unavailable date added successfully";
        header("Location: admin_nutritionist_schedule.php?id=$nutritionist_id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error adding unavailable date: " . $e->getMessage();
    }
}

// Handle availability deletion
if (isset($_GET['delete_availability'])) {
    $availability_id = (int)$_GET['delete_availability'];
    try {
        $stmt = $conn->prepare("DELETE FROM nutritionist_availability WHERE availability_id = ?");
        $stmt->execute([$availability_id]);
        
        $_SESSION['message'] = "Availability removed successfully";
        header("Location: admin_nutritionist_schedule.php?id=$nutritionist_id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error removing availability: " . $e->getMessage();
    }
}

// Handle unavailable date deletion
if (isset($_GET['delete_unavailable_date'])) {
    $unavailable_id = (int)$_GET['delete_unavailable_date'];
    try {
        $stmt = $conn->prepare("DELETE FROM nutritionist_unavailable_dates WHERE unavailable_id = ?");
        $stmt->execute([$unavailable_id]);
        
        $_SESSION['message'] = "Unavailable date removed successfully";
        header("Location: admin_nutritionist_schedule.php?id=$nutritionist_id");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error removing unavailable date: " . $e->getMessage();
    }
}

// Fetch current availability
$availability = $conn->query("SELECT * FROM nutritionist_availability WHERE nutritionist_id = $nutritionist_id ORDER BY day_of_week, start_time")->fetchAll();

// Fetch unavailable dates
$unavailable_dates = $conn->query("SELECT * FROM nutritionist_unavailable_dates WHERE nutritionist_id = $nutritionist_id ORDER BY date")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Manage Nutritionist Schedule</title>
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
        .day-label {
            width: 120px;
            display: inline-block;
            font-weight: bold;
        }
        
        .time-slot {
            background-color: #e9ecef;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
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
            <a href="admin_users.php" class="active"><i class="fas fa-users"></i> Users</a>
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
            <h2>Manage Schedule for <?php echo htmlspecialchars($nutritionist['name']); ?></h2>
            <a href="admin_nutritionists.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Nutritionists
            </a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Weekly Availability -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Weekly Availability</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="day_of_week" class="form-label">Day of Week</label>
                                    <select class="form-select" id="day_of_week" name="day_of_week" required>
                                        <option value="Monday">Monday</option>
                                        <option value="Tuesday">Tuesday</option>
                                        <option value="Wednesday">Wednesday</option>
                                        <option value="Thursday">Thursday</option>
                                        <option value="Friday">Friday</option>
                                        <option value="Saturday">Saturday</option>
                                        <option value="Sunday">Sunday</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="start_time" class="form-label">Start Time</label>
                                    <input type="time" class="form-control" id="start_time" name="start_time" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="end_time" class="form-label">End Time</label>
                                    <input type="time" class="form-control" id="end_time" name="end_time" required>
                                </div>
                            </div>
                            <button type="submit" name="add_availability" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Availability
                            </button>
                        </form>
                        
                        <hr>
                        
                        <h6>Current Availability</h6>
                        <?php if (empty($availability)): ?>
                            <p class="text-muted">No availability set</p>
                        <?php else: ?>
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach ($days as $day): 
                                $day_availability = array_filter($availability, function($a) use ($day) {
                                    return $a['day_of_week'] === $day;
                                });
                                if (!empty($day_availability)): ?>
                                    <div class="mb-2">
                                        <span class="day-label"><?php echo $day; ?>:</span>
                                        <?php foreach ($day_availability as $slot): ?>
                                            <span class="time-slot">
                                                <?php echo date('g:i a', strtotime($slot['start_time'])); ?> - <?php echo date('g:i a', strtotime($slot['end_time'])); ?>
                                                <a href="admin_nutritionist_schedule.php?id=<?php echo $nutritionist_id; ?>&delete_availability=<?php echo $slot['availability_id']; ?>" class="text-danger ms-2" onclick="return confirm('Are you sure you want to remove this availability?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif;
                            endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Unavailable Dates -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Unavailable Dates</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="reason" class="form-label">Reason</label>
                                    <input type="text" class="form-control" id="reason" name="reason" required>
                                </div>
                            </div>
                            <button type="submit" name="add_unavailable_date" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Unavailable Date
                            </button>
                        </form>
                        
                        <hr>
                        
                        <h6>Upcoming Unavailable Dates</h6>
                        <?php if (empty($unavailable_dates)): ?>
                            <p class="text-muted">No unavailable dates set</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($unavailable_dates as $date): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo date('F j, Y', strtotime($date['date'])); ?> - <?php echo htmlspecialchars($date['reason']); ?>
                                        <a href="admin_nutritionist_schedule.php?id=<?php echo $nutritionist_id; ?>&delete_unavailable_date=<?php echo $date['unavailable_id']; ?>" class="text-danger" onclick="return confirm('Are you sure you want to remove this unavailable date?')">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
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
        
        // Set minimum date for unavailable date picker to today
        document.getElementById('date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>