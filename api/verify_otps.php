<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request body.']);
    exit;
}

$pendingId = intval($data['pending_id'] ?? 0);
$emailOtp = trim($data['email_otp'] ?? '');
$phoneOtp = trim($data['phone_otp'] ?? '');

if ($pendingId <= 0 || $emailOtp === '' || $phoneOtp === '') {
    echo json_encode(['success' => false, 'message' => 'Missing OTP(s).']);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT email_otp, phone_otp, otp_expires_at, email_verified, phone_verified FROM pending_users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $pendingId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $dbEmailOtp, $dbPhoneOtp, $dbExpiresAt, $dbEmailVerified, $dbPhoneVerified);

if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Pending signup not found.']);
    exit;
}
mysqli_stmt_close($stmt);

// expiry
if ($dbExpiresAt === null) {
    echo json_encode(['success' => false, 'message' => 'OTP not generated. Please click Send Code again.']);
    exit;
}
$now = new DateTime('now');
$exp = DateTime::createFromFormat('Y-m-d H:i:s', $dbExpiresAt);
if (!$exp || $now > $exp) {
    echo json_encode(['success' => false, 'message' => 'OTP expired. Please click Send Code again.']);
    exit;
}

$emailOk = hash_equals((string)$dbEmailOtp, (string)$emailOtp);
$phoneOk = hash_equals((string)$dbPhoneOtp, (string)$phoneOtp);

if (!$emailOk && !$phoneOk) {
    echo json_encode(['success' => false, 'message' => 'Both OTP codes are incorrect.']);
    exit;
}
if (!$emailOk) {
    echo json_encode(['success' => false, 'message' => 'Email OTP is incorrect.']);
    exit;
}
if (!$phoneOk) {
    echo json_encode(['success' => false, 'message' => 'Phone OTP is incorrect.']);
    exit;
}

// Update verified flags
$stmt = mysqli_prepare($conn, "UPDATE pending_users SET email_verified = 1, phone_verified = 1 WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $pendingId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode([
    'success' => true,
    'message' => 'OTP verified successfully.'
]);
