<?php
// add_donor.php - LifeFlow Add New Donor
require "includes/auth.php";
require_admin();
require "config.php";

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['name'] ?? '');
    $blood_group = $_POST['blood_type'] ?? '';
    $mobile_no = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $city = trim($_POST['city'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $mother_name = trim($_POST['mother_name'] ?? '');
    
    if ($full_name && $blood_group && $mobile_no) {
        $stmt = mysqli_prepare($conn, "INSERT INTO donor (full_name, father_name, mother_name, dob, gender, mobile_no, email, blood_group, city, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssssssssss", $full_name, $father_name, $mother_name, $dob, $gender, $mobile_no, $email, $blood_group, $city, $address);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Donor added successfully!';
        } else {
            $error = 'Failed to add donor';
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = 'Please fill required fields';
    }
}

$pageTitle = "Add Donor - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content">
        <div class="page-header" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-user-plus" style="color: var(--primary);"></i> Add New Donor</h1>
            <p class="page-subtitle">Register a new blood donor to the system</p>
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
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name *</label>
                        <input type="text" name="name" placeholder="Enter donor's full name" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Mobile Number *</label>
                        <input type="tel" name="phone" placeholder="+880 1XXX-XXXXXX" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-tint"></i> Blood Group *</label>
                    <div class="blood-type-grid">
                        <label class="blood-radio"><input type="radio" name="blood_type" value="A+" required><span>A+</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_type" value="A-"><span>A-</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_type" value="B+"><span>B+</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_type" value="B-"><span>B-</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_type" value="AB+"><span>AB+</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_type" value="AB-"><span>AB-</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_type" value="O+"><span>O+</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_type" value="O-"><span>O-</span></label>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Father's Name</label>
                        <input type="text" name="father_name" placeholder="Father's name">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Mother's Name</label>
                        <input type="text" name="mother_name" placeholder="Mother's name">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Date of Birth</label>
                        <input type="date" name="dob" class="datepicker">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" placeholder="donor@email.com">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-city"></i> City</label>
                        <input type="text" name="city" placeholder="City name">
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Address</label>
                    <textarea name="address" rows="3" placeholder="Enter full address" style="width: 100%; padding: 14px 18px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius-md); color: white; font-family: inherit; resize: vertical;"></textarea>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 25px;">
                    <a href="all_donor.php" class="btn btn--glass btn--full">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn--primary btn--full">
                        <i class="fas fa-save"></i> Save Donor
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
