<?php
// admin_signup.php - LifeFlow Admin Registration with Real OTP
session_start();

$pageTitle = "Admin Registration - LifeFlow";
include 'includes/header.php';

// Get Firebase config
$firebaseConfig = require 'config/firebase.php';
?>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.0/firebase-auth-compat.js"></script>

<div class="page-wrapper">
    <div class="hero__bg">
        <div class="floating-cells">
            <div class="cell cell--1"></div>
            <div class="cell cell--2"></div>
            <div class="cell cell--3"></div>
        </div>
    </div>
    
    <div style="min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center; padding: 40px 20px;">
        <div class="form-container" id="signupForm" style="max-width: 550px;">
            <div class="form-header">
                <div style="width: 80px; height: 80px; margin: 0 auto 20px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-shield" style="font-size: 2rem; color: white;"></i>
                </div>
                <h2>Admin Registration</h2>
                <p>Create an admin account to manage the system</p>
            </div>
            
            <!-- Step Indicator -->
            <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 30px;">
                <div class="step-dot active" data-step="1" style="width: 12px; height: 12px; border-radius: 50%; background: var(--primary);"></div>
                <div class="step-dot" data-step="2" style="width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.2);"></div>
                <div class="step-dot" data-step="3" style="width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.2);"></div>
            </div>
            
            <!-- Step 1: Admin Info -->
            <div id="step1" class="form-step">
                <h4 style="color: var(--primary); margin-bottom: 20px; text-align: center;">
                    <i class="fas fa-user-cog"></i> Admin Information
                </h4>
                
                <div class="form-group">
                    <label>Admin Username</label>
                    <input type="text" id="username" placeholder="Choose admin username" minlength="3" required>
                    <span class="validation-msg" id="usernameMsg"></span>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" id="email" placeholder="admin@email.com" required>
                    <span class="validation-msg" id="emailMsg"></span>
                </div>
                
                <div class="form-group">
                    <label>Phone Number (with country code)</label>
                    <input type="tel" id="phone" placeholder="+8801XXXXXXXXX" required>
                    <span class="validation-msg" id="phoneMsg"></span>
                    <small style="color: var(--text-muted);">Example: +8801712345678</small>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="password-input">
                        <input type="password" id="password" placeholder="Create strong password" required>
                        <button type="button" class="toggle-password">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div id="passwordStrength" style="height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-top: 8px;">
                        <div id="strengthBar" style="height: 100%; width: 0%; transition: all 0.3s;"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <div class="password-input">
                        <input type="password" id="confirmPassword" placeholder="Confirm password" required>
                        <button type="button" class="toggle-password">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <span class="validation-msg" id="confirmMsg"></span>
                </div>
                
                <button type="button" class="btn btn--primary btn--full btn--large" onclick="goToStep(2)">
                    Next <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            
            <!-- Step 2: Email OTP Verification -->
            <div id="step2" class="form-step" style="display: none;">
                <h4 style="color: var(--primary); margin-bottom: 20px; text-align: center;">
                    <i class="fas fa-envelope"></i> Email Verification
                </h4>
                
                <div style="background: rgba(255,255,255,0.05); border-radius: var(--radius-md); padding: 20px; margin-bottom: 20px; text-align: center;">
                    <p style="color: var(--text-muted); margin-bottom: 15px;">
                        We'll send a verification code to: <strong id="displayEmail" style="color: var(--primary);"></strong>
                    </p>
                    <button type="button" class="btn btn--primary" onclick="sendEmailOTP()" id="sendEmailBtn">
                        <i class="fas fa-paper-plane"></i> Send OTP
                    </button>
                </div>
                
                <div id="emailOtpSection" style="display: none;">
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Enter Email OTP</label>
                        <input type="text" id="emailOtp" placeholder="6-digit code" maxlength="6" style="text-align: center; font-size: 1.5rem; letter-spacing: 8px;">
                    </div>
                    
                    <button type="button" class="btn btn--success btn--full" onclick="verifyEmailOTP()" id="verifyEmailBtn">
                        <i class="fas fa-check"></i> Verify Email
                    </button>
                    
                    <p style="text-align: center; margin-top: 15px;">
                        <a href="javascript:void(0)" onclick="sendEmailOTP()" style="color: var(--primary);">Resend OTP</a>
                    </p>
                </div>
                
                <div id="emailVerified" style="display: none; text-align: center; padding: 20px;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success);"></i>
                    <p style="color: var(--success); margin-top: 10px;">Email Verified!</p>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="button" class="btn btn--glass btn--full" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn btn--primary btn--full" onclick="goToStep(3)" id="toStep3Btn" disabled>
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Phone OTP Verification -->
            <div id="step3" class="form-step" style="display: none;">
                <h4 style="color: var(--primary); margin-bottom: 20px; text-align: center;">
                    <i class="fas fa-phone"></i> Phone Verification
                </h4>
                
                <div style="background: rgba(255,255,255,0.05); border-radius: var(--radius-md); padding: 20px; margin-bottom: 20px; text-align: center;">
                    <p style="color: var(--text-muted); margin-bottom: 15px;">
                        We'll send a verification code to: <strong id="displayPhone" style="color: var(--primary);"></strong>
                    </p>
                    
                    <!-- reCAPTCHA container -->
                    <div id="recaptcha-container"></div>
                    
                    <button type="button" class="btn btn--primary" onclick="sendPhoneOTP()" id="sendPhoneBtn" style="margin-top: 15px;">
                        <i class="fas fa-sms"></i> Send SMS OTP
                    </button>
                </div>
                
                <div id="phoneOtpSection" style="display: none;">
                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Enter Phone OTP</label>
                        <input type="text" id="phoneOtp" placeholder="6-digit code" maxlength="6" style="text-align: center; font-size: 1.5rem; letter-spacing: 8px;">
                    </div>
                    
                    <button type="button" class="btn btn--success btn--full" onclick="verifyPhoneOTP()" id="verifyPhoneBtn">
                        <i class="fas fa-check"></i> Verify Phone
                    </button>
                </div>
                
                <div id="phoneVerified" style="display: none; text-align: center; padding: 20px;">
                    <i class="fas fa-check-circle" style="font-size: 3rem; color: var(--success);"></i>
                    <p style="color: var(--success); margin-top: 10px;">Phone Verified!</p>
                </div>
                
                <div style="display: flex; gap: 15px; margin-top: 20px;">
                    <button type="button" class="btn btn--glass btn--full" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left"></i> Back
                    </button>
                    <button type="button" class="btn btn--primary btn--full" onclick="createAdmin()" id="createAdminBtn" disabled>
                        <i class="fas fa-user-plus"></i> Create Admin
                    </button>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <p style="color: var(--text-muted);">Already an admin? <a href="admin_login.php" style="color: var(--primary);">Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
