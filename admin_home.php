<?php
// admin_home.php - LifeFlow Admin Dashboard
require "includes/auth.php";
require_admin();
require "config.php";

// Get statistics
$stats = [];

// Total donors
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM donor");
$stats['donors'] = mysqli_fetch_assoc($result)['count'] ?? 0;

// Total users
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM users");
$stats['users'] = mysqli_fetch_assoc($result)['count'] ?? 0;

// Pending requests (all requests since no status column)
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM request");
$stats['pending_requests'] = mysqli_fetch_assoc($result)['count'] ?? 0;

// Total blood stock
$result = mysqli_query($conn, "SELECT SUM(units) as total FROM stock");
$stats['total_stock'] = mysqli_fetch_assoc($result)['total'] ?? 0;

// Get blood stock by type
$stockByType = [];
$result = mysqli_query($conn, "SELECT blood_group, units FROM stock ORDER BY blood_group");
while ($row = mysqli_fetch_assoc($result)) {
    $stockByType[$row['blood_group']] = $row['units'];
}

$pageTitle = "Admin Dashboard - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="dashboard">
        <!-- Dashboard Header -->
        <div class="dashboard__header" id="dashHeader">
            <p class="dashboard__welcome">Welcome back,</p>
            <h1 class="dashboard__title"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?> 👋</h1>
            <p class="dashboard__subtitle">Here's what's happening with your blood bank today.</p>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid" id="statsGrid">
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card__content">
                    <h3><?php echo number_format($stats['donors']); ?></h3>
                    <p>Total Donors</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--success">
                    <i class="fas fa-tint"></i>
                </div>
                <div class="stat-card__content">
                    <h3><?php echo number_format($stats['total_stock']); ?></h3>
                    <p>Blood Units</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--warning">
                    <i class="fas fa-hand-holding-medical"></i>
                </div>
                <div class="stat-card__content">
                    <h3><?php echo number_format($stats['pending_requests']); ?></h3>
                    <p>Total Requests</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-card__icon stat-card__icon--info">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-card__content">
                    <h3><?php echo number_format($stats['users']); ?></h3>
                    <p>Registered Users</p>
                </div>
            </div>
        </div>
        
        <!-- Blood Stock Section -->
        <div class="card" style="margin-bottom: 30px;" id="stockSection">
            <div class="card__header">
                <h3 class="card__title"><i class="fas fa-boxes-stacked" style="color: var(--primary);"></i> Blood Inventory</h3>
                <a href="details.php" class="btn btn--outline btn--small">View Details</a>
            </div>
            
            <div class="stock-grid">
                <?php
                $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                foreach ($bloodTypes as $type):
                    $units = $stockByType[$type] ?? 0;
                    $status = $units > 50 ? 'high' : ($units > 20 ? 'medium' : 'low');
                    $statusText = $units > 50 ? 'Well Stocked' : ($units > 20 ? 'Moderate' : 'Low Stock');
                ?>
                <div class="stock-card">
                    <div class="stock-card__type"><?php echo $type; ?></div>
                    <div class="stock-card__units"><?php echo $units; ?></div>
                    <div class="stock-card__label">units available</div>
                    <span class="stock-card__status stock-card__status--<?php echo $status; ?>">
                        <?php echo $statusText; ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Quick Actions & Video -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Quick Actions -->
            <div class="card" id="quickActions">
                <div class="card__header">
                    <h3 class="card__title"><i class="fas fa-bolt" style="color: var(--primary);"></i> Quick Actions</h3>
                </div>
                
                <div style="display: grid; gap: 15px;">
                    <a href="add_donor.php" class="btn btn--primary btn--full" style="justify-content: flex-start;">
                        <i class="fas fa-user-plus"></i> Add New Donor
                    </a>
                    <a href="stock_increase.php" class="btn btn--success btn--full" style="justify-content: flex-start;">
                        <i class="fas fa-plus-circle"></i> Increase Stock
                    </a>
                    <a href="view_requests.php" class="btn btn--outline btn--full" style="justify-content: flex-start;">
                        <i class="fas fa-hand-holding-medical"></i> View Blood Requests
                    </a>
                    <a href="search_donor.php" class="btn btn--glass btn--full" style="justify-content: flex-start;">
                        <i class="fas fa-search"></i> Search Donors
                    </a>
                    <a href="all_donor.php" class="btn btn--glass btn--full" style="justify-content: flex-start;">
                        <i class="fas fa-list"></i> All Donors List
                    </a>
                </div>
            </div>
            
            <!-- Educational Video -->
            <div class="card" id="videoCard">
                <div class="card__header">
                    <h3 class="card__title"><i class="fas fa-video" style="color: var(--primary);"></i> Why Blood Donation Matters</h3>
                </div>
                
                <div class="video-wrapper" style="margin: 0;">
                    <iframe
                        src="https://www.youtube.com/embed/ezafVzfJw60?rel=0"
                        title="Why You Should Donate Blood"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 15px; text-align: center;">
                    <i class="fas fa-info-circle"></i> Every donation can save up to 3 lives
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    const tl = gsap.timeline();
    
    tl.from('#dashHeader', { y: 30, opacity: 0, duration: 0.6, ease: 'power3.out' })
      .from('#statsGrid .stat-card', { y: 30, opacity: 0, duration: 0.5, stagger: 0.1, ease: 'power3.out' }, '-=0.3')
      .from('#stockSection', { y: 30, opacity: 0, duration: 0.6, ease: 'power3.out' }, '-=0.2')
      .from('#quickActions', { x: -30, opacity: 0, duration: 0.5, ease: 'power3.out' }, '-=0.3')
      .from('#videoCard', { x: 30, opacity: 0, duration: 0.5, ease: 'power3.out' }, '-=0.5');
}
</script>
