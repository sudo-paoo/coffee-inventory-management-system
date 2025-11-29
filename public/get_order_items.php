<?php
// Set timezone
date_default_timezone_set('Asia/Manila');

session_start();
require_once __DIR__ . '/../config/database.php';

// Get order ID from request
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Order ID is required']);
    exit;
}

try {
    // Get database connection
    $pdo = Database::connect();

    // Fetch order items
    $stmt = $pdo->prepare("
        SELECT 
            item_name,
            quantity,
            unit,
            unit_cost,
            total_cost
        FROM order_items
        WHERE order_id = ?
        ORDER BY item_name
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'items' => $items,
        'count' => count($items),
        'order_id' => $order_id
    ]);
    
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
exit;
