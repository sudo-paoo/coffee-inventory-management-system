<?php
$page_title = 'Transactions';
$page_css = 'transactions';
$active_page = 'transactions';
$current_user = get_session_user();

// Get active tab
$active_tab = $_GET['tab'] ?? 'pending-orders';

// Get filters
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Get pending orders
$pending_sql = "SELECT o.*, s.name as supplier_name, s.contact_person, s.email as supplier_email,
                u.first_name as ordered_by_name, u.last_name as ordered_by_lastname
                FROM orders o
                LEFT JOIN suppliers s ON o.supplier_id = s.id
                LEFT JOIN users u ON o.ordered_by = u.id
                WHERE o.status = 'pending'";

if ($search) {
    $pending_sql .= " AND (o.order_number LIKE ? OR s.name LIKE ?)";
}

$pending_sql .= " ORDER BY o.ordered_at DESC";

$pending_stmt = $pdo->prepare($pending_sql);
if ($search) {
    $search_param = "%$search%";
    $pending_stmt->execute([$search_param, $search_param]);
} else {
    $pending_stmt->execute();
}
$pending_orders = $pending_stmt->fetchAll();

// Get transaction history
$history_sql = "SELECT o.*, s.name as supplier_name, s.contact_person,
                u1.first_name as ordered_by_name, u1.last_name as ordered_by_lastname,
                u2.first_name as received_by_name, u2.last_name as received_by_lastname
                FROM orders o
                LEFT JOIN suppliers s ON o.supplier_id = s.id
                LEFT JOIN users u1 ON o.ordered_by = u1.id
                LEFT JOIN users u2 ON o.received_by = u2.id
                WHERE o.status = 'received'";

if ($search) {
    $history_sql .= " AND (o.order_number LIKE ? OR s.name LIKE ?)";
}

if ($date_filter) {
    $history_sql .= " AND DATE(o.received_at) = ?";
}

$history_sql .= " ORDER BY o.received_at DESC";

