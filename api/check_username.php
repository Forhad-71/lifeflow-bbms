<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

$username = isset($_GET['username']) ? trim($_GET['username']) : '';
// Grab the role from the URL (defaults to 'user' if not provided)
$role = isset($_GET['role']) ? trim($_GET['role']) : 'user';

if ($username === '' || strlen($username) < 3) {
    echo json_encode([
        'available' => false,
        'message' => 'Type at least 3 characters.'
    ]);
    exit;
}

$available = true;

// 1. Check the specific table based on their role
if ($role === 'admin') {
    $stmt = mysqli_prepare($conn, "SELECT 1 FROM admins WHERE username = ? LIMIT 1");
} else {
    $stmt = mysqli_prepare($conn, "SELECT 1 FROM users WHERE username = ? LIMIT 1");
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    $available = false;
}
mysqli_stmt_close($stmt);

// 2. Also check pending_users (since both admins and users sit here during OTP verification)
if ($available) {
    $pendingExists = mysqli_query($conn, "SHOW TABLES LIKE 'pending_users'");
    if ($pendingExists && mysqli_num_rows($pendingExists) > 0) {
        $stmt2 = mysqli_prepare($conn, "SELECT 1 FROM pending_users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt2, "s", $username);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_store_result($stmt2);
        if (mysqli_stmt_num_rows($stmt2) > 0) {
            $available = false;
        }
        mysqli_stmt_close($stmt2);
    }
}

echo json_encode([
    'available' => $available,
    'message' => $available ? 'Username is available.' : 'Username already exists.'
]);
?>