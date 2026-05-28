<script setup>
import { useTheme } from '@/Composables/useTheme';

const { isDark, toggle } = useTheme();

defineProps({
    /**
     * Visual size of the toggle. 'sm' for compact placements (mobile drawer),
     * 'md' (default) for header.
     */
    size: { type: String, default: 'md' },
});
</script>

<template>
    <button
        type="button"
        @click="toggle"
        :aria-label="isDark ? 'Включить светлую тему' : 'Включить тёмную тему'"
        :title="isDark ? 'Светлая тема' : 'Тёмная тема'"
        class="theme-toggle group"
        :class="size === 'sm' ? 'theme-toggle--sm' : 'theme-toggle--md'"
    >
        <span class="theme-toggle__icon" aria-hidden="true">
            <!-- Sun -->
            <svg
                class="theme-toggle__sun"
                :class="{ 'is-active': !isDark }"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <circle cx="12" cy="12" r="4" />
                <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
            </svg>
            <!-- Moon -->
            <svg
                class="theme-toggle__moon"
                :class="{ 'is-active': isDark }"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79z" />
            </svg>
        </span>
    </button>
</template>

<style scoped>
.theme-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: rgb(var(--text-secondary));
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
    cursor: pointer;
    transition: color var(--dur-base) ease, border-color var(--dur-base) ease, background-color var(--dur-base) ease, transform var(--dur-base) ease;
}

.theme-toggle--md {
    width: 40px;
    height: 40px;
}

.theme-toggle--sm {
    width: 36px;
    height: 36px;
}

.theme-toggle:hover {
    color: rgb(var(--text-primary));
    border-color: var(--border-strong);
    transform: translateY(-1px);
}

.theme-toggle:focus-visible {
    outline: none;
    box-shadow: 0 0 0 4px var(--ring-focus);
}

.theme-toggle__icon {
    position: relative;
    display: block;
    width: 18px;
    height: 18px;
}

.theme-toggle__sun,
.theme-toggle__moon {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    transition: opacity var(--dur-base) ease, transform var(--dur-base) cubic-bezier(0.34, 1.56, 0.64, 1);
    transform-origin: center;
}

.theme-toggle__sun {
    opacity: 0;
    transform: rotate(-90deg) scale(0.6);
}
.theme-toggle__sun.is-active {
    opacity: 1;
    transform: rotate(0) scale(1);
}

.theme-toggle__moon {
    opacity: 0;
    transform: rotate(90deg) scale(0.6);
}
.theme-toggle__moon.is-active {
    opacity: 1;
    transform: rotate(0) scale(1);
}
</style>
