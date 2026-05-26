<div class="sidebar">
    <div class="sidebar-header">
        <h3>NutriCare Admin</h3>
    </div>
    
    <div class="sidebar-menu">
        <a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="admin_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="admin_nutritionists.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_nutritionists.php' ? 'active' : ''; ?>">
            <i class="fas fa-user-md"></i> Nutritionists
        </a>
        <a href="admin_foods.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_foods.php' ? 'active' : ''; ?>">
            <i class="fas fa-utensils"></i> Foods
        </a>
        <a href="admin_conditions.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_conditions.php' ? 'active' : ''; ?>">
            <i class="fas fa-heartbeat"></i> Medical Conditions
        </a>
        <a href="admin_allergens.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_allergens.php' ? 'active' : ''; ?>">
            <i class="fas fa-allergies"></i> Allergens
        </a>
        <a href="admin_consultations.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_consultations.php' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Consultations
        </a>
        
        <a href="admin_faqs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_faqs.php' ? 'active' : ''; ?>">
            <i class="fas fa-question-circle"></i> FAQs
        </a>
        <a href="admin_logout.php">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>