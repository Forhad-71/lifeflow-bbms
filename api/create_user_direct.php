<?php
// api/create_user_direct.php - Direct user creation (Demo Mode)
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request body.']);
    exit;
}

$username = trim($data['username'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$password = $data['password'] ?? '';

// Validate inputs
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password required.']);
    exit;
}

// Check if username already exists
$stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
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

// Insert user (matching your table structure: username, password, email, phone, email_verified, phone_verified)
$stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, email, phone, email_verified, phone_verified) VALUES (?, ?, ?, ?, 1, 1)");
mysqli_stmt_bind_param($stmt, "ssss", $username, $hashedPassword, $email, $phone);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'message' => 'User created successfully.']);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Failed to create user: ' . mysqli_error($conn)]);
}
