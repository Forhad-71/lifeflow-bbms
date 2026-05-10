<?php
// api/create_post.php - Create new community post with file upload (images, videos, docs)
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$username = $_SESSION['username'];
$body = trim($_POST['body'] ?? '');
$filePath = null;

// Handle file upload (images, videos, PDFs, text files)
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/posts/';
    
    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Get file info
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileTmp = $_FILES['file']['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Allowed file types
    $allowedImages = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $allowedVideos = ['mp4', 'webm', 'ogg', 'mov'];
    $allowedDocs = ['pdf', 'txt', 'doc', 'docx'];
    $allAllowed = array_merge($allowedImages, $allowedVideos, $allowedDocs);
    
    if (!in_array($fileExt, $allAllowed)) {
        echo json_encode(['success' => false, 'message' => 'File type not allowed. Allowed: ' . implode(', ', $allAllowed)]);
        exit;
    }
    
    // Check file size
    $maxSize = 50 * 1024 * 1024; // 50MB for videos
    if (in_array($fileExt, $allowedImages)) {
        $maxSize = 10 * 1024 * 1024; // 10MB for images
    } elseif (in_array($fileExt, $allowedDocs)) {
        $maxSize = 20 * 1024 * 1024; // 20MB for documents
    }
    
    if ($fileSize > $maxSize) {
        $maxMB = $maxSize / (1024 * 1024);
        echo json_encode(['success' => false, 'message' => "File must be less than {$maxMB}MB"]);
        exit;
    }
    
    // Generate unique filename
    $newFileName = uniqid('post_') . '_' . time() . '.' . $fileExt;
    $targetPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmp, $targetPath)) {
        $filePath = 'uploads/posts/' . $newFileName;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit;
    }
}

// Also check old 'image' field for backward compatibility
if (empty($filePath) && isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/posts/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid('post_') . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $filePath = 'uploads/posts/' . $filename;
    }
}

// Validate input
if (empty($body) && empty($filePath)) {
    echo json_encode(['success' => false, 'message' => 'Please write something or add a file']);
    exit;
}

// Insert post into database
$stmt = mysqli_prepare($conn, "INSERT INTO posts (username, body, image_path) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sss", $username, $body, $filePath);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => true, 'message' => 'Post created successfully']);
} else {
    mysqli_stmt_close($stmt);
    echo json_encode(['success' => false, 'message' => 'Failed to create post: ' . mysqli_error($conn)]);
}