$history_stmt = $pdo->prepare($history_sql);
$params = [];
if ($search) {
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($date_filter) {
    $params[] = $date_filter;
}
$history_stmt->execute($params);
$transaction_history = $history_stmt->fetchAll();

// Get all suppliers for the modal
$all_suppliers = $pdo->query("SELECT * FROM suppliers WHERE status = 'active' ORDER BY name")->fetchAll();

// Get all items for the order modal
$items = $pdo->query("SELECT i.*, c.name as category_name FROM items i LEFT JOIN categories c ON i.category_id = c.id ORDER BY i.name")->fetchAll();

// Start output buffering for layout
ob_start();
?>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success" id="successAlert">
        <i class="fa-solid fa-circle-check"></i>
        <span><?= escape($_SESSION['success_message']) ?></span>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger" id="errorAlert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?= escape($_SESSION['error_message']) ?></span>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<div class="page-header">
    <h2>Transactions</h2>
    <?php if ($current_user['role'] === 'admin'): ?>
    <button type="button" class="btn btn-new-order" onclick="openMakeOrderModal()">
        <i class="fa-solid fa-plus"></i>
        <span>Make Order</span>
    </button>
    <?php endif; ?>
</div>

<div class="page-body scrollable">
    <!-- Filters -->
    <div class="filters">
        <form method="GET" id="filterForm">
            <input type="hidden" name="page" value="transactions" />
            <input type="hidden" name="tab" value="<?= $active_tab ?>" />
            
            <input 
                type="text" 
                name="search" 
                id="searchInput"
                placeholder="Search transaction..." 
                value="<?php echo escape($search); ?>"
                autocomplete="off"
            />
            
            <?php if ($active_tab === 'transaction-history'): ?>
            <input 
                type="date" 
                name="date" 
                id="dateFilter"
                value="<?= escape($date_filter) ?>"
                onchange="document.getElementById('filterForm').submit()"
            />
            <?php endif; ?>
            
            <?php if ($search || $date_filter): ?>
            <a href="?page=transactions&tab=<?= $active_tab ?>" class="btn btn-secondary">
                <i class="fa-solid fa-times"></i> Clear
            </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tabs">
            <a href="?page=transactions&tab=pending-orders" class="tab <?= $active_tab === 'pending-orders' ? 'active' : '' ?>">
                Pending Orders (<?= count($pending_orders) ?>)
            </a>
            <a href="?page=transactions&tab=transaction-history" class="tab <?= $active_tab === 'transaction-history' ? 'active' : '' ?>">
                Transaction History (<?= count($transaction_history) ?>)
            </a>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        <?php if ($active_tab === 'pending-orders'): ?>
            <!-- Pending Orders List -->
            <div class="orders-list">
                <?php if (count($pending_orders) > 0): ?>
                    <?php foreach ($pending_orders as $order): ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div>
                                <h3><?= escape($order['order_number']) ?></h3>
                                <p class="order-supplier">Supplier: <?= escape($order['supplier_name']) ?></p>
                            </div>
                            <?php if ($current_user['role'] === 'admin' || $current_user['role'] === 'staff'): ?>
                            <button class="btn btn-primary" style="padding: 10px 20px;" onclick="markAsReceived('<?= $order['id'] ?>', '<?= escape($order['order_number']) ?>', '<?= escape($order['supplier_name']) ?>')">
                                <i class="fa-solid fa-check"></i> Mark Received
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="order-card-body">
                            <div class="order-info">
                                <div class="info-item">
                                    <span class="info-label">Order ID:</span>
                                    <span class="info-value"><?= escape($order['order_number']) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Ordered By:</span>
                                    <span class="info-value"><?= escape($order['ordered_by_name'] . ' ' . $order['ordered_by_lastname']) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Created:</span>
                                    <span class="info-value"><?= date('M d, Y', strtotime($order['ordered_at'])) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total Cost:</span>
                                    <span class="info-value"><?= format_currency($order['total_cost']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fa-solid fa-box-open"></i>
                        <h3>No Pending Orders</h3>
                        <p>All orders have been received</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Transaction History List -->
            <div class="orders-list">
                <?php if (count($transaction_history) > 0): ?>
                    <?php foreach ($transaction_history as $transaction): ?>
                    <div class="order-card">
                        <div class="order-card-header">
                            <div>
                                <h3><?= escape($transaction['order_number']) ?></h3>
                                <p class="order-supplier">Supplier: <?= escape($transaction['supplier_name']) ?></p>
                            </div>
                            <span class="status-badge status-received">Received</span>
                        </div>
                        <div class="order-card-body">
                            <div class="order-info">
                                <div class="info-item">
                                    <span class="info-label">Order ID:</span>
                                    <span class="info-value"><?= escape($transaction['order_number']) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Ordered By:</span>
                                    <span class="info-value"><?= escape($transaction['ordered_by_name'] . ' ' . $transaction['ordered_by_lastname']) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Received:</span>
                                    <span class="info-value"><?= date('M d, Y', strtotime($transaction['received_at'])) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Received By:</span>
                                    <span class="info-value"><?= escape($transaction['received_by_name'] . ' ' . $transaction['received_by_lastname']) ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total Cost:</span>
                                    <span class="info-value"><?= format_currency($transaction['total_cost']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fa-solid fa-box-open"></i>
                        <h3>No Transaction History</h3>
                        <p>No received orders yet</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Make Order Modal -->
<?php require __DIR__ . '/partials/modals/make_order_modal.php'; ?>

<!-- Mark Received Modal -->
<?php require __DIR__ . '/partials/modals/mark_received_modal.php'; ?>

<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    // Hide modals on page load
    const makeOrderModal = document.getElementById('makeOrderModal');
    const markReceivedModal = document.getElementById('markReceivedModal');
    if (makeOrderModal) makeOrderModal.style.display = 'none';
    if (markReceivedModal) markReceivedModal.style.display = 'none';
    
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');
    
    if (successAlert) {
        setTimeout(function() {
            successAlert.style.opacity = '0';
            successAlert.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                successAlert.style.display = 'none';
            }, 500);
        }, 5000);
    }
    
    if (errorAlert) {
        setTimeout(function() {
            errorAlert.style.opacity = '0';
            errorAlert.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                errorAlert.style.display = 'none';
            }, 500);
        }, 5000);
    }
});

// Search debounce functionality
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const filterForm = document.getElementById('filterForm');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        
        // Store cursor position
        const cursorPosition = this.selectionStart;
        const searchValue = this.value;
        
        searchTimeout = setTimeout(function() {
            // Store the search value and cursor position before submit
            sessionStorage.setItem('transactionsSearchValue', searchValue);
            sessionStorage.setItem('transactionsSearchCursor', cursorPosition);
            filterForm.submit();
        }, 500);
    });
    
    // Restore focus and cursor position after page load
    window.addEventListener('load', function() {
        const savedValue = sessionStorage.getItem('transactionsSearchValue');
        const savedCursor = sessionStorage.getItem('transactionsSearchCursor');
        
        if (savedValue !== null && searchInput.value === savedValue) {
            searchInput.focus();
            if (savedCursor !== null) {
                searchInput.setSelectionRange(savedCursor, savedCursor);
            }
            // Clear the stored values
            sessionStorage.removeItem('transactionsSearchValue');
            sessionStorage.removeItem('transactionsSearchCursor');
        }
    });
}

