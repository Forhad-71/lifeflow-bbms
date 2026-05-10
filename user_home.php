<?php
// user_home.php - LifeFlow User Dashboard
require "includes/auth.php";
require_user();
require "config.php";

$username = $_SESSION['username'] ?? 'User';

// Get blood stock summary
$stocks = [];
$result = mysqli_query($conn, "SELECT blood_group, units FROM stock ORDER BY blood_group");
while ($row = mysqli_fetch_assoc($result)) {
    $stocks[$row['blood_group']] = $row['units'];
}

// Get recent requests (no username in request table)
$myRequests = [];
$result = mysqli_query($conn, "SELECT * FROM request ORDER BY date DESC LIMIT 3");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $myRequests[] = $row;
    }
}

$pageTitle = "Dashboard - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="dashboard">
        <!-- Welcome Header -->
        <div class="dashboard__header" id="dashHeader">
            <p class="dashboard__welcome">Welcome back,</p>
            <h1 class="dashboard__title"><?php echo htmlspecialchars($username); ?> 👋</h1>
            <p class="dashboard__subtitle">Thank you for being part of our donor community!</p>
        </div>
        
        <!-- Quick Actions -->
        <div class="stats-grid" id="quickActions">
            <a href="request_blood.php" class="stat-card" style="cursor: pointer; text-decoration: none;">
                <div class="stat-card__icon stat-card__icon--primary">
                    <i class="fas fa-tint"></i>
                </div>
                <div class="stat-card__content">
                    <h3>Request Blood</h3>
                    <p>Submit a new request</p>
                </div>
            </a>
            
            <a href="eligibility.php" class="stat-card" style="cursor: pointer; text-decoration: none;">
                <div class="stat-card__icon stat-card__icon--success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card__content">
                    <h3>Eligibility</h3>
                    <p>Check if you can donate</p>
                </div>
            </a>
            
            <a href="community.php" class="stat-card" style="cursor: pointer; text-decoration: none;">
                <div class="stat-card__icon stat-card__icon--info">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card__content">
                    <h3>Community</h3>
                    <p>Connect with others</p>
                </div>
            </a>
            
            <a href="profile.php" class="stat-card" style="cursor: pointer; text-decoration: none;">
                <div class="stat-card__icon stat-card__icon--warning">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div class="stat-card__content">
                    <h3>Profile</h3>
                    <p>Manage your account</p>
                </div>
            </a>
        </div>
        
        <!-- Blood Availability -->
        <div class="card" style="margin-bottom: 30px;" id="stockSection">
            <div class="card__header">
                <h3 class="card__title"><i class="fas fa-tint" style="color: var(--primary);"></i> Blood Availability</h3>
            </div>
            
            <div class="stock-grid">
                <?php
                $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                foreach ($bloodTypes as $type):
                    $units = $stocks[$type] ?? 0;
                    $status = $units > 50 ? 'high' : ($units > 20 ? 'medium' : 'low');
                ?>
                <div class="stock-card" style="padding: 20px;">
                    <div class="stock-card__type" style="font-size: 2rem;"><?php echo $type; ?></div>
                    <div class="stock-card__units" style="font-size: 1.2rem;"><?php echo $units; ?></div>
                    <div class="stock-card__label">units</div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- My Requests -->
            <div class="card" id="myRequests">
                <div class="card__header">
                    <h3 class="card__title"><i class="fas fa-hand-holding-medical" style="color: var(--primary);"></i> My Requests</h3>
                    <a href="my_requests.php" class="btn btn--outline btn--small">View All</a>
                </div>
                
                <?php if (empty($myRequests)): ?>
                <p style="color: var(--text-muted); text-align: center; padding: 30px;">
                    You haven't made any blood requests yet.
                </p>
                <?php else: ?>
                <div style="display: grid; gap: 15px;">
                    <?php foreach ($myRequests as $req): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 15px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                <?php echo htmlspecialchars($req['blood_group']); ?>
                            </div>
                            <div>
                                <p style="margin: 0; font-weight: 500;"><?php echo htmlspecialchars($req['name']); ?></p>
                                <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">
                                    <?php echo date('M d, Y', strtotime($req['date'])); ?>
                                </p>
                            </div>
                        </div>
                        <span class="badge badge--warning">
                            <?php echo $req['units_needed']; ?> units
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Educational Video -->
            <div class="card" id="videoCard">
                <div class="card__header">
                    <h3 class="card__title"><i class="fas fa-video" style="color: var(--primary);"></i> Learn About Donation</h3>
                </div>
                
                <div class="video-wrapper" style="margin: 0;">
                    <iframe
                        src="https://www.youtube.com/embed/ezafVzfJw60?rel=0"
                        title="Blood Donation Video"
                        allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    const tl = gsap.timeline();
    tl.from('#dashHeader', { y: 30, opacity: 0, duration: 0.6 })
      .from('#quickActions .stat-card', { y: 30, opacity: 0, duration: 0.5, stagger: 0.1 }, '-=0.3')
      .from('#stockSection', { y: 30, opacity: 0, duration: 0.6 }, '-=0.2')
      .from('#myRequests', { x: -30, opacity: 0, duration: 0.5 }, '-=0.3')
      .from('#videoCard', { x: 30, opacity: 0, duration: 0.5 }, '-=0.5');
}
</script>
