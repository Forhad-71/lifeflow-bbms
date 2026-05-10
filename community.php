<?php
// community.php - LifeFlow Community Feed with Facebook-style Comments & Share
require "includes/auth.php";
require_user();
require "config.php";

$username = $_SESSION['username'] ?? 'Guest';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get posts with reaction counts
$posts = [];
$result = mysqli_query($conn, "
    SELECT p.*, 
           (SELECT COUNT(*) FROM reactions r WHERE r.post_id = p.post_id AND r.reaction = 'like') as likes,
           (SELECT COUNT(*) FROM reactions r WHERE r.post_id = p.post_id AND r.reaction = 'love') as loves,
           (SELECT COUNT(*) FROM reactions r WHERE r.post_id = p.post_id AND r.reaction = 'haha') as hahas,
           (SELECT COUNT(*) FROM reactions r WHERE r.post_id = p.post_id AND r.reaction = 'sad') as sads,
           (SELECT COUNT(*) FROM reactions r WHERE r.post_id = p.post_id AND r.reaction = 'angry') as angrys,
           (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.post_id) as comment_count,
           (SELECT reaction FROM reactions r WHERE r.post_id = p.post_id AND r.username = '$username' LIMIT 1) as my_reaction
    FROM posts p 
    ORDER BY p.created_at DESC 
    LIMIT $perPage OFFSET $offset
");
while ($row = mysqli_fetch_assoc($result)) {
    $posts[] = $row;
}

// Total posts count
$countResult = mysqli_query($conn, "SELECT COUNT(*) as total FROM posts");
$totalPosts = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalPosts / $perPage);

// Function to check if post is a share and get original post
function getSharedPost($body, $conn) {
    if (preg_match('/\[\[SHARED_POST:(\d+)\]\]/', $body, $matches)) {
        $originalId = intval($matches[1]);
        $cleanBody = trim(preg_replace('/\[\[SHARED_POST:\d+\]\]/', '', $body));
        
        $stmt = mysqli_prepare($conn, "SELECT * FROM posts WHERE post_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $originalId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $original = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ['isShare' => true, 'cleanBody' => $cleanBody, 'original' => $original];
    }
    return ['isShare' => false, 'cleanBody' => $body, 'original' => null];
}

$pageTitle = "Community - LifeFlow";
include 'includes/header.php';
?>

<style>
/* Reaction Styles */
.reaction-container { position: relative; display: inline-block; }
.reaction-btn-main {
    background: none; border: none; color: var(--text-muted); cursor: pointer;
    display: flex; align-items: center; gap: 8px; font-size: 0.95rem;
    padding: 10px 15px; border-radius: var(--radius-md); transition: all 0.3s ease;
}
.reaction-btn-main:hover { background: rgba(255,255,255,0.05); }
.reaction-btn-main.reacted { color: var(--primary); }

.reaction-popup {
    position: absolute; bottom: 100%; left: 0;
    background: var(--card-bg); border: 1px solid rgba(255,255,255,0.15);
    border-radius: 50px; padding: 8px 12px; display: flex; gap: 5px;
    opacity: 0; visibility: hidden; transform: translateY(10px) scale(0.9);
    transition: all 0.2s ease; box-shadow: 0 10px 40px rgba(0,0,0,0.4); z-index: 100;
}
.reaction-container:hover .reaction-popup { opacity: 1; visibility: visible; transform: translateY(-5px) scale(1); }
.reaction-emoji { font-size: 1.8rem; cursor: pointer; transition: transform 0.2s ease; padding: 5px; }
.reaction-emoji:hover { transform: scale(1.4); }

/* Comments Section */
.comments-section {
    margin-top: 15px; padding-top: 15px;
    border-top: 1px solid rgba(255,255,255,0.1);
    max-height: 400px; overflow-y: auto;
}
.comment-item {
    display: flex; gap: 10px; margin-bottom: 15px;
}
.comment-avatar {
    width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 600; flex-shrink: 0;
}
.comment-bubble {
    background: rgba(255,255,255,0.05); padding: 10px 15px;
    border-radius: 18px; flex: 1; max-width: calc(100% - 50px);
}
.comment-bubble .name { font-weight: 600; font-size: 0.9rem; margin-bottom: 3px; }
.comment-bubble .text { font-size: 0.9rem; color: var(--text-secondary); word-wrap: break-word; }
.comment-bubble .time { font-size: 0.75rem; color: var(--text-muted); margin-top: 5px; }

.comment-form {
    display: flex; gap: 10px; margin-top: 15px; padding-top: 15px;
    border-top: 1px solid rgba(255,255,255,0.05);
}
.comment-input {
    flex: 1; padding: 12px 18px; background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1); border-radius: 25px;
    color: white; font-size: 0.9rem; outline: none;
}
.comment-input:focus { border-color: var(--primary); }
.comment-submit {
    background: var(--primary); border: none; color: white;
    width: 40px; height: 40px; border-radius: 50%; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
}
.comment-submit:hover { background: var(--accent); }

