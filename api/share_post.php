<?php
// api/share_post.php - Share a post to your feed (Facebook style)
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$username = $_SESSION['username'];
$originalPostId = intval($_POST['post_id'] ?? 0);
$shareText = trim($_POST['share_text'] ?? '');

if ($originalPostId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post']);
    exit;
}

// Get original post details
$stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE post_id = ?");
mysqli_stmt_bind_param($stmt, "i", $originalPostId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$originalPost = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$originalPost) {
    echo json_encode(['success' => false, 'message' => 'Post not found']);
    exit;
}

// Check if user is trying to share their own post
if ($originalPost['username'] === $username) {
    echo json_encode(['success' => false, 'message' => 'You cannot share your own post']);
    exit;
}

// Create shared post body with reference to original
$sharedBody = $shareText;

// Insert the shared post with reference to original
// We'll store the shared_post_id in the body as a special marker
$shareMarker = "[[SHARED_POST:{$originalPostId}]]";
$fullBody = $sharedBody . "\n" . $shareMarker;

$stmt = mysqli_prepare($conn, "INSERT INTO posts (username, body, image_path, created_at) VALUES (?, ?, NULL, NOW())");
mysqli_stmt_bind_param($stmt, "ss", $username, $fullBody);

if (mysqli_stmt_execute($stmt)) {
    $newPostId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Post shared successfully!',
        'post_id' => $newPostId
    ]);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Failed to share post']);
}
