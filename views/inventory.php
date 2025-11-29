<?php
$page_title = 'Inventory';
$page_css = 'inventory';
$active_page = 'inventory';
$current_user = get_session_user();

// Get all items with category and supplier info
$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

$sql = "SELECT i.*, c.name as category_name, s.name as supplier_name, s.email as supplier_email, s.phone as supplier_phone
        FROM items i
        LEFT JOIN categories c ON i.category_id = c.id
        LEFT JOIN suppliers s ON i.supplier_id = s.id
        WHERE 1=1";

$params = [];

if ($search) {
    $sql .= " AND i.name LIKE ?";
    $params[] = "%$search%";
}

if ($category_filter) {
    $sql .= " AND i.category_id = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY i.name";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Get all categories and suppliers for the add/edit modal
$all_categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
$all_suppliers = $pdo->query("SELECT * FROM suppliers WHERE status = 'active' ORDER BY name")->fetchAll();

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
    <h2>Inventory</h2>
    <?php if ($current_user['role'] === 'admin'): ?>
    <button type="button" class="btn btn-new-item" onclick="openAddModal()">
        <i class="fa-solid fa-plus"></i>
        <span>Add New Item</span>
    </button>
    <?php endif; ?>
</div>

<div class="page-body scrollable">
    <div class="filters">
        <form method="GET" id="filterForm">
            <input type="hidden" name="page" value="inventory" />
            <input 
                type="text" 
                name="search" 
                id="searchInput"
                placeholder="Search item name..." 
                value="<?php echo escape($search); ?>"
                autocomplete="off"
            />
            
            <select name="category" id="categorySelect" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>>
                    <?php echo escape($cat['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
            
            <?php if ($search || $category_filter): ?>
            <a href="?page=inventory" class="btn btn-secondary">
                <i class="fa-solid fa-times"></i> Clear
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="inventory-table-container">
        <div class="inventory-table-header">
            <h3>Inventory Items (<?php echo count($items); ?>)</h3>
        </div>
        
        <div class="table-wrapper">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Reorder Level</th>
                        <th>Unit</th>
                        <th>Cost</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Supplier</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td class="item-name"><?php echo escape($item['name']); ?></td>
                            <td><?php echo escape($item['category_name']); ?></td>
                            <td class="text-center"><?php echo $item['stock_quantity']; ?></td>
                            <td class="text-center"><?php echo $item['reorder_level']; ?></td>
                            <td class="text-center"><?php echo escape($item['unit']); ?></td>
                            <td class="text-right"><?php echo format_currency($item['cost']); ?></td>
                            <td class="text-right"><?php echo format_currency($item['price']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $item['status']; ?>">
                                    <?php echo ucfirst(str_replace('-', ' ', $item['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo escape($item['supplier_name']); ?></td>
                            <td class="actions-cell">
                                <button class="action-btn view-btn" onclick='viewItem(<?php echo json_encode($item); ?>)' title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <?php if ($current_user['role'] === 'admin'): ?>
                                <button class="action-btn edit-btn" onclick='editItem(<?php echo json_encode($item); ?>)' title="Edit Item">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="action-btn delete-btn" onclick='deleteItem(<?php echo $item['id']; ?>, "<?php echo escape($item['name']); ?>")' title="Delete Item">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="no-data">
                                <i class="fa-solid fa-box-open"></i>
                                <p>No items found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Item Modal -->
<?php require __DIR__ . '/partials/modals/view_item_modal.php'; ?>

<!-- Add/Edit Item Modal -->
<?php require __DIR__ . '/partials/modals/edit_item_modal.php'; ?>

<!-- Delete Confirmation Modal -->
<?php require __DIR__ . '/partials/modals/delete_item_modal.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('viewItemModal').style.display = 'none';
    document.getElementById('editItemModal').style.display = 'none';
    document.getElementById('deleteItemModal').style.display = 'none';
    
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');
    
    if (successAlert) {
        setTimeout(function() {
            successAlert.style.opacity = '0';
            successAlert.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                successAlert.style.display = 'none';
            }, 500);
        }, 3000);
    }
    
    if (errorAlert) {
        setTimeout(function() {
            errorAlert.style.opacity = '0';
            errorAlert.style.transition = 'opacity 0.5s ease';
            setTimeout(function() {
                errorAlert.style.display = 'none';
            }, 500);
        }, 3000);
    }
});

// View item modal
function viewItem(item) {
    document.getElementById('view-item-name').textContent = item.name;
    document.getElementById('view-category').textContent = item.category_name;
    document.getElementById('view-supplier').textContent = item.supplier_name;
    document.getElementById('view-stock').textContent = item.stock_quantity + ' ' + item.unit;
    document.getElementById('view-reorder').textContent = item.reorder_level + ' ' + item.unit;
    document.getElementById('view-cost').textContent = '₱ ' + parseFloat(item.cost).toFixed(2);
    document.getElementById('view-price').textContent = '₱ ' + parseFloat(item.price).toFixed(2);
    document.getElementById('view-status').textContent = item.status.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase());
    document.getElementById('view-status').className = 'status-badge status-' + item.status;
    document.getElementById('view-expiration').textContent = item.expiration_date || 'N/A';
    document.getElementById('view-description').textContent = item.notes || 'No notes available';
    
    const modal = document.getElementById('viewItemModal');
    modal.style.display = 'flex';
}

function closeViewModal() {
    document.getElementById('viewItemModal').style.display = 'none';
}

// Add/Edit item modal
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Item';
    document.getElementById('itemForm').reset();
    document.getElementById('item-id').value = '';
    document.getElementById('editItemModal').style.display = 'flex';
}

function editItem(item) {
    document.getElementById('modalTitle').textContent = 'Edit Item';
    document.getElementById('item-id').value = item.id;
    document.getElementById('item-name').value = item.name;
    document.getElementById('item-category').value = item.category_id;
    document.getElementById('item-supplier').value = item.supplier_id;
    document.getElementById('item-stock').value = item.stock_quantity;
    document.getElementById('item-reorder').value = item.reorder_level;
    document.getElementById('item-unit').value = item.unit;
    document.getElementById('item-cost').value = item.cost;
    document.getElementById('item-price').value = item.price;
    document.getElementById('item-expiration').value = item.expiration_date || '';
    document.getElementById('item-description').value = item.notes || '';
    
    document.getElementById('editItemModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editItemModal').style.display = 'none';
}

// Delete item modal
function deleteItem(id, name) {
    document.getElementById('delete-item-id').value = id;
    document.getElementById('delete-item-name').textContent = name;
    document.getElementById('deleteItemModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteItemModal').style.display = 'none';
}

window.onclick = function(event) {
    const modals = ['viewItemModal', 'editItemModal', 'deleteItemModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Search debounce shit
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const filterForm = document.getElementById('filterForm');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    
    const cursorPosition = this.selectionStart;
    const searchValue = this.value;
    
    searchTimeout = setTimeout(function() {
        sessionStorage.setItem('inventorySearchValue', searchValue);
        sessionStorage.setItem('inventorySearchCursor', cursorPosition);
        filterForm.submit();
    }, 500);
});

window.addEventListener('load', function() {
    const savedValue = sessionStorage.getItem('inventorySearchValue');
    const savedCursor = sessionStorage.getItem('inventorySearchCursor');
    
    if (savedValue !== null && searchInput.value === savedValue) {
        searchInput.focus();
        if (savedCursor !== null) {
            searchInput.setSelectionRange(savedCursor, savedCursor);
        }
        sessionStorage.removeItem('inventorySearchValue');
        sessionStorage.removeItem('inventorySearchCursor');
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>