/* Share Modal */
.share-modal {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.8); display: none; align-items: center;
    justify-content: center; z-index: 1000; padding: 20px;
}
.share-modal.active { display: flex; }
.share-modal-content {
    background: var(--card-bg); border-radius: var(--radius-xl);
    width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;
    border: 1px solid rgba(255,255,255,0.1);
}
.share-modal-header {
    padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex; justify-content: space-between; align-items: center;
}
.share-modal-header h3 { margin: 0; }
.share-modal-close {
    background: none; border: none; color: var(--text-muted);
    font-size: 1.5rem; cursor: pointer;
}
.share-modal-body { padding: 20px; }
.share-textarea {
    width: 100%; padding: 15px; background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius-md);
    color: white; font-size: 1rem; resize: none; margin-bottom: 15px;
}
.share-preview {
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius-md); padding: 15px;
}
.share-preview-header { display: flex; gap: 10px; margin-bottom: 10px; }
.share-preview-avatar {
    width: 35px; height: 35px; background: var(--primary);
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem;
}
.share-preview-name { font-weight: 600; font-size: 0.9rem; }
.share-preview-text { font-size: 0.9rem; color: var(--text-secondary); }
.share-modal-footer { padding: 15px 20px; border-top: 1px solid rgba(255,255,255,0.1); }

/* Shared Post */
.shared-post-embed {
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--radius-md); padding: 15px; margin-top: 10px;
}
.shared-label { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 10px; }
.shared-label i { margin-right: 5px; }

