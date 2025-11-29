<?php
$page_title = 'Settings';
$page_css = 'settings';
$active_page = 'settings';
$session_user = get_session_user();
$user_id = $session_user['id'];

// Fetch full user data from database
try {
    $pdo = Database::connect();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $current_user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$current_user) {
        $_SESSION['error_message'] = 'User not found.';
        header('Location: ' . base_url('?page=dashboard'));
        exit();
    }
} catch (PDOException $e) {
    error_log("Settings Page Error: " . $e->getMessage());
    $_SESSION['error_message'] = 'An error occurred loading settings.';
    header('Location: ' . base_url('?page=dashboard'));
    exit();
}

ob_start();
?>

<!-- Page Header -->
<div class="page-header">
    <h2>Account Settings</h2>
</div>

<!-- Page Body -->
<div class="page-body">
    <div class="scrollable">
        <div class="settings-content">
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

            <div class="settings-container">
    <!-- Profile Information Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3><i class="fa-solid fa-user"></i> Profile Information</h3>
            <p>Update your account details</p>
        </div>
        <form id="profileForm" method="POST" action="<?php echo base_url('update_settings.php'); ?>">
            <input type="hidden" name="action" value="update_profile">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first-name">First Name *</label>
                    <input type="text" id="first-name" name="first_name" class="form-input" value="<?= escape($current_user['first_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last-name">Last Name *</label>
                    <input type="text" id="last-name" name="last_name" class="form-input" value="<?= escape($current_user['last_name']) ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?= escape($current_user['email']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="contact-number">Contact Number</label>
                    <input type="text" id="contact-number" name="contact_number" class="form-input" value="<?= escape($current_user['contact_number'] ?? '') ?>" placeholder="09XXXXXXXXX">
                </div>
            </div>
            
            <div class="form-group">
                <label for="current-password-profile">Current Password (to confirm changes) *</label>
                <input type="password" id="current-password-profile" name="current_password" class="form-input" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
    
    <!-- Change Password Section -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3><i class="fa-solid fa-lock"></i> Change Password</h3>
            <p>Update your account password</p>
        </div>
        <form id="passwordForm" method="POST" action="<?php echo base_url('update_settings.php'); ?>">
            <input type="hidden" name="action" value="change_password">
            
            <div class="form-group">
                <label for="current-password">Current Password *</label>
                <input type="password" id="current-password" name="current_password" class="form-input" required>
            </div>
            
            <div class="form-group">
                <label for="new-password">New Password *</label>
                <input type="password" id="new-password" name="new_password" class="form-input" minlength="6" required>
                <small class="form-hint">Minimum 6 characters</small>
            </div>
            
            <div class="form-group">
                <label for="confirm-password">Confirm New Password *</label>
                <input type="password" id="confirm-password" name="confirm_password" class="form-input" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-key"></i> Change Password
                </button>
            </div>
        </form>
    </div>
    
    <!-- Account Information -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h3><i class="fa-solid fa-info-circle"></i> Account Information</h3>
            <p>View your account details</p>
        </div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Role:</span>
                <span class="info-value"><?= ucfirst(escape($current_user['role'])) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Account Status:</span>
                <span class="info-value">
                    <?php if ($current_user['is_active']): ?>
                        <span class="status-badge status-active">Active</span>
                    <?php else: ?>
                        <span class="status-badge status-inactive">Inactive</span>
                    <?php endif; ?>
                </span>
            </div>
            <?php if (!empty($current_user['created_at'])): ?>
            <div class="info-item">
                <span class="info-label">Member Since:</span>
                <span class="info-value"><?= date('F d, Y', strtotime($current_user['created_at'])) ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($current_user['updated_at'])): ?>
            <div class="info-item">
                <span class="info-label">Last Updated:</span>
                <span class="info-value"><?= date('F d, Y h:i A', strtotime($current_user['updated_at'])) ?></span>
            </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="modal">
    <div class="modal-content modal-content-small">
        <div class="modal-header">
            <div>
                <h2>Confirm Changes</h2>
                <p class="modal-subtitle">Are you sure you want to save these changes?</p>
            </div>
            <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="confirmMessage" style="margin: 0; color: var(--foreground);"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeConfirmModal()">Cancel</button>
            <button type="button" class="btn btn-primary" id="confirmButton">
                <i class="fa-solid fa-check"></i> Confirm
            </button>
        </div>
    </div>
</div>

<script>
// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    // Hide modals on page load
    const confirmModal = document.getElementById('confirmModal');
    if (confirmModal) confirmModal.style.display = 'none';
    
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
    
    // Password confirmation validation
    const confirmPasswordInput = document.getElementById('confirm-password');
    const newPasswordInput = document.getElementById('new-password');
    
    if (confirmPasswordInput && newPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== newPasswordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        newPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && confirmPasswordInput.value !== this.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });
    }
});

let currentForm = null;

// Profile form submission with confirmation
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    currentForm = this;
    
    const firstName = document.getElementById('first-name').value;
    const lastName = document.getElementById('last-name').value;
    const email = document.getElementById('email').value;
    
    document.getElementById('confirmMessage').innerHTML = 
        `You are about to update your profile information:<br><br>` +
        `<strong>Name:</strong> ${firstName} ${lastName}<br>` +
        `<strong>Email:</strong> ${email}`;
    
    document.getElementById('confirmModal').style.display = 'flex';
});

// Password form submission with confirmation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }
    
    currentForm = this;
    
    document.getElementById('confirmMessage').innerHTML = 
        `You are about to change your password.<br><br>` +
        `Make sure to remember your new password.`;
    
    document.getElementById('confirmModal').style.display = 'flex';
});

// Confirm button click
document.getElementById('confirmButton').addEventListener('click', function() {
    if (currentForm) {
        currentForm.submit();
    }
});

function closeConfirmModal() {
    document.getElementById('confirmModal').style.display = 'none';
    currentForm = null;
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('confirmModal');
    if (event.target === modal) {
        closeConfirmModal();
    }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';
?>