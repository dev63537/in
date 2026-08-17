/* ============================================
   Gujju Clothing — Particle System
   Canvas-based particle network background
   with mouse interaction and connections
   ============================================ */

class ParticleSystem {
    constructor(canvasId) {
        this.canvas = document.getElementById(canvasId);
        if (!this.canvas) return;

        this.ctx = this.canvas.getContext('2d');
        this.particles = [];
        this.mouse = { x: null, y: null, radius: 150 };
        this.isRunning = false;
        this.animationFrame = null;

        this.baseCount = 50;
        this.connectionDistance = 120;

        this.colors = [
            { r: 214, g: 156, b: 99 },   /* warm gold */
            { r: 180, g: 100, b: 80 },   /* terracotta */
            { r: 139, g: 92, b: 246 },   /* purple */
            { r: 15, g: 23, b: 42 },     /* dark slate */
        ];

        this.resize();
        this.addEventListeners();
    }

    resize() {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        this.canvas.width = window.innerWidth * dpr;
        this.canvas.height = window.innerHeight * dpr;
        this.canvas.style.width = window.innerWidth + 'px';
        this.canvas.style.height = window.innerHeight + 'px';
        this.ctx.scale(dpr, dpr);
        this.width = window.innerWidth;
        this.height = window.innerHeight;
    }

    createParticles() {
        this.particles = [];
        const area = this.width * this.height;
        const count = Math.min(Math.floor((area / (1920 * 1080)) * this.baseCount), 80);

        for (let i = 0; i < count; i++) {
            const color = this.colors[Math.floor(Math.random() * this.colors.length)];
            this.particles.push({
                x: Math.random() * this.width,
                y: Math.random() * this.height,
                vx: (Math.random() - 0.5) * 0.25,
                vy: (Math.random() - 0.5) * 0.25,
                radius: Math.random() * 1.8 + 0.5,
                color: color,
                opacity: Math.random() * 0.4 + 0.15,
            });
        }
    }

    addEventListeners() {
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.resize();
                this.createParticles();
            }, 250);
        });

        window.addEventListener('mousemove', (e) => {
            this.mouse.x = e.clientX;
            this.mouse.y = e.clientY;
        });

        window.addEventListener('mouseleave', () => {
            this.mouse.x = null;
            this.mouse.y = null;
        });
    }

    drawParticle(p) {
        this.ctx.beginPath();
        this.ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
        this.ctx.fillStyle = `rgba(${p.color.r}, ${p.color.g}, ${p.color.b}, ${p.opacity})`;
        this.ctx.fill();
    }

    drawConnections() {
        const len = this.particles.length;
        for (let i = 0; i < len; i++) {
            for (let j = i + 1; j < len; j++) {
                const dx = this.particles[i].x - this.particles[j].x;
                const dy = this.particles[i].y - this.particles[j].y;
                const distSq = dx * dx + dy * dy;
                const maxDistSq = this.connectionDistance * this.connectionDistance;

                if (distSq < maxDistSq) {
                    const opacity = (1 - distSq / maxDistSq) * 0.12;
                    this.ctx.beginPath();
                    this.ctx.strokeStyle = `rgba(214, 156, 99, ${opacity})`;
                    this.ctx.lineWidth = 0.5;
                    this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
                    this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
                    this.ctx.stroke();
                }
            }
        }
    }

    updateParticles() {
        this.particles.forEach((p) => {
            if (this.mouse.x !== null && this.mouse.y !== null) {
                const dx = p.x - this.mouse.x;
                const dy = p.y - this.mouse.y;
                const distSq = dx * dx + dy * dy;
                const radiusSq = this.mouse.radius * this.mouse.radius;

                if (distSq < radiusSq) {
                    const dist = Math.sqrt(distSq);
                    const force = (this.mouse.radius - dist) / this.mouse.radius;
                    p.vx += (dx / dist) * force * 0.015;
                    p.vy += (dy / dist) * force * 0.015;
                }
            }

            p.x += p.vx;
            p.y += p.vy;

            p.vx *= 0.998;
            p.vy *= 0.998;

            const speed = Math.sqrt(p.vx * p.vx + p.vy * p.vy);
            if (speed < 0.05) {
                p.vx += (Math.random() - 0.5) * 0.02;
                p.vy += (Math.random() - 0.5) * 0.02;
            }

            if (p.x < -20) p.x = this.width + 20;
            if (p.x > this.width + 20) p.x = -20;
            if (p.y < -20) p.y = this.height + 20;
            if (p.y > this.height + 20) p.y = -20;
        });
    }

    animate() {
        if (!this.isRunning) return;
        this.ctx.clearRect(0, 0, this.width, this.height);
        this.drawConnections();
        this.particles.forEach((p) => this.drawParticle(p));
        this.updateParticles();
        this.animationFrame = requestAnimationFrame(() => this.animate());
    }

    start() {
        if (!this.canvas) return;
        this.createParticles();
        this.isRunning = true;
        this.canvas.classList.add('visible');
        this.animate();
    }

    stop() {
        this.isRunning = false;
        if (this.animationFrame) cancelAnimationFrame(this.animationFrame);
        this.canvas.classList.remove('visible');
    }
}
