<?php
// api/get_rating.php - Get post rating info
session_start();
require __DIR__ . '/../config.php';

header('Content-Type: application/json');

$postId = intval($_GET['post_id'] ?? 0);
$username = $_SESSION['username'] ?? '';

if ($postId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid post ID']);
    exit;
}

// Check if ratings table exists
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'ratings'");
if (mysqli_num_rows($checkTable) == 0) {
    echo json_encode([
        'ok' => true,
        'avg_rating' => 0,
        'total_ratings' => 0,
        'user_rating' => 0
    ]);
    exit;
}

// Get average rating
$avgStmt = mysqli_prepare($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM ratings WHERE post_id = ?");
mysqli_stmt_bind_param($avgStmt, "i", $postId);
mysqli_stmt_execute($avgStmt);
$avgResult = mysqli_fetch_assoc(mysqli_stmt_get_result($avgStmt));

// Get user's rating if logged in
$userRating = 0;
if ($username) {
    $userStmt = mysqli_prepare($conn, "SELECT rating FROM ratings WHERE post_id = ? AND username = ?");
    mysqli_stmt_bind_param($userStmt, "is", $postId, $username);
    mysqli_stmt_execute($userStmt);
    $userResult = mysqli_fetch_assoc(mysqli_stmt_get_result($userStmt));
    $userRating = $userResult['rating'] ?? 0;
}

echo json_encode([
    'ok' => true,
    'avg_rating' => round($avgResult['avg_rating'] ?? 0, 1),
    'total_ratings' => $avgResult['total'] ?? 0,
    'user_rating' => $userRating
]);
