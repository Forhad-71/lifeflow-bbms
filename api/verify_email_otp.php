<?php
// api/verify_email_otp.php - Verify Email OTP
header('Content-Type: application/json; charset=utf-8');
session_start();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$otp = trim($data['otp'] ?? '');
$email = trim($data['email'] ?? '');

if (empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'OTP is required.']);
    exit;
}

// Check if OTP exists in session
if (!isset($_SESSION['email_otp']) || !isset($_SESSION['email_otp_email'])) {
    echo json_encode(['success' => false, 'message' => 'No OTP found. Please request a new one.']);
    exit;
}

// Check if OTP is expired
if (isset($_SESSION['email_otp_expires']) && strtotime($_SESSION['email_otp_expires']) < time()) {
    unset($_SESSION['email_otp'], $_SESSION['email_otp_email'], $_SESSION['email_otp_expires']);
    echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.']);
    exit;
}

// Verify email matches
if (!empty($email) && $_SESSION['email_otp_email'] !== $email) {
    echo json_encode(['success' => false, 'message' => 'Email mismatch.']);
    exit;
}

// Verify OTP
if ($_SESSION['email_otp'] === $otp) {
    // Mark as verified
    $_SESSION['email_verified'] = true;
    $_SESSION['verified_email'] = $_SESSION['email_otp_email'];
    $_SESSION['email_otp_verified'] = true; // For password reset flow
    
    // Clear OTP data (but keep verified status)
    unset($_SESSION['email_otp'], $_SESSION['email_otp_expires']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Email verified successfully!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid OTP. Please try again.'
    ]);
}
