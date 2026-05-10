<?php
// index.php - LifeFlow Landing Page
session_start();

// If already logged in, route to the correct home
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin_home.php');
        exit;
    }
    if ($_SESSION['role'] === 'user') {
        header('Location: user_home.php');
        exit;
    }
}

$pageTitle = "LifeFlow - Blood Bank Management System";
include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero__bg">
        <div class="floating-cells">
            <div class="cell cell--1"></div>
            <div class="cell cell--2"></div>
            <div class="cell cell--3"></div>
            <div class="cell cell--4"></div>
            <div class="cell cell--5"></div>
        </div>
    </div>
    
    <div class="hero__content">
        <div class="hero__text">
            <span class="hero__badge" id="heroBadge">🩸 Every Drop Counts</span>
            <h1 class="hero__title" id="heroTitle">
                <span class="line">Give the Gift</span>
                <span class="line">of <span class="gradient-text">Life</span></span>
            </h1>
            <p class="hero__description" id="heroDesc">
                Connect donors with those in need. Our intelligent blood bank management system ensures no life is lost due to blood shortage.
            </p>
            <div class="hero__cta" id="heroCta">
                <a href="user_signup.php" class="btn btn--large btn--primary">
                    <i class="fas fa-hand-holding-heart"></i>
                    Become a Donor
                </a>
                <a href="request_blood_guest.php" class="btn btn--large btn--glass">
                    <i class="fas fa-tint"></i>
                    Request Blood
                </a>
            </div>
            <div class="hero__stats" id="heroStats">
                <div class="stat">
                    <span class="stat__number" data-count="15420">0</span>
                    <span class="stat__label">Lives Saved</span>
                </div>
                <div class="stat">
                    <span class="stat__number" data-count="8750">0</span>
                    <span class="stat__label">Active Donors</span>
                </div>
                <div class="stat">
                    <span class="stat__number" data-count="125">0</span>
                    <span class="stat__label">Blood Banks</span>
                </div>
            </div>
        </div>
        
        <div class="hero__visual" id="heroVisual">
            <div class="blood-bag">
                <div class="bag-top"></div>
                <div class="bag-body">
                    <div class="blood-level"></div>
                    <div class="bag-label">
                        <span class="blood-type">O+</span>
                        <span class="volume">450ml</span>
                    </div>
                </div>
                <div class="bag-tube"></div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section" id="features">
    <div class="section-header">
        <span class="section-tag">What We Offer</span>
        <h2 class="section-title">Powerful Features</h2>
        <p class="section-desc">Everything you need for efficient blood bank management</p>
    </div>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-card__icon">
                <i class="fas fa-user-check"></i>
            </div>
            <h3>Real-time Verification</h3>
            <p>Instant username, email, and phone verification with OTP authentication</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-card__icon">
                <i class="fas fa-comments"></i>
            </div>
            <h3>Community Posts</h3>
            <p>Engage with donors and recipients through posts, likes, and comments</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-card__icon">
                <i class="fas fa-boxes-stacked"></i>
            </div>
            <h3>Stock Management</h3>
            <p>Track blood inventory levels with real-time updates and alerts</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-card__icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>Donor Search</h3>
            <p>Find compatible donors quickly by blood type and location</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-card__icon">
                <i class="fas fa-hand-holding-medical"></i>
            </div>
            <h3>Blood Requests</h3>
            <p>Submit and track blood requests with priority handling</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-card__icon">
                <i class="fas fa-video"></i>
            </div>
            <h3>Educational Content</h3>
            <p>Watch informative videos about blood donation and its importance</p>
        </div>
    </div>
</section>

<!-- Blood Types Section -->
<section class="section" style="background: linear-gradient(135deg, var(--primary-dark), #5a0d1a); padding: 80px 20px;">
    <div class="section-header">
        <span class="section-tag">Know Your Type</span>
        <h2 class="section-title">Blood Type Compatibility</h2>
        <p class="section-desc">Understanding blood types is crucial for safe transfusions</p>
    </div>
    
    <div class="stock-grid" style="max-width: 1000px; margin: 0 auto;">
        <div class="stock-card">
            <div class="stock-card__type">A+</div>
            <div class="stock-card__label">Can donate to: A+, AB+</div>
        </div>
        <div class="stock-card">
            <div class="stock-card__type">A-</div>
            <div class="stock-card__label">Can donate to: A+, A-, AB+, AB-</div>
        </div>
        <div class="stock-card">
            <div class="stock-card__type">B+</div>
            <div class="stock-card__label">Can donate to: B+, AB+</div>
        </div>
        <div class="stock-card">
            <div class="stock-card__type">B-</div>
            <div class="stock-card__label">Can donate to: B+, B-, AB+, AB-</div>
        </div>
        <div class="stock-card">
            <div class="stock-card__type">AB+</div>
            <div class="stock-card__label">Universal Recipient</div>
        </div>
        <div class="stock-card">
            <div class="stock-card__type">AB-</div>
            <div class="stock-card__label">Can donate to: AB+, AB-</div>
        </div>
        <div class="stock-card">
            <div class="stock-card__type">O+</div>
            <div class="stock-card__label">Can donate to: A+, B+, AB+, O+</div>
        </div>
        <div class="stock-card">
            <div class="stock-card__type">O-</div>
            <div class="stock-card__label">Universal Donor</div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section text-center">
    <div class="section-header">
        <span class="section-tag">Get Started</span>
        <h2 class="section-title">Ready to Save Lives?</h2>
        <p class="section-desc">Join our community of donors and make a difference today</p>
    </div>
    
    <div class="d-flex justify-center gap-2" style="flex-wrap: wrap;">
        <a href="user_signup.php" class="btn btn--large btn--primary">
            <i class="fas fa-user-plus"></i> Register as Donor
        </a>
        <a href="eligibility.php" class="btn btn--large btn--outline">
            <i class="fas fa-check-circle"></i> Check Eligibility
        </a>
        <a href="admin_login.php" class="btn btn--large btn--glass">
            <i class="fas fa-user-shield"></i> Admin Portal
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Hero Entrance Animations
function playEntranceAnimations() {
    const tl = gsap.timeline();
    
    tl.from('#heroBadge', { y: 30, opacity: 0, duration: 0.8, ease: 'power3.out' })
      .from('#heroTitle .line', { y: 100, opacity: 0, duration: 1, stagger: 0.2, ease: 'power3.out' }, '-=0.4')
      .from('#heroDesc', { y: 30, opacity: 0, duration: 0.8, ease: 'power3.out' }, '-=0.6')
      .from('#heroCta .btn', { y: 20, opacity: 0, duration: 0.6, stagger: 0.15, ease: 'power3.out' }, '-=0.4')
      .from('#heroStats .stat', { y: 30, opacity: 0, duration: 0.6, stagger: 0.1, ease: 'power3.out' }, '-=0.3')
      .from('#heroVisual', { scale: 0.8, opacity: 0, duration: 1, ease: 'elastic.out(1, 0.5)' }, '-=0.8');
    
    // Animate counters
    document.querySelectorAll('.stat__number').forEach(counter => {
        const target = parseInt(counter.dataset.count);
        gsap.to(counter, {
            innerText: target,
            duration: 2,
            snap: { innerText: 1 },
            delay: 1.5,
            onUpdate: function() {
                counter.innerText = Math.floor(counter.innerText).toLocaleString();
            }
        });
    });
    
    // Blood bag floating animation
    gsap.to('.blood-bag', { y: 15, duration: 2, yoyo: true, repeat: -1, ease: 'sine.inOut' });
}
</script>