/* File Preview */
.file-preview {
    margin-bottom: 15px; padding: 15px;
    background: rgba(255,255,255,0.03); border-radius: var(--radius-md);
    border: 1px solid rgba(255,255,255,0.1);
}
.file-preview-item {
    display: flex; align-items: center; gap: 15px; padding: 10px;
    background: rgba(255,255,255,0.05); border-radius: var(--radius-md);
}
.file-preview-icon {
    width: 50px; height: 50px; border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
}
.file-preview-icon.image { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
.file-preview-icon.video { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
.file-preview-icon.pdf { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
.file-preview-icon.text { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.file-preview-info { flex: 1; }
.file-preview-name { font-weight: 500; margin-bottom: 3px; }
.file-preview-size { font-size: 0.8rem; color: var(--text-muted); }
.file-preview-remove {
    background: rgba(239, 68, 68, 0.2); border: none; color: #ef4444;
    width: 30px; height: 30px; border-radius: 50%; cursor: pointer;
}

.post-file {
    margin: 15px 0; padding: 15px;
    background: rgba(255,255,255,0.03); border-radius: var(--radius-md);
    border: 1px solid rgba(255,255,255,0.1);
}
.post-file a {
    display: flex; align-items: center; gap: 10px;
    color: var(--primary); text-decoration: none;
}

.post-actions {
    display: flex; align-items: center; gap: 5px; padding-top: 15px;
    border-top: 1px solid rgba(255,255,255,0.1);
}
.post-actions button {
    flex: 1; background: none; border: none; color: var(--text-muted);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    gap: 8px; font-size: 0.95rem; padding: 10px; border-radius: var(--radius-md);
}
.post-actions button:hover { background: rgba(255,255,255,0.05); }

/* Star Rating System */
.star-rating-container {
    display: flex; align-items: center; gap: 15px; padding: 12px 0;
    border-top: 1px solid rgba(255,255,255,0.05);
}
.star-rating {
    display: flex; gap: 3px;
}
.star-rating .star {
    font-size: 1.3rem; cursor: pointer; color: rgba(255,255,255,0.2);
    transition: all 0.2s ease;
}
.star-rating .star:hover,
.star-rating .star.hovered {
    color: #fbbf24; transform: scale(1.2);
}
.star-rating .star.filled {
    color: #fbbf24;
}
.rating-info {
    font-size: 0.85rem; color: var(--text-muted);
}
.rating-info .avg {
    font-weight: 700; color: #fbbf24; font-size: 1rem;
}

/* YouTube Embed */
.youtube-embed {
    position: relative; width: 100%; padding-bottom: 56.25%;
    margin: 15px 0; border-radius: var(--radius-md); overflow: hidden;
    background: #000;
}
.youtube-embed iframe {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    border: none;
}
.youtube-input-wrapper {
    display: flex; align-items: center; gap: 8px; margin-top: 10px;
    padding: 10px 15px; background: rgba(255,255,255,0.03);
    border-radius: var(--radius-md); border: 1px solid rgba(255,255,255,0.1);
}
.youtube-input-wrapper i { color: #ff0000; font-size: 1.2rem; }
.youtube-input-wrapper input {
    flex: 1; background: none; border: none; color: white;
    font-size: 0.9rem; outline: none;
}
.youtube-input-wrapper input::placeholder { color: var(--text-muted); }
</style>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-header" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-users" style="color: var(--primary);"></i> Community</h1>
            <p class="page-subtitle">Share stories, connect with donors, and spread awareness</p>
        </div>
        
        <!-- Create Post -->
        <div class="card" id="createPost" style="margin-bottom: 30px;">
            <form id="postForm" enctype="multipart/form-data">
                <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                    <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                        <?php echo strtoupper(substr($username, 0, 2)); ?>
                    </div>
                    <textarea id="postContent" placeholder="What's on your mind, <?php echo htmlspecialchars($username); ?>?" rows="3" style="flex: 1; padding: 15px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius-lg); color: white; font-family: inherit; resize: none;"></textarea>
                </div>
                
                <div id="filePreview" class="file-preview" style="display: none;"></div>
                
                <!-- YouTube Link Input -->
                <div class="youtube-input-wrapper" id="youtubeInputWrapper" style="display: none;">
                    <i class="fab fa-youtube"></i>
                    <input type="text" id="youtubeLink" placeholder="Paste YouTube link here...">
                    <button type="button" onclick="clearYoutubeLink()" style="background: none; border: none; color: var(--text-muted); cursor: pointer;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid rgba(255,255,255,0.1);">
                    <div style="display: flex; gap: 15px;">
                        <label style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); cursor: pointer; padding: 8px 12px; border-radius: var(--radius-md);" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='none'">
                            <i class="fas fa-image" style="color: #10b981; font-size: 1.2rem;"></i> Photo/Video
                            <input type="file" id="postFile" accept="image/*,video/*,.pdf,.txt,.doc,.docx" onchange="previewFiles(this)" style="display: none;">
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); cursor: pointer; padding: 8px 12px; border-radius: var(--radius-md);" onclick="document.getElementById('postFile').click()" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='none'">
                            <i class="fas fa-file-alt" style="color: #3b82f6; font-size: 1.2rem;"></i> Document
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; color: var(--text-muted); cursor: pointer; padding: 8px 12px; border-radius: var(--radius-md);" onclick="toggleYoutubeInput()" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='none'">
                            <i class="fab fa-youtube" style="color: #ff0000; font-size: 1.2rem;"></i> YouTube
                        </label>
                    </div>
                    <button type="button" onclick="createPost()" class="btn btn--primary">
                        <i class="fas fa-paper-plane"></i> Post
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Posts Feed -->
        <div id="postsFeed">
            <?php if (empty($posts)): ?>
            <div class="card" style="text-align: center; padding: 60px;">
                <i class="fas fa-comment-slash" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 20px;"></i>
                <h3>No Posts Yet</h3>
                <p style="color: var(--text-muted);">Be the first to share something!</p>
            </div>
            <?php else: ?>
            <?php foreach ($posts as $post): 
                $totalReactions = ($post['likes'] ?? 0) + ($post['loves'] ?? 0) + ($post['hahas'] ?? 0) + ($post['sads'] ?? 0) + ($post['angrys'] ?? 0);
                $myReaction = $post['my_reaction'] ?? '';
                $shareData = getSharedPost($post['body'] ?? '', $conn);
            ?>
            <div class="card post-card" data-post-id="<?php echo $post['post_id']; ?>" style="margin-bottom: 20px;">
                <!-- Post Header -->
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                    <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                        <?php echo strtoupper(substr($post['username'] ?? 'U', 0, 2)); ?>
                    </div>
                    <div>
                        <h4 style="margin: 0;"><?php echo htmlspecialchars($post['username'] ?? 'Anonymous'); ?></h4>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">
                            <i class="fas fa-globe-asia"></i> <?php echo date('M d, Y \a\t h:i A', strtotime($post['created_at'])); ?>
                        </p>
                    </div>
                </div>
                
                <?php if ($shareData['isShare']): ?>
                <!-- Shared Post -->
                <?php if (!empty($shareData['cleanBody'])): ?>
                <div style="margin-bottom: 15px;">
                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($shareData['cleanBody'])); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($shareData['original']): ?>
                <div class="shared-post-embed">
                    <div class="shared-label"><i class="fas fa-share"></i> Shared post</div>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <div style="width: 35px; height: 35px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                            <?php echo strtoupper(substr($shareData['original']['username'], 0, 2)); ?>
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($shareData['original']['username']); ?></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($shareData['original']['created_at'])); ?></div>
                        </div>
                    </div>
                    <p style="margin: 0; font-size: 0.9rem; color: var(--text-secondary);"><?php echo nl2br(htmlspecialchars($shareData['original']['body'] ?? '')); ?></p>
                    
                    <?php if (!empty($shareData['original']['image_path'])): 
                        $origPath = $shareData['original']['image_path'];
                        $origExt = strtolower(pathinfo($origPath, PATHINFO_EXTENSION));
                    ?>
                        <?php if (in_array($origExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                        <div style="margin-top: 10px; border-radius: var(--radius-md); overflow: hidden;">
                            <img src="<?php echo htmlspecialchars($origPath); ?>" alt="" style="width: 100%; display: block;">
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <!-- Regular Post -->
                <?php 
                // Check for YouTube link in post body
                $youtubeId = null;
                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $post['body'] ?? '', $ytMatches)) {
                    $youtubeId = $ytMatches[1];
                }
                ?>
                <div style="margin-bottom: 15px;">
                    <p style="margin: 0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($post['body'] ?? '')); ?></p>
                </div>
                
                <!-- YouTube Embed -->
                <?php if ($youtubeId): ?>
                <div class="youtube-embed">
                    <iframe src="https://www.youtube.com/embed/<?php echo $youtubeId; ?>" allowfullscreen></iframe>
                </div>
                <?php endif; ?>
                
                <!-- Post Media -->
                <?php if (!empty($post['image_path'])): 
                    $filePath = $post['image_path'];
                    $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                ?>
                    <?php if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): ?>
                    <div style="margin-bottom: 15px; border-radius: var(--radius-md); overflow: hidden;">
                        <img src="<?php echo htmlspecialchars($filePath); ?>" alt="" style="width: 100%; display: block;">
                    </div>
                    <?php elseif (in_array($fileExt, ['mp4', 'webm', 'ogg'])): ?>
                    <div style="margin-bottom: 15px; border-radius: var(--radius-md); overflow: hidden;">
                        <video controls style="width: 100%; display: block;">
                            <source src="<?php echo htmlspecialchars($filePath); ?>" type="video/<?php echo $fileExt; ?>">
                        </video>
                    </div>
                    <?php else: ?>
                    <div class="post-file">
                        <a href="<?php echo htmlspecialchars($filePath); ?>" target="_blank" download>
                            <i class="fas fa-file-<?php echo $fileExt === 'pdf' ? 'pdf' : 'alt'; ?>" style="font-size: 1.5rem;"></i>
                            <span>Download <?php echo strtoupper($fileExt); ?> File</span>
                        </a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                <?php endif; ?>
                
                <!-- Reaction Summary -->
                <?php if ($totalReactions > 0 || $post['comment_count'] > 0): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0; color: var(--text-muted); font-size: 0.9rem;">
                    <div style="display: flex; align-items: center; gap: 5px;">
                        <?php if ($totalReactions > 0): ?>
                        <span style="display: flex;">
                            <?php if (($post['likes'] ?? 0) > 0): ?><span>👍</span><?php endif; ?>
                            <?php if (($post['loves'] ?? 0) > 0): ?><span style="margin-left:-5px;">❤️</span><?php endif; ?>
                            <?php if (($post['hahas'] ?? 0) > 0): ?><span style="margin-left:-5px;">😂</span><?php endif; ?>
                            <?php if (($post['sads'] ?? 0) > 0): ?><span style="margin-left:-5px;">😢</span><?php endif; ?>
                            <?php if (($post['angrys'] ?? 0) > 0): ?><span style="margin-left:-5px;">😡</span><?php endif; ?>
                        </span>
                        <span><?php echo $totalReactions; ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($post['comment_count'] > 0): ?>
                    <span onclick="toggleComments(<?php echo $post['post_id']; ?>)" style="cursor: pointer;"><?php echo $post['comment_count']; ?> comments</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Post Actions -->
                <div class="post-actions">
                    <div class="reaction-container" style="flex: 1;">
                        <button class="reaction-btn-main <?php echo $myReaction ? 'reacted' : ''; ?>" id="reaction-btn-<?php echo $post['post_id']; ?>" style="width: 100%; justify-content: center;">
                            <span class="current-reaction"><?php 
                                $emojis = ['like' => '👍', 'love' => '❤️', 'haha' => '😂', 'sad' => '😢', 'angry' => '😡'];
                                echo $myReaction ? $emojis[$myReaction] : '👍';
                            ?></span>
                            <span class="reaction-text"><?php echo $myReaction ? ucfirst($myReaction) : 'Like'; ?></span>
                        </button>
                        <div class="reaction-popup">
                            <span class="reaction-emoji" onclick="setReaction(<?php echo $post['post_id']; ?>, 'like')" title="Like">👍</span>
                            <span class="reaction-emoji" onclick="setReaction(<?php echo $post['post_id']; ?>, 'love')" title="Love">❤️</span>
                            <span class="reaction-emoji" onclick="setReaction(<?php echo $post['post_id']; ?>, 'haha')" title="Haha">😂</span>
                            <span class="reaction-emoji" onclick="setReaction(<?php echo $post['post_id']; ?>, 'sad')" title="Sad">😢</span>
                            <span class="reaction-emoji" onclick="setReaction(<?php echo $post['post_id']; ?>, 'angry')" title="Angry">😡</span>
                        </div>
                    </div>
                    <button onclick="toggleComments(<?php echo $post['post_id']; ?>)" style="flex: 1;">
                        <i class="fas fa-comment"></i> Comment
                    </button>
                    <button onclick="openShareModal(<?php echo $post['post_id']; ?>, '<?php echo addslashes(htmlspecialchars($post['username'])); ?>', '<?php echo addslashes(htmlspecialchars(substr($shareData['isShare'] ? ($shareData['original']['body'] ?? '') : ($post['body'] ?? ''), 0, 100))); ?>')" style="flex: 1;">
                        <i class="fas fa-share"></i> Share
                    </button>
                </div>
                
                <!-- Star Rating Section -->
                <div class="star-rating-container" data-post-id="<?php echo $post['post_id']; ?>">
                    <span style="color: var(--text-muted); font-size: 0.9rem;">Rate:</span>
                    <div class="star-rating" id="star-rating-<?php echo $post['post_id']; ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star" data-rating="<?php echo $i; ?>" onclick="ratePost(<?php echo $post['post_id']; ?>, <?php echo $i; ?>)" onmouseover="hoverStars(<?php echo $post['post_id']; ?>, <?php echo $i; ?>)" onmouseout="unhoverStars(<?php echo $post['post_id']; ?>)">
                            <i class="fas fa-star"></i>
                        </span>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-info" id="rating-info-<?php echo $post['post_id']; ?>">
                        <span class="avg">0.0</span> <span style="color: var(--text-muted);">• 0 ratings</span>
                    </div>
                </div>
                
                <!-- Comments Section -->
                <div class="comments-section" id="comments-<?php echo $post['post_id']; ?>" style="display: none;">
                    <div class="comments-list" id="comments-list-<?php echo $post['post_id']; ?>">
                        <div style="text-align: center; padding: 20px; color: var(--text-muted);">
                            <i class="fas fa-spinner fa-spin"></i> Loading comments...
                        </div>
                    </div>
                    <div class="comment-form">
                        <div style="width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; flex-shrink: 0;">
                            <?php echo strtoupper(substr($username, 0, 2)); ?>
                        </div>
                        <input type="text" class="comment-input" id="comment-input-<?php echo $post['post_id']; ?>" placeholder="Write a comment..." onkeypress="if(event.key==='Enter')addComment(<?php echo $post['post_id']; ?>)">
                        <button class="comment-submit" onclick="addComment(<?php echo $post['post_id']; ?>)">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 30px;">
                <?php if ($page > 1): ?>
                <a href="?page=<?php echo $page - 1; ?>" class="btn btn--glass btn--small"><i class="fas fa-chevron-left"></i> Previous</a>
                <?php endif; ?>
                <span style="display: flex; align-items: center; color: var(--text-muted); padding: 0 20px;">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                <?php if ($page < $totalPages): ?>
                <a href="?page=<?php echo $page + 1; ?>" class="btn btn--glass btn--small">Next <i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Share Modal -->
