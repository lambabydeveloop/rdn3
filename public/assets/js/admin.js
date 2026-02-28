document.addEventListener('DOMContentLoaded', () => {
    
    // --- Responsive sidebar toggle logic ---
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileCloseBtn = document.getElementById('mobile-close-btn');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    
    if (mobileMenuBtn && sidebar && overlay) {
        const toggleSidebar = () => {
            const isClosed = sidebar.classList.contains('sidebar-closed');
            
            if (isClosed) {
                // Open sidebar
                sidebar.classList.remove('sidebar-closed', '-translate-x-full');
                sidebar.classList.add('translate-x-0');
                
                // Show overlay
                overlay.classList.remove('opacity-0', 'pointer-events-none');
                overlay.classList.add('opacity-100', 'pointer-events-auto');
                
                // Animate hamburger to X (basic)
                const spans = mobileMenuBtn.querySelectorAll('span');
                if(spans.length === 3) {
                    spans[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
                    spans[1].style.opacity = '0';
                    spans[2].style.transform = 'rotate(-45deg) translate(4px, -4px)';
                }
            } else {
                // Close sidebar
                sidebar.classList.add('sidebar-closed', '-translate-x-full');
                sidebar.classList.remove('translate-x-0');
                
                // Hide overlay
                overlay.classList.remove('opacity-100', 'pointer-events-auto');
                overlay.classList.add('opacity-0', 'pointer-events-none');
                
                // Reset hamburger
                const spans = mobileMenuBtn.querySelectorAll('span');
                if(spans.length === 3) {
                    spans[0].style.transform = 'none';
                    spans[1].style.opacity = '1';
                    spans[2].style.transform = 'none';
                }
            }
        };

        mobileMenuBtn.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', toggleSidebar);
        if (mobileCloseBtn) {
            mobileCloseBtn.addEventListener('click', toggleSidebar);
        }
    }

    // --- GSAP Animations (if loaded) ---
    // We defer the animation to ensure GSAP is available and DOM is fully laid out
    setTimeout(() => {
        if(typeof gsap !== 'undefined') {
            
            // Sidebars stagger animation
            gsap.from('.sidebar-item', {
                x: -50,
                opacity: 0,
                duration: 1,
                stagger: 0.15,
                ease: 'power3.out'
            });

            // Main dashboard intro
            gsap.from('.dashboard-main-content', {
                y: 30,
                opacity: 0.5,
                scale: 0.98,
                duration: 1,
                delay: 0.2,
                ease: 'power3.out'
            });

            // Greet elements stagger
            gsap.from('.greeting-anim', {
                y: 20,
                opacity: 0,
                duration: 0.8,
                stagger: 0.1,
                delay: 0.5,
                ease: 'power2.out'
            });

            // Cards stagger
            if (document.querySelectorAll('.dashboard-card').length) {
                gsap.from('.dashboard-card', {
                    y: 30,
                    opacity: 0,
                    duration: 0.8,
                    stagger: 0.1,
                    delay: 0.7,
                    ease: 'back.out(1.2)'
                });
            }
            
            // Charts interior stagger
            if (document.querySelectorAll('.dashboard-card .bg-black').length) {
                gsap.from('.dashboard-card .bg-black', {
                    height: 0,
                    duration: 1.2,
                    stagger: 0.05,
                    delay: 1.2,
                    ease: 'elastic.out(1, 0.6)'
                });
            }
        }
    }, 150);
});
