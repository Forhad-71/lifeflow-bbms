<?php
// profile.php - LifeFlow User Profile
session_start();
require "config.php";

// Check if logged in
if (!isset($_SESSION['username'])) {
    header("Location: user_login.php");
    exit;
}

$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'user';
$isAdmin = ($role === 'admin');

// Get user/admin data
if ($isAdmin) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE username = ?");
} else {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
}
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    header("Location: logout.php");
    exit;
}

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if ($isAdmin) {
            $stmt = mysqli_prepare($conn, "UPDATE admins SET email = ?, phone = ? WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "sss", $email, $phone, $username);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET email = ?, phone = ? WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "sss", $email, $phone, $username);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Profile updated successfully!';
            // Refresh user data
            $user['email'] = $email;
            $user['phone'] = $phone;
        } else {
            $error = 'Failed to update profile.';
        }
        mysqli_stmt_close($stmt);
    }
    
    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            if ($isAdmin) {
                $stmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE username = ?");
            } else {
                $stmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE username = ?");
            }
            mysqli_stmt_bind_param($stmt, "ss", $hashedPassword, $username);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$pageTitle = "My Profile - LifeFlow";
include 'includes/header.php';
?>

<style>
.profile-container {
    max-width: 900px;
    margin: 0 auto;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 30px;
    margin-bottom: 40px;
    padding: 30px;
    background: var(--card-bg);
    border-radius: var(--radius-xl);
    border: 1px solid rgba(255,255,255,0.1);
}

.profile-avatar {
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 10px 30px rgba(196, 30, 58, 0.3);
}

.profile-info h1 {
    margin: 0 0 5px 0;
    font-size: 2rem;
}

