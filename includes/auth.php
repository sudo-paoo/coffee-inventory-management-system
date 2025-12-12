<?php
function login_user($pdo, $email, $password) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_avatar'] = strtoupper($user['first_name'][0]);
        return true;
    }
    return false;
}

function logout_user() {
    session_destroy();
    redirect('?page=login');
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_auth() {
    if (!is_logged_in()) {
        http_response_code(403);
        render_view('403.php');
        exit;
    }
}

function require_role($allowed_roles) {
    require_auth();
    $user_role = $_SESSION['user_role'] ?? '';
    
    if (!in_array($user_role, $allowed_roles)) {
        http_response_code(403);
        render_view('403.php');
        exit;
    }
}

function show_404() {
    http_response_code(404);
    render_view('404.php');
    exit;
}

function get_session_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'avatar' => $_SESSION['user_avatar'] ?? 'U'
    ];
}
?>
