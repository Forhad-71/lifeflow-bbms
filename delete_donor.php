<?php
// delete_donor.php - LifeFlow Delete Donor
require "includes/auth.php";
require_admin();
require "config.php";

$success = '';
$error = '';

// Handle GET request (from all_donor.php link)
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "DELETE FROM donor WHERE donor_id = $id";
    if (mysqli_query($conn, $sql)) {
        header('Location: all_donor.php?deleted=1');
        exit;
    } else {
        $error = "Failed to delete donor.";
    }
}

// Handle POST request (from form)
if (isset($_POST['delete'])) {
    $id = intval($_POST['donor_id']);
    
    $sql = "DELETE FROM donor WHERE donor_id = $id";
    if (mysqli_query($conn, $sql)) {
        $success = "Donor deleted successfully!";
    } else {
        $error = "Failed to delete donor.";
    }
}

// Find donor by ID
$donor = null;
if (isset($_POST['find'])) {
    $id = intval($_POST['donor_id']);
    $result = mysqli_query($conn, "SELECT * FROM donor WHERE donor_id = $id");
    if (mysqli_num_rows($result) == 1) {
        $donor = mysqli_fetch_assoc($result);
    } else {
        $error = "Donor not found.";
    }
}

$pageTitle = "Delete Donor - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content" style="max-width: 600px;">
        <div class="page-header" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-user-minus" style="color: var(--danger);"></i> Delete Donor</h1>
            <p class="page-subtitle">Search and remove a donor from the system</p>
        </div>
        
        <div class="card" id="deleteForm">
            <?php if ($success): ?>
            <div class="toast toast--success" style="position: relative; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success; ?></span>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="toast toast--error" style="position: relative; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Search Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label><i class="fas fa-id-badge"></i> Enter Donor ID</label>
                    <input type="number" name="donor_id" placeholder="e.g., 1, 2, 3..." required>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <a href="all_donor.php" class="btn btn--glass btn--full">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <button type="submit" name="find" class="btn btn--primary btn--full">
                        <i class="fas fa-search"></i> Find Donor
                    </button>
                </div>
            </form>
            
            <?php if ($donor): ?>
            <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 25px 0;">
            
            <h3 style="margin-bottom: 15px; color: var(--success);"><i class="fas fa-user-check"></i> Donor Found</h3>
            
            <div style="background: rgba(255,255,255,0.03); border-radius: var(--radius-md); padding: 20px; margin-bottom: 20px;">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    <div>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 3px;">Name</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($donor['full_name']); ?></p>
                    </div>
                    <div>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 3px;">Blood Group</p>
                        <p><span class="badge badge--danger"><?php echo htmlspecialchars($donor['blood_group']); ?></span></p>
                    </div>
                    <div>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 3px;">Mobile</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($donor['mobile_no']); ?></p>
                    </div>
                    <div>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 3px;">City</p>
                        <p style="font-weight: 600;"><?php echo htmlspecialchars($donor['city'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="donor_id" value="<?php echo $donor['donor_id']; ?>">
                
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); border-radius: var(--radius-md); padding: 15px; margin-bottom: 20px;">
                    <p style="color: var(--danger); margin: 0;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action cannot be undone. The donor record will be permanently deleted.
                    </p>
                </div>
                
                <button type="submit" name="delete" class="btn btn--danger btn--full" onclick="return confirm('Are you absolutely sure you want to delete this donor?');">
                    <i class="fas fa-trash"></i> Confirm Delete
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#deleteForm', { y: 50, opacity: 0, duration: 0.8, delay: 0.2 });
}
</script>
