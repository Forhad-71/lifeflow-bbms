<?php
// api/reset_password.php - Reset password for admin or user
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$type = $data['type'] ?? 'admin'; // 'admin' or 'user'

// Validate inputs
if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username and password required']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

// Verify OTP was verified (check session)
if (!isset($_SESSION['email_otp_verified']) || $_SESSION['email_otp_verified'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Please verify OTP first']);
    exit;
}

// Hash the new password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update password based on type
if ($type === 'admin') {
    $stmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE username = ?");
} else {
    $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = ?");
}

mysqli_stmt_bind_param($stmt, "ss", $hashedPassword, $username);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        // Clear OTP session
        unset($_SESSION['email_otp']);
        unset($_SESSION['email_otp_email']);
        unset($_SESSION['email_otp_expires']);
        unset($_SESSION['email_otp_verified']);
        
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => true, 'message' => 'Password reset successfully']);
    } else {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Account not found']);
    }
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Failed to reset password']);
}
