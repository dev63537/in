/* ============================================
   Gujju Clothing — Navigation Controller
   ============================================ */

class NavigationController {
    constructor() {
        this.nav = document.getElementById('main-nav');
        this.sectionIndicator = document.getElementById('current-section');
        this.progressBar = document.getElementById('nav-progress');
        this.sectionCounter = document.getElementById('section-counter');
        this.hamburger = document.getElementById('hamburger-btn');
        this.mobileMenu = document.getElementById('mobile-menu');
        this.mobileOverlay = document.getElementById('mobile-menu-overlay');
        this.presentation = document.getElementById('presentation');

        this.sections = [];
        this.currentSectionIndex = 0;
        this.isMenuOpen = false;
        this.ticking = false;

        this.init();
    }

    init() {
        this.sections = Array.from(document.querySelectorAll('.section'));
        if (this.sections.length > 0) this.onSectionChange(0);
        this.setupScrollTracking();
        this.setupMobileMenu();
        this.setupKeyboardNavigation();
        this.setupMenuLinks();
    }

    show() { if (this.nav) this.nav.classList.add('visible'); }
    hide() { if (this.nav) this.nav.classList.remove('visible'); }

    setupScrollTracking() {
        if (!this.presentation) return;

        this.presentation.addEventListener('scroll', () => {
            if (!this.ticking) {
                requestAnimationFrame(() => {
                    this.updateProgress();
                    this.updateCurrentSection();
                    this.ticking = false;
                });
                this.ticking = true;
            }
        }, { passive: true });
    }

    updateProgress() {
        if (!this.presentation || !this.progressBar) return;
        const scrollTop = this.presentation.scrollTop;
        const scrollHeight = this.presentation.scrollHeight - this.presentation.clientHeight;
        const progress = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
        this.progressBar.style.width = `${progress}%`;
    }

    updateCurrentSection() {
        if (!this.presentation) return;
        const scrollPosition = this.presentation.scrollTop + window.innerHeight * 0.4;

        for (let i = this.sections.length - 1; i >= 0; i--) {
            if (scrollPosition >= this.sections[i].offsetTop) {
                if (this.currentSectionIndex !== i) {
                    this.currentSectionIndex = i;
                    this.onSectionChange(i);
                }
                break;
            }
        }
    }

    onSectionChange(index) {
        const section = this.sections[index];
        const sectionName = section.dataset.sectionName || 'Section';

        if (this.sectionIndicator) {
            this.sectionIndicator.style.opacity = '0';
            this.sectionIndicator.style.transform = 'translateY(-8px)';
            setTimeout(() => {
                this.sectionIndicator.textContent = sectionName;
                this.sectionIndicator.style.opacity = '1';
                this.sectionIndicator.style.transform = 'translateY(0)';
            }, 200);
        }

        if (this.sectionCounter) {
            const current = String(index + 1).padStart(2, '0');
            const total = String(this.sections.length).padStart(2, '0');
            this.sectionCounter.textContent = `${current} / ${total}`;
        }

        this.updateMenuActiveState(index);
    }

    setupMobileMenu() {
        if (this.hamburger) this.hamburger.addEventListener('click', () => this.toggleMenu());
        if (this.mobileOverlay) this.mobileOverlay.addEventListener('click', () => this.closeMenu());
    }

    setupMenuLinks() {
        if (!this.mobileMenu) return;
        this.mobileMenu.querySelectorAll('a[data-section]').forEach((link) => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.scrollToSection(link.dataset.section);
                this.closeMenu();
            });
        });
    }

    toggleMenu() { this.isMenuOpen ? this.closeMenu() : this.openMenu(); }

    openMenu() {
        this.isMenuOpen = true;
        if (this.hamburger) this.hamburger.classList.add('active');
        if (this.mobileMenu) this.mobileMenu.classList.add('open');
        if (this.mobileOverlay) this.mobileOverlay.classList.add('open');
    }

    closeMenu() {
        this.isMenuOpen = false;
        if (this.hamburger) this.hamburger.classList.remove('active');
        if (this.mobileMenu) this.mobileMenu.classList.remove('open');
        if (this.mobileOverlay) this.mobileOverlay.classList.remove('open');
    }

    updateMenuActiveState(index) {
        if (!this.mobileMenu) return;
        this.mobileMenu.querySelectorAll('a').forEach((link, i) => {
            link.classList.toggle('active', i === index);
        });
    }

    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            if (this.isMenuOpen) { if (e.key === 'Escape') this.closeMenu(); return; }
            switch (e.key) {
                case 'ArrowDown': case 'PageDown': case ' ':
                    e.preventDefault(); this.navigateToSection(this.currentSectionIndex + 1); break;
                case 'ArrowUp': case 'PageUp':
                    e.preventDefault(); this.navigateToSection(this.currentSectionIndex - 1); break;
                case 'Home':
                    e.preventDefault(); this.navigateToSection(0); break;
                case 'End':
                    e.preventDefault(); this.navigateToSection(this.sections.length - 1); break;
            }
        });
    }

    navigateToSection(index) {
        if (index < 0 || index >= this.sections.length) return;
        this.sections[index].scrollIntoView({ behavior: 'smooth' });
    }

    scrollToSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) section.scrollIntoView({ behavior: 'smooth' });
    }
}