<div class="share-modal" id="shareModal">
    <div class="share-modal-content">
        <div class="share-modal-header">
            <h3><i class="fas fa-share"></i> Share Post</h3>
            <button class="share-modal-close" onclick="closeShareModal()">&times;</button>
        </div>
        <div class="share-modal-body">
            <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600;">
                    <?php echo strtoupper(substr($username, 0, 2)); ?>
                </div>
                <div>
                    <div style="font-weight: 600;"><?php echo htmlspecialchars($username); ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-globe-asia"></i> Public</div>
                </div>
            </div>
            <textarea id="shareText" class="share-textarea" rows="3" placeholder="Say something about this..."></textarea>
            <div class="share-preview" id="sharePreview">
                <!-- Preview content will be loaded here -->
            </div>
        </div>
        <div class="share-modal-footer">
            <button class="btn btn--primary btn--full" onclick="submitShare()">
                <i class="fas fa-share"></i> Share Now
            </button>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
let selectedFile = null;
let currentSharePostId = null;

// File preview
function previewFiles(input) {
    const preview = document.getElementById('filePreview');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        selectedFile = file;
        const fileType = file.type.split('/')[0];
        const fileExt = file.name.split('.').pop().toLowerCase();
        
        let iconClass = 'text', iconName = 'fa-file-alt';
        if (fileType === 'image') { iconClass = 'image'; iconName = 'fa-image'; }
        else if (fileType === 'video') { iconClass = 'video'; iconName = 'fa-video'; }
        else if (fileExt === 'pdf') { iconClass = 'pdf'; iconName = 'fa-file-pdf'; }
        
        let fileSize = file.size > 1024*1024 ? (file.size/(1024*1024)).toFixed(1)+' MB' : (file.size/1024).toFixed(1)+' KB';
        
        preview.style.display = 'block';
        preview.innerHTML = `
            <div class="file-preview-item">
                <div class="file-preview-icon ${iconClass}"><i class="fas ${iconName}"></i></div>
                <div class="file-preview-info">
                    <div class="file-preview-name">${file.name}</div>
                    <div class="file-preview-size">${fileSize}</div>
                </div>
                <button type="button" class="file-preview-remove" onclick="removeFile()"><i class="fas fa-times"></i></button>
            </div>
        `;
    }
}

