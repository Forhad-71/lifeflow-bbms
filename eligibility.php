<?php
// eligibility.php - LifeFlow Blood Donation Eligibility Checker
session_start();

$pageTitle = "Eligibility Check - LifeFlow";
include 'includes/header.php';
?>

<div class="page-wrapper">
    <div class="page-content" style="max-width: 800px;">
        <div class="page-header text-center" id="pageHeader">
            <h1 class="page-title"><i class="fas fa-check-circle" style="color: var(--primary);"></i> Eligibility Check</h1>
            <p class="page-subtitle">Find out if you're eligible to donate blood</p>
        </div>
        
        <div class="card" id="eligibilityForm">
            <form id="checkForm" onsubmit="checkEligibility(event)">
                <!-- Age -->
                <div class="form-group">
                    <label><i class="fas fa-birthday-cake"></i> Your Age</label>
                    <input type="number" id="age" placeholder="Enter your age" min="1" max="100" required>
                </div>
                
                <!-- Weight -->
                <div class="form-group">
                    <label><i class="fas fa-weight"></i> Weight (kg)</label>
                    <input type="number" id="weight" placeholder="Enter your weight in kg" min="1" max="200" required>
                </div>
                
                <!-- Last Donation -->
                <div class="form-group">
                    <label><i class="fas fa-calendar-alt"></i> Last Blood Donation</label>
                    <select id="lastDonation" required>
                        <option value="">Select an option</option>
                        <option value="never">Never donated before</option>
                        <option value="3months">Less than 3 months ago</option>
                        <option value="3to6">3-6 months ago</option>
                        <option value="6plus">More than 6 months ago</option>
                    </select>
                </div>
                
                <!-- Health Conditions -->
                <div class="form-group">
                    <label><i class="fas fa-heartbeat"></i> Do you have any of these conditions?</label>
                    <div style="display: grid; gap: 10px; margin-top: 10px;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="conditions" value="heart">
                            <span class="checkmark"></span>
                            Heart disease or heart surgery
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="conditions" value="diabetes">
                            <span class="checkmark"></span>
                            Diabetes (on insulin)
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="conditions" value="hepatitis">
                            <span class="checkmark"></span>
                            Hepatitis B or C
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="conditions" value="hiv">
                            <span class="checkmark"></span>
                            HIV/AIDS
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="conditions" value="cancer">
                            <span class="checkmark"></span>
                            Cancer (currently being treated)
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="conditions" value="pregnant">
                            <span class="checkmark"></span>
                            Currently pregnant or breastfeeding
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="conditions" value="tattoo">
                            <span class="checkmark"></span>
                            Got a tattoo in the last 6 months
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="conditions" value="none" id="noneCondition">
                            <span class="checkmark"></span>
                            None of the above
                        </label>
                    </div>
                </div>
                
                <!-- Recent Illness -->
                <div class="form-group">
                    <label><i class="fas fa-thermometer-half"></i> Any illness in the last 2 weeks?</label>
                    <select id="recentIllness" required>
                        <option value="">Select an option</option>
                        <option value="no">No, I'm feeling healthy</option>
                        <option value="cold">Cold or flu</option>
                        <option value="fever">Fever</option>
                        <option value="other">Other illness</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn--primary btn--full btn--large">
                    <i class="fas fa-clipboard-check"></i> Check My Eligibility
                </button>
            </form>
            
            <!-- Result -->
            <div id="resultBox" style="display: none; margin-top: 30px; padding: 30px; border-radius: var(--radius-lg); text-align: center;">
                <div id="resultIcon" style="font-size: 4rem; margin-bottom: 20px;"></div>
                <h2 id="resultTitle" style="margin-bottom: 10px;"></h2>
                <p id="resultMessage" style="color: var(--text-secondary);"></p>
                <div id="resultActions" style="margin-top: 25px;"></div>
            </div>
        </div>
        
        <!-- Info Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 30px;" id="infoCards">
            <div class="card" style="border-left: 4px solid var(--success);">
                <h4 style="margin-bottom: 10px;"><i class="fas fa-check" style="color: var(--success);"></i> Basic Requirements</h4>
                <ul style="color: var(--text-secondary); padding-left: 20px;">
                    <li>Age: 18-65 years</li>
                    <li>Weight: At least 50 kg</li>
                    <li>Good general health</li>
                    <li>No recent illness</li>
                </ul>
            </div>
            
            <div class="card" style="border-left: 4px solid var(--warning);">
                <h4 style="margin-bottom: 10px;"><i class="fas fa-clock" style="color: var(--warning);"></i> Wait Period</h4>
                <ul style="color: var(--text-secondary); padding-left: 20px;">
                    <li>After donation: 3 months (men) / 4 months (women)</li>
                    <li>After tattoo: 6 months</li>
                    <li>After surgery: 6-12 months</li>
                </ul>
            </div>
            
            <div class="card" style="border-left: 4px solid var(--danger);">
                <h4 style="margin-bottom: 10px;"><i class="fas fa-times" style="color: var(--danger);"></i> Cannot Donate</h4>
                <ul style="color: var(--text-secondary); padding-left: 20px;">
                    <li>HIV positive</li>
                    <li>Hepatitis B or C</li>
                    <li>Currently pregnant</li>
                    <li>Active cancer treatment</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function playEntranceAnimations() {
    gsap.from('#pageHeader', { y: 30, opacity: 0, duration: 0.6 });
    gsap.from('#eligibilityForm', { y: 50, opacity: 0, duration: 0.8, delay: 0.2 });
    gsap.from('#infoCards .card', { y: 30, opacity: 0, duration: 0.5, stagger: 0.1, delay: 0.4 });
}

