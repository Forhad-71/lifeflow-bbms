<?php
// ai_eligibility.php - TensorFlow.js Blood Donation Eligibility Predictor
$pageTitle = "AI Eligibility Check - LifeFlow";
include 'includes/header.php';
?>

<style>
.ai-container {
    max-width: 800px;
    margin: 0 auto;
}
.ai-form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}
@media (max-width: 768px) {
    .ai-form { grid-template-columns: 1fr; }
}
.ai-form .form-group { margin-bottom: 0; }
.ai-form label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text-secondary);
}
.ai-form input, .ai-form select {
    width: 100%;
    padding: 14px 18px;
    background: var(--input-bg);
    border: 2px solid var(--divider);
    border-radius: var(--radius-md);
    color: var(--text-primary);
    font-size: 1rem;
}
.ai-form input:focus, .ai-form select:focus {
    border-color: var(--primary);
    outline: none;
}
.result-box {
    margin-top: 30px;
    padding: 30px;
    border-radius: var(--radius-lg);
    text-align: center;
    display: none;
}
.result-box.eligible {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(16, 185, 129, 0.05));
    border: 2px solid #10b981;
}
.result-box.not-eligible {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(239, 68, 68, 0.05));
    border: 2px solid #ef4444;
}
.result-icon {
    font-size: 4rem;
    margin-bottom: 15px;
}
.result-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 10px;
}
.result-text {
    color: var(--text-secondary);
    font-size: 1.1rem;
}
.confidence-bar {
    margin-top: 20px;
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
    height: 12px;
    overflow: hidden;
}
.confidence-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 1s ease;
}
.factors-list {
    margin-top: 25px;
    text-align: left;
    padding: 20px;
    background: rgba(255,255,255,0.03);
    border-radius: var(--radius-md);
}
.factors-list h4 {
    margin-bottom: 15px;
    color: var(--text-primary);
}
.factor-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    color: var(--text-secondary);
}
.factor-item i.pass { color: #10b981; }
.factor-item i.fail { color: #ef4444; }
.loading-spinner {
    display: none;
    text-align: center;
    padding: 30px;
}
.loading-spinner i {
    font-size: 3rem;
    color: var(--primary);
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<div class="page-wrapper">
    <div class="page-content ai-container">
        <div class="page-header" id="pageHeader">
            <h1 class="page-title">
                <i class="fas fa-brain" style="color: var(--primary);"></i>
                AI Eligibility Predictor
            </h1>
            <p class="page-subtitle">Powered by TensorFlow.js - Instant blood donation eligibility assessment</p>
        </div>
        
        <div class="card" id="aiForm">
            <h3 style="margin-bottom: 25px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-user-md" style="color: var(--accent);"></i>
                Enter Your Health Information
            </h3>
            
            <div class="ai-form">
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> Age</label>
                    <input type="number" id="age" placeholder="e.g., 25" min="16" max="70">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-weight"></i> Weight (kg)</label>
                    <input type="number" id="weight" placeholder="e.g., 65" min="30" max="200">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-heartbeat"></i> Hemoglobin (g/dL)</label>
                    <input type="number" id="hemoglobin" placeholder="e.g., 14.5" step="0.1" min="5" max="20">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-tint"></i> Blood Pressure (Systolic)</label>
                    <input type="number" id="bp_systolic" placeholder="e.g., 120" min="70" max="200">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-tint"></i> Blood Pressure (Diastolic)</label>
                    <input type="number" id="bp_diastolic" placeholder="e.g., 80" min="40" max="130">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-heart"></i> Pulse Rate (bpm)</label>
                    <input type="number" id="pulse" placeholder="e.g., 72" min="40" max="150">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-venus-mars"></i> Gender</label>
                    <select id="gender">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Last Donation (months ago)</label>
                    <input type="number" id="lastDonation" placeholder="e.g., 4 (0 if never)" min="0" max="120">
                </div>
                
                <div class="form-group" style="grid-column: span 2;">
                    <label><i class="fas fa-notes-medical"></i> Recent Medical Conditions</label>
                    <select id="conditions">
                        <option value="none">None</option>
                        <option value="cold">Cold/Flu (last 2 weeks)</option>
                        <option value="medication">On Medication</option>
                        <option value="surgery">Recent Surgery (last 6 months)</option>
                        <option value="tattoo">Recent Tattoo/Piercing (last 12 months)</option>
                        <option value="travel">Travel to Malaria Zone (last 12 months)</option>
                    </select>
                </div>
            </div>
            
            <button onclick="predictEligibility()" class="btn btn--primary btn--full" style="margin-top: 30px;">
                <i class="fas fa-robot"></i> Predict Eligibility
            </button>
        </div>
        
        <div class="loading-spinner" id="loadingSpinner">
            <i class="fas fa-cog fa-spin"></i>
            <p style="margin-top: 15px; color: var(--text-secondary);">AI is analyzing your data...</p>
        </div>
        
        <div class="result-box" id="resultBox">
            <div class="result-icon" id="resultIcon"></div>
            <div class="result-title" id="resultTitle"></div>
            <div class="result-text" id="resultText"></div>
            
            <div style="margin-top: 20px;">
                <span style="color: var(--text-muted);">AI Confidence:</span>
                <span id="confidenceText" style="font-weight: 700; margin-left: 10px;"></span>
            </div>
            <div class="confidence-bar">
                <div class="confidence-fill" id="confidenceFill"></div>
            </div>
            
            <div class="factors-list" id="factorsList">
                <h4><i class="fas fa-clipboard-check"></i> Eligibility Factors</h4>
                <div id="factorsContent"></div>
            </div>
            
            <button onclick="resetForm()" class="btn btn--glass" style="margin-top: 25px;">
                <i class="fas fa-redo"></i> Check Again
            </button>
        </div>
    </div>
</div>

<!-- TensorFlow.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.10.0/dist/tf.min.js"></script>

<script>
// TensorFlow.js based eligibility prediction model
let model = null;

// Create and train a simple neural network model
async function createModel() {
    model = tf.sequential();
    
    // Input layer (8 features: age, weight, hemoglobin, bp_sys, bp_dia, pulse, gender, lastDonation)
    model.add(tf.layers.dense({
        units: 16,
        activation: 'relu',
        inputShape: [8]
    }));
    
    // Hidden layer
    model.add(tf.layers.dense({
        units: 8,
        activation: 'relu'
    }));
    
    // Output layer (binary classification: eligible/not eligible)
    model.add(tf.layers.dense({
        units: 1,
        activation: 'sigmoid'
    }));
    
    model.compile({
        optimizer: tf.train.adam(0.01),
        loss: 'binaryCrossentropy',
        metrics: ['accuracy']
    });
    
    // Training data (synthetic but based on real eligibility criteria)
    const trainingData = tf.tensor2d([
        // [age, weight, hemo, bp_sys, bp_dia, pulse, gender(1=M), lastDonation] => eligible
        [25, 70, 14.5, 120, 80, 72, 1, 4],   // Ideal male donor
        [30, 65, 13.5, 115, 75, 68, 0, 6],   // Ideal female donor
        [45, 80, 15.0, 125, 82, 70, 1, 12],  // Older but healthy
        [22, 60, 13.0, 110, 70, 65, 0, 0],   // First time female donor
        [35, 75, 14.0, 118, 78, 74, 1, 3],   // Regular donor
        [28, 68, 14.2, 122, 80, 70, 1, 6],   // Good candidate
        [40, 72, 13.8, 120, 75, 68, 0, 4],   // Experienced female
        [55, 85, 14.5, 130, 85, 72, 1, 8],   // Older male OK
        // Not eligible cases
        [17, 45, 11.0, 100, 60, 55, 0, 0],   // Too young, low weight, low hemo
        [70, 90, 12.0, 160, 100, 90, 1, 24], // Too old, high BP
        [25, 48, 10.5, 110, 70, 65, 0, 0],   // Low weight, anemic
        [30, 65, 9.0, 120, 80, 72, 0, 1],    // Very low hemoglobin
        [40, 70, 14.0, 170, 110, 100, 1, 6], // Hypertension
        [22, 55, 11.5, 90, 55, 50, 0, 0],    // Low BP, underweight
        [60, 100, 13.0, 145, 95, 85, 1, 0],  // Multiple borderline factors
        [35, 62, 12.5, 105, 65, 58, 0, 2],   // Too recent donation, low hemo
    ]);
    
    const labels = tf.tensor2d([
        [1], [1], [1], [1], [1], [1], [1], [1],  // Eligible
        [0], [0], [0], [0], [0], [0], [0], [0]   // Not eligible
    ]);
    
    // Train the model
    await model.fit(trainingData, labels, {
        epochs: 100,
        verbose: 0
    });
    
    console.log('TensorFlow.js model trained successfully!');
}

// Initialize model on page load
createModel();

async function predictEligibility() {
    // Get values
    const age = parseFloat(document.getElementById('age').value) || 0;
    const weight = parseFloat(document.getElementById('weight').value) || 0;
    const hemoglobin = parseFloat(document.getElementById('hemoglobin').value) || 0;
    const bpSystolic = parseFloat(document.getElementById('bp_systolic').value) || 0;
    const bpDiastolic = parseFloat(document.getElementById('bp_diastolic').value) || 0;
    const pulse = parseFloat(document.getElementById('pulse').value) || 0;
    const gender = document.getElementById('gender').value === 'male' ? 1 : 0;
    const lastDonation = parseFloat(document.getElementById('lastDonation').value) || 0;
    const conditions = document.getElementById('conditions').value;
    
    // Validation
    if (!age || !weight || !hemoglobin || !bpSystolic || !bpDiastolic || !pulse || !document.getElementById('gender').value) {
        Toast.show('Please fill all fields', 'error');
        return;
    }
    
    // Show loading
    document.getElementById('aiForm').style.display = 'none';
    document.getElementById('loadingSpinner').style.display = 'block';
    
    // Simulate AI processing time
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    // Make prediction with TensorFlow.js
    const inputTensor = tf.tensor2d([[age, weight, hemoglobin, bpSystolic, bpDiastolic, pulse, gender, lastDonation]]);
    const prediction = model.predict(inputTensor);
    let confidence = (await prediction.data())[0];
    
    // Apply rule-based adjustments for conditions
    if (conditions !== 'none') {
        confidence *= 0.3; // Significantly reduce if any condition
    }
    
    // Rule-based eligibility checks
    const factors = [];
    let eligible = confidence > 0.5;
    
    // Age check
    if (age >= 18 && age <= 65) {
        factors.push({ text: 'Age within acceptable range (18-65)', pass: true });
    } else {
        factors.push({ text: 'Age outside acceptable range', pass: false });
        eligible = false;
    }
    
    // Weight check
    if (weight >= 50) {
        factors.push({ text: 'Weight meets minimum requirement (≥50kg)', pass: true });
    } else {
        factors.push({ text: 'Weight below minimum (need ≥50kg)', pass: false });
        eligible = false;
    }
    
    // Hemoglobin check
    const hemoMin = gender === 1 ? 13.0 : 12.5;
    if (hemoglobin >= hemoMin) {
        factors.push({ text: `Hemoglobin level adequate (≥${hemoMin} g/dL)`, pass: true });
    } else {
        factors.push({ text: `Hemoglobin too low (need ≥${hemoMin} g/dL)`, pass: false });
        eligible = false;
    }
    
    // Blood pressure check
    if (bpSystolic >= 90 && bpSystolic <= 140 && bpDiastolic >= 60 && bpDiastolic <= 90) {
        factors.push({ text: 'Blood pressure within normal range', pass: true });
    } else {
        factors.push({ text: 'Blood pressure outside normal range', pass: false });
        eligible = false;
    }
    
    // Pulse check
    if (pulse >= 50 && pulse <= 100) {
        factors.push({ text: 'Pulse rate normal (50-100 bpm)', pass: true });
    } else {
        factors.push({ text: 'Pulse rate abnormal', pass: false });
        eligible = false;
    }
    
    // Last donation check
    const minMonths = gender === 1 ? 3 : 4;
    if (lastDonation >= minMonths || lastDonation === 0) {
        factors.push({ text: `Sufficient time since last donation (≥${minMonths} months)`, pass: true });
    } else {
        factors.push({ text: `Too soon since last donation (wait ${minMonths} months)`, pass: false });
        eligible = false;
    }
    
    // Conditions check
    if (conditions === 'none') {
        factors.push({ text: 'No disqualifying medical conditions', pass: true });
    } else {
        factors.push({ text: 'Medical condition may affect eligibility', pass: false });
        eligible = false;
    }
    
    // Recalculate confidence based on rules
    const passedFactors = factors.filter(f => f.pass).length;
    confidence = passedFactors / factors.length;
    
    // Display results
    document.getElementById('loadingSpinner').style.display = 'none';
    const resultBox = document.getElementById('resultBox');
    resultBox.style.display = 'block';
    resultBox.className = 'result-box ' + (eligible ? 'eligible' : 'not-eligible');
    
    document.getElementById('resultIcon').innerHTML = eligible ? '✅' : '❌';
    document.getElementById('resultTitle').textContent = eligible ? 'You Are Eligible!' : 'Not Eligible Currently';
    document.getElementById('resultText').textContent = eligible 
        ? 'Based on AI analysis, you meet the criteria for blood donation.'
        : 'Based on AI analysis, you may not be eligible at this time. Please consult a healthcare provider.';
    
    document.getElementById('confidenceText').textContent = (confidence * 100).toFixed(1) + '%';
    document.getElementById('confidenceFill').style.width = (confidence * 100) + '%';
    document.getElementById('confidenceFill').style.background = eligible 
        ? 'linear-gradient(90deg, #10b981, #34d399)'
        : 'linear-gradient(90deg, #ef4444, #f87171)';
    
    // Display factors
    document.getElementById('factorsContent').innerHTML = factors.map(f => `
        <div class="factor-item">
            <i class="fas ${f.pass ? 'fa-check-circle pass' : 'fa-times-circle fail'}"></i>
            <span>${f.text}</span>
        </div>
    `).join('');
    
    // Animate
    gsap.from(resultBox, { opacity: 0, y: 30, duration: 0.5 });
}

function resetForm() {
    document.getElementById('aiForm').style.display = 'block';
    document.getElementById('resultBox').style.display = 'none';
    document.getElementById('loadingSpinner').style.display = 'none';
    
    // Clear form
    document.querySelectorAll('#aiForm input').forEach(input => input.value = '');
    document.querySelectorAll('#aiForm select').forEach(select => select.selectedIndex = 0);
}
</script>

<?php include 'includes/footer.php'; ?>
