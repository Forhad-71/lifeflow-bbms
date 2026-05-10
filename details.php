<?php
// details.php - LifeFlow Stock Details
require "includes/auth.php";
require_admin();
require "config.php";

// Get all blood stock
$stocks = [];
$result = mysqli_query($conn, "SELECT * FROM stock ORDER BY blood_group");
while ($row = mysqli_fetch_assoc($result)) {
    $stocks[] = $row;
}

// Get total units
$totalResult = mysqli_query($conn, "SELECT SUM(units) as total FROM stock");
$totalUnits = mysqli_fetch_assoc($totalResult)['total'] ?? 0;

$pageTitle = "Blood Stock Details - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-header" id="pageHeader" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 class="page-title"><i class="fas fa-boxes-stacked" style="color: var(--primary);"></i> Blood Stock Details</h1>
                <p class="page-subtitle">Total <?php echo number_format($totalUnits); ?> units available across all blood types</p>
            </div>
            <div style="display: flex; gap: 15px;">
                <a href="stock_increase.php" class="btn btn--success">
                    <i class="fas fa-plus"></i> Increase Stock
                </a>
                <a href="stock_decrease.php" class="btn btn--danger">
                    <i class="fas fa-minus"></i> Decrease Stock
                </a>
            </div>
        </div>
        
        <!-- Stock Overview Cards -->
        <div class="stock-grid" id="stockGrid" style="margin-bottom: 40px;">
            <?php
            $bloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            $stockMap = [];
            foreach ($stocks as $s) {
                $stockMap[$s['blood_group']] = $s['units'];
            }
            
            foreach ($bloodTypes as $type):
                $units = $stockMap[$type] ?? 0;
                $status = $units > 50 ? 'high' : ($units > 20 ? 'medium' : 'low');
                $statusText = $units > 50 ? 'Well Stocked' : ($units > 20 ? 'Moderate' : 'Critical!');
            ?>
            <div class="stock-card">
                <div class="stock-card__type"><?php echo $type; ?></div>
                <div class="stock-card__units"><?php echo number_format($units); ?></div>
                <div class="stock-card__label">units available</div>
                <span class="stock-card__status stock-card__status--<?php echo $status; ?>">
                    <?php if ($status === 'low'): ?><i class="fas fa-exclamation-triangle"></i><?php endif; ?>
                    <?php echo $statusText; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Stock Table -->
        <div class="card" id="stockTable">
            <div class="card__header">
                <h3 class="card__title"><i class="fas fa-table" style="color: var(--primary);"></i> Detailed View</h3>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Blood Type</th>
                            <th>Units Available</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bloodTypes as $type): 
                            $units = $stockMap[$type] ?? 0;
                            $status = $units > 50 ? 'success' : ($units > 20 ? 'warning' : 'danger');
                            $statusText = $units > 50 ? 'Sufficient' : ($units > 20 ? 'Moderate' : 'Low');
                        ?>
                        <tr>
                            <td>
                                <span style="font-family: 'Clash Display', sans-serif; font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                    <?php echo $type; ?>
                                </span>
                            </td>
                            <td>
                                <span style="font-size: 1.2rem; font-weight: 600;"><?php echo number_format($units); ?></span>
                                <span style="color: var(--text-muted);"> units</span>
                            </td>
                            <td>
                                <span class="badge badge--<?php echo $status; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td style="color: var(--text-muted);">
                                <?php echo date('M d, Y'); ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="stock_increase.php?type=<?php echo urlencode($type); ?>" class="btn btn--small btn--success" title="Add">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                    <a href="stock_decrease.php?type=<?php echo urlencode($type); ?>" class="btn btn--small btn--danger" title="Remove">
                                        <i class="fas fa-minus"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Info Card -->
        <div class="card" style="margin-top: 30px; background: linear-gradient(135deg, rgba(196, 30, 58, 0.1), rgba(255, 107, 107, 0.05));">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 60px; height: 60px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-info" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <div>
                    <h4 style="margin-bottom: 5px;">Blood Stock Guidelines</h4>
                    <p style="color: var(--text-secondary); margin: 0;">
                        <strong style="color: var(--success);">Green (>50 units):</strong> Well stocked | 
                        <strong style="color: var(--warning);">Yellow (20-50 units):</strong> Order more soon | 
                        <strong style="color: var(--danger);">Red (<20 units):</strong> Critical - urgent restocking needed
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#stockGrid .stock-card', { y: 30, opacity: 0, duration: 0.5, stagger: 0.1, delay: 0.2 });
    gsap.from('#stockTable', { y: 50, opacity: 0, duration: 0.8, delay: 0.5 });
}
</script>
