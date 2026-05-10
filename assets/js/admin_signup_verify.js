document.addEventListener("DOMContentLoaded", function() {
    const btnContinue = document.getElementById('btnContinue');
    const btnBack = document.getElementById('btnBack');
    const btnSendEmailOtp = document.getElementById('btnSendEmailOtp');
    const btnSendPhoneOtp = document.getElementById('btnSendPhoneOtp');
    const btnVerifyOtps = document.getElementById('btnVerifyOtps');

    const msgBox = document.getElementById('msg');
    const usernameInput = document.getElementById('username');
    const usernameStatus = document.getElementById('usernameStatus');

    let pendingId = 0;

    // 1. Real-time Username Check (Using your existing API)
    usernameInput.addEventListener('input', function() {
        let uname = this.value.trim();
        if (uname.length < 3) {
            usernameStatus.innerHTML = "<span style='color:red;'>Username must be at least 3 chars.</span>";
            return;
        }
        fetch('api/check_username.php?role=admin&username=' + encodeURIComponent(uname))
            .then(r => r.json())
            .then(data => {
                if (data.available) {
                    usernameStatus.innerHTML = `<span style='color:green;'><b>${data.message}</b></span>`;
                } else {
                    usernameStatus.innerHTML = `<span style='color:red;'><b>${data.message}</b></span>`;
                }
            }).catch(() => {});
    });

    // 2. Continue to Step 2 (Creates the pending user)
    btnContinue.addEventListener('click', function() {
        let payload = {
            role: 'admin',
            username: usernameInput.value.trim(),
            email: document.getElementById('email').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            password: document.getElementById('password').value,
            cpassword: document.getElementById('cpassword').value
        };

        fetch('api/create_pending.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                pendingId = data.pending_id; // Store the ID for the OTP steps!
                msgBox.style.display = 'none';
                document.getElementById('step1').style.display = 'none';
                document.getElementById('step2').style.display = 'block';
            } else {
                showError(data.message);
            }
        })
        .catch(e => showError("Server error. Check console."));
    });

    // 3. Back Button
    btnBack.addEventListener('click', function() {
        document.getElementById('step2').style.display = 'none';
        document.getElementById('step1').style.display = 'block';
    });

    // 4. Send Email OTP
    btnSendEmailOtp.addEventListener('click', function() {
        fetch('api/send_otp.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ pending_id: pendingId, type: 'email' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Force the div to be visible just in case
                let demoDiv = document.getElementById('emailDemo');
                 demoDiv.innerHTML = "Demo OTP (Email): <b style='color:blue;'>" + data.otp + "</b> (expires in " + data.expires_in_sec + "s)";               
                 demoDiv.style.display = "block"; 
                
                // AUTO-FILL the input box!
                document.getElementById('emailOtp').value = data.otp;
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => alert("Network Error: " + err.message));
    });

    // 5. Send Phone OTP
    btnSendPhoneOtp.addEventListener('click', function() {
        fetch('api/send_otp.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ pending_id: pendingId, type: 'phone' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Force the div to be visible just in case
                let demoDiv = document.getElementById('phoneDemo');
                demoDiv.innerHTML = "Demo OTP (Phone): <b style='color:blue;'>" + data.otp + "</b> (expires in " + data.expires_in_sec + "s)";
                demoDiv.style.display = "block";
                
                // AUTO-FILL the input box!
                document.getElementById('phoneOtp').value = data.otp;
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => alert("Network Error: " + err.message));
    });
  

    // 6. Final Verify & Complete Signup
    btnVerifyOtps.addEventListener('click', function() {
        let emailOtp = document.getElementById('emailOtp').value.trim();
        let phoneOtp = document.getElementById('phoneOtp').value.trim();

        if (!emailOtp || !phoneOtp) {
            alert("Please enter both OTP codes.");
            return;
        }

        fetch('api/verify_otps.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ pending_id: pendingId, email_otp: emailOtp, phone_otp: phoneOtp })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // IMPORTANT: We pass "role: 'admin'" so the backend knows where to put this user!
                fetch('api/complete_signup.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ pending_id: pendingId, role: 'admin' })
                })
                .then(r => r.json())
                .then(data2 => {
                    if (data2.success) {
                        document.getElementById('step2').style.display = 'none';
                        document.getElementById('step3').style.display = 'block';
                    } else {
                        alert("Database Error: " + data2.message);
                    }
                });
            } else {
                alert(data.message);
            }
        });
    });

    function showError(msg) {
        msgBox.style.display = 'block';
        msgBox.style.color = 'red';
        msgBox.innerText = msg;
    }
});