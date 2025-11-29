<?php
// views/404.php - Page Not Found
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="<?php echo asset_url('logo.png'); ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo css_url('globals.css'); ?>">
    <link rel="stylesheet" href="<?php echo css_url('pages/404.css'); ?>">
    <script src="https://kit.fontawesome.com/443395de6d.js" crossorigin="anonymous"></script>
    <title>404 - Page Not Found</title>
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
            <div class="error-code">404</div>
            <p class="error-subtitle">Page Not Found</p>
            
            <!-- Error message -->
            <p class="error-message">
                The page you're looking for doesn't exist or has been moved.
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