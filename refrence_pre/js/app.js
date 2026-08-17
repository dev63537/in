/* ============================================
   InventMitra — Main Application Controller
   Bootstraps all modules on DOMContentLoaded
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
    const presentation = document.getElementById('presentation');

    /* ── Initialize Loading Screen ── */
    const loader = new LoadingScreen();

    /* ── Initialize Particle System ── */
    const particles = new ParticleSystem('particles-canvas');

    /* ── Start Loading Sequence ── */
    loader.start(() => {
        /* Loading complete → reveal the experience */

        /* 1. Show navigation bar */
        const nav = new NavigationController();
        nav.show();

        /* 2. Start particle background */
        particles.start();

        /* 3. Initialize scroll-triggered animations */
        const animator = new ScrollAnimator(presentation);

        /* 4. Activate mouse glow tracking */
        initMouseGlow();

        /* 5. Make first section visible */
        const firstSection = document.querySelector('.section');
        if (firstSection) {
            firstSection.classList.add('in-view');
        }
    });
});

/* ─── Mouse Glow Effect ─── 
   Updates CSS custom properties so the radial 
   gradient glow follows the cursor position    */
function initMouseGlow() {
    let ticking = false;

    document.addEventListener('mousemove', (e) => {
        if (!ticking) {
            requestAnimationFrame(() => {
                document.documentElement.style.setProperty('--mouse-x', `${e.clientX}px`);
                document.documentElement.style.setProperty('--mouse-y', `${e.clientY}px`);
                ticking = false;
            });
            ticking = true;
        }
    });
}
