<?php
// api/find_user_account.php - Find user account by email for password reset
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$email = trim($data['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email is required']);
    exit;
}

// Find user by email
$stmt = mysqli_prepare($conn, "SELECT username FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    mysqli_stmt_close($stmt);
    echo json_encode([
        'success' => true,
        'username' => $row['username']
    ]);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'No account found with this email']);
}
