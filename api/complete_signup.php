<?php
header('Content-Type: application/json; charset=utf-8');
// This prevents PHP from printing HTML errors that break JSON
error_reporting(0); 
require_once __DIR__ . '/../config.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid request body.']);
    exit;
}

$pendingId = intval($data['pending_id'] ?? 0);
$role = $data['role'] ?? 'user'; 

if ($pendingId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid pending id.']);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT username, email, phone, password, email_verified, phone_verified FROM pending_users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $pendingId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $username, $email, $phone, $password, $emailVerified, $phoneVerified);

if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Pending signup not found.']);
    exit;
}
mysqli_stmt_close($stmt);

if (intval($emailVerified) !== 1 || intval($phoneVerified) !== 1) {
    echo json_encode(['success' => false, 'message' => 'Please verify both email and phone first.']);
    exit;
}

// Final uniqueness check dynamically based on role
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
    echo json_encode(['success' => false, 'message' => 'Username already exists.']);
    exit;
}
mysqli_stmt_close($stmt);

// Build insert dynamically based on role and table structure
if ($role === 'admin') {
    $cols = [];
    $res = mysqli_query($conn, "DESCRIBE admins");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $cols[strtolower($row['Field'])] = true;
        }
    }

    if (isset($cols['email']) && isset($cols['phone']) && isset($cols['email_verified']) && isset($cols['phone_verified'])) {
        $stmt = mysqli_prepare($conn, "INSERT INTO admins (username, password, email, phone, email_verified, phone_verified) VALUES (?, ?, ?, ?, 1, 1)");
        if ($stmt) mysqli_stmt_bind_param($stmt, "ssss", $username, $password, $email, $phone);
    } elseif (isset($cols['email']) && isset($cols['phone'])) {
        $stmt = mysqli_prepare($conn, "INSERT INTO admins (username, password, email, phone) VALUES (?, ?, ?, ?)");
        if ($stmt) mysqli_stmt_bind_param($stmt, "ssss", $username, $password, $email, $phone);
    } else {
        // Fallback for simple admin tables
        $stmt = mysqli_prepare($conn, "INSERT INTO admins (username, password) VALUES (?, ?)");
        if ($stmt) mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    }
} else {
    // Users table logic
    $cols = [];
    $res = mysqli_query($conn, "DESCRIBE users");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $cols[strtolower($row['Field'])] = true;
        }
    }

    if (isset($cols['email']) && isset($cols['phone']) && isset($cols['email_verified']) && isset($cols['phone_verified'])) {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, email, phone, email_verified, phone_verified) VALUES (?, ?, ?, ?, 1, 1)");
        if ($stmt) mysqli_stmt_bind_param($stmt, "ssss", $username, $password, $email, $phone);
    } elseif (isset($cols['email']) && isset($cols['phone'])) {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, email, phone) VALUES (?, ?, ?, ?)");
        if ($stmt) mysqli_stmt_bind_param($stmt, "ssss", $username, $password, $email, $phone);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt) mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    }
}

// Execute the final insertion
if (!$stmt || !mysqli_stmt_execute($stmt)) {
    $errorMsg = mysqli_error($conn);
    if ($stmt) mysqli_stmt_close($stmt);
    // Send a clean JSON error instead of crashing
    echo json_encode(['success' => false, 'message' => 'Database Error: ' . $errorMsg]);
    exit;
}
mysqli_stmt_close($stmt);

// Cleanup pending row
$stmt = mysqli_prepare($conn, "DELETE FROM pending_users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $pendingId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode(['success' => true]);
?>