function removeFile() {
    selectedFile = null;
    document.getElementById('filePreview').style.display = 'none';
    document.getElementById('postFile').value = '';
}

// YouTube link toggle
function toggleYoutubeInput() {
    const wrapper = document.getElementById('youtubeInputWrapper');
    wrapper.style.display = wrapper.style.display === 'none' ? 'flex' : 'none';
    if (wrapper.style.display === 'flex') {
        document.getElementById('youtubeLink').focus();
    }
}

function clearYoutubeLink() {
    document.getElementById('youtubeLink').value = '';
    document.getElementById('youtubeInputWrapper').style.display = 'none';
}

function createPost() {
    let content = document.getElementById('postContent').value.trim();
    const youtubeLink = document.getElementById('youtubeLink').value.trim();
    
    // Append YouTube link to content if provided
    if (youtubeLink) {
        content += (content ? '\n\n' : '') + youtubeLink;
    }
    
    if (!content && !selectedFile) {
        Toast.show('Please write something or add a file', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('body', content);
    if (selectedFile) formData.append('file', selectedFile);
    
    Toast.show('Posting...', 'info');
    
    fetch('api/create_post.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.show('Posted!', 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            Toast.show(data.message || 'Failed', 'error');
        }
    });
}

// Star Rating System
function ratePost(postId, rating) {
    fetch('api/rate_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&rating=${rating}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            updateStarDisplay(postId, data.user_rating, data.avg_rating, data.total_ratings);
            Toast.show(`Rated ${rating} star${rating > 1 ? 's' : ''}!`, 'success');
        } else {
            Toast.show(data.error || 'Failed to rate', 'error');
        }
    });
}

