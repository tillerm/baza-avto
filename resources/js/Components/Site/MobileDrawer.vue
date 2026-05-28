<script setup>
import { computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    open: { type: Boolean, default: false },
    navItems: { type: Array, default: () => [] },
    isAuthed: { type: Boolean, default: false },
    isManager: { type: Boolean, default: false },
});

const emit = defineEmits(['update:open', 'logout']);

const close = () => emit('update:open', false);

watch(() => props.open, (val) => {
    if (typeof document === 'undefined') return;
    document.body.style.overflow = val ? 'hidden' : '';
});

const isActive = (match) => {
    try { return route().current(match); } catch (_) { return false; }
};

const social = computed(() => ([
    { label: 'Telegram', href: 'https://t.me/+uXvETOFXQ99iMjUy', icon: 'pi-telegram' },
]));
</script>

<template>
    <Teleport to="body">
        <Transition name="drawer-fade">
            <div v-if="open" class="drawer-overlay" @click="close" aria-hidden="true" />
        </Transition>
        <Transition name="drawer-slide">
            <aside
                v-if="open"
                class="drawer"
                role="dialog"
                aria-modal="true"
                aria-label="Меню навигации"
            >
                <div class="drawer__head">
                    <span class="section-eyebrow">Меню</span>
                    <button
                        type="button"
                        class="drawer__close"
                        aria-label="Закрыть меню"
                        @click="close"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <nav class="drawer__nav" aria-label="Главное меню">
                    <Link
                        v-for="item in navItems"
                        :key="item.match"
                        :href="item.href"
                        class="drawer__link"
                        :class="{ 'is-active': isActive(item.match) }"
                        @click="close"
                    >
                        <span>{{ item.label }}</span>
                        <i class="pi pi-arrow-right text-[12px]"></i>
                    </Link>
                </nav>

                <div class="drawer__cta">
                    <Link
                        :href="route('catalog.index')"
                        class="premium-btn w-full !justify-between"
                        @click="close"
                    >
                        Подобрать авто
                        <i class="pi pi-arrow-right text-[12px]"></i>
                    </Link>
                </div>

                <div v-if="isAuthed" class="drawer__section">
                    <div class="section-eyebrow">Аккаунт</div>
                    <div class="drawer__sublist">
                        <Link :href="route('profile.show')" class="drawer__sublink" @click="close">Профиль</Link>
                        <Link :href="route('crm')" class="drawer__sublink" @click="close">Админка</Link>
                        <button type="button" class="drawer__sublink text-left" @click="emit('logout'); close()">Выйти</button>
                    </div>
                </div>

                <div class="drawer__section">
                    <div class="section-eyebrow">Связь</div>
                    <div class="drawer__social">
                        <a
                            v-for="s in social"
                            :key="s.label"
                            :href="s.href"
                            target="_blank"
                            rel="noopener"
                            class="drawer__social-link"
                            :aria-label="s.label"
                        >
                            <i class="pi" :class="s.icon"></i>
                            <span>{{ s.label }}</span>
                        </a>
                    </div>
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>

<style scoped>
.drawer-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(8px);
    z-index: 60;
}

.drawer {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: min(86vw, 380px);
    background: rgb(var(--bg-elevated));
    color: rgb(var(--text-primary));
    border-left: 1px solid var(--border-subtle);
    box-shadow: var(--shadow-elevation);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding: 1.25rem 1.25rem 2rem;
    z-index: 61;
    overflow-y: auto;
    overscroll-behavior: contain;
}

.drawer__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--border-subtle);
}

.drawer__close {
    width: 36px;
    height: 36px;
    border-radius: 9999px;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    color: rgb(var(--text-secondary));
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: color var(--dur-base) ease, border-color var(--dur-base) ease;
}

.drawer__close:hover {
    color: rgb(var(--text-primary));
    border-color: var(--border-strong);
}

.drawer__close svg {
    width: 16px;
    height: 16px;
}

.drawer__nav {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
    margin-top: 0.5rem;
}

.drawer__link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.875rem 1rem;
    font-family: 'Manrope', 'Inter', sans-serif;
    font-size: 1.125rem;
    font-weight: 600;
    letter-spacing: -0.01em;
    color: rgb(var(--text-primary));
    border-radius: var(--radius-md);
    border: 1px solid transparent;
    transition: background-color var(--dur-base) ease, border-color var(--dur-base) ease, color var(--dur-base) ease, transform var(--dur-base) ease;
}

.drawer__link i {
    color: rgb(var(--text-muted));
    transition: transform var(--dur-base) ease, color var(--dur-base) ease;
}

.drawer__link:hover {
    background: var(--bg-glass);
    border-color: var(--border-subtle);
}

.drawer__link:hover i {
    color: rgb(var(--accent));
    transform: translateX(2px);
}

.drawer__link.is-active {
    background: var(--accent-soft);
    color: rgb(var(--accent));
    border-color: rgb(var(--accent) / 0.3);
}

.drawer__link.is-active i {
    color: rgb(var(--accent));
}

.drawer__cta {
    margin-top: auto;
}

.drawer__section {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-subtle);
}

.drawer__sublist {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.drawer__sublink {
    display: block;
    padding: 0.625rem 0.75rem;
    font-size: 0.9375rem;
    color: rgb(var(--text-secondary));
    border-radius: var(--radius-sm);
    transition: background-color var(--dur-base) ease, color var(--dur-base) ease;
}

.drawer__sublink:hover {
    background: var(--bg-glass);
    color: rgb(var(--text-primary));
}

.drawer__social {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.5rem;
}

.drawer__social-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(var(--text-primary));
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    transition: border-color var(--dur-base) ease, color var(--dur-base) ease;
}

.drawer__social-link:hover {
    border-color: rgb(var(--accent) / 0.5);
    color: rgb(var(--accent));
}

.drawer__social-link i {
    font-size: 1.125rem;
}

/* Transitions */
.drawer-fade-enter-active,
.drawer-fade-leave-active {
    transition: opacity var(--dur-base) ease;
}
.drawer-fade-enter-from,
.drawer-fade-leave-to {
    opacity: 0;
}

.drawer-slide-enter-active,
.drawer-slide-leave-active {
    transition: transform var(--dur-base) cubic-bezier(0.16, 1, 0.3, 1);
}
.drawer-slide-enter-from,
.drawer-slide-leave-to {
    transform: translateX(100%);
}
</style>
