<?php
// user_forgot_password.php - LifeFlow User Password Reset
session_start();
require "config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - LifeFlow</title>
    <link rel="stylesheet" href="assets/css/lifeflow.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Forgot Password</h1>
                <p>Reset your account password</p>
            </div>
            
            <!-- Step Indicators -->
            <div class="steps-indicator" style="display: flex; justify-content: center; gap: 10px; margin-bottom: 30px;">
                <span class="step-dot active" id="dot1"></span>
                <span class="step-dot" id="dot2"></span>
                <span class="step-dot" id="dot3"></span>
            </div>
            
            <!-- Step 1: Enter Email -->
            <div id="step1">
                <form id="findAccountForm">
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" id="email" class="form-input" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" class="btn btn--primary btn--full">
                        <i class="fas fa-search"></i> Find Account
                    </button>
                </form>
                <p style="text-align: center; margin-top: 20px; color: var(--text-muted);">
                    Remember your password? <a href="user_login.php" style="color: var(--primary);">Login</a>
                </p>
            </div>
            
            <!-- Step 2: Verify OTP -->
            <div id="step2" style="display: none;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <p style="color: var(--text-muted);">We sent an OTP to</p>
                    <p style="color: var(--primary); font-weight: 600;" id="maskedEmail"></p>
                </div>
                <form id="verifyOtpForm">
                    <div class="form-group">
                        <label class="form-label">Enter OTP</label>
                        <input type="text" id="otp" class="form-input" placeholder="6-digit OTP" maxlength="6" required style="text-align: center; font-size: 1.5rem; letter-spacing: 8px;">
                    </div>
                    <button type="submit" class="btn btn--primary btn--full">
                        <i class="fas fa-check"></i> Verify OTP
                    </button>
                </form>
                <p style="text-align: center; margin-top: 15px;">
                    <button onclick="resendOTP()" class="btn btn--ghost btn--small" id="resendBtn">
                        <i class="fas fa-redo"></i> Resend OTP
                    </button>
                </p>
            </div>
            
            <!-- Step 3: New Password -->
            <div id="step3" style="display: none;">
                <form id="resetPasswordForm">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" id="newPassword" class="form-input" placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" id="confirmPassword" class="form-input" placeholder="Confirm new password" required>
                    </div>
                    <button type="submit" class="btn btn--primary btn--full">
                        <i class="fas fa-save"></i> Reset Password
                    </button>
                </form>
            </div>
            
            <!-- Success Message -->
            <div id="successStep" style="display: none; text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <i class="fas fa-check" style="font-size: 2rem; color: white;"></i>
                </div>
                <h2>Password Reset Successful!</h2>
                <p style="color: var(--text-muted); margin-bottom: 20px;">You can now login with your new password.</p>
                <a href="user_login.php" class="btn btn--primary">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    let resetData = {
        username: '',
        email: ''
    };
    
    // Step 1: Find Account by Email
    document.getElementById('findAccountForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value.trim();
        
        const btn = this.querySelector('button');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
        
        try {
            const res = await fetch('api/find_user_account.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            });
            const data = await res.json();
            
            if (data.success) {
                resetData.username = data.username;
                resetData.email = email;
                
                // Mask email for display
                const parts = email.split('@');
                const name = parts[0];
                const domain = parts[1];
                const maskedName = name.substring(0, 2) + '*'.repeat(Math.max(0, name.length - 2));
                document.getElementById('maskedEmail').textContent = maskedName + '@' + domain;
                
                // Send OTP
                await sendOTP(email);
                
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
                document.getElementById('dot1').classList.remove('active');
                document.getElementById('dot2').classList.add('active');
            } else {
                Toast.show(data.message || 'Account not found', 'error');
            }
        } catch (err) {
            Toast.show('Error finding account', 'error');
        }
        
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-search"></i> Find Account';
    });
    
    // Send OTP function
    async function sendOTP(email) {
        const res = await fetch('api/send_otp.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, type: 'reset' })
        });
        const data = await res.json();
        if (data.success) {
            Toast.show('OTP sent to your email!', 'success');
        } else {
            Toast.show(data.message || 'Failed to send OTP', 'error');
        }
    }
    
    // Resend OTP
    async function resendOTP() {
        const btn = document.getElementById('resendBtn');
        btn.disabled = true;
        await sendOTP(resetData.email);
        
        let seconds = 60;
        const interval = setInterval(() => {
            btn.innerHTML = `<i class="fas fa-clock"></i> Resend in ${seconds}s`;
            seconds--;
            if (seconds < 0) {
                clearInterval(interval);
                btn.innerHTML = '<i class="fas fa-redo"></i> Resend OTP';
                btn.disabled = false;
            }
        }, 1000);
    }
    
    // Step 2: Verify OTP
    document.getElementById('verifyOtpForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const otp = document.getElementById('otp').value.trim();
        
        const btn = this.querySelector('button');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        
        try {
            const res = await fetch('api/verify_email_otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ otp })
            });
            const data = await res.json();
            
            if (data.success) {
                document.getElementById('step2').style.display = 'none';
                document.getElementById('step3').style.display = 'block';
                document.getElementById('dot2').classList.remove('active');
                document.getElementById('dot3').classList.add('active');
                Toast.show('OTP verified!', 'success');
            } else {
                Toast.show(data.message || 'Invalid OTP', 'error');
            }
        } catch (err) {
            Toast.show('Error verifying OTP', 'error');
        }
        
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Verify OTP';
    });
    
    // Step 3: Reset Password
    document.getElementById('resetPasswordForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (newPassword !== confirmPassword) {
            Toast.show('Passwords do not match!', 'error');
            return;
        }
        
        if (newPassword.length < 6) {
            Toast.show('Password must be at least 6 characters', 'error');
            return;
        }
        
        const btn = this.querySelector('button');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
        
        try {
            const res = await fetch('api/reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    username: resetData.username,
                    password: newPassword,
                    type: 'user'
                })
            });
            const data = await res.json();
            
            if (data.success) {
                document.getElementById('step3').style.display = 'none';
                document.getElementById('successStep').style.display = 'block';
                document.querySelectorAll('.step-dot').forEach(d => d.classList.remove('active'));
            } else {
                Toast.show(data.message || 'Failed to reset password', 'error');
            }
        } catch (err) {
            Toast.show('Error resetting password', 'error');
        }
        
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Reset Password';
    });
    </script>
    
    <style>
    .step-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        transition: all 0.3s ease;
    }
    .step-dot.active {
        background: var(--primary);
        box-shadow: 0 0 10px var(--primary);
    }
    </style>
</body>
</html>
