<?php
// api/create_admin_direct.php - Direct admin creation (Demo Mode)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request body.']);
    exit;
}

$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

// Validate inputs
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password required.']);
    exit;
}

// Check if username already exists
$stmt = mysqli_prepare($conn, "SELECT id FROM admins WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Username already exists.']);
    exit;
}
mysqli_stmt_close($stmt);

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Insert admin (only username and password - matching your table structure)
$stmt = mysqli_prepare($conn, "INSERT INTO admins (username, password) VALUES (?, ?)");
mysqli_stmt_bind_param($stmt, "ss", $username, $hashedPassword);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'message' => 'Admin created successfully.']);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Failed to create admin: ' . mysqli_error($conn)]);
}