.profile-info .role-badge {
    display: inline-block;
    padding: 5px 15px;
    background: <?php echo $isAdmin ? 'linear-gradient(135deg, #f59e0b, #d97706)' : 'linear-gradient(135deg, var(--primary), var(--accent))'; ?>;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.profile-info .member-since {
    color: var(--text-muted);
    font-size: 0.9rem;
}

.profile-stats {
    display: flex;
    gap: 30px;
    margin-top: 15px;
}

.stat-item {
    text-align: center;
}

.stat-item .value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.stat-item .label {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.profile-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 15px;
}

.tab-btn {
    padding: 12px 25px;
    background: transparent;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    border-radius: var(--radius-md);
    transition: all 0.3s ease;
    font-size: 1rem;
}

.tab-btn:hover {
    color: white;
    background: rgba(255,255,255,0.05);
}

.tab-btn.active {
    color: white;
    background: var(--primary);
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.info-card {
    background: rgba(255,255,255,0.03);
    border-radius: var(--radius-lg);
    padding: 20px;
    border: 1px solid rgba(255,255,255,0.05);
}

.info-card__label {
    color: var(--text-muted);
    font-size: 0.85rem;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-card__value {
    font-size: 1.1rem;
    font-weight: 500;
}

.verification-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    margin-left: 10px;
}

.verification-badge.verified {
    background: rgba(16, 185, 129, 0.2);
    color: #10b981;
}

.verification-badge.unverified {
    background: rgba(239, 68, 68, 0.2);
    color: #ef4444;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: rgba(255,255,255,0.03);
    border-radius: var(--radius-md);
    margin-bottom: 10px;
}

.activity-icon {
    width: 45px;
    height: 45px;
    background: rgba(196, 30, 58, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
}

.activity-info h4 {
    margin: 0 0 5px 0;
    font-size: 0.95rem;
}

.activity-info p {
    margin: 0;
    color: var(--text-muted);
    font-size: 0.85rem;
}

.danger-zone {
    border: 1px solid rgba(239, 68, 68, 0.3);
    background: rgba(239, 68, 68, 0.05);
}

.danger-zone h3 {
    color: #ef4444;
}
</style>

<div class="page-wrapper">
    <div class="page-content">
        <div class="profile-container">
            
            <!-- Success/Error Messages -->
            <?php if ($success): ?>
            <div class="toast toast--success" style="position: relative; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="toast toast--error" style="position: relative; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Profile Header -->
            <div class="profile-header" id="profileHeader">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($username, 0, 2)); ?>
                </div>
                <div class="profile-info">
                    <span class="role-badge">
                        <i class="fas fa-<?php echo $isAdmin ? 'shield-alt' : 'user'; ?>"></i>
                        <?php echo $isAdmin ? 'Administrator' : 'Member'; ?>
                    </span>
                    <h1><?php echo htmlspecialchars($username); ?></h1>
                    <p class="member-since">
                        <i class="fas fa-calendar-alt"></i> 
                        Member since <?php echo date('F Y', strtotime($user['created_at'] ?? 'now')); ?>
                    </p>
                    
                    <?php if (!$isAdmin): ?>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="value"><?php echo $user['blood_group'] ?? 'N/A'; ?></div>
                            <div class="label">Blood Type</div>
                        </div>
                        <div class="stat-item">
                            <div class="value">
                                <?php 
                                $reqCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM request WHERE mobile = '{$user['phone']}'"));
                                echo $reqCount['c'] ?? 0;
                                ?>
                            </div>
                            <div class="label">Requests</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="profile-tabs" id="profileTabs">
                <button class="tab-btn active" onclick="showTab('overview')">
                    <i class="fas fa-user"></i> Overview
                </button>
                <button class="tab-btn" onclick="showTab('edit')">
                    <i class="fas fa-edit"></i> Edit Profile
                </button>
                <button class="tab-btn" onclick="showTab('security')">
                    <i class="fas fa-lock"></i> Security
                </button>
                <button class="tab-btn" onclick="showTab('activity')">
                    <i class="fas fa-history"></i> Activity
                </button>
            </div>
            
            <!-- Overview Tab -->
            <div class="tab-content active" id="tab-overview">
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title"><i class="fas fa-info-circle" style="color: var(--primary);"></i> Account Information</h3>
                    </div>
                    
                    <div class="form-grid">
                        <div class="info-card">
                            <div class="info-card__label"><i class="fas fa-user"></i> Username</div>
                            <div class="info-card__value"><?php echo htmlspecialchars($username); ?></div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-card__label"><i class="fas fa-envelope"></i> Email</div>
                            <div class="info-card__value">
                                <?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?>
                                <?php if (isset($user['email_verified'])): ?>
                                <span class="verification-badge <?php echo $user['email_verified'] ? 'verified' : 'unverified'; ?>">
                                    <i class="fas fa-<?php echo $user['email_verified'] ? 'check' : 'times'; ?>"></i>
                                    <?php echo $user['email_verified'] ? 'Verified' : 'Not Verified'; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-card__label"><i class="fas fa-phone"></i> Phone</div>
                            <div class="info-card__value">
                                <?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?>
                                <?php if (isset($user['phone_verified'])): ?>
                                <span class="verification-badge <?php echo $user['phone_verified'] ? 'verified' : 'unverified'; ?>">
                                    <i class="fas fa-<?php echo $user['phone_verified'] ? 'check' : 'times'; ?>"></i>
                                    <?php echo $user['phone_verified'] ? 'Verified' : 'Not Verified'; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-card__label"><i class="fas fa-shield-alt"></i> Account Type</div>
                            <div class="info-card__value"><?php echo $isAdmin ? 'Administrator' : 'Regular User'; ?></div>
                        </div>
                        
                        <?php if (!$isAdmin && isset($user['blood_group'])): ?>
                        <div class="info-card">
                            <div class="info-card__label"><i class="fas fa-tint"></i> Blood Group</div>
                            <div class="info-card__value" style="color: var(--primary); font-weight: 700; font-size: 1.3rem;">
                                <?php echo htmlspecialchars($user['blood_group'] ?? 'Not set'); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-card">
                            <div class="info-card__label"><i class="fas fa-calendar"></i> Joined</div>
                            <div class="info-card__value"><?php echo date('d M Y, h:i A', strtotime($user['created_at'] ?? 'now')); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profile Tab -->
            <div class="tab-content" id="tab-edit">
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title"><i class="fas fa-edit" style="color: var(--primary);"></i> Edit Profile</h3>
                    </div>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Username</label>
                                <input type="text" value="<?php echo htmlspecialchars($username); ?>" disabled style="opacity: 0.6;">
                                <small style="color: var(--text-muted);">Username cannot be changed</small>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> Email Address</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Enter email">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> Phone Number</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Enter phone number">
                            </div>
                        </div>
                        
                        <div style="margin-top: 25px;">
                            <button type="submit" class="btn btn--primary btn--large">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Security Tab -->
            <div class="tab-content" id="tab-security">
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title"><i class="fas fa-lock" style="color: var(--primary);"></i> Change Password</h3>
                    </div>
                    
                    <form method="POST" action="" id="passwordForm">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label><i class="fas fa-key"></i> Current Password</label>
                            <div class="password-input">
                                <input type="password" name="current_password" required placeholder="Enter current password">
                                <button type="button" class="toggle-password">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> New Password</label>
                                <div class="password-input">
                                    <input type="password" name="new_password" required placeholder="Enter new password" minlength="6">
                                    <button type="button" class="toggle-password">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-lock"></i> Confirm New Password</label>
                                <div class="password-input">
                                    <input type="password" name="confirm_password" required placeholder="Confirm new password">
                                    <button type="button" class="toggle-password">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 25px;">
                            <button type="submit" class="btn btn--primary btn--large">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Danger Zone -->
                <div class="card danger-zone" style="margin-top: 30px;">
                    <div class="card__header">
                        <h3 class="card__title"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                    </div>
                    <p style="color: var(--text-muted); margin-bottom: 20px;">
                        Once you delete your account, there is no going back. Please be certain.
                    </p>
                    <button class="btn btn--danger" onclick="confirmDeleteAccount()">
                        <i class="fas fa-trash"></i> Delete Account
                    </button>
                </div>
            </div>
            
            <!-- Activity Tab -->
            <div class="tab-content" id="tab-activity">
                <div class="card">
                    <div class="card__header">
                        <h3 class="card__title"><i class="fas fa-history" style="color: var(--primary);"></i> Recent Activity</h3>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <div class="activity-info">
                            <h4>Logged in</h4>
                            <p>Current session started</p>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="activity-info">
                            <h4>Account created</h4>
                            <p><?php echo date('d M Y, h:i A', strtotime($user['created_at'] ?? 'now')); ?></p>
                        </div>
                    </div>
                    
                    <?php if (!$isAdmin): ?>
                    <?php
                    $requests = mysqli_query($conn, "SELECT * FROM request WHERE mobile = '{$user['phone']}' ORDER BY date DESC LIMIT 5");
                    while ($req = mysqli_fetch_assoc($requests)):
                    ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-tint"></i>
                        </div>
                        <div class="activity-info">
                            <h4>Blood request: <?php echo htmlspecialchars($req['blood_group']); ?></h4>
                            <p><?php echo date('d M Y', strtotime($req['date'])); ?> - <?php echo $req['units_needed']; ?> units</p>
                        </div>
                    </div>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Tab switching
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    
    // Show selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
    
    // Animation
    gsap.from('#tab-' + tabName, { opacity: 0, y: 20, duration: 0.3 });
}

// Delete account confirmation
function confirmDeleteAccount() {
    if (confirm('Are you sure you want to delete your account? This action cannot be undone!')) {
        if (confirm('This is your final warning. All your data will be permanently deleted. Continue?')) {
            window.location.href = 'api/delete_account.php';
        }
    }
}

// Animations
function playEntranceAnimations() {
    gsap.from('#profileHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#profileTabs', { y: 20, opacity: 0, duration: 0.5, delay: 0.2 });
    gsap.from('.tab-content.active', { y: 20, opacity: 0, duration: 0.5, delay: 0.3 });
}

// Password form validation
document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
    const newPass = this.querySelector('[name="new_password"]').value;
    const confirmPass = this.querySelector('[name="confirm_password"]').value;
    
    if (newPass !== confirmPass) {
        e.preventDefault();
        Toast.show('Passwords do not match!', 'error');
    }
});
</script>
