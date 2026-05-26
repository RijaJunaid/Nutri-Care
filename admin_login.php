<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $admin_username = 'admin';
    $admin_password_hash = password_hash('nutricare_admin123', PASSWORD_DEFAULT);
    
    if ($username === $admin_username && password_verify($password, $admin_password_hash)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin_dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NutriCare - Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --green-light: #E3F4E1;
            --green: #5FB65A;
            --green-dark: #3C8D37;
        }
        
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-logo i {
            font-size: 3rem;
            color: var(--green);
        }
        
        .btn-login {
            background-color: var(--green);
            border: none;
            width: 100%;
            padding: 10px;
        }
        
        .btn-login:hover {
            background-color: var(--green-dark);
        }
        
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 1.5rem;
            color: var(--green);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .back-button:hover {
            color: var(--green-dark);
            transform: translateX(-3px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <!-- Back button -->
            <div class="back-button" onclick="window.location.href='home.php'">
                <i class="fas fa-arrow-left"></i>
            </div>
            
            <div class="login-logo">
                <i class="fas fa-user-shield"></i>
                <h2>NutriCare Admin</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-login">Login</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
