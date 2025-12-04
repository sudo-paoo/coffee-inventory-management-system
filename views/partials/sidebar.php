<?php
$current_user = get_session_user();
$active_page = $active_page ?? '';
?>
<div class="sidebar">
    <div>
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="<?php echo asset_url('logo.png'); ?>" alt="logo" />
            </div>
            <div class="sidebar-title">Brew-kenhearted</div>
        </div>

        <div class="sidebar-menu">
            <a class="menu-item <?php echo ($active_page === 'dashboard') ? 'active' : ''; ?>" 
               href="<?php echo base_url('?page=dashboard'); ?>">
                <i class="fa-solid fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
            <a class="menu-item <?php echo ($active_page === 'inventory') ? 'active' : ''; ?>" 
               href="<?php echo base_url('?page=inventory'); ?>">
                <i class="fa-solid fa-boxes-stacked"></i>
                <span>Inventory</span>
            </a>
            <a class="menu-item <?php echo ($active_page === 'transactions') ? 'active' : ''; ?>" 
               href="<?php echo base_url('?page=transactions'); ?>">
                <i class="fa-solid fa-receipt"></i>
                <span>Transactions</span>
            </a>
            <a class="menu-item <?php echo ($active_page === 'settings') ? 'active' : ''; ?>" 
               href="<?php echo base_url('?page=settings'); ?>">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>
            <?php if ($current_user['role'] === 'admin'): ?>
            <a class="menu-item <?php echo ($active_page === 'users') ? 'active' : ''; ?>" 
               href="<?php echo base_url('?page=users'); ?>">
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar"><?php echo escape($current_user['avatar']); ?></div>
            <div class="user-info">
                <div class="name"><?php echo escape($current_user['name']); ?></div>
                <div class="email"><?php echo escape($current_user['email']); ?></div>
            </div>
            <div class="user-options">
                <a href="<?php echo base_url('?page=logout'); ?>" title="Logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </div>
</div>
