<?php
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login_user($pdo, $email, $password)) {
        redirect('?page=dashboard');
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="<?php echo asset_url('logo.png'); ?>" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo css_url('pages/login.css'); ?>" />
    <link rel="stylesheet" href="<?php echo css_url('globals.css'); ?>" />
    <title>Login - Coffee Inventory</title>
</head>
<body>
    <div class="logo">
        <img src="<?php echo asset_url('logo.png'); ?>" alt="logo" />
    </div>

    <div class="header">
        <h3>Coffee Inventory Management System</h3>
    </div>

    <div class="container">
        <div class="forms">
            <div class="form-header">
                <h3 class="form-head">Sign in</h3>
            </div>
            
            <p class="form-subhead">Enter your credentials to access your account</p>

            <?php if ($error): ?>
            <div style="background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px 0; border-radius: 4px; color: #c33;">
                <?php echo escape($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="admin@brew-kenhearted.com" required />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required />
                </div>

                <button type="submit">Sign in</button>
            </form>
        </div>
    </div>

    <div class="footer">
        <footer>&copy; 2025 Brew-kenhearted. All rights reserved.</footer>
    </div>
</body>
</html>