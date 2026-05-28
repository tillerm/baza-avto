<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    /** Minimum visible duration in ms — keeps short flashes from feeling janky. */
    minDuration: { type: Number, default: 450 },
});

const visible = ref(true);              // shown on first paint, hidden after window load
const navigating = ref(false);          // shown during Inertia navigation
const showStartedAt = ref(Date.now());

const isShown = computed(() => visible.value || navigating.value);

let lockedScrollY = 0;
let isScrollLocked = false;
let hideTimer = null;

const lockScroll = () => {
    if (typeof document === 'undefined' || isScrollLocked) return;
    lockedScrollY = window.scrollY || document.documentElement.scrollTop || 0;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${lockedScrollY}px`;
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';
    document.body.style.overflow = 'hidden';
    isScrollLocked = true;
};

const unlockScroll = () => {
    if (typeof document === 'undefined' || !isScrollLocked) return;
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    window.scrollTo(0, lockedScrollY);
    isScrollLocked = false;
};

watch(isShown, (val) => {
    if (val) lockScroll();
    else unlockScroll();
}, { immediate: true });

const hideInitial = () => {
    if (hideTimer) clearTimeout(hideTimer);
    const elapsed = Date.now() - showStartedAt.value;
    const wait = Math.max(0, props.minDuration - elapsed);
    hideTimer = setTimeout(() => { visible.value = false; }, wait);
};

const onStart = () => {
    showStartedAt.value = Date.now();
    navigating.value = true;
};

const onFinish = () => {
    const elapsed = Date.now() - showStartedAt.value;
    const wait = Math.max(0, props.minDuration - elapsed);
    setTimeout(() => { navigating.value = false; }, wait);
};

let unsubStart = null;
let unsubFinish = null;

onMounted(() => {
    if (typeof window === 'undefined') return;

    if (document.readyState === 'complete') {
        hideInitial();
    } else {
        window.addEventListener('load', hideInitial, { once: true });
        // Fallback if window.load is delayed by slow third-party assets.
        setTimeout(hideInitial, 2500);
    }

    // Inertia router events for SPA navigation.
    unsubStart = router.on('start', onStart);
    unsubFinish = router.on('finish', onFinish);
});

onBeforeUnmount(() => {
    if (hideTimer) clearTimeout(hideTimer);
    if (unsubStart) unsubStart();
    if (unsubFinish) unsubFinish();
    unlockScroll();
});
</script>

<template>
    <Teleport to="body">
        <Transition name="loader-fade">
            <div v-if="isShown" class="page-loader" role="status" aria-live="polite" aria-label="Загрузка">
                <div class="page-loader__bg" aria-hidden="true">
                    <div class="page-loader__glow"></div>
                </div>
                <div class="page-loader__inner">
                    <div class="page-loader__mark">
                        <span class="page-loader__ring"></span>
                        <span class="page-loader__ring page-loader__ring--2"></span>
                        <span class="page-loader__core"></span>
                    </div>
                    <div class="page-loader__text">
                        <span class="page-loader__brand">БазаАвто</span>
                        <span class="page-loader__hint">Загрузка</span>
                    </div>
                </div>
                <div class="page-loader__progress" aria-hidden="true">
                    <div class="page-loader__progress-bar"></div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.page-loader {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 2rem;
    background: rgb(var(--bg-base, 9 9 11));
    color: rgb(var(--text-primary, 250 250 250));
    overscroll-behavior: contain;
}

.page-loader__bg {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}

.page-loader__glow {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 80vw;
    height: 80vw;
    max-width: 800px;
    max-height: 800px;
    transform: translate(-50%, -50%);
    background: radial-gradient(circle, rgba(239, 68, 68, 0.18), transparent 60%);
    filter: blur(60px);
    animation: loader-pulse 2.4s ease-in-out infinite;
}

@keyframes loader-pulse {
    0%, 100% { opacity: 0.6; transform: translate(-50%, -50%) scale(1); }
    50%      { opacity: 1; transform: translate(-50%, -50%) scale(1.15); }
}

.page-loader__inner {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
}

.page-loader__mark {
    position: relative;
    width: 64px;
    height: 64px;
}

.page-loader__ring {
    position: absolute;
    inset: 0;
    border-radius: 9999px;
    border: 2px solid transparent;
    border-top-color: rgb(var(--accent, 239 68 68));
    border-right-color: rgba(239, 68, 68, 0.5);
    animation: loader-spin 1.1s cubic-bezier(0.4, 0.1, 0.5, 0.9) infinite;
}

.page-loader__ring--2 {
    inset: 8px;
    border-top-color: rgba(239, 68, 68, 0.4);
    border-right-color: transparent;
    animation: loader-spin 1.6s cubic-bezier(0.4, 0.1, 0.5, 0.9) infinite reverse;
}

@keyframes loader-spin {
    to { transform: rotate(360deg); }
}

.page-loader__core {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    background: rgb(var(--accent, 239 68 68));
    transform: translate(-50%, -50%);
    box-shadow: 0 0 16px rgba(239, 68, 68, 0.6);
}

.page-loader__text {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.375rem;
}

.page-loader__brand {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: 1.125rem;
    letter-spacing: 0.04em;
    color: rgb(var(--text-primary, 250 250 250));
}

.page-loader__hint {
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgb(var(--text-muted, 113 113 122));
}

.page-loader__progress {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(255, 255, 255, 0.04);
    overflow: hidden;
}

.page-loader__progress-bar {
    width: 35%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgb(var(--accent, 239 68 68)), transparent);
    animation: loader-slide 1.4s cubic-bezier(0.4, 0.1, 0.5, 0.9) infinite;
}

@keyframes loader-slide {
    0%   { transform: translateX(-100%); }
    100% { transform: translateX(360%); }
}

/* Transition */
.loader-fade-enter-active { transition: opacity var(--dur-fast, 150ms) ease; }
.loader-fade-leave-active { transition: opacity var(--dur-base, 300ms) ease; }
.loader-fade-enter-from,
.loader-fade-leave-to { opacity: 0; }
</style>
