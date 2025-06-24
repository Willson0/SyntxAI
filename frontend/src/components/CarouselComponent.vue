<script>
import config from "@/config.json"
export default {
    name: "CarouselComponent",
    data() {
        return {
            youtubeBtn: null,
            vkBtn: null,
            carousel: null,
            slides: [],
            // updatedSlides: [],
            currentSlide: 0,
            slideInterval: null,
            progressInterval: null,
            progress: 0,
            isHolding: false,
            isTransitioning: false,
            startX: 0,
            currentX: 0,
            updatedSlides: config.slides,
        };
    },
    mounted() {
        this.youtubeBtn = document.getElementById('youtubeBtn');
        this.vkBtn = document.getElementById('vkBtn');
        setInterval(this.toggleButtons, 3000);

        this.carousel = this.$refs.carousel;
        this.container = this.$refs.container;
        this.arrowContainer = this.$refs.arrowContainer;
        this.progressBar = this.$refs.progressBar;

        // const firstSlide = this.allSlides[0];
        // const lastSlide = this.allSlides[this.allSlides.length - 1];
        // this.updatedSlides = [lastSlide, ...this.allSlides, firstSlide];

        console.log(this.updatedSlides);  // Выведи updatedSlides, чтобы проверить правильность данных

        this.container.style.transform = `translateX(-100%)`;
        this.resetTimer();

        const tooltip = document.getElementById('emojiTooltip');
        // tooltip.addEventListener('click', this.toggleEmojiTooltip);
        document.addEventListener('click', this.hideEmojiTooltip);
    },
    beforeUnmount() {
        const tooltip = document.getElementById('emojiTooltip');

        tooltip.removeEventListener('click', this.toggleEmojiTooltip);
        document.removeEventListener('click', this.hideEmojiTooltip);
    },
    methods: {
        toggleButtons() {
            this.youtubeBtn.classList.toggle('hidden');
            this.vkBtn.classList.toggle('hidden');
        },
        showSlide(index, smooth = true) {
            if (this.isTransitioning) return;
            if (index > this.updatedSlides.length - 3) return this.showSlide(0);
            this.isTransitioning = true;
            this.currentSlide = index;

            const offset = -(this.currentSlide + 1) * 100;
            const container = this.$refs.container;
            container.style.transition = smooth ? 'transform 0.3s ease-in-out' : 'none';
            container.style.transform = `translateX(${offset}%)`;

            if (smooth) {
                const handler = (e) => {
                    if (e.propertyName !== 'transform') return;
                    this.onTransitionEnd();
                    container.removeEventListener('transitionend', handler);
                };
                container.addEventListener('transitionend', handler);
            } else {
                this.resetTimer();
                this.isTransitioning = false;
                if (this.currentSlide === 0) this.resetArrowAnimation();
            }
        },
        onTransitionEnd() {
            const container = this.$refs.container;
            if (this.currentSlide === -1) {
                this.currentSlide = this.updatedSlides.length - 3;
                container.style.transition = 'none';
                container.style.transform = `translateX(-${(this.currentSlide + 1) * 100}%)`;
            } else if (this.currentSlide === this.updatedSlides.length - 2) {
                this.currentSlide = 0;
                container.style.transition = 'none';
                container.style.transform = `translateX(-100%)`;
            }
            this.resetTimer();
            this.isTransitioning = false;
            if (this.currentSlide === 0) this.resetArrowAnimation();
        },
        nextSlide() {
            this.showSlide(this.currentSlide + 1);
        },
        previousSlide() {
            this.showSlide(this.currentSlide - 1);
        },
        resetTimer() {
            clearTimeout(this.slideInterval);
            clearInterval(this.progressInterval);
            const duration = parseInt(this.updatedSlides[this.currentSlide + 1]?.duration) || 3000;
            this.slideInterval = setTimeout(this.nextSlide, duration);
            this.startProgress(duration);
        },
        resetProgress() {
            this.progress = 0;
            this.$refs.progressBar.style.width = '0%';
        },
        startProgress(duration) {
            this.resetProgress();
            const interval = 16;
            const steps = duration / interval;
            let step = 0;
            this.progressInterval = setInterval(() => {
                step++;
                this.progress = (step / steps) * 100;
                this.$refs.progressBar.style.width = `${this.progress}%`;
                if (this.progress >= 100) clearInterval(this.progressInterval);
            }, interval);
        },
        resetArrowAnimation() {
            const arrowContainer = this.$refs.arrowContainer;
            arrowContainer.classList.remove('animate-arrows', 'fade-out-arrows');
            void arrowContainer.offsetWidth;
            arrowContainer.classList.add('animate-arrows');
        },
        startHolding(e) {
            e.preventDefault();

            this.isHolding = true;
            this.isTransitioning = false;
            clearTimeout(this.slideInterval);
            clearInterval(this.progressInterval);
            this.startX = e.type.includes('mouse') ? e.pageX : e.touches[0].pageX;
            this.currentX = this.startX;
            this.$refs.container.style.transition = 'none';
        },
        moveSlide(e) {
            if (!this.isHolding) return;
            this.currentX = e.type.includes('mouse') ? e.pageX : e.touches[0].pageX;
            const diff = this.currentX - this.startX;
            const movePercent = (diff / this.carousel.offsetWidth) * 100;
            this.$refs.container.style.transform = `translateX(calc(${-(this.currentSlide + 1) * 100}% + ${movePercent}%))`;
        },
        endHolding() {
            if (!this.isHolding) return;
            this.isHolding = false;
            const diff = this.currentX - this.startX;
            if (Math.abs(diff) > this.carousel.offsetWidth / 4) {
                diff > 0 ? this.previousSlide() : this.nextSlide();
            } else {
                this.showSlide(this.currentSlide);
            }
        },
        toggleEmojiTooltip(event) {
            const tooltip = document.getElementById('emojiTooltip');
            tooltip.classList.toggle('show-tooltip');
            event.stopPropagation();
        },
        hideEmojiTooltip(event) {
            const tooltip = document.getElementById('emojiTooltip');
            if (!tooltip.contains(event.target)) {
                tooltip.classList.remove('show-tooltip');
            }
        },
    }
};
</script>

<template>
    <div ref="carousel" class="carousel" @mousedown="startHolding" @mousemove="moveSlide" @mouseup="endHolding" @mouseleave="endHolding"
         @touchstart.passive="startHolding" @touchmove.prevent="moveSlide" @touchend="endHolding">
        <div class="carousel-container" ref="container">
            <a v-for="(slide, index) in updatedSlides" :key="index" :href="slide.href">
                <img :src="slide.src" :alt="slide.alt" :data-duration="slide.duration" :class="{ active: index === currentSlide + 1 }" />
            </a>
        </div>

        <div class="progress-bar">
            <div class="progress" ref="progressBar"></div>
        </div>

        <div class="arrow-container animate-arrows" @click="nextSlide" ref="arrowContainer">
            <div class="arrow arrow1"></div>
            <div class="arrow arrow2"></div>
            <div class="arrow arrow3"></div>
        </div>
    </div>
</template>

<style src="../assets/main.css" scoped>
</style>