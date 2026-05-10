<?php
// includes/footer.php - LifeFlow Footer Component
?>

<!-- Footer -->
<footer class="footer">
    <div class="footer__content">
        <div class="footer__brand">
            <a href="index.php" class="navbar__logo">
                <div class="logo-icon">
                    <svg viewBox="0 0 100 140" width="28" height="36">
                        <path d="M50 0 C50 0 0 60 0 90 C0 120 22 140 50 140 C78 140 100 120 100 90 C100 60 50 0 50 0Z" fill="currentColor"/>
                    </svg>
                </div>
                <span>LifeFlow</span>
            </a>
            <p>Connecting donors with those in need. Every drop counts in saving lives.</p>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
        
        <div class="footer__links">
            <h4>Quick Links</h4>
            <a href="index.php">Home</a>
            <a href="eligibility.php">Eligibility Check</a>
            <a href="request_blood_guest.php">Request Blood</a>
            <a href="admin_login.php">Admin Portal</a>
        </div>
        
        <div class="footer__links">
            <h4>Resources</h4>
            <a href="#">Blood Compatibility</a>
            <a href="#">Donation Guidelines</a>
            <a href="#">FAQs</a>
            <a href="#">Contact Us</a>
        </div>
    </div>
    
    <div class="footer__bottom">
        <p>&copy; <?php echo date('Y'); ?> LifeFlow Blood Bank Management System. All rights reserved.</p>
    </div>
</footer>

<!-- Main JavaScript -->
<script>
// Preloader
window.addEventListener('load', () => {
    const preloader = document.getElementById('preloader');
    gsap.to(preloader, {
        opacity: 0,
        duration: 0.5,
        delay: 0.5,
        onComplete: () => {
            preloader.classList.add('hidden');
            // Trigger entrance animations
            if (typeof playEntranceAnimations === 'function') {
                playEntranceAnimations();
            }
        }
    });
});

// Toast Notification System
const Toast = {
    container: document.getElementById('toastContainer'),
    
    show(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast--${type}`;
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            info: 'fa-info-circle',
            warning: 'fa-exclamation-triangle'
        };
        
        toast.innerHTML = `
            <i class="fas ${icons[type]}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#fff;cursor:pointer;margin-left:15px;">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        this.container.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            gsap.to(toast, {
                x: 100,
                opacity: 0,
                duration: 0.3,
                onComplete: () => toast.remove()
            });
        }, 5000);
    }
};

// GSAP Scroll Animations
gsap.registerPlugin(ScrollTrigger);

// Animate elements on scroll
document.querySelectorAll('.card, .stat-card, .stock-card, .feature-card').forEach((el, i) => {
    gsap.from(el, {
        y: 50,
        opacity: 0,
        duration: 0.8,
        delay: i * 0.1,
        scrollTrigger: {
            trigger: el,
            start: 'top 85%',
            toggleActions: 'play none none reverse'
        }
    });
});

// Password Toggle
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.parentElement.querySelector('input');
        const icon = btn.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
});

// Initialize Flatpickr on date inputs
document.querySelectorAll('input[type="date"], .datepicker').forEach(input => {
    flatpickr(input, {
        theme: 'dark',
        dateFormat: 'Y-m-d'
    });
});

// ==================== Theme Toggle (Day/Night Mode) ====================
// Load saved theme on page load
(function() {
    const savedTheme = localStorage.getItem('lifeflow-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);
})();

// Toggle theme function
function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Apply theme with smooth transition
    document.documentElement.style.transition = 'all 0.3s ease';
    document.documentElement.setAttribute('data-theme', newTheme);
    
    // Save preference
    localStorage.setItem('lifeflow-theme', newTheme);
    
    // Show toast
    const icon = newTheme === 'light' ? '☀️' : '🌙';
    Toast.show(`${icon} ${newTheme === 'light' ? 'Day' : 'Night'} mode activated!`, 'info');
    
    // Remove transition after animation
    setTimeout(() => {
        document.documentElement.style.transition = '';
    }, 300);
}
</script>

</body>
</html>