// Make Order Modal
function openMakeOrderModal() {
    document.getElementById('makeOrderModal').style.display = 'flex';
}

function closeMakeOrderModal() {
    document.getElementById('makeOrderModal').style.display = 'none';
}

// Mark Received Modal
function markAsReceived(orderId, orderNumber, supplierName) {
    document.getElementById('receive-order-id').value = orderId;
    document.getElementById('receive-order-number').textContent = orderNumber;
    document.getElementById('receive-supplier-name').textContent = 'Supplier: ' + supplierName;
    
    // Fetch order items via AJAX
    fetch('<?php echo base_url('get_order_items.php'); ?>?order_id=' + orderId)
        .then(response => response.json())
        .then(data => {
            const itemsContainer = document.getElementById('receive-order-items');
            const totalContainer = document.getElementById('receive-order-total');
            
            console.log('Order items response:', data); // Debug log
            console.log('Items count:', data.count);
            console.log('Order ID sent:', orderId);
            
            if (data.success && data.items && data.items.length > 0) {
                let itemsHTML = '<table style="width: 100%; border-collapse: collapse;">';
                itemsHTML += '<thead><tr style="background: var(--background); border-bottom: 2px solid var(--border);">';
                itemsHTML += '<th style="padding: 8px; text-align: left; font-size: var(--text-sm);">Item Name</th>';
                itemsHTML += '<th style="padding: 8px; text-align: center; font-size: var(--text-sm);">Quantity</th>';
                itemsHTML += '<th style="padding: 8px; text-align: center; font-size: var(--text-sm);">Unit</th>';
                itemsHTML += '<th style="padding: 8px; text-align: right; font-size: var(--text-sm);">Unit Cost</th>';
                itemsHTML += '<th style="padding: 8px; text-align: right; font-size: var(--text-sm);">Total</th>';
                itemsHTML += '</tr></thead><tbody>';
                
                let total = 0;
                
                data.items.forEach(item => {
                    // Use total_cost from database if available, otherwise calculate
                    const itemTotal = item.total_cost ? parseFloat(item.total_cost) : (parseFloat(item.quantity) * parseFloat(item.unit_cost));
                    total += itemTotal;
                    
                    itemsHTML += '<tr class="order-item-row">';
                    itemsHTML += '<td style="padding: 10px; border-bottom: 1px solid #e5e7eb;">' + item.item_name + '</td>';
                    itemsHTML += '<td style="padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: center;">' + parseFloat(item.quantity).toFixed(2) + '</td>';
                    itemsHTML += '<td style="padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: center;">' + item.unit + '</td>';
                    itemsHTML += '<td style="padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: right;">₱' + parseFloat(item.unit_cost).toFixed(2) + '</td>';
                    itemsHTML += '<td style="padding: 10px; border-bottom: 1px solid #e5e7eb; text-align: right; font-weight: 600; color: var(--primary);">₱' + itemTotal.toFixed(2) + '</td>';
                    itemsHTML += '</tr>';
                });
                
                itemsHTML += '</tbody></table>';
                
                itemsContainer.innerHTML = itemsHTML;
                totalContainer.innerHTML = 'Total: <span style="color: var(--primary);">₱' + total.toFixed(2) + '</span>';
            } else {
                itemsContainer.innerHTML = '<p style="text-align: center; color: #6b7280; padding: 20px;">No items found for this order (Order ID: ' + orderId + ')</p>';
                totalContainer.innerHTML = '';
                console.log('No items in response. Data:', data);
            }
        })
        .catch(error => {
            console.error('Error fetching order items:', error);
            document.getElementById('receive-order-items').innerHTML = '<p style="text-align: center; color: #ef4444; padding: 20px;">Error loading order items: ' + error.message + '</p>';
        });
    
    document.getElementById('markReceivedModal').style.display = 'flex';
}

function closeMarkReceivedModal() {
    document.getElementById('markReceivedModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modals = ['makeOrderModal', 'markReceivedModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal && event.target === modal) {
            modal.style.display = 'none';
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>