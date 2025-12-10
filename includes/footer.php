<footer class="modern-footer">
    <div class="footer-content">
        <div>
            <div style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; color: white;">
                <i class="fas fa-search"></i> EWULost&Found
            </div>
            <p style="color: #adb5bd; margin-bottom: 1.5rem;">
                East West University's official lost and found portal.
            </p>
        </div>
        
        <div>
            <h4 style="color: white; margin-bottom: 1rem;">Quick Links</h4>
            <a href="/ewu-lostfound/index.php" style="color: #adb5bd; text-decoration: none; display: block; margin-bottom: 0.5rem;">
                <i class="fas fa-home"></i> Home
            </a>
            <a href="/ewu-lostfound/search.php" style="color: #adb5bd; text-decoration: none; display: block; margin-bottom: 0.5rem;">
                <i class="fas fa-search"></i> Search
            </a>
            <a href="/ewu-lostfound/report_lost.php" style="color: #adb5bd; text-decoration: none; display: block; margin-bottom: 0.5rem;">
                <i class="fas fa-exclamation-triangle"></i> Report Lost
            </a>
            <a href="/ewu-lostfound/report_found.php" style="color: #adb5bd; text-decoration: none; display: block; margin-bottom: 0.5rem;">
                <i class="fas fa-hands-helping"></i> Report Found
            </a>
        </div>
        
        <div>
            <h4 style="color: white; margin-bottom: 1rem;">Contact</h4>
            <p style="color: #adb5bd; margin-bottom: 0.5rem;">
                <i class="fas fa-map-marker-alt"></i> East West University
            </p>
            <p style="color: #adb5bd; margin-bottom: 0.5rem;">
                <i class="fas fa-envelope"></i> lostfound@ewu.edu.bd
            </p>
            <p style="color: #adb5bd; margin-bottom: 0.5rem;">
                <i class="fas fa-phone"></i> +880 961 000 000
            </p>
        </div>
    </div>
    
    <div style="text-align: center; color: #adb5bd; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
        <p>&copy; <?php echo date('Y'); ?> East West University - SQL Course Project</p>
    </div>
</footer>

<script>
// Simple JavaScript for animations
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in to elements when they scroll into view
    const fadeElements = document.querySelectorAll('.item-card-modern, .glass-card');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, { threshold: 0.1 });
    
    fadeElements.forEach(el => observer.observe(el));
    
    // Auto-hide messages after 5 seconds
    setTimeout(() => {
        const messages = document.querySelectorAll('.message');
        messages.forEach(msg => {
            msg.style.opacity = '0';
            msg.style.transition = 'opacity 0.5s ease';
            setTimeout(() => msg.style.display = 'none', 500);
        });
    }, 5000);
});
</script>
</body>
</html>