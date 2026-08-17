/* ============================================
   Gujju Clothing — Scroll Animation Engine
   IntersectionObserver-based reveal animations
   and counter animation utility
   ============================================ */

class ScrollAnimator {
    constructor(scrollContainer) {
        this.scrollContainer = scrollContainer || null;
        this.observers = [];
        this.init();
    }

    init() {
        this.setupElementObserver();
        this.setupSectionObserver();
        this.setupCounterObserver();
    }

    setupElementObserver() {
        const options = {
            root: this.scrollContainer,
            rootMargin: '0px 0px -60px 0px',
            threshold: 0.1,
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    const children = entry.target.querySelectorAll('[data-animate]');
                    children.forEach((child, index) => {
                        setTimeout(() => { child.classList.add('visible'); }, index * 100);
                    });
                }
            });
        }, options);

        const selectors = [
            '.animate-on-scroll',
            '.animate-fade-up',
            '.animate-fade-down',
            '.animate-fade-left',
            '.animate-fade-right',
            '.animate-scale',
            '.animate-blur',
        ];

        document.querySelectorAll(selectors.join(', ')).forEach((el) => {
            observer.observe(el);
        });

        this.observers.push(observer);
    }

    setupSectionObserver() {
        const options = {
            root: this.scrollContainer,
            rootMargin: '0px',
            threshold: 0.3,
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in-view');
                } else {
                    entry.target.classList.remove('in-view');
                }
            });
        }, options);

        document.querySelectorAll('.section').forEach((section) => {
            observer.observe(section);
        });

        this.observers.push(observer);
    }

    setupCounterObserver() {
        const options = {
            root: this.scrollContainer,
            rootMargin: '0px',
            threshold: 0.5,
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    this.animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, options);

        document.querySelectorAll('[data-count-to]').forEach((el) => {
            observer.observe(el);
        });

        this.observers.push(observer);
    }

    animateCounter(element) {
        const target = parseInt(element.dataset.countTo, 10);
        const duration = parseInt(element.dataset.countDuration, 10) || 2000;
        const suffix = element.dataset.countSuffix || '';
        const prefix = element.dataset.countPrefix || '';
        const startTime = performance.now();

        const update = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(target * eased);
            element.textContent = prefix + current.toLocaleString() + suffix;
            if (progress < 1) requestAnimationFrame(update);
        };

        requestAnimationFrame(update);
    }

    destroy() {
        this.observers.forEach((obs) => obs.disconnect());
        this.observers = [];
    }
}
