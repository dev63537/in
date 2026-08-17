/* ============================================
   Gujju Clothing — Loading Screen Controller
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

    start(onComplete) {
        this.onComplete = onComplete;
        this.animateProgress();
    }

    animateProgress() {
        const duration = 2400;
        const startTime = performance.now();

        const update = (currentTime) => {
            const elapsed = currentTime - startTime;
            const linearProgress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - linearProgress, 3);
            const percentage = Math.floor(eased * 100);

            if (this.progressBar) this.progressBar.style.width = `${eased * 100}%`;
            if (this.statusText) this.statusText.textContent = `${percentage}%`;

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

        setTimeout(() => {
            if (this.screen) this.screen.classList.add('hidden');

            setTimeout(() => {
                if (this.screen) this.screen.style.display = 'none';
                if (this.onComplete) this.onComplete();
            }, 850);
        }, 350);
    }
}
