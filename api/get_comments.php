<?php
// api/get_comments.php - Get comments with nested replies
session_start();
require __DIR__ . '/../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['username'])) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in', 'comments' => []]);
    exit;
}

$postId = intval($_GET['post_id'] ?? 0);
if ($postId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Invalid post_id', 'comments' => []]);
    exit;
}

// Check if parent_id column exists, if not add it
$checkColumn = mysqli_query($conn, "SHOW COLUMNS FROM comments LIKE 'parent_id'");
if (mysqli_num_rows($checkColumn) == 0) {
    mysqli_query($conn, "ALTER TABLE comments ADD COLUMN parent_id INT DEFAULT NULL AFTER post_id");
}

// Get all comments for this post (including replies)
$allComments = [];
$query = "SELECT comment_id, parent_id, username, comment_text, created_at FROM comments WHERE post_id = ? ORDER BY created_at ASC";
$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    echo json_encode(['ok' => true, 'comments' => []]);
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $postId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($res)) {
    $allComments[] = [
        'id' => $row['comment_id'],
        'parent_id' => $row['parent_id'],
        'username' => $row['username'] ?? 'Unknown',
        'content' => $row['comment_text'] ?? '',
        'created_at' => date('M d, Y h:i A', strtotime($row['created_at'] ?? 'now')),
        'replies' => []
    ];
}
mysqli_stmt_close($stmt);

// Build nested structure
$commentMap = [];
$rootComments = [];

// First pass: create a map of all comments by ID
foreach ($allComments as $comment) {
    $commentMap[$comment['id']] = $comment;
}

// Second pass: organize into parent-child relationships
foreach ($allComments as $comment) {
    if ($comment['parent_id'] && isset($commentMap[$comment['parent_id']])) {
        // This is a reply, add to parent's replies
        $commentMap[$comment['parent_id']]['replies'][] = $commentMap[$comment['id']];
    } else {
        // This is a root comment
        $rootComments[] = &$commentMap[$comment['id']];
    }
}

// Update root comments with their replies
foreach ($rootComments as &$root) {
    if (isset($commentMap[$root['id']]['replies'])) {
        $root['replies'] = $commentMap[$root['id']]['replies'];
    }
}

echo json_encode(['ok' => true, 'comments' => array_values($rootComments)]);