// Handle "None" checkbox
document.getElementById('noneCondition').addEventListener('change', function() {
    if (this.checked) {
        document.querySelectorAll('input[name="conditions"]').forEach(cb => {
            if (cb !== this) cb.checked = false;
        });
    }
});

document.querySelectorAll('input[name="conditions"]:not(#noneCondition)').forEach(cb => {
    cb.addEventListener('change', function() {
        if (this.checked) {
            document.getElementById('noneCondition').checked = false;
        }
    });
});

function checkEligibility(e) {
    e.preventDefault();
    
    const age = parseInt(document.getElementById('age').value);
    const weight = parseInt(document.getElementById('weight').value);
    const lastDonation = document.getElementById('lastDonation').value;
    const recentIllness = document.getElementById('recentIllness').value;
    
    const conditions = Array.from(document.querySelectorAll('input[name="conditions"]:checked')).map(cb => cb.value);
    
    let eligible = true;
    let reasons = [];
    
    // Check age
    if (age < 18) {
        eligible = false;
        reasons.push('You must be at least 18 years old');
    } else if (age > 65) {
        eligible = false;
        reasons.push('Maximum donor age is 65 years');
    }
    
    // Check weight
    if (weight < 50) {
        eligible = false;
        reasons.push('You must weigh at least 50 kg');
    }
    
    // Check last donation
    if (lastDonation === '3months') {
        eligible = false;
        reasons.push('You need to wait at least 3 months between donations');
    }
    
    // Check conditions
    const disqualifyingConditions = ['heart', 'hepatitis', 'hiv', 'cancer', 'pregnant'];
    disqualifyingConditions.forEach(cond => {
        if (conditions.includes(cond)) {
            eligible = false;
            reasons.push(`You have a disqualifying health condition`);
        }
    });
    
    if (conditions.includes('tattoo')) {
        eligible = false;
        reasons.push('Please wait 6 months after getting a tattoo');
    }
    
    if (conditions.includes('diabetes')) {
        reasons.push('Diabetes on insulin requires medical clearance');
    }
    
    // Check recent illness
    if (recentIllness !== 'no') {
        eligible = false;
        reasons.push('Please wait until you fully recover from your illness');
    }
    
    // Show result
    const resultBox = document.getElementById('resultBox');
    const resultIcon = document.getElementById('resultIcon');
    const resultTitle = document.getElementById('resultTitle');
    const resultMessage = document.getElementById('resultMessage');
    const resultActions = document.getElementById('resultActions');
    
    resultBox.style.display = 'block';
    
    if (eligible) {
        resultBox.style.background = 'rgba(16, 185, 129, 0.1)';
        resultBox.style.border = '2px solid var(--success)';
        resultIcon.innerHTML = '<i class="fas fa-check-circle" style="color: var(--success);"></i>';
        resultTitle.textContent = 'You Are Eligible! 🎉';
        resultTitle.style.color = 'var(--success)';
        resultMessage.textContent = 'Great news! Based on your responses, you appear to be eligible to donate blood. Your donation can save up to 3 lives!';
        resultActions.innerHTML = '<a href="request_blood.php" class="btn btn--success btn--large"><i class="fas fa-calendar-check"></i> Schedule Donation</a>';
    } else {
        resultBox.style.background = 'rgba(239, 68, 68, 0.1)';
        resultBox.style.border = '2px solid var(--danger)';
        resultIcon.innerHTML = '<i class="fas fa-times-circle" style="color: var(--danger);"></i>';
        resultTitle.textContent = 'Not Eligible Currently';
        resultTitle.style.color = 'var(--danger)';
        resultMessage.innerHTML = '<strong>Reasons:</strong><br>' + [...new Set(reasons)].join('<br>');
        resultActions.innerHTML = '<p style="color: var(--text-muted); margin-top: 15px;">Please consult with a healthcare provider for personalized advice.</p>';
    }
    
    // Animate result
    gsap.from(resultBox, { y: 30, opacity: 0, duration: 0.5 });
    resultBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>