function hoverStars(postId, rating) {
    const container = document.getElementById(`star-rating-${postId}`);
    const stars = container.querySelectorAll('.star');
    stars.forEach((star, i) => {
        star.classList.toggle('hovered', i < rating);
    });
}

function unhoverStars(postId) {
    const container = document.getElementById(`star-rating-${postId}`);
    const stars = container.querySelectorAll('.star');
    stars.forEach(star => star.classList.remove('hovered'));
}

function updateStarDisplay(postId, userRating, avgRating, totalRatings) {
    const container = document.getElementById(`star-rating-${postId}`);
    const stars = container.querySelectorAll('.star');
    stars.forEach((star, i) => {
        star.classList.toggle('filled', i < userRating);
    });
    
    const info = document.getElementById(`rating-info-${postId}`);
    info.innerHTML = `<span class="avg">${avgRating || 0}</span> <span style="color: var(--text-muted);">• ${totalRatings || 0} rating${totalRatings !== 1 ? 's' : ''}</span>`;
}

function loadRating(postId) {
    fetch(`api/get_rating.php?post_id=${postId}`)
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            updateStarDisplay(postId, data.user_rating, data.avg_rating, data.total_ratings);
        }
    });
}

// Load ratings for all posts on page load
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.star-rating-container').forEach(container => {
        const postId = container.getAttribute('data-post-id');
        if (postId) loadRating(postId);
    });
});

