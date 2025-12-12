<?php
$page_title = 'User Management';
$page_css = 'users';
$additional_css = ['inventory'];
$active_page = 'users';
$current_user = get_session_user();

// Check if user is admin
if ($current_user['role'] !== 'admin') {
    redirect('index.php?page=403');
}

// Get all users
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($role_filter) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
}

if ($status_filter !== '') {
    $sql .= " AND is_active = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

ob_start();
?>

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
    <h2>User Management</h2>
    <button type="button" class="btn btn-primary btn-add-user" onclick="openAddModal()">
        <i class="fa-solid fa-plus"></i>
        <span>Add New User</span>
    </button>
</div>

<div class="page-body scrollable">
    <div class="filters">
        <form method="GET" id="filterForm">
            <input type="hidden" name="page" value="users" />
            <input 
                type="text" 
                name="search" 
                id="searchInput"
                placeholder="Search by name or email..." 
                value="<?php echo escape($search); ?>"
                autocomplete="off"
            />
            
            <select name="role" id="roleSelect" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Roles</option>
                <option value="admin" <?php echo ($role_filter === 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="staff" <?php echo ($role_filter === 'staff') ? 'selected' : ''; ?>>Staff</option>
            </select>
            
            <select name="status" id="statusSelect" onchange="document.getElementById('filterForm').submit()">
                <option value="">All Status</option>
                <option value="1" <?php echo ($status_filter === '1') ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo ($status_filter === '0') ? 'selected' : ''; ?>>Inactive</option>
            </select>
            
            <?php if ($search || $role_filter || $status_filter !== ''): ?>
            <a href="?page=users" class="btn btn-secondary">
                <i class="fa-solid fa-times"></i> Clear
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="inventory-table-container">
        <div class="inventory-table-header">
            <h3>Users (<?php echo count($users); ?>)</h3>
        </div>
        
        <div class="table-wrapper">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact Number</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="item-name"><?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo escape($user['email']); ?></td>
                            <td><?php echo escape($user['contact_number'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge <?php echo $user['role'] === 'admin' ? 'status-admin' : 'status-staff'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $user['is_active'] ? 'status-in-stock' : 'status-out-of-stock'; ?>">
                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="actions-cell">
                                <button class="action-btn view-btn" onclick='viewUser(<?php echo json_encode($user); ?>)' title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button class="action-btn edit-btn" onclick='editUser(<?php echo json_encode($user); ?>)' title="Edit User">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <?php if ($user['id'] != $current_user['id']): ?>
                                <button class="action-btn <?php echo $user['is_active'] ? 'warning-btn' : 'success-btn'; ?>" 
                                        onclick='toggleUserStatus(<?php echo $user['id']; ?>, "<?php echo escape($user['first_name'] . ' ' . $user['last_name']); ?>", <?php echo $user['is_active']; ?>)' 
                                        title="<?php echo $user['is_active'] ? 'Disable Account' : 'Enable Account'; ?>">
                                    <i class="fa-solid fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                <i class="fa-solid fa-users-slash"></i>
                                <p>No users found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View User Modal -->
<?php require __DIR__ . '/partials/modals/view_user_modal.php'; ?>

<!-- Add/Edit User Modal -->
<?php require __DIR__ . '/partials/modals/edit_user_modal.php'; ?>

<!-- Toggle Status Modal -->
<?php require __DIR__ . '/partials/modals/toggle_user_status_modal.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('viewUserModal').style.display = 'none';
    document.getElementById('editUserModal').style.display = 'none';
    document.getElementById('toggleStatusModal').style.display = 'none';
    
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

// View user modal
function viewUser(user) {
    document.getElementById('view-user-name').textContent = user.first_name + ' ' + user.last_name;
    document.getElementById('view-user-email').textContent = user.email;
    document.getElementById('view-user-contact').textContent = user.contact_number || 'N/A';
    document.getElementById('view-user-role').textContent = user.role.charAt(0).toUpperCase() + user.role.slice(1);
    document.getElementById('view-user-role').className = 'status-badge ' + (user.role === 'admin' ? 'status-admin' : 'status-staff');
    document.getElementById('view-user-status').textContent = user.is_active ? 'Active' : 'Inactive';
    document.getElementById('view-user-status').className = 'status-badge ' + (user.is_active ? 'status-in-stock' : 'status-out-of-stock');
    document.getElementById('view-user-created').textContent = new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    document.getElementById('view-user-updated').textContent = new Date(user.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    
    const modal = document.getElementById('viewUserModal');
    modal.style.display = 'flex';
}

function closeViewModal() {
    document.getElementById('viewUserModal').style.display = 'none';
}

// Add/Edit user modal
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New User';
    document.getElementById('userForm').reset();
    document.getElementById('user-id').value = '';
    document.getElementById('password-group').style.display = 'block';
    document.getElementById('password-label').textContent = 'Password *';
    document.getElementById('user-password').required = true;
    document.getElementById('password-hint').textContent = 'Minimum 6 characters';
    document.getElementById('editUserModal').style.display = 'flex';
}

function editUser(user) {
    document.getElementById('modalTitle').textContent = 'Edit User';
    document.getElementById('user-id').value = user.id;
    document.getElementById('user-first-name').value = user.first_name;
    document.getElementById('user-last-name').value = user.last_name;
    document.getElementById('user-email').value = user.email;
    document.getElementById('user-contact').value = user.contact_number || '';
    document.getElementById('user-role').value = user.role;
    document.getElementById('password-group').style.display = 'block';
    document.getElementById('password-label').textContent = 'New Password (optional)';
    document.getElementById('user-password').value = '';
    document.getElementById('user-password').required = false;
    document.getElementById('password-hint').textContent = 'Minimum 6 characters. Leave blank to keep current password.';
    
    document.getElementById('editUserModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
}

// Toggle status modal
function toggleUserStatus(userId, userName, currentStatus) {
    document.getElementById('toggle-user-id').value = userId;
    document.getElementById('toggle-user-name').textContent = userName;
    document.getElementById('toggle-action').textContent = currentStatus ? 'disable' : 'enable';
    document.getElementById('toggle-new-status').value = currentStatus ? '0' : '1';
    
    document.getElementById('toggleStatusModal').style.display = 'flex';
}

function closeToggleStatusModal() {
    document.getElementById('toggleStatusModal').style.display = 'none';
}

window.onclick = function(event) {
    const modals = ['viewUserModal', 'editUserModal', 'toggleStatusModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Search debounce
let searchTimeout;
const searchInput = document.getElementById('searchInput');
const filterForm = document.getElementById('filterForm');

searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    
    const cursorPosition = this.selectionStart;
    const searchValue = this.value;
    
    searchTimeout = setTimeout(function() {
        sessionStorage.setItem('usersSearchValue', searchValue);
        sessionStorage.setItem('usersSearchCursor', cursorPosition);
        filterForm.submit();
    }, 500);
});

window.addEventListener('load', function() {
    const savedValue = sessionStorage.getItem('usersSearchValue');
    const savedCursor = sessionStorage.getItem('usersSearchCursor');
    
    if (savedValue !== null && searchInput.value === savedValue) {
        searchInput.focus();
        if (savedCursor !== null) {
            searchInput.setSelectionRange(savedCursor, savedCursor);
        }
        sessionStorage.removeItem('usersSearchValue');
        sessionStorage.removeItem('usersSearchCursor');
    }
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>
