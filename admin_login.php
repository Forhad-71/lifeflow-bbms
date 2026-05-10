<?php
// admin_login.php - LifeFlow Admin Login
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: admin_home.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'config.php';
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM admins WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = 'admin';
                header('Location: admin_home.php');
                exit;
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Admin not found';
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = 'Please fill all fields';
    }
}

$pageTitle = "Admin Login - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="hero__bg">
        <div class="floating-cells">
            <div class="cell cell--1"></div>
            <div class="cell cell--2"></div>
            <div class="cell cell--3"></div>
        </div>
    </div>
    
    <div style="min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div class="form-container" id="loginForm">
            <div class="form-header">
                <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-shield" style="font-size: 2rem; color: white;"></i>
                </div>
                <h2>Admin Portal</h2>
                <p>Login to manage the blood bank system</p>
            </div>
            
            <?php if ($error): ?>
            <div class="toast toast--error" style="position: relative; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" placeholder="Enter admin username" required 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <div class="password-input">
                        <input type="password" name="password" placeholder="Enter password" required>
                        <button type="button" class="toggle-password">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div style="text-align: right; margin-top: 8px;">
                        <a href="admin_forgot_password.php" style="color: var(--primary); font-size: 0.9rem;">
                            <i class="fas fa-key"></i> Forgot Password?
                        </a>
                    </div>
                </div>
                
                <button type="submit" class="btn btn--primary btn--full btn--large">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); text-align: center;">
                <p style="color: var(--text-secondary); margin-bottom: 15px;">New admin?</p>
                <a href="admin_signup.php" class="btn btn--outline btn--full">
                    <i class="fas fa-user-plus"></i> Create Admin Account
                </a>
            </div>
            
            <div style="margin-top: 20px; text-align: center;">
                <a href="user_login.php" style="color: var(--text-muted); font-size: 0.9rem;">
                    <i class="fas fa-arrow-left"></i> Go to User Portal
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#loginForm', {
        y: 50,
        opacity: 0,
        duration: 0.8,
        ease: 'power3.out'
    });
}
</script>
