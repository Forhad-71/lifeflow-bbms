<?php
// stock_decrease.php - LifeFlow Decrease Blood Stock
require "includes/auth.php";
require_admin();
require "config.php";

$success = '';
$error = '';
$selectedType = $_GET['type'] ?? '';

// Get current stock
$currentStock = [];
$result = mysqli_query($conn, "SELECT blood_group, units FROM stock");
while ($row = mysqli_fetch_assoc($result)) {
    $currentStock[$row['blood_group']] = $row['units'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_type = $_POST['blood_type'] ?? '';
    $units = intval($_POST['units'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    
    if ($blood_type && $units > 0) {
        $available = $currentStock[$blood_type] ?? 0;
        
        if ($units > $available) {
            $error = "Cannot remove $units units. Only $available units available.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE stock SET units = units - ? WHERE blood_group = ?");
            mysqli_stmt_bind_param($stmt, "is", $units, $blood_type);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = "Removed $units units of $blood_type blood from inventory.";
                $currentStock[$blood_type] -= $units;
            } else {
                $error = 'Failed to update stock';
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error = 'Please select blood type and enter valid units';
    }
}

$pageTitle = "Decrease Stock - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content" style="max-width: 600px;">
        <div class="page-header text-center" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-minus-circle" style="color: var(--danger);"></i> Decrease Stock</h1>
            <p class="page-subtitle">Remove blood units from inventory</p>
        </div>
        
        <div class="card" id="stockForm">
            <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); border-radius: var(--radius-md); padding: 20px; margin-bottom: 25px; text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success); margin-bottom: 15px;"></i>
                <h3 style="color: var(--success);"><?php echo $success; ?></h3>
                <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
                    <a href="stock_decrease.php" class="btn btn--danger">Remove More</a>
                    <a href="details.php" class="btn btn--outline">View Inventory</a>
                </div>
            </div>
            <?php else: ?>
            
            <?php if ($error): ?>
            <div class="toast toast--error" style="position: relative; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label><i class="fas fa-tint"></i> Blood Type *</label>
                    <div class="blood-type-grid">
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type): 
                            $available = $currentStock[$type] ?? 0;
                        ?>
                        <label class="blood-radio" title="<?php echo $available; ?> units available">
                            <input type="radio" name="blood_type" value="<?php echo $type; ?>" <?php echo $selectedType === $type ? 'checked' : ''; ?> <?php echo $available <= 0 ? 'disabled' : ''; ?> required>
                            <span style="<?php echo $available <= 0 ? 'opacity: 0.5;' : ''; ?>">
                                <?php echo $type; ?>
                                <small style="display: block; font-size: 0.7rem; color: var(--text-muted);"><?php echo $available; ?> units</small>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-vial"></i> Units to Remove *</label>
                    <input type="number" name="units" placeholder="Enter number of units" min="1" max="100" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-notes-medical"></i> Reason *</label>
                    <select name="reason" required>
                        <option value="">Select reason</option>
                        <option value="transfusion">Patient Transfusion</option>
                        <option value="expired">Expired</option>
                        <option value="transfer">Transfer to other bank</option>
                        <option value="damaged">Damaged/Contaminated</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <a href="details.php" class="btn btn--glass btn--full">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn--danger btn--full">
                        <i class="fas fa-minus"></i> Remove from Stock
                    </button>
                </div>
            </form>
            <?php endif; ?>
        </div>
        
        <!-- Warning -->
        <div class="card" style="margin-top: 25px; background: rgba(245, 158, 11, 0.1); border: 1px solid var(--warning);">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--warning);"></i>
                <p style="margin: 0; color: var(--text-secondary);">
                    <strong style="color: var(--warning);">Important:</strong> Make sure to document the reason for stock reduction. This action cannot be undone.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#stockForm', { y: 50, opacity: 0, duration: 0.8, delay: 0.2 });
}
</script>
