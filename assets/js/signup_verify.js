(function () {
  const apiBase = 'api';

  const el = (id) => document.getElementById(id);

  const msgBox = el('msg');
  const step1 = el('step1');
  const step2 = el('step2');
  const step3 = el('step3');

  const username = el('username');
  const email = el('email');
  const phone = el('phone');
  const password = el('password');
  const cpassword = el('cpassword');

  const usernameStatus = el('usernameStatus');

  const btnContinue = el('btnContinue');
  const btnSendEmailOtp = el('btnSendEmailOtp');
  const btnSendPhoneOtp = el('btnSendPhoneOtp');
  const btnVerifyOtps = el('btnVerifyOtps');
  const btnBack = el('btnBack');

  const emailOtp = el('emailOtp');
  const phoneOtp = el('phoneOtp');

  const emailDemo = el('emailDemo');
  const phoneDemo = el('phoneDemo');

  let pendingId = null;
  let usernameTimer = null;

  function showMsg(type, text) {
    msgBox.className = 'msg ' + (type || '');
    msgBox.textContent = text;
    msgBox.style.display = 'block';
  }

  function hideMsg() {
    msgBox.style.display = 'none';
  }

  async function postJSON(url, data) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data || {})
    });
    return res.json();
  }

  async function getJSON(url) {
    const res = await fetch(url);
    return res.json();
  }

  function validateStep1() {
    if (!username.value.trim()) return 'Username is required.';
    if (!email.value.trim()) return 'Email is required.';
    if (!phone.value.trim()) return 'Phone is required.';
    if (!password.value) return 'Password is required.';
    if (password.value !== cpassword.value) return 'Passwords do not match.';
    return null;
  }

  async function checkUsernameNow() {
    const u = username.value.trim();
    if (u.length < 3) {
      usernameStatus.textContent = 'Type at least 3 characters.';
      usernameStatus.className = 'hint';
      return;
    }
    const data = await getJSON(`${apiBase}/check_username.php?username=${encodeURIComponent(u)}`);
    usernameStatus.textContent = data.message || '';
    usernameStatus.className = 'hint ' + (data.available ? 'good' : 'bad');
  }

  username.addEventListener('keyup', function () {
    clearTimeout(usernameTimer);
    usernameTimer = setTimeout(checkUsernameNow, 250);
  });

  btnContinue.addEventListener('click', async function () {
    hideMsg();
    const err = validateStep1();
    if (err) {
      showMsg('error', err);
      return;
    }

    // extra guard: check username before creating pending
    await checkUsernameNow();
    if (usernameStatus.className.includes('bad')) {
      showMsg('error', 'Please choose a different username.');
      return;
    }

    btnContinue.disabled = true;
    btnContinue.textContent = 'Please wait...';

    try {
      const resp = await postJSON(`${apiBase}/create_pending.php`, {
        username: username.value.trim(),
        email: email.value.trim(),
        phone: phone.value.trim(),
        password: password.value,
        cpassword: cpassword.value
      });

      if (!resp.success) {
        showMsg('error', resp.message || 'Failed to continue.');
        return;
      }

      pendingId = resp.pending_id;

      
    // move to OTP step
      step1.style.display = 'none';
      step2.style.display = 'block';


      showMsg('success', 'Please click the "Send Code" buttons to generate your OTPs.');
      
    } catch (e) {
      showMsg('error', 'Network error. Please try again.');
    } finally {
      btnContinue.disabled = false;
      btnContinue.textContent = 'Continue';
    }
  });

  async function sendOtp(type) {
    if (!pendingId) return;

    const resp = await postJSON(`${apiBase}/send_otp.php`, {
      pending_id: pendingId,
      type: type
    });

    if (!resp.success) {
      showMsg('error', resp.message || 'Failed to send OTP.');
      return;
    }

    // Display the blue demo text
    const target = (type === 'email') ? emailDemo : phoneDemo;
    target.style.display = 'block';
    target.innerHTML = `Demo OTP (${type}): <b style='color:blue;'>${resp.otp}</b> (expires in ${resp.expires_in_sec}s)`;

    // Auto-fill the input box!
    if (type === 'email') {
      emailOtp.value = resp.otp;
    } else if (type === 'phone') {
      phoneOtp.value = resp.otp;
    }
  }

  btnSendEmailOtp.addEventListener('click', () => sendOtp('email'));
  btnSendPhoneOtp.addEventListener('click', () => sendOtp('phone'));

  btnVerifyOtps.addEventListener('click', async function () {
    hideMsg();
    if (!pendingId) {
      showMsg('error', 'Session expired. Please go back and try again.');
      return;
    }
    if (!emailOtp.value.trim() || !phoneOtp.value.trim()) {
      showMsg('error', 'Please enter both Email OTP and Phone OTP.');
      return;
    }

    btnVerifyOtps.disabled = true;
    btnVerifyOtps.textContent = 'Verifying...';

    try {
      const resp = await postJSON(`${apiBase}/verify_otps.php`, {
        pending_id: pendingId,
        email_otp: emailOtp.value.trim(),
        phone_otp: phoneOtp.value.trim()
      });

      if (!resp.success) {
        showMsg('error', resp.message || 'OTP verification failed.');
        return;
      }

      // complete signup
      const done = await postJSON(`${apiBase}/complete_signup.php`, { pending_id: pendingId });

      if (!done.success) {
        showMsg('error', done.message || 'Could not create account.');
        return;
      }

      step2.style.display = 'none';
      step3.style.display = 'block';
    } catch (e) {
      showMsg('error', 'Network error. Please try again.');
    } finally {
      btnVerifyOtps.disabled = false;
      btnVerifyOtps.textContent = 'Verify Codes';
    }
  });

  btnBack.addEventListener('click', function () {
    // reset flow (keeps pending in DB, but user can restart)
    step2.style.display = 'none';
    step1.style.display = 'block';
    pendingId = null;

    emailOtp.value = '';
    phoneOtp.value = '';

    emailDemo.style.display = 'none';
    phoneDemo.style.display = 'none';
    hideMsg();
  });

})();