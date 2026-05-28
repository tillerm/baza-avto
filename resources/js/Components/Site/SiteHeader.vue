<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ThemeToggle from '@/Components/Site/ThemeToggle.vue';
import MobileDrawer from '@/Components/Site/MobileDrawer.vue';

const page = usePage();
const isManager = computed(() => Boolean(page.props.access?.isManager));
const isAuthed = computed(() => Boolean(page.props.auth?.user));

const drawerOpen = ref(false);
const scrolled = ref(false);

const onScroll = () => {
    scrolled.value = window.scrollY > 12;
};

onMounted(() => {
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
});
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));

const logout = () => router.post(route('logout'));

const navItems = computed(() => ([
    { label: 'Главная', href: route('home'), match: 'home' },
    { label: 'Каталог', href: route('catalog.index'), match: 'catalog.index' },
    { label: 'Доставка', href: route('catalog.calculator'), match: 'catalog.calculator' },
    { label: 'О нас', href: route('about'), match: 'about' },
    { label: 'FAQ', href: route('faq'), match: 'faq' },
    { label: 'Отзывы', href: route('testimonials.index'), match: 'testimonials.index' },
]));

const isActive = (match) => {
    try { return route().current(match); } catch (_) { return false; }
};
</script>

<template>
    <header
        class="site-header"
        :class="{ 'is-scrolled': scrolled }"
    >
        <div class="site-header__inner max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-6 h-[72px] lg:h-[80px]">
                <!-- Logo -->
                <Link :href="route('home')" class="site-header__brand group" aria-label="БазаАвто">
                    <img
                        src="/images/logo-wide.png"
                        alt="БазаАвто"
                        class="site-header__brand-mark"
                    />
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden lg:flex items-center gap-1 ml-6" aria-label="Главное меню">
                    <Link
                        v-for="item in navItems"
                        :key="item.match"
                        :href="item.href"
                        class="site-nav-link"
                        :class="{ 'is-active': isActive(item.match) }"
                    >
                        <span>{{ item.label }}</span>
                    </Link>
                </nav>

                <div class="flex items-center gap-2 ml-auto">
                    <!-- Social (desktop only) -->
                    <div class="hidden md:flex items-center gap-2">
                        <a
                            href="https://t.me/+uXvETOFXQ99iMjUy"
                            target="_blank"
                            rel="noopener"
                            class="site-icon-link"
                            aria-label="Telegram"
                        >
                            <i class="pi pi-telegram"></i>
                        </a>
                    </div>

                    <ThemeToggle />

                    <!-- Auth dropdown -->
                    <div v-if="isAuthed" class="hidden lg:flex items-center">
                        <Dropdown align="right" width="56">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="site-icon-link"
                                    aria-label="Меню аккаунта"
                                >
                                    <i class="pi pi-user"></i>
                                </button>
                            </template>
                            <template #content>
                                <div class="block px-4 py-2 text-xs text-gray-500">Аккаунт</div>
                                <DropdownLink :href="route('profile.show')">Профиль</DropdownLink>
                                <DropdownLink :href="route('crm')">Админка</DropdownLink>
                                <div class="border-t border-gray-200" />
                                <form @submit.prevent="logout">
                                    <DropdownLink as="button">Выйти</DropdownLink>
                                </form>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- "Подобрать авто" CTA — desktop -->
                    <Link
                        :href="route('catalog.index')"
                        class="hidden xl:inline-flex premium-btn !py-2.5 !px-5 !text-sm ml-2"
                    >
                        Подобрать авто
                        <i class="pi pi-arrow-right text-[10px]"></i>
                    </Link>

                    <!-- Hamburger -->
                    <button
                        type="button"
                        class="site-burger lg:hidden"
                        @click="drawerOpen = true"
                        aria-label="Открыть меню"
                    >
                        <span></span><span></span><span></span>
                    </button>
                </div>
            </div>
        </div>

        <MobileDrawer
            v-model:open="drawerOpen"
            :nav-items="navItems"
            :is-authed="isAuthed"
            :is-manager="isManager"
            @logout="logout"
        />
    </header>
</template>

<style scoped>
.site-header {
    position: sticky;
    top: 0;
    z-index: 50;
    background: rgb(var(--bg-base) / 0.65);
    backdrop-filter: blur(24px) saturate(140%);
    -webkit-backdrop-filter: blur(24px) saturate(140%);
    border-bottom: 1px solid transparent;
    transition: border-color var(--dur-base) ease, background-color var(--dur-base) ease;
}

.site-header.is-scrolled {
    background: rgb(var(--bg-base) / 0.85);
    border-bottom-color: var(--border-subtle);
}

.site-header__brand {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    color: rgb(var(--text-primary));
}

.site-header__brand-mark {
    height: 44px;
    width: auto;
    display: block;
    transition: transform var(--dur-base) ease;
}

@media (min-width: 1024px) {
    .site-header__brand-mark {
        height: 52px;
    }
}

.site-header__brand:hover .site-header__brand-mark {
    transform: scale(1.04);
}

.site-nav-link {
    position: relative;
    display: inline-flex;
    align-items: center;
    height: 40px;
    padding: 0 0.875rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(var(--text-secondary));
    border-radius: 9999px;
    transition: color var(--dur-base) ease, background-color var(--dur-base) ease;
}

.site-nav-link::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: 4px;
    width: 0;
    height: 1.5px;
    background: rgb(var(--accent));
    border-radius: 2px;
    transform: translateX(-50%);
    transition: width var(--dur-base) cubic-bezier(0.16, 1, 0.3, 1);
}

.site-nav-link:hover {
    color: rgb(var(--text-primary));
}

.site-nav-link:hover::after {
    width: 18px;
}

.site-nav-link.is-active {
    color: rgb(var(--text-primary));
    background: var(--bg-glass);
}

.site-nav-link.is-active::after {
    width: 24px;
}

.site-icon-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 9999px;
    color: rgb(var(--text-secondary));
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    font-size: 1rem;
    transition: color var(--dur-base) ease, border-color var(--dur-base) ease, background-color var(--dur-base) ease, transform var(--dur-base) ease;
}

.site-icon-link:hover {
    color: rgb(var(--accent));
    border-color: var(--border-strong);
    transform: translateY(-1px);
}

.site-burger {
    position: relative;
    width: 40px;
    height: 40px;
    border-radius: 9999px;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    color: rgb(var(--text-primary));
    display: inline-flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 4px;
    transition: border-color var(--dur-base) ease;
}

.site-burger:hover {
    border-color: var(--border-strong);
}

.site-burger span {
    display: block;
    width: 16px;
    height: 1.5px;
    background: currentColor;
    border-radius: 2px;
}

.site-burger span:nth-child(2) {
    width: 12px;
}
</style>
