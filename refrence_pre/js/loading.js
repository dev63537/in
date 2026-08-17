/* ============================================
   InventMitra — Loading Screen Controller
   Animated progress bar with eased fill,
   triggers callback on completion
   ============================================ */

class LoadingScreen {
    constructor() {
        this.screen = document.getElementById('loading-screen');
        this.progressBar = document.getElementById('loading-progress');
        this.statusText = document.getElementById('loading-text');
        this.progress = 0;
        this.isComplete = false;
        this.onComplete = null;
    }

    /* Start the loading animation. 
       Calls onComplete when finished. */
    start(onComplete) {
        this.onComplete = onComplete;
        this.animateProgress();
    }

    animateProgress() {
        const duration = 2400; /* 2.4 seconds total */
        const startTime = performance.now();

        const update = (currentTime) => {
            const elapsed = currentTime - startTime;
            const linearProgress = Math.min(elapsed / duration, 1);

            /* Ease-out cubic for natural deceleration */
            const eased = 1 - Math.pow(1 - linearProgress, 3);
            const percentage = Math.floor(eased * 100);

            if (this.progressBar) {
                this.progressBar.style.width = `${eased * 100}%`;
            }

            if (this.statusText) {
                this.statusText.textContent = `${percentage}%`;
            }

            if (linearProgress < 1) {
                requestAnimationFrame(update);
            } else {
                this.complete();
            }
        };

        requestAnimationFrame(update);
    }

    complete() {
        if (this.isComplete) return;
        this.isComplete = true;

        /* Brief pause at 100% before hiding */
        setTimeout(() => {
            if (this.screen) {
                this.screen.classList.add('hidden');
            }

            /* Remove from layout after CSS transition ends */
            setTimeout(() => {
                if (this.screen) {
                    this.screen.style.display = 'none';
                }
                if (this.onComplete) {
                    this.onComplete();
                }
            }, 850);
        }, 350);
    }
}
