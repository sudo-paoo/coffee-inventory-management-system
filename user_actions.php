<?php
// User management CRUD operations

// Set timezone
date_default_timezone_set('Asia/Manila');

session_start();

// Require authentication
require_once __DIR__ . '/includes/auth.php';
require_auth();

// Load database and functions
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Get database connection
$pdo = Database::connect();

// Get current user
$current_user = get_session_user();

// Check if user is admin
if ($current_user['role'] !== 'admin') {
    $_SESSION['error_message'] = 'You do not have permission to manage users.';
    redirect('index.php?page=dashboard');
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php?page=users');
}

// Get action from POST data
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'save':
            // Check if adding new or editing existing
            $user_id = $_POST['id'] ?? null;
            
            // Validate required fields
            $first_name = trim($_POST['first_name'] ?? '');
            $last_name = trim($_POST['last_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contact_number = trim($_POST['contact_number'] ?? '');
            $role = $_POST['role'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Validation
            if (empty($first_name) || empty($last_name)) {
                $_SESSION['error_message'] = 'First name and last name are required.';
                redirect('index.php?page=users');
            }
            
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error_message'] = 'Valid email address is required.';
                redirect('index.php?page=users');
            }
            
            if (!in_array($role, ['admin', 'staff'])) {
                $_SESSION['error_message'] = 'Valid role is required.';
                redirect('index.php?page=users');
            }
            
            if ($user_id) {
                // Update existing user
                
                // Check if email is already taken by another user
                $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $check_email->execute([$email, $user_id]);
                if ($check_email->fetch()) {
                    $_SESSION['error_message'] = 'Email address is already in use by another user.';
                    redirect('index.php?page=users');
                }
                
                // Build update query
                $sql = "UPDATE users SET 
                        first_name = :first_name,
                        last_name = :last_name,
                        email = :email,
                        contact_number = :contact_number,
                        role = :role,
                        updated_at = NOW()";
                
                $params = [
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':email' => $email,
                    ':contact_number' => $contact_number ?: null,
                    ':role' => $role,
                    ':id' => $user_id
                ];
                
                // Only update password if provided
                if (!empty($password)) {
                    if (strlen($password) < 6) {
                        $_SESSION['error_message'] = 'Password must be at least 6 characters.';
                        redirect('index.php?page=users');
                    }
                    $sql .= ", password_hash = :password_hash";
                    $params[':password_hash'] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                $sql .= " WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                
                $_SESSION['success_message'] = 'User updated successfully!';
            } else {
                // Add new user
                
                // Validate password for new user
                if (empty($password) || strlen($password) < 6) {
                    $_SESSION['error_message'] = 'Password is required and must be at least 6 characters.';
                    redirect('index.php?page=users');
                }
                
                // Check if email already exists
                $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $check_email->execute([$email]);
                if ($check_email->fetch()) {
                    $_SESSION['error_message'] = 'Email address is already in use.';
                    redirect('index.php?page=users');
                }
                
                $sql = "INSERT INTO users (
                        first_name, last_name, email, contact_number, role, password_hash, is_active, created_at, updated_at
                    ) VALUES (
                        :first_name, :last_name, :email, :contact_number, :role, :password_hash, 1, NOW(), NOW()
                    )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':first_name' => $first_name,
                    ':last_name' => $last_name,
                    ':email' => $email,
                    ':contact_number' => $contact_number ?: null,
                    ':role' => $role,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT)
                ]);
                
                $_SESSION['success_message'] = 'User added successfully!';
            }
            
            redirect('index.php?page=users');
            break;
            
        case 'toggle-status':
            // Toggle user active status
            $user_id = $_POST['id'] ?? null;
            $is_active = $_POST['is_active'] ?? null;
            
            if (empty($user_id) || $is_active === null) {
                $_SESSION['error_message'] = 'Invalid request.';
                redirect('index.php?page=users');
            }
            
            // Prevent disabling own account
            if ($user_id == $current_user['id']) {
                $_SESSION['error_message'] = 'You cannot disable your own account.';
                redirect('index.php?page=users');
            }
            
            $sql = "UPDATE users SET is_active = :is_active, updated_at = NOW() WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':is_active' => $is_active,
                ':id' => $user_id
            ]);
            
            $status_text = $is_active ? 'enabled' : 'disabled';
            $_SESSION['success_message'] = "User account {$status_text} successfully!";
            redirect('index.php?page=users');
            break;
            
        default:
            $_SESSION['error_message'] = 'Invalid action.';
            redirect('index.php?page=users');
            break;
    }
} catch (PDOException $e) {
    error_log('Database error in user_actions.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    redirect('index.php?page=users');
}
