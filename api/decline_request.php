<?php
// api/decline_request.php - Decline blood request
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

if ($requestId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID']);
    exit;
}

// Delete the request
$stmt = mysqli_prepare($conn, "DELETE FROM request WHERE request_id = ?");
mysqli_stmt_bind_param($stmt, "i", $requestId);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Request declined successfully']);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Request not found']);
    }
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Failed to decline request']);
}
