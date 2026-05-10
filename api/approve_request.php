<?php
// api/approve_request.php - Approve blood request and update stock
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../config.php';

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$requestId = intval($data['request_id'] ?? 0);
$bloodGroup = trim($data['blood_group'] ?? '');
$units = intval($data['units'] ?? 0);

if ($requestId <= 0 || empty($bloodGroup) || $units <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Start transaction
mysqli_begin_transaction($conn);

try {
    // Check if enough stock is available
    $stmt = mysqli_prepare($conn, "SELECT units FROM stock WHERE blood_group = ?");
    mysqli_stmt_bind_param($stmt, "s", $bloodGroup);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stock = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$stock) {
        throw new Exception("Blood group $bloodGroup not found in stock");
    }
    
    if ($stock['units'] < $units) {
        throw new Exception("Not enough stock. Available: {$stock['units']} units, Requested: $units units");
    }
    
    // Decrease stock
    $stmt = mysqli_prepare($conn, "UPDATE stock SET units = units - ? WHERE blood_group = ?");
    mysqli_stmt_bind_param($stmt, "is", $units, $bloodGroup);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Failed to update stock");
    }
    mysqli_stmt_close($stmt);
    
    // Delete the request (or you could update status to 'approved' if you have a status column)
    $stmt = mysqli_prepare($conn, "DELETE FROM request WHERE request_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $requestId);
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Failed to delete request");
    }
    mysqli_stmt_close($stmt);
    
    // Commit transaction
    mysqli_commit($conn);
    
    echo json_encode([
        'success' => true,
        'message' => 'Request approved successfully',
        'new_stock' => $stock['units'] - $units
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
