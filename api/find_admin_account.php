<?php
// api/find_admin_account.php - Find admin account for password reset
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$username = trim($data['username'] ?? '');

if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username is required']);
    exit;
}

// Check admins table - it only has username and password, no email
// So we'll need to check if there's an email column
$stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    mysqli_stmt_close($stmt);
    
    // Check if email column exists
    if (isset($row['email']) && !empty($row['email'])) {
        $email = $row['email'];
        // Mask email for display
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
        $maskedEmail = $maskedName . '@' . $domain;
        
        echo json_encode([
            'success' => true,
            'email' => $email,
            'masked_email' => $maskedEmail
        ]);
    } else {
        // No email in admins table - check pending_admins
        $stmt2 = mysqli_prepare($conn, "SELECT email FROM pending_admins WHERE username = ?");
        mysqli_stmt_bind_param($stmt2, "s", $username);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);
        
        if ($row2 = mysqli_fetch_assoc($result2)) {
            $email = $row2['email'];
            $parts = explode('@', $email);
            $name = $parts[0];
            $domain = $parts[1] ?? '';
            $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
            $maskedEmail = $maskedName . '@' . $domain;
            
            mysqli_stmt_close($stmt2);
            echo json_encode([
                'success' => true,
                'email' => $email,
                'masked_email' => $maskedEmail
            ]);
        } else {
            mysqli_stmt_close($stmt2);
            // Admin exists but no email found
            echo json_encode([
                'success' => false,
                'message' => 'No email associated with this account. Please contact administrator.'
            ]);
        }
    }
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Account not found']);
}
