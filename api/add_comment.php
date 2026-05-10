<?php
// api/add_comment.php - Add a comment or reply to a post
session_start();
require __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['ok' => false, 'error' => 'Please login first']);
    exit;
}

$postId = intval($_POST['post_id'] ?? 0);
$parentId = intval($_POST['parent_id'] ?? 0); // For replies
$commentText = trim($_POST['content'] ?? '');
$username = $_SESSION['username'];

if ($postId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid post ID']);
    exit;
}

if ($commentText === '') {
    echo json_encode(['ok' => false, 'error' => 'Comment cannot be empty']);
    exit;
}

// Check if parent_id column exists
$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM comments LIKE 'parent_id'");
if (mysqli_num_rows($checkColumn) == 0) {
    mysqli_query($conn, "ALTER TABLE comments ADD COLUMN parent_id INT DEFAULT NULL AFTER post_id");
}

// Insert comment with parent_id for replies
if ($parentId > 0) {
    $stmt = mysqli_prepare($conn, "INSERT INTO comments (post_id, parent_id, username, comment_text, created_at) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "iiss", $postId, $parentId, $username, $commentText);
} else {
    $stmt = mysqli_prepare($conn, "INSERT INTO comments (post_id, parent_id, username, comment_text, created_at) VALUES (?, NULL, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "iss", $postId, $username, $commentText);
}

if (!$stmt) {
    echo json_encode(['ok' => false, 'error' => 'Database error']);
    exit;
}

$ok = mysqli_stmt_execute($stmt);
$newId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

if (!$ok) {
    echo json_encode(['ok' => false, 'error' => 'Failed to add comment']);
    exit;
}

echo json_encode([
    'ok' => true,
    'comment' => [
        'id' => $newId,
        'parent_id' => $parentId > 0 ? $parentId : null,
        'username' => $username,
        'content' => $commentText,
        'created_at' => date('M d, Y h:i A'),
        'replies' => []
    ]
]);
