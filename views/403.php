<?php
// views/403.php - Access Forbidden Page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="<?php echo asset_url('logo.png'); ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo css_url('globals.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('pages/403.css'); ?>">
    <script src="https://kit.fontawesome.com/443395de6d.js" crossorigin="anonymous"></script>
    <title>403 - Access Forbidden</title>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <!-- Logo -->
            <div class="logo">
                <img src="<?php echo asset_url('logo.png'); ?>" alt="logo" />
            </div>
            
            <!-- Title -->
            <h3 class="system-title">Coffee Inventory Management System</h3>
            
            <!-- Error code -->
            <div class="error-code">403</div>
            <p class="error-subtitle">Access Forbidden</p>
            
            <!-- Error message -->
            <p class="error-message">
                You don't have permission to access this resource. 
                <?php if (is_logged_in()): ?>
                    Please contact your administrator if you believe this is an error.
                <?php else: ?>
                    Please log in to continue.
                <?php endif; ?>
            </p>
            
            <!-- Action buttons -->
            <div class="error-actions">
                <?php if (is_logged_in()): ?>
                    <a href="<?php echo base_url('?page=dashboard'); ?>" class="btn btn-primary">
                        <i class="fas fa-home"></i>
                        Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?php echo base_url('?page=login'); ?>" class="btn btn-primary">
                        <i class="fas fa-arrow-right-to-bracket"></i>
                        Go to Login
                    </a>
                <?php endif; ?>
            </div>
            
            <!-- Footer -->
            <footer class="error-footer">
                © 2025 Brew-kenhearted. All rights reserved.
            </footer>
        </div>
    </div>
</body>
</html>