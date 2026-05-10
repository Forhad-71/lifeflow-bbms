<?php
require_once __DIR__ . '/../includes/auth.php';
require_user();
require __DIR__ . '/../config.php';

header('Content-Type: application/json');

$postId = intval($_POST['post_id'] ?? 0);
$reaction = $_POST['reaction'] ?? '';
$username = $_SESSION['username'] ?? '';

// Valid reactions: like, love, haha, sad, angry
$validReactions = ['like', 'love', 'haha', 'sad', 'angry'];

if ($postId <= 0 || !in_array($reaction, $validReactions) || $username === '') {
    echo json_encode(['ok' => false, 'error' => 'Invalid input']);
    exit;
}

// Check existing reaction
$stmt = mysqli_prepare($conn, "SELECT reaction FROM reactions WHERE post_id = ? AND username = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "is", $postId, $username);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$existing = null;
if ($row = mysqli_fetch_assoc($res)) {
    $existing = $row['reaction'];
}
mysqli_stmt_close($stmt);

if ($existing === $reaction) {
    // Toggle off (same reaction clicked again)
    $stmt = mysqli_prepare($conn, "DELETE FROM reactions WHERE post_id = ? AND username = ?");
    mysqli_stmt_bind_param($stmt, "is", $postId, $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $my = null;
} else if ($existing === null) {
    // Insert new reaction
    $stmt = mysqli_prepare($conn, "INSERT INTO reactions (post_id, username, reaction) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $postId, $username, $reaction);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $my = $reaction;
} else {
    // Update existing reaction
    $stmt = mysqli_prepare($conn, "UPDATE reactions SET reaction = ? WHERE post_id = ? AND username = ?");
    mysqli_stmt_bind_param($stmt, "sis", $reaction, $postId, $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $my = $reaction;
}

// Return updated counts
$stmt = mysqli_prepare($conn, "
    SELECT
        SUM(CASE WHEN reaction='like' THEN 1 ELSE 0 END) AS likes,
        SUM(CASE WHEN reaction='love' THEN 1 ELSE 0 END) AS loves,
        SUM(CASE WHEN reaction='haha' THEN 1 ELSE 0 END) AS hahas,
        SUM(CASE WHEN reaction='sad' THEN 1 ELSE 0 END) AS sads,
        SUM(CASE WHEN reaction='angry' THEN 1 ELSE 0 END) AS angrys
    FROM reactions
    WHERE post_id = ?
");
mysqli_stmt_bind_param($stmt, "i", $postId);
mysqli_stmt_execute($stmt);
$counts = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
mysqli_stmt_close($stmt);

echo json_encode([
    'ok' => true,
    'likes' => intval($counts['likes'] ?? 0),
    'loves' => intval($counts['loves'] ?? 0),
    'hahas' => intval($counts['hahas'] ?? 0),
    'sads' => intval($counts['sads'] ?? 0),
    'angrys' => intval($counts['angrys'] ?? 0),
    'my_reaction' => $my
]);
