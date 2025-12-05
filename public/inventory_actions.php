<?php
// Inventory CRUD operations (Create, Update, Delete)

// Set timezone
date_default_timezone_set('Asia/Manila');

session_start();

// Require authentication
require_once __DIR__ . '/../includes/auth.php';
require_auth();

// Load database and functions
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Get database connection
$pdo = Database::connect();

// Get current user
$current_user = get_session_user();

// Response helper function
function json_response($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php?page=inventory');
}

// Get action from POST data
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'save':
            // Check if adding new or editing existing
            $item_id = $_POST['id'] ?? null;
            
            // Validate required fields
            $name = trim($_POST['name'] ?? '');
            $category_id = $_POST['category_id'] ?? null;
            $supplier_id = $_POST['supplier_id'] ?? null;
            $unit = trim($_POST['unit'] ?? '');
            $stock_quantity = $_POST['stock_quantity'] ?? 0;
            $reorder_level = $_POST['reorder_level'] ?? 0;
            $cost = $_POST['cost'] ?? 0;
            $price = $_POST['price'] ?? 0;
            $expiration_date = $_POST['expiration_date'] ?? null;
            $notes = trim($_POST['notes'] ?? '');
            $existing_image = $_POST['existing_image'] ?? null;
            
            // Validation
            if (empty($name)) {
                $_SESSION['error_message'] = 'Item name is required.';
                redirect('index.php?page=inventory');
            }
            
            if (empty($category_id)) {
                $_SESSION['error_message'] = 'Category is required.';
                redirect('index.php?page=inventory');
            }
            
            // Handle image upload
            $image_path = $existing_image;
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $file_name = $file['name'];
                $file_tmp = $file['tmp_name'];
                $file_size = $file['size'];
                $file_error = $file['error'];
                
                // Validate file size
                if ($file_size > 2 * 1024 * 1024) {
                    $_SESSION['error_message'] = 'Image size must be less than 2MB.';
                    redirect('index.php?page=inventory');
                }
                
                // Validate file type
                $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                $file_type = mime_content_type($file_tmp);
                
                if (!in_array($file_type, $allowed_types)) {
                    $_SESSION['error_message'] = 'Invalid image format. Only JPG, PNG, and GIF are allowed.';
                    redirect('index.php?page=inventory');
                }
                
                // Get file extension
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                // Generate unique filename
                $new_filename = uniqid('item_', true) . '.' . $file_extension;
                
                // Determine upload directory
                $category_stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                $category_stmt->execute([$category_id]);
                $category = $category_stmt->fetch();
                
                if ($category) {
                    $category_folder = strtolower(str_replace(' ', '-', $category['name']));
                } else {
                    $category_folder = 'uncategorized';
                }
                
                // Create upload directory
                $upload_dir = __DIR__ . '/assets/' . $category_folder . '/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Upload file
                $upload_path = $upload_dir . $new_filename;
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Store relative path
                    $image_path = 'assets/' . $category_folder . '/' . $new_filename;
                    
                    // Delete old image if exists and is different
                    if ($existing_image && $existing_image !== $image_path) {
                        $old_image_path = __DIR__ . '/' . $existing_image;
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                } else {
                    $_SESSION['error_message'] = 'Failed to upload image.';
                    redirect('index.php?page=inventory');
                }
            }
            
            if ($item_id) {
                // Update existing item (admin only)
                if ($current_user['role'] !== 'admin') {
                    $_SESSION['error_message'] = 'You do not have permission to edit items.';
                    redirect('index.php?page=inventory');
                }
                
                $sql = "UPDATE items SET 
                        name = :name,
                        category_id = :category_id,
                        supplier_id = :supplier_id,
                        unit = :unit,
                        stock_quantity = :stock_quantity,
                        reorder_level = :reorder_level,
                        cost = :cost,
                        price = :price,
                        expiration_date = :expiration_date,
                        notes = :notes,
                        image_path = :image_path,
                        updated_at = NOW(),
                        updated_by = :updated_by
                        WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':category_id' => $category_id,
                    ':supplier_id' => $supplier_id ?: null,
                    ':unit' => $unit,
                    ':stock_quantity' => $stock_quantity,
                    ':reorder_level' => $reorder_level,
                    ':cost' => $cost,
                    ':price' => $price,
                    ':expiration_date' => $expiration_date ?: null,
                    ':notes' => $notes ?: null,
                    ':image_path' => $image_path,
                    ':updated_by' => $current_user['id'],
                    ':id' => $item_id
                ]);
                
                $_SESSION['success_message'] = 'Item updated successfully!';
            } else {
                // Add new item (admin only)
                if ($current_user['role'] !== 'admin') {
                    $_SESSION['error_message'] = 'You do not have permission to add items.';
                    redirect('index.php?page=inventory');
                }
                
                $sql = "INSERT INTO items (
                        name, category_id, supplier_id, unit, stock_quantity, 
                        reorder_level, cost, price, expiration_date, notes, image_path,
                        created_at, updated_at
                    ) VALUES (
                        :name, :category_id, :supplier_id, :unit, :stock_quantity,
                        :reorder_level, :cost, :price, :expiration_date, :notes, :image_path,
                        NOW(), NOW()
                    )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $name,
                    ':category_id' => $category_id,
                    ':supplier_id' => $supplier_id ?: null,
                    ':unit' => $unit,
                    ':stock_quantity' => $stock_quantity,
                    ':reorder_level' => $reorder_level,
                    ':cost' => $cost,
                    ':price' => $price,
                    ':expiration_date' => $expiration_date ?: null,
                    ':notes' => $notes ?: null,
                    ':image_path' => $image_path
                ]);
                
                $_SESSION['success_message'] = 'Item added successfully!';
            }
            
            redirect('index.php?page=inventory');
            break;
            
        case 'delete':
            // Delete item (adminonly)
            if ($current_user['role'] !== 'admin') {
                $_SESSION['error_message'] = 'You do not have permission to delete items.';
                redirect('index.php?page=inventory');
            }
            
            $item_id = $_POST['id'] ?? null;
            
            if (empty($item_id)) {
                $_SESSION['error_message'] = 'Item ID is required.';
                redirect('index.php?page=inventory');
            }
            
            // Get item details including image path
            $item_sql = "SELECT image_path FROM items WHERE id = :id";
            $item_stmt = $pdo->prepare($item_sql);
            $item_stmt->execute([':id' => $item_id]);
            $item = $item_stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if item has related pending orders
            $check_orders_sql = "SELECT COUNT(*) as count FROM order_items oi
                                 INNER JOIN orders o ON oi.order_id = o.id
                                 WHERE oi.item_id = :id AND o.status = 'pending'";
            $check_orders_stmt = $pdo->prepare($check_orders_sql);
            $check_orders_stmt->execute([':id' => $item_id]);
            $orders_result = $check_orders_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($orders_result['count'] > 0) {
                $_SESSION['error_message'] = 'Cannot delete item with pending orders. Please wait for orders to be received or cancel them first.';
                redirect('index.php?page=inventory');
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            try {
                // Delete related transactions
                $delete_transactions_sql = "DELETE FROM transactions WHERE item_id = :id";
                $delete_transactions_stmt = $pdo->prepare($delete_transactions_sql);
                $delete_transactions_stmt->execute([':id' => $item_id]);
                
                // Delete related order items
                $delete_order_items_sql = "DELETE FROM order_items WHERE item_id = :id";
                $delete_order_items_stmt = $pdo->prepare($delete_order_items_sql);
                $delete_order_items_stmt->execute([':id' => $item_id]);
                
                // Delete the item
                $sql = "DELETE FROM items WHERE id = :id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':id' => $item_id]);
                
                $pdo->commit();
                
                // Delete image file if exists
                if ($item && !empty($item['image_path'])) {
                    $image_file_path = __DIR__ . '/' . $item['image_path'];
                    if (file_exists($image_file_path)) {
                        unlink($image_file_path);
                    }
                }
                
                $_SESSION['success_message'] = 'Item deleted successfully!';
            } catch (PDOException $e) {
                // Rollback on error
                $pdo->rollBack();
                error_log('Error deleting item: ' . $e->getMessage());
                $_SESSION['error_message'] = 'Failed to delete item: ' . $e->getMessage();
                redirect('index.php?page=inventory');
            }
            redirect('index.php?page=inventory');
            break;
            
        default:
            $_SESSION['error_message'] = 'Invalid action.';
            redirect('index.php?page=inventory');
            break;
    }
} catch (PDOException $e) {
    error_log('Database error in inventory_actions.php: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    redirect('index.php?page=inventory');
}