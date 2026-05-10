<?php
// request_blood.php - LifeFlow Blood Request Form
session_start();

// Allow both logged in users and guests
$isLoggedIn = isset($_SESSION['username']);

require "config.php";

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $blood_group = $_POST['blood_group'] ?? '';
    $units_needed = intval($_POST['units_needed'] ?? 1);
    $mobile = trim($_POST['mobile'] ?? '');
    
    if ($name && $blood_group && $mobile) {
        $stmt = mysqli_prepare($conn, "INSERT INTO request (name, blood_group, units_needed, mobile, date) VALUES (?, ?, ?, ?, CURDATE())");
        mysqli_stmt_bind_param($stmt, "ssis", $name, $blood_group, $units_needed, $mobile);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = 'Blood request submitted successfully! We will contact you soon.';
        } else {
            $error = 'Failed to submit request. Please try again.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = 'Please fill all required fields';
    }
}

$pageTitle = "Request Blood - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content" style="max-width: 700px;">
        <div class="page-header text-center" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-tint" style="color: var(--primary);"></i> Request Blood</h1>
            <p class="page-subtitle">Fill out the form below to request blood. We'll match you with available donors.</p>
        </div>
        
        <div class="card" id="requestForm">
            <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); border-radius: var(--radius-md); padding: 20px; margin-bottom: 25px; text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success); margin-bottom: 15px;"></i>
                <h3 style="color: var(--success); margin-bottom: 10px;">Request Submitted!</h3>
                <p style="color: var(--text-secondary);"><?php echo $success; ?></p>
                <a href="<?php echo $isLoggedIn ? 'user_home.php' : 'index.php'; ?>" class="btn btn--success" style="margin-top: 15px;">
                    <i class="fas fa-home"></i> Go to Home
                </a>
            </div>
            <?php else: ?>
            
            <?php if ($error): ?>
            <div class="toast toast--error" style="position: relative; margin-bottom: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <!-- Patient Name -->
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Patient Name *</label>
                    <input type="text" name="name" placeholder="Full name of the patient" required>
                </div>
                
                <!-- Blood Group -->
                <div class="form-group">
                    <label><i class="fas fa-tint"></i> Required Blood Group *</label>
                    <div class="blood-type-grid">
                        <label class="blood-radio"><input type="radio" name="blood_group" value="A+" required><span>A+</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_group" value="A-"><span>A-</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_group" value="B+"><span>B+</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_group" value="B-"><span>B-</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_group" value="AB+"><span>AB+</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_group" value="AB-"><span>AB-</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_group" value="O+"><span>O+</span></label>
                        <label class="blood-radio"><input type="radio" name="blood_group" value="O-"><span>O-</span></label>
                    </div>
                </div>
                
                <!-- Units -->
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-vial"></i> Units Needed</label>
                        <select name="units_needed">
                            <option value="1">1 Unit</option>
                            <option value="2">2 Units</option>
                            <option value="3">3 Units</option>
                            <option value="4">4 Units</option>
                            <option value="5">5+ Units</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Mobile Number *</label>
                        <input type="tel" name="mobile" placeholder="+880 1XXX-XXXXXX" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn--primary btn--full btn--large">
                    <i class="fas fa-paper-plane"></i> Submit Request
                </button>
            </form>
            <?php endif; ?>
        </div>
        
        <!-- Info -->
        <div class="card" style="margin-top: 25px; background: linear-gradient(135deg, rgba(196, 30, 58, 0.1), rgba(255, 107, 107, 0.05));">
            <div style="display: flex; align-items: center; gap: 15px;">
                <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--primary);"></i>
                <p style="margin: 0; color: var(--text-secondary);">
                    After submitting, our team will verify availability and contact you. For emergencies, please also call your nearest hospital directly.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#requestForm', { y: 50, opacity: 0, duration: 0.8, delay: 0.2 });
}
</script>
