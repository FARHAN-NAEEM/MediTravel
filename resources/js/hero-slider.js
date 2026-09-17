export default (count, interval) => ({
    count,
    interval,
    active: 0,
    reducedMotion: false,
    hidden: false,
    timer: null,
    touch: null,
    motionQuery: null,
    motionListener: null,

    init() {
        this.hidden = document.hidden;
        this.motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.reducedMotion = this.motionQuery.matches;
        this.motionListener = (event) => {
            this.reducedMotion = event.matches;
        };
        this.motionQuery.addEventListener('change', this.motionListener);
        this.restart();
    },

    restart() {
        clearInterval(this.timer);
        if (this.count < 2) return;
        this.timer = setInterval(() => {
            if (!this.reducedMotion && !this.hidden && !this.touch) {
                this.active = (this.active + 1) % this.count;
            }
        }, this.interval);
    },

    go(index) {
        if (this.count < 2) return;
        this.active = (index + this.count) % this.count;
        this.restart();
    },

    get indicatorStart() {
        return Math.min(Math.max(this.active - 2, 0), Math.max(this.count - 5, 0));
    },

    touchStart(event) {
        if (event.touches.length !== 1) return;
        this.touch = { x: event.touches[0].clientX, y: event.touches[0].clientY };
    },

    touchEnd(event) {
        if (!this.touch) return;
        const dx = event.changedTouches[0].clientX - this.touch.x;
        const dy = event.changedTouches[0].clientY - this.touch.y;
        if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy) * 1.5) {
            this.go(this.active + (dx < 0 ? 1 : -1));
        }
        this.touch = null;
    },

    destroy() {
        clearInterval(this.timer);
        this.motionQuery?.removeEventListener('change', this.motionListener);
    },
});
