<?php
// Set timezone
date_default_timezone_set('Asia/Manila');

session_start();

// Load dependencies
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Get database connection
$pdo = Database::connect();

// Get requested page
$page = $_GET['page'] ?? 'login';

// Handle logout
if ($page === 'logout') {
    logout_user();
}

// Define role-based page access
$page_access = [
    'dashboard' => ['admin', 'staff'],
    'inventory' => ['admin', 'staff'],
    'transactions' => ['admin', 'staff'],
    'orders' => ['admin', 'staff'],
    'settings' => ['admin', 'staff'],
    'users' => ['admin'],
];

// Routes
switch ($page) {
    case 'login':
        if (is_logged_in()) {
            redirect('?page=dashboard');
        }
        render_view('login.php', ['pdo' => $pdo]);
        break;
        
    case 'dashboard':
        require_auth();
        if (isset($page_access[$page])) {
            require_role($page_access[$page]);
        }
        render_view('dashboard.php', ['pdo' => $pdo]);
        break;
        
    case 'inventory':
        require_auth();
        if (isset($page_access[$page])) {
            require_role($page_access[$page]);
        }
        render_view('inventory.php', ['pdo' => $pdo]);
        break;
        
    case 'transactions':
        require_auth();
        if (isset($page_access[$page])) {
            require_role($page_access[$page]);
        }
        render_view('transactions.php', ['pdo' => $pdo]);
        break;
        
    case 'orders':
        require_auth();
        if (isset($page_access[$page])) {
            require_role($page_access[$page]);
        }
        render_view('orders.php', ['pdo' => $pdo]);
        break;
        
    case 'settings':
        require_auth();
        if (isset($page_access[$page])) {
            require_role($page_access[$page]);
        }
        render_view('settings.php', ['pdo' => $pdo]);
        break;
        
    case 'users':
        require_auth();
        if (isset($page_access[$page])) {
            require_role($page_access[$page]);
        }
        render_view('users.php', ['pdo' => $pdo]);
        break;
        
    case '403':
        http_response_code(403);
        render_view('403.php');
        break;
        
    case '404':
        http_response_code(404);
        render_view('404.php');
        break;
        
    default:
        // Page not found
        show_404();
}
?>