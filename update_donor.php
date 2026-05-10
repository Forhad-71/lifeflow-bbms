<?php
// update_donor.php - LifeFlow Update Donor
require "includes/auth.php";
require_admin();
require "config.php";

$success = '';
$error = '';
$donor = null;

// Get donor ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id) {
    $result = mysqli_query($conn, "SELECT * FROM donor WHERE donor_id = $id");
    $donor = mysqli_fetch_assoc($result);
}

if (!$donor && !isset($_POST['id'])) {
    header('Location: all_donor.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $full_name = trim($_POST['full_name'] ?? '');
    $blood_group = $_POST['blood_group'] ?? '';
    $mobile_no = trim($_POST['mobile_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $mother_name = trim($_POST['mother_name'] ?? '');
    
    if ($full_name && $blood_group && $mobile_no) {
        $stmt = mysqli_prepare($conn, "UPDATE donor SET full_name=?, father_name=?, mother_name=?, dob=?, gender=?, mobile_no=?, email=?, blood_group=?, city=?, address=? WHERE donor_id=?");
        mysqli_stmt_bind_param($stmt, "ssssssssssi", $full_name, $father_name, $mother_name, $dob, $gender, $mobile_no, $email, $blood_group, $city, $address, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Donor updated successfully!';
            // Refresh donor data
            $result = mysqli_query($conn, "SELECT * FROM donor WHERE donor_id = $id");
            $donor = mysqli_fetch_assoc($result);
        } else {
            $error = 'Failed to update donor';
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = 'Please fill required fields';
    }
}

$pageTitle = "Update Donor - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-header" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-user-edit" style="color: var(--primary);"></i> Update Donor</h1>
            <p class="page-subtitle">Edit donor information</p>
        </div>
        
        <div class="form-container" id="donorForm" style="max-width: 700px;">
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
            
            <form method="POST" action="">
                <input type="hidden" name="id" value="<?php echo $donor['donor_id']; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name *</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($donor['full_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Mobile Number *</label>
                        <input type="tel" name="mobile_no" value="<?php echo htmlspecialchars($donor['mobile_no']); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-tint"></i> Blood Group *</label>
                    <div class="blood-type-grid">
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type): ?>
                        <label class="blood-radio">
                            <input type="radio" name="blood_group" value="<?php echo $type; ?>" <?php echo $donor['blood_group'] === $type ? 'checked' : ''; ?> required>
                            <span><?php echo $type; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Father's Name</label>
                        <input type="text" name="father_name" value="<?php echo htmlspecialchars($donor['father_name'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Mother's Name</label>
                        <input type="text" name="mother_name" value="<?php echo htmlspecialchars($donor['mother_name'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Date of Birth</label>
                        <input type="date" name="dob" value="<?php echo $donor['dob'] ?? ''; ?>" class="datepicker">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male" <?php echo ($donor['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo ($donor['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo ($donor['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($donor['email'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-city"></i> City</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($donor['city'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea name="address" rows="3" style="width: 100%; padding: 14px 18px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius-md); color: white; font-family: inherit; resize: vertical;"><?php echo htmlspecialchars($donor['address'] ?? ''); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <a href="all_donor.php" class="btn btn--glass btn--full">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn--primary btn--full">
                        <i class="fas fa-save"></i> Update Donor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#donorForm', { y: 50, opacity: 0, duration: 0.8, delay: 0.2 });
}
</script>
