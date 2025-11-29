<?php
// views/layouts/main.php
$current_user = get_session_user();
$page_title = $page_title ?? 'Dashboard';
$page_css = $page_css ?? 'dashboard';
$active_page = $active_page ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="<?php echo asset_url('logo.png'); ?>" type="image/x-icon" />
    <link rel="stylesheet" href="<?php echo css_url('globals.css'); ?>?v=<?php echo time(); ?>" />
    <link rel="stylesheet" href="<?php echo css_url('pages/' . $page_css . '.css'); ?>?v=<?php echo time(); ?>" />
    <script src="https://kit.fontawesome.com/443395de6d.js" crossorigin="anonymous"></script>
    <title><?php echo $page_title; ?> - Coffee Inventory</title>
</head>
<body>
    <div class="layout-container">
        <?php require __DIR__ . '/../partials/sidebar.php'; ?>
        
        <div class="main-content">
            <?php echo $content; ?>
        </div>
    </div>
</body>
</html>
