<?php
// includes/header.php - LifeFlow Header Component
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['username']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$username = $_SESSION['username'] ?? '';
$initials = $username ? strtoupper(substr($username, 0, 2)) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'LifeFlow - Blood Bank Management System'; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Clash+Display:wght@400;500;600;700&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- GSAP Animation Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.4/ScrollTrigger.min.js"></script>
    
    <!-- Flatpickr for Date Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <!-- LifeFlow CSS -->
    <link rel="stylesheet" href="assets/css/lifeflow.css">
</head>
<body>

<!-- Preloader -->
<div class="preloader" id="preloader">
    <div class="blood-drop">
        <svg viewBox="0 0 100 140" class="drop-svg">
            <path d="M50 0 C50 0 0 60 0 90 C0 120 22 140 50 140 C78 140 100 120 100 90 C100 60 50 0 50 0Z" fill="currentColor"/>
        </svg>
        <div class="pulse-ring"></div>
    </div>
    <p class="preloader__text">LifeFlow</p>
</div>

<!-- Navigation -->
<nav class="navbar" id="navbar">
    <div class="navbar__container">
        <!-- Logo -->
        <a href="<?php echo $isAdmin ? 'admin_home.php' : ($isLoggedIn ? 'user_home.php' : 'index.php'); ?>" class="navbar__logo">
            <div class="logo-icon">
                <svg viewBox="0 0 100 140" width="28" height="36">
                    <path d="M50 0 C50 0 0 60 0 90 C0 120 22 140 50 140 C78 140 100 120 100 90 C100 60 50 0 50 0Z" fill="currentColor"/>
                </svg>
            </div>
            <span>LifeFlow</span>
        </a>
        
        <?php if ($isLoggedIn): ?>
        <!-- Menu for Logged In Users -->
        <ul class="navbar__menu">
            <?php if ($isAdmin): ?>
            <!-- Admin Menu -->
            <li>
                <a href="#"><i class="fas fa-user-plus"></i> Donor ▾</a>
                <div class="dropdown-menu">
                    <a href="add_donor.php"><i class="fas fa-plus-circle"></i> Add New Donor</a>
                    <a href="update_donor.php"><i class="fas fa-edit"></i> Update Details</a>
                    <a href="all_donor.php"><i class="fas fa-list"></i> All Donors</a>
                </div>
            </li>
            <li>
                <a href="search_donor.php"><i class="fas fa-search"></i> Search</a>
            </li>
            <li>
                <a href="#"><i class="fas fa-boxes-stacked"></i> Stock ▾</a>
                <div class="dropdown-menu">
                    <a href="stock_increase.php"><i class="fas fa-plus"></i> Increase Stock</a>
                    <a href="stock_decrease.php"><i class="fas fa-minus"></i> Decrease Stock</a>
                    <a href="details.php"><i class="fas fa-chart-bar"></i> Stock Details</a>
                </div>
            </li>
            <li>
                <a href="view_requests.php"><i class="fas fa-hand-holding-medical"></i> Requests</a>
            </li>
            <?php else: ?>
            <!-- User Menu -->
            <li>
                <a href="find_blood_banks.php"><i class="fas fa-map-marked-alt"></i> Find Banks</a>
            </li>
            <li>
                <a href="community.php"><i class="fas fa-users"></i> Community</a>
            </li>
            <li>
                <a href="request_blood.php"><i class="fas fa-tint"></i> Request Blood</a>
            </li>
            <li>
                <a href="#"><i class="fas fa-check-circle"></i> Eligibility ▾</a>
                <div class="dropdown-menu">
                    <a href="eligibility.php"><i class="fas fa-clipboard-list"></i> Basic Check</a>
                    <a href="ai_eligibility.php"><i class="fas fa-brain"></i> AI Predictor</a>
                </div>
            </li>
            <?php endif; ?>
        </ul>
        
        <!-- User Actions -->
        <div class="navbar__actions">
            <!-- Theme Toggle -->
            <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                <div class="theme-toggle-slider">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </div>
            </button>
            
            <a href="profile.php" class="navbar__user" style="text-decoration: none; cursor: pointer;" title="My Profile">
                <div class="avatar"><?php echo $initials; ?></div>
                <span><?php echo htmlspecialchars($username); ?></span>
            </a>
            <a href="logout.php" class="btn btn--outline btn--small">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        <?php else: ?>
        <!-- Guest Menu -->
        <ul class="navbar__menu">
            <li><a href="find_blood_banks.php"><i class="fas fa-map-marked-alt"></i> Find Banks</a></li>
            <li>
                <a href="#"><i class="fas fa-check-circle"></i> Eligibility ▾</a>
                <div class="dropdown-menu">
                    <a href="eligibility.php"><i class="fas fa-clipboard-list"></i> Basic Check</a>
                    <a href="ai_eligibility.php"><i class="fas fa-brain"></i> AI Predictor</a>
                </div>
            </li>
            <li><a href="request_blood_guest.php"><i class="fas fa-tint"></i> Request Blood</a></li>
        </ul>
        <div class="navbar__actions">
            <!-- Theme Toggle -->
            <button class="theme-toggle" onclick="toggleTheme()" title="Toggle Theme">
                <div class="theme-toggle-slider">
                    <i class="fas fa-moon"></i>
                    <i class="fas fa-sun"></i>
                </div>
            </button>
            
            <a href="admin_login.php" class="btn btn--glass btn--small">Admin</a>
            <a href="user_login.php" class="btn btn--primary btn--small">Login / Register</a>
        </div>
        <?php endif; ?>
    </div>
</nav>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>