// Reactions
function setReaction(postId, reaction) {
    fetch('api/toggle_reaction.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&reaction=${reaction}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            const btn = document.getElementById(`reaction-btn-${postId}`);
            const emojis = { 'like': '👍', 'love': '❤️', 'haha': '😂', 'sad': '😢', 'angry': '😡' };
            
            if (data.my_reaction) {
                btn.classList.add('reacted');
                btn.querySelector('.current-reaction').textContent = emojis[data.my_reaction];
                btn.querySelector('.reaction-text').textContent = data.my_reaction.charAt(0).toUpperCase() + data.my_reaction.slice(1);
            } else {
                btn.classList.remove('reacted');
                btn.querySelector('.current-reaction').textContent = '👍';
                btn.querySelector('.reaction-text').textContent = 'Like';
            }
        }
    });
}

// Comments
function toggleComments(postId) {
    const section = document.getElementById(`comments-${postId}`);
    if (section.style.display === 'none') {
        section.style.display = 'block';
        loadComments(postId);
        gsap.from(section, { opacity: 0, height: 0, duration: 0.3 });
    } else {
        gsap.to(section, { opacity: 0, duration: 0.2, onComplete: () => section.style.display = 'none' });
    }
}

// Render a single comment with its replies
function renderComment(c, postId, isReply = false) {
    const replyIndent = isReply ? 'margin-left: 45px;' : '';
    const avatarSize = isReply ? '30px' : '36px';
    const fontSize = isReply ? '0.85rem' : '0.9rem';
    
    let repliesHtml = '';
    if (c.replies && c.replies.length > 0) {
        repliesHtml = c.replies.map(r => renderComment(r, postId, true)).join('');
    }
    
    return `
        <div class="comment-item" style="${replyIndent}" data-comment-id="${c.id}">
            <div class="comment-avatar" style="width: ${avatarSize}; height: ${avatarSize}; font-size: ${isReply ? '0.65rem' : '0.75rem'};">${c.username.substring(0,2).toUpperCase()}</div>
            <div style="flex: 1;">
                <div class="comment-bubble">
                    <div class="name" style="font-size: ${fontSize};">${c.username}</div>
                    <div class="text" style="font-size: ${fontSize};">${c.content}</div>
                </div>
                <div class="comment-actions" style="display: flex; gap: 15px; margin-top: 5px; margin-left: 10px;">
                    <span class="time" style="font-size: 0.75rem; color: var(--text-muted);">${c.created_at}</span>
                    <button onclick="showReplyInput(${postId}, ${c.id}, '${c.username}')" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 0.75rem; font-weight: 600;">Reply</button>
                </div>
                <div class="reply-input-container" id="reply-input-${c.id}" style="display: none; margin-top: 10px; margin-left: 0;">
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <input type="text" class="comment-input" id="reply-text-${c.id}" placeholder="Write a reply..." style="flex: 1; padding: 8px 15px; font-size: 0.85rem;" onkeypress="if(event.key==='Enter')submitReply(${postId}, ${c.id})">
                        <button onclick="submitReply(${postId}, ${c.id})" style="background: var(--primary); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer;">
                            <i class="fas fa-paper-plane" style="font-size: 0.75rem;"></i>
                        </button>
                        <button onclick="hideReplyInput(${c.id})" style="background: rgba(255,255,255,0.1); border: none; color: var(--text-muted); width: 32px; height: 32px; border-radius: 50%; cursor: pointer;">
                            <i class="fas fa-times" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        ${repliesHtml}
    `;
}

