/* ============================================
   Gujju Clothing — Main Application Controller
   ============================================ */

document.addEventListener('DOMContentLoaded', () => {
    const presentation = document.getElementById('presentation');

    const loader = new LoadingScreen();
    const particles = new ParticleSystem('particles-canvas');

    loader.start(() => {
        const nav = new NavigationController();
        nav.show();

        particles.start();

        const animator = new ScrollAnimator(presentation);

        initMouseGlow();

        const firstSection = document.querySelector('.section');
        if (firstSection) firstSection.classList.add('in-view');
    });
});

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
