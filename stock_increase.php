<?php
// stock_increase.php - LifeFlow Increase Blood Stock
require "includes/auth.php";
require_admin();
require "config.php";

$success = '';
$error = '';
$selectedType = $_GET['type'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $blood_type = $_POST['blood_type'] ?? '';
    $units = intval($_POST['units'] ?? 0);
    $source = trim($_POST['source'] ?? '');
    
    if ($blood_type && $units > 0) {
        // Check if blood type exists
        $check = mysqli_query($conn, "SELECT units FROM stock WHERE blood_group = '$blood_type'");
        
        if (mysqli_num_rows($check) > 0) {
            // Update existing
            $stmt = mysqli_prepare($conn, "UPDATE stock SET units = units + ? WHERE blood_group = ?");
            mysqli_stmt_bind_param($stmt, "is", $units, $blood_type);
        } else {
            // Insert new
            $stmt = mysqli_prepare($conn, "INSERT INTO stock (blood_group, units) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "si", $blood_type, $units);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Added $units units of $blood_type blood to inventory!";
        } else {
            $error = 'Failed to update stock';
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = 'Please select blood type and enter valid units';
    }
}

$pageTitle = "Increase Stock - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content" style="max-width: 600px;">
        <div class="page-header text-center" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-plus-circle" style="color: var(--success);"></i> Increase Stock</h1>
            <p class="page-subtitle">Add blood units to inventory</p>
        </div>
        
        <div class="card" id="stockForm">
            <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); border-radius: var(--radius-md); padding: 20px; margin-bottom: 25px; text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success); margin-bottom: 15px;"></i>
                <h3 style="color: var(--success);"><?php echo $success; ?></h3>
                <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
                    <a href="stock_increase.php" class="btn btn--success">Add More</a>
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
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type): ?>
                        <label class="blood-radio">
                            <input type="radio" name="blood_type" value="<?php echo $type; ?>" <?php echo $selectedType === $type ? 'checked' : ''; ?> required>
                            <span><?php echo $type; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-vial"></i> Units to Add *</label>
                    <input type="number" name="units" placeholder="Enter number of units" min="1" max="100" required>
                    <span class="hint">Each unit is approximately 450ml</span>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-notes-medical"></i> Source (Optional)</label>
                    <select name="source">
                        <option value="">Select source</option>
                        <option value="donation">Direct Donation</option>
                        <option value="blood_drive">Blood Drive</option>
                        <option value="transfer">Transfer from other bank</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <a href="details.php" class="btn btn--glass btn--full">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" class="btn btn--success btn--full">
                        <i class="fas fa-plus"></i> Add to Stock
                    </button>
                </div>
            </form>
            <?php endif; ?>
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