function loadComments(postId) {
    fetch(`api/get_comments.php?post_id=${postId}`)
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById(`comments-list-${postId}`);
        if (data.ok && data.comments.length > 0) {
            list.innerHTML = data.comments.map(c => renderComment(c, postId)).join('');
        } else {
            list.innerHTML = '<p style="text-align: center; color: var(--text-muted); padding: 20px;">No comments yet. Be the first to comment!</p>';
        }
    })
    .catch(() => {
        document.getElementById(`comments-list-${postId}`).innerHTML = '<p style="text-align: center; color: var(--text-muted);">Failed to load comments</p>';
    });
}

function showReplyInput(postId, commentId, username) {
    // Hide all other reply inputs first
    document.querySelectorAll('.reply-input-container').forEach(el => el.style.display = 'none');
    
    const container = document.getElementById(`reply-input-${commentId}`);
    container.style.display = 'block';
    const input = document.getElementById(`reply-text-${commentId}`);
    input.placeholder = `Reply to ${username}...`;
    input.focus();
}

function hideReplyInput(commentId) {
    document.getElementById(`reply-input-${commentId}`).style.display = 'none';
}

function submitReply(postId, parentId) {
    const input = document.getElementById(`reply-text-${parentId}`);
    const content = input.value.trim();
    if (!content) return;
    
    fetch('api/add_comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&parent_id=${parentId}&content=${encodeURIComponent(content)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            input.value = '';
            hideReplyInput(parentId);
            loadComments(postId);
            Toast.show('Reply added!', 'success');
        } else {
            Toast.show(data.error || 'Failed to add reply', 'error');
        }
    });
}

function addComment(postId, parentId = null) {
    const input = document.getElementById(`comment-input-${postId}`);
    const content = input.value.trim();
    if (!content) return;
    
    let body = `post_id=${postId}&content=${encodeURIComponent(content)}`;
    if (parentId) body += `&parent_id=${parentId}`;
    
    fetch('api/add_comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body
    })
    .then(res => res.json())
    .then(data => {
        if (data.ok) {
            input.value = '';
            loadComments(postId);
            
            // Update count in UI
            const card = document.querySelector(`[data-post-id="${postId}"]`);
            const countSpan = card.querySelector('.comment-count');
            if (countSpan) countSpan.textContent = parseInt(countSpan.textContent || 0) + 1;
            
            Toast.show('Comment added!', 'success');
        } else {
            Toast.show(data.error || 'Failed to add comment', 'error');
        }
    });
}

// Share
function openShareModal(postId, username, text) {
    currentSharePostId = postId;
    document.getElementById('shareText').value = '';
    document.getElementById('sharePreview').innerHTML = `
        <div class="share-preview-header">
            <div class="share-preview-avatar">${username.substring(0,2).toUpperCase()}</div>
            <div class="share-preview-name">${username}</div>
        </div>
        <div class="share-preview-text">${text}${text.length >= 100 ? '...' : ''}</div>
    `;
    document.getElementById('shareModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeShareModal() {
    document.getElementById('shareModal').classList.remove('active');
    document.body.style.overflow = '';
    currentSharePostId = null;
}

function submitShare() {
    if (!currentSharePostId) return;
    
    const shareText = document.getElementById('shareText').value.trim();
    
    Toast.show('Sharing...', 'info');
    
    fetch('api/share_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${currentSharePostId}&share_text=${encodeURIComponent(shareText)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.show('Shared to your feed!', 'success');
            closeShareModal();
            setTimeout(() => location.reload(), 800);
        } else {
            Toast.show(data.message || 'Failed to share', 'error');
        }
    });
}

// Close modal on outside click
document.getElementById('shareModal').addEventListener('click', function(e) {
    if (e.target === this) closeShareModal();
});

// Animations
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#createPost', { y: 20, opacity: 0, duration: 0.5, delay: 0.2 });
    gsap.from('.post-card', { y: 30, opacity: 0, stagger: 0.1, delay: 0.3 });
}
</script>