// Firebase Configuration
const firebaseConfig = {
    apiKey: "<?php echo $firebaseConfig['apiKey']; ?>",
    authDomain: "<?php echo $firebaseConfig['authDomain']; ?>",
    projectId: "<?php echo $firebaseConfig['projectId']; ?>",
    storageBucket: "<?php echo $firebaseConfig['storageBucket']; ?>",
    messagingSenderId: "<?php echo $firebaseConfig['messagingSenderId']; ?>",
    appId: "<?php echo $firebaseConfig['appId']; ?>"
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();

let adminData = {};
let confirmationResult = null;
let emailVerified = false;
let phoneVerified = false;

// Username check
let debounceTimer;
document.getElementById('username').addEventListener('input', function() {
    clearTimeout(debounceTimer);
    const username = this.value.trim();
    const msgEl = document.getElementById('usernameMsg');
    
    if (username.length < 3) {
        msgEl.textContent = 'Username must be at least 3 characters';
        msgEl.className = 'validation-msg error';
        return;
    }
    
    debounceTimer = setTimeout(() => {
        fetch(`api/check_username.php?username=${encodeURIComponent(username)}&role=admin`)
            .then(res => res.json())
            .then(data => {
                msgEl.textContent = data.available ? '✓ Available' : '✗ Already taken';
                msgEl.className = 'validation-msg ' + (data.available ? 'success' : 'error');
            });
    }, 500);
});

// Password strength
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strengthBar');
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (/[a-z]/.test(password)) strength += 25;
    if (/[A-Z]/.test(password)) strength += 25;
    if (/[0-9!@#$%^&*]/.test(password)) strength += 25;
    
    strengthBar.style.width = strength + '%';
    strengthBar.style.background = strength <= 25 ? 'var(--danger)' : 
                                    strength <= 50 ? 'var(--warning)' : 
                                    strength <= 75 ? 'var(--info)' : 'var(--success)';
});

// Confirm password
document.getElementById('confirmPassword').addEventListener('input', function() {
    const msgEl = document.getElementById('confirmMsg');
    if (document.getElementById('password').value === this.value) {
        msgEl.textContent = '✓ Passwords match';
        msgEl.className = 'validation-msg success';
    } else {
        msgEl.textContent = 'Passwords do not match';
        msgEl.className = 'validation-msg error';
    }
});

function goToStep(step) {
    // Validation for step 1
    if (step === 2) {
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value;
        
        if (!username || username.length < 3) {
            Toast.show('Please enter a valid username', 'error');
            return;
        }
        if (!email || !email.includes('@')) {
            Toast.show('Please enter a valid email', 'error');
            return;
        }
        if (!phone || !phone.startsWith('+')) {
            Toast.show('Phone must start with + (e.g., +8801712345678)', 'error');
            return;
        }
        if (password !== document.getElementById('confirmPassword').value) {
            Toast.show('Passwords do not match', 'error');
            return;
        }
        
        adminData = { username, email, phone, password };
        document.getElementById('displayEmail').textContent = email;
        document.getElementById('displayPhone').textContent = phone;
    }
    
    // Show step
    document.querySelectorAll('.form-step').forEach(s => s.style.display = 'none');
    document.querySelectorAll('.step-dot').forEach(d => d.style.background = 'rgba(255,255,255,0.2)');
    document.getElementById('step' + step).style.display = 'block';
    document.querySelector(`.step-dot[data-step="${step}"]`).style.background = 'var(--primary)';
    gsap.from('#step' + step, { x: 30, opacity: 0, duration: 0.4 });
}

// ==================== EMAIL OTP ====================
function sendEmailOTP() {
    const btn = document.getElementById('sendEmailBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    fetch('api/send_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: adminData.email, type: 'signup' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.show('OTP sent to your email!', 'success');
            document.getElementById('emailOtpSection').style.display = 'block';
            btn.style.display = 'none';
        } else {
            Toast.show(data.message || 'Failed to send OTP', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send OTP';
        }
    })
    .catch(err => {
        Toast.show('Error sending OTP', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Send OTP';
    });
}

function verifyEmailOTP() {
    const otp = document.getElementById('emailOtp').value.trim();
    if (otp.length !== 6) {
        Toast.show('Please enter 6-digit OTP', 'error');
        return;
    }
    
    const btn = document.getElementById('verifyEmailBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    
    fetch('api/verify_email_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ otp: otp, email: adminData.email })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.show('Email verified!', 'success');
            emailVerified = true;
            document.getElementById('emailOtpSection').style.display = 'none';
            document.getElementById('emailVerified').style.display = 'block';
            document.getElementById('toStep3Btn').disabled = false;
        } else {
            Toast.show(data.message || 'Invalid OTP', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Verify Email';
        }
    })
    .catch(err => {
        Toast.show('Verification error', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Verify Email';
    });
}

// ==================== PHONE OTP (Firebase) ====================
let recaptchaRendered = false;

function sendPhoneOTP() {
    const btn = document.getElementById('sendPhoneBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    // Clear existing reCAPTCHA if any
    if (window.recaptchaVerifier) {
        try {
            window.recaptchaVerifier.clear();
        } catch(e) {}
        window.recaptchaVerifier = null;
    }
    
    // Clear the container
    document.getElementById('recaptcha-container').innerHTML = '';
    
    // Create new reCAPTCHA
    window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
        'size': 'invisible',
        'callback': (response) => {
            // reCAPTCHA solved
        },
        'expired-callback': () => {
            Toast.show('reCAPTCHA expired. Please try again.', 'warning');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-sms"></i> Send SMS OTP';
        }
    });
    
    const phoneNumber = adminData.phone;
    
    window.recaptchaVerifier.render().then(() => {
        return auth.signInWithPhoneNumber(phoneNumber, window.recaptchaVerifier);
    })
    .then((result) => {
        confirmationResult = result;
        Toast.show('SMS OTP sent!', 'success');
        document.getElementById('phoneOtpSection').style.display = 'block';
        btn.style.display = 'none';
    })
    .catch((error) => {
        console.error('SMS Error:', error);
        Toast.show('Failed to send SMS: ' + error.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sms"></i> Send SMS OTP';
        
        // Clear reCAPTCHA on error
        if (window.recaptchaVerifier) {
            try {
                window.recaptchaVerifier.clear();
            } catch(e) {}
            window.recaptchaVerifier = null;
        }
        document.getElementById('recaptcha-container').innerHTML = '';
    });
}

function verifyPhoneOTP() {
    const otp = document.getElementById('phoneOtp').value.trim();
    if (otp.length !== 6) {
        Toast.show('Please enter 6-digit OTP', 'error');
        return;
    }
    
    const btn = document.getElementById('verifyPhoneBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    
    confirmationResult.confirm(otp)
        .then((result) => {
            Toast.show('Phone verified!', 'success');
            phoneVerified = true;
            document.getElementById('phoneOtpSection').style.display = 'none';
            document.getElementById('phoneVerified').style.display = 'block';
            document.getElementById('createAdminBtn').disabled = false;
        })
        .catch((error) => {
            Toast.show('Invalid OTP', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Verify Phone';
        });
}

// ==================== CREATE ADMIN ====================
function createAdmin() {
    if (!emailVerified || !phoneVerified) {
        Toast.show('Please verify both email and phone', 'error');
        return;
    }
    
    const btn = document.getElementById('createAdminBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    
    fetch('api/create_admin_direct.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(adminData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Toast.show('Admin account created!', 'success');
            setTimeout(() => window.location.href = 'admin_login.php', 1500);
        } else {
            Toast.show(data.message || 'Failed to create admin', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user-plus"></i> Create Admin';
        }
    })
    .catch(err => {
        Toast.show('Error creating admin', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-user-plus"></i> Create Admin';
    });
}

function playEntranceAnimations() {
    gsap.from('#signupForm', { y: 50, opacity: 0, duration: 0.8 });
}
</script>
