<?php
// Set timezone
date_default_timezone_set('Asia/Manila');

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ' . base_url('login.php'));
    exit();
}

$current_user = get_session_user();
$user_id = $current_user['id'];

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        $pdo = Database::connect();
        
        if ($action === 'update_profile') {
            // Get form data
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contact_number = trim($_POST['contact_number'] ?? '');
            $current_password = $_POST['current_password'] ?? '';
            
            // Validate required fields
            if (empty($first_name) || empty($last_name) || empty($email) || empty($current_password)) {
                $_SESSION['error_message'] = 'All fields marked with * are required.';
                header('Location: ' . base_url('?page=settings'));
                exit();
            }
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_message'] = 'Invalid email format.';
                header('Location: ' . base_url('?page=settings'));
                exit();
            }
            
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($current_password, $user['password_hash'])) {
                $_SESSION['error_message'] = 'Current password is incorrect.';
                header('Location: ' . base_url('?page=settings'));
                exit();
            }
            
            // Check if email is already taken
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $_SESSION['error_message'] = 'Email address is already in use by another account.';
                header('Location: ' . base_url('?page=settings'));
                exit();
            }
            
            // Update user profile
            $stmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?, contact_number = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$first_name, $last_name, $email, $contact_number, $user_id]);
            
            // Update session data
            $_SESSION['user']['first_name'] = $first_name;
            $_SESSION['user']['last_name'] = $last_name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['contact_number'] = $contact_number;
            
            $_SESSION['success_message'] = 'Profile information updated successfully.';
            header('Location: ' . base_url('?page=settings'));
            exit();
            
        } elseif ($action === 'change_password') {
            // Get form data
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validate required fields
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $_SESSION['error_message'] = 'All password fields are required.';
                header('Location: ' . base_url('?page=settings'));
                exit();
            }
            
            // Validate password length
            if (strlen($new_password) < 6) {
                $_SESSION['error_message'] = 'New password must be at least 6 characters long.';
                header('Location: ' . base_url('?page=settings'));
                exit();
            }
            
            // Check if new passwords match
            if ($new_password !== $confirm_password) {
                $_SESSION['error_message'] = 'New passwords do not match.';
                header('Location: ' . base_url('?page=settings'));
                exit();
            }
            
            // Verify current password
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($current_password, $user['password_hash'])) {
                $_SESSION['error_message'] = 'Current password is incorrect.';
                header('Location: ' . base_url('?page=settings'));
                exit();
            }
            
            // Check if new password is same as current password
            if (password_verify($new_password, $user['password_hash'])) {
                $_SESSION['error_message'] = 'New password must be different from your current password.';
                header('Location: ' . base_url('?page=settings'));
                exit();
            }
            
            // Hash new password
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $stmt = $pdo->prepare("
                UPDATE users 
                SET password_hash = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$new_password_hash, $user_id]);
            
            $_SESSION['success_message'] = 'Password changed successfully.';
            header('Location: ' . base_url('?page=settings'));
            exit();
            
        } else {
            $_SESSION['error_message'] = 'Invalid action.';
            header('Location: ' . base_url('?page=settings'));
            exit();
        }
        
    } catch (PDOException $e) {
        error_log("Settings Update Error: " . $e->getMessage());
        $_SESSION['error_message'] = 'An error occurred while updating settings. Please try again.';
        header('Location: ' . base_url('?page=settings'));
        exit();
    }
} else {
    header('Location: ' . base_url('?page=settings'));
    exit();
}
?>