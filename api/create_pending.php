<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request body.']);
    exit;
}

$role      = $data['role'] ?? 'user'; // Tells us if this is an admin or user
$username  = trim($data['username'] ?? '');
$email     = trim($data['email'] ?? '');
$phone     = trim($data['phone'] ?? '');
$password  = (string)($data['password'] ?? '');
$cpassword = (string)($data['cpassword'] ?? '');

if ($username === '' || strlen($username) < 3) {
    echo json_encode(['success' => false, 'message' => 'Username must be at least 3 characters.']);
    exit;
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email.']);
    exit;
}
if ($phone === '' || strlen($phone) < 6) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid phone number.']);
    exit;
}
if ($password === '' || strlen($password) < 4) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 4 characters.']);
    exit;
}
if ($password !== $cpassword) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

// Ensure pending_users table exists
$createSql = "CREATE TABLE IF NOT EXISTS pending_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(200) NOT NULL,
  phone VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  email_otp VARCHAR(10) DEFAULT NULL,
  phone_otp VARCHAR(10) DEFAULT NULL,
  email_verified TINYINT(1) NOT NULL DEFAULT 0,
  phone_verified TINYINT(1) NOT NULL DEFAULT 0,
  otp_expires_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
mysqli_query($conn, $createSql);

// Check uniqueness ONLY in the table they are signing up for!
if ($role === 'admin') {
    $stmt = mysqli_prepare($conn, "SELECT 1 FROM admins WHERE username = ? LIMIT 1");
} else {
    $stmt = mysqli_prepare($conn, "SELECT 1 FROM users WHERE username = ? LIMIT 1");
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Username already exists. Please choose another.']);
    exit;
}
mysqli_stmt_close($stmt);

// Create initial OTPs
$emailOtp = strval(random_int(100000, 999999));
$phoneOtp = strval(random_int(100000, 999999));
$expiresAt = (new DateTime('now'))->add(new DateInterval('PT10M'))->format('Y-m-d H:i:s');

// Insert pending user
$stmt = mysqli_prepare($conn, "INSERT INTO pending_users (username, email, phone, password, email_otp, phone_otp, otp_expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sssssss", $username, $email, $phone, $password, $emailOtp, $phoneOtp, $expiresAt);

if (!mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Could not create pending signup.']);
    exit;
}

$pendingId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

echo json_encode([
    'success' => true,
    'pending_id' => $pendingId
]);
?>