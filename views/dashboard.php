<?php
$page_title = 'Dashboard';
$page_css = 'dashboard';
$active_page = 'dashboard';

// Get dashboard stats
$stmt = $pdo->query("SELECT COUNT(*) as count FROM items");
$total_items = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM items WHERE status = 'low-stock'");
$low_stock = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(stock_quantity * cost) as total FROM items");
$inventory_value = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as count FROM items WHERE status = 'out-of-stock'");
$out_of_stock = $stmt->fetch()['count'];

// Get categories with items
$categories = $pdo->query("
    SELECT c.name as category,
           COUNT(i.id) as item_count,
           SUM(CASE WHEN i.status = 'low-stock' THEN 1 ELSE 0 END) as low_count,
           SUM(i.stock_quantity * i.cost) as total_value
    FROM categories c
    LEFT JOIN items i ON c.id = i.category_id
    GROUP BY c.id, c.name
    ORDER BY c.name
")->fetchAll();

// Get recent activities
$activities = $pdo->query("
    SELECT t.transaction_date, i.name as item_name, t.quantity, t.unit, t.transaction_type
    FROM transactions t
    JOIN items i ON t.item_id = i.id
    ORDER BY t.transaction_date DESC
    LIMIT 6
")->fetchAll();

ob_start();
?>

<div class="page-header">
    <h2>Dashboard</h2>
</div>

<div class="page-body scrollable">
    <!-- Summary Cards -->
    <div class="summary">
        <div class="card">
            <div class="card-header">
                <h3>Total Items</h3>
                <i class="fa-solid fa-box card-icon"></i>
            </div>
            <p class="value"><?php echo $total_items; ?></p>
            <h4>Across all categories</h4>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Low Stock Alerts</h3>
                <i class="fa-solid fa-triangle-exclamation card-icon"></i>
            </div>
            <p class="valueLow"><?php echo $low_stock; ?></p>
            <h4>Items need reordering</h4>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Inventory Value</h3>
                <i class="fa-solid fa-dollar-sign card-icon"></i>
            </div>
            <p class="value"><?php echo format_currency($inventory_value); ?></p>
            <h4>Total stock value</h4>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Out of stock</h3>
                <i class="fa-solid fa-boxes-stacked card-icon"></i>
            </div>
            <p class="value out-of-stock"><?php echo $out_of_stock; ?></p>
            <h4>Items need restocking</h4>
        </div>
    </div>

    <!-- Categories and Activities -->
    <div class="middle-section">
        <div class="inventory">
            <h2>Inventory by Category</h2>
            <div class="category-list">
                <?php foreach ($categories as $cat): ?>
                <div class="card">
                    <div class="category-header">
                        <h3><?php echo escape($cat['category']); ?></h3>
                        <div class="category-meta">
                            <span class="item-count"><?php echo $cat['item_count']; ?> items</span>
                            <?php if ($cat['low_count'] > 0): ?>
                            <span class="low-badge"><?php echo $cat['low_count']; ?> low</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo min(100, $cat['item_count'] * 20); ?>%"></div>
                    </div>
                    <p class="category-value">Value: <?php echo format_currency($cat['total_value']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="activity">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <?php foreach ($activities as $act): 
                    $is_warning = in_array($act['transaction_type'], ['damaged', 'expired']);
                ?>
                <div class="activity-item <?php echo $is_warning ? 'warning' : ''; ?>">
                    <i class="fa-solid <?php echo $is_warning ? 'fa-triangle-exclamation' : 'fa-box'; ?> card-icon"></i>
                    <div class="activity-content">
                        <div class="activity-left">
                            <h3><?php echo escape($act['item_name']); ?></h3>
                            <p><?php echo ucfirst(str_replace('-', ' ', $act['transaction_type'])); ?></p>
                        </div>
                        <div class="activity-right">
                            <span class="activity-amount"><?php echo $act['quantity'] . ' ' . $act['unit']; ?></span>
                            <span class="activity-date"><?php echo time_ago($act['transaction_date'], "Asia/Manila"); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>
