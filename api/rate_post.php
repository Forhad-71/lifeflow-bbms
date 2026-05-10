<?php
// api/rate_post.php - Rate a post with stars (1-5)
session_start();
require __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['ok' => false, 'error' => 'Please login first']);
    exit;
}

$postId = intval($_POST['post_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);
$username = $_SESSION['username'];

if ($postId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid post ID']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['ok' => false, 'error' => 'Rating must be 1-5']);
    exit;
}

// Check if ratings table exists, create if not
$checkTable = mysqli_query($conn, "SHOW TABLES LIKE 'ratings'");
if (mysqli_num_rows($checkTable) == 0) {
    mysqli_query($conn, "CREATE TABLE ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        username VARCHAR(100) NOT NULL,
        rating INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_rating (post_id, username)
    )");
}

// Check if user already rated this post
$checkStmt = mysqli_prepare($conn, "SELECT id FROM ratings WHERE post_id = ? AND username = ?");
mysqli_stmt_bind_param($checkStmt, "is", $postId, $username);
mysqli_stmt_execute($checkStmt);
$existing = mysqli_stmt_get_result($checkStmt);

if (mysqli_num_rows($existing) > 0) {
    // Update existing rating
    $stmt = mysqli_prepare($conn, "UPDATE ratings SET rating = ? WHERE post_id = ? AND username = ?");
    mysqli_stmt_bind_param($stmt, "iis", $rating, $postId, $username);
} else {
    // Insert new rating
    $stmt = mysqli_prepare($conn, "INSERT INTO ratings (post_id, username, rating) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isi", $postId, $username, $rating);
}

$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if (!$ok) {
    echo json_encode(['ok' => false, 'error' => 'Failed to save rating']);
    exit;
}

// Get updated average rating
$avgStmt = mysqli_prepare($conn, "SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM ratings WHERE post_id = ?");
mysqli_stmt_bind_param($avgStmt, "i", $postId);
mysqli_stmt_execute($avgStmt);
$avgResult = mysqli_fetch_assoc(mysqli_stmt_get_result($avgStmt));

echo json_encode([
    'ok' => true,
    'avg_rating' => round($avgResult['avg_rating'], 1),
    'total_ratings' => $avgResult['total'],
    'user_rating' => $rating
]);
