<?php
// api/send_otp.php - Send Email OTP using PHPMailer
header('Content-Type: application/json; charset=utf-8');

// Manual PHPMailer include (without composer)
$phpmailerPath = __DIR__ . '/../vendor/phpmailer/phpmailer/src/';

if (!file_exists($phpmailerPath . 'PHPMailer.php')) {
    echo json_encode([
        'success' => false, 
        'message' => 'PHPMailer not found. Please install PHPMailer in vendor/phpmailer/phpmailer/src/'
    ]);
    exit;
}

// Include PHPMailer classes manually
require $phpmailerPath . 'Exception.php';
require $phpmailerPath . 'PHPMailer.php';
require $phpmailerPath . 'SMTP.php';

require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Get mail config
$mailConfig = require __DIR__ . '/../config/mail.php';

// Get request data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$email = trim($data['email'] ?? '');
$type = $data['type'] ?? 'signup';

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

// Generate 6-digit OTP
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiresAt = date('Y-m-d H:i:s', strtotime('+' . $mailConfig['otp_expiry_minutes'] . ' minutes'));

// Store OTP in session
session_start();
$_SESSION['email_otp'] = $otp;
$_SESSION['email_otp_email'] = $email;
$_SESSION['email_otp_expires'] = $expiresAt;

// Create PHPMailer instance
$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = $mailConfig['smtp_host'];
    $mail->SMTPAuth = $mailConfig['smtp_auth'];
    $mail->Username = $mailConfig['smtp_username'];
    $mail->Password = $mailConfig['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $mailConfig['smtp_port'];
    
    // Sender & Recipient
    $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
    $mail->addAddress($email);
    
    // Email Content
    $mail->isHTML(true);
    $mail->Subject = 'LifeFlow - Your Verification Code: ' . $otp;
    
    // Beautiful HTML Email Template
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #0f0f1a;">
        <div style="max-width: 500px; margin: 0 auto; padding: 40px 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="display: inline-block; background: linear-gradient(135deg, #c41e3a, #ff6b6b); padding: 15px 25px; border-radius: 50px;">
                    <span style="color: white; font-size: 24px; font-weight: bold;">LifeFlow</span>
                </div>
            </div>
            
            <div style="background: linear-gradient(145deg, #1a1a2e, #16162a); border-radius: 20px; padding: 40px; border: 1px solid rgba(196, 30, 58, 0.3);">
                <h1 style="color: #ffffff; text-align: center; margin: 0 0 10px 0; font-size: 24px;">
                    Email Verification
                </h1>
                <p style="color: #a0a0a0; text-align: center; margin: 0 0 30px 0;">
                    Use this code to verify your email address
                </p>
                
                <div style="background: linear-gradient(135deg, #c41e3a, #ff6b6b); border-radius: 15px; padding: 25px; text-align: center; margin-bottom: 30px;">
                    <p style="color: rgba(255,255,255,0.8); margin: 0 0 10px 0; font-size: 14px; letter-spacing: 2px;">
                        YOUR VERIFICATION CODE
                    </p>
                    <h2 style="color: #ffffff; margin: 0; font-size: 42px; letter-spacing: 8px; font-family: monospace;">
                        ' . $otp . '
                    </h2>
                </div>
                
                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 15px; margin-bottom: 20px;">
                    <p style="color: #f59e0b; margin: 0; font-size: 14px; text-align: center;">
                        This code expires in ' . $mailConfig['otp_expiry_minutes'] . ' minutes. Do not share it.
                    </p>
                </div>
                
                <p style="color: #666; font-size: 13px; text-align: center; margin: 0;">
                    If you did not request this code, please ignore this email.
                </p>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <p style="color: #666; font-size: 12px; margin: 0;">
                    LifeFlow Blood Bank Management System
                </p>
            </div>
        </div>
    </body>
    </html>';
    
    $mail->AltBody = "LifeFlow - Your verification code is: $otp (Expires in {$mailConfig['otp_expiry_minutes']} minutes)";
    
    // Send email
    $mail->send();
    
    echo json_encode([
        'success' => true,
        'message' => 'OTP sent to your email!',
        'expires_in' => $mailConfig['otp_expiry_minutes'] * 60
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email: ' . $mail->ErrorInfo
    ]);
}
