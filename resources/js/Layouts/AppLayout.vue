<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SiteHeader from '@/Components/Site/SiteHeader.vue';
import SiteFooter from '@/Components/Site/SiteFooter.vue';
import PageLoader from '@/Components/Site/PageLoader.vue';
import 'primeflex/primeflex.css';

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const locationMenuOpen = ref(false);
const locationMenu = ref(null);
const page = usePage();
const isManager = computed(() => Boolean(page.props.access?.isManager));

// CRM area uses the legacy header (admin sidebar + dropdown). Public pages
// receive the new premium SiteHeader/SiteFooter from the redesign.
const isCrmArea = computed(() => {
    try { return route().current('crm') || route().current('crm.*'); } catch (_) { return false; }
});

const logout = () => {
    router.post(route('logout'));
};

const onClickOutside = (event) => {
    if (locationMenu.value && !locationMenu.value.contains(event.target)) {
        locationMenuOpen.value = false;
    }
};

onMounted(() => document.addEventListener('click', onClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', onClickOutside));
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <!-- ========================= CRM AREA (legacy chrome) ========================= -->
        <div
            v-if="isCrmArea"
            class="min-h-screen bg-slate-50 flex flex-col crm-theme"
        >
            <nav class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-900 border-b border-white/10 shadow-lg relative">
                <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between min-h-[96px] py-4 gap-10">
                        <div class="flex items-center gap-12">
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('catalog.index')" class="flex items-center gap-3">
                                    <ApplicationMark class="block h-16 w-16 sm:h-20 sm:w-20 lg:h-14 lg:w-14" />
                                </Link>
                            </div>

                            <div class="hidden lg:flex items-center gap-3">
                                <NavLink :href="route('home')" :active="route().current('home')">Главная</NavLink>
                                <NavLink :href="route('catalog.index')" :active="route().current('catalog.index')">Каталог</NavLink>
                                <NavLink :href="route('catalog.calculator')" :active="route().current('catalog.calculator')">Доставка</NavLink>
                                <NavLink :href="route('about')" :active="route().current('about')">О нас</NavLink>
                                <NavLink :href="route('faq')" :active="route().current('faq')">FAQ</NavLink>
                                <NavLink :href="route('testimonials.index')" :active="route().current('testimonials.index')">Отзывы</NavLink>
                            </div>
                        </div>

                        <div class="hidden lg:flex items-center gap-3 text-white ml-auto">
                            <div class="flex items-center gap-2">
                                <a href="https://t.me/+uXvETOFXQ99iMjUy" target="_blank" rel="noopener" class="w-[41px] h-9 rounded-lg bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition" aria-label="Telegram">
                                    <i class="pi pi-telegram text-lg"></i>
                                </a>
                            </div>
                            <div v-if="$page.props.auth.user" class="flex items-center">
                                <Dropdown align="right" width="56">
                                    <template #trigger>
                                        <span class="inline-flex rounded-lg bg-white/10 hover:bg-white/15 shadow-sm overflow-hidden">
                                            <Link
                                                :href="route('crm')"
                                                class="inline-flex items-center justify-center px-3 h-9 text-sm font-medium text-white transition ease-in-out duration-150"
                                                aria-label="Админка"
                                                @click.stop
                                            >
                                                <i class="pi pi-cog text-lg"></i>
                                            </Link>
                                            <button
                                                type="button"
                                                class="inline-flex items-center justify-center px-2 h-9 text-sm font-medium text-white transition ease-in-out duration-150 border-l border-white/10"
                                                aria-label="Меню аккаунта"
                                            >
                                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="block px-4 py-2 text-xs text-gray-700">Аккаунт</div>
                                        <DropdownLink :href="route('profile.show')">Профиль</DropdownLink>
                                        <div class="px-4 pt-3 pb-1 text-[11px] uppercase text-gray-500">Админка</div>
                                        <DropdownLink :href="route('crm.brands.index')">1. Марки</DropdownLink>
                                        <DropdownLink :href="route('crm.models.index')">2. Модели</DropdownLink>
                                        <DropdownLink :href="route('crm.engines.index')">3. Двигатели</DropdownLink>
                                        <DropdownLink :href="route('crm.equipments.index')">4. Комплектации</DropdownLink>
                                        <DropdownLink :href="route('crm.cars.index')">5. Авто</DropdownLink>
                                        <DropdownLink :href="route('crm.countries.index')">Страны</DropdownLink>
                                        <template v-if="!isManager">
                                            <div class="px-3 pt-2 pb-1 text-[11px] uppercase text-gray-500">Отзывы</div>
                                            <DropdownLink :href="route('crm.testimonials.index')">Список отзывов</DropdownLink>
                                            <DropdownLink :href="route('crm.testimonials.create')">Создать отзыв</DropdownLink>
                                            <div class="px-3 pt-2 pb-1 text-[11px] uppercase text-gray-500">Команда</div>
                                            <DropdownLink :href="route('crm.team.index')">Команда</DropdownLink>
                                            <DropdownLink :href="route('crm.managers.index')">Менеджеры</DropdownLink>
                                        </template>
                                        <div class="border-t border-gray-200" />
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">Выйти</DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-mr-2 flex items-center lg:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-200 hover:bg-white/10 focus:outline-none" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg class="h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                    <path :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="lg:hidden space-y-3 border-t border-white/10 bg-slate-900 text-white">
                    <div class="space-y-1 pt-2">
                        <ResponsiveNavLink :href="route('home')" :active="route().current('home')">Главная</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('catalog.index')" :active="route().current('catalog.index')">Каталог</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('catalog.calculator')" :active="route().current('catalog.calculator')">Доставка</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('about')" :active="route().current('about')">О нас</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('faq')" :active="route().current('faq')">FAQ</ResponsiveNavLink>
                    </div>
                    <div v-if="$page.props.auth.user" class="space-y-1 border-t border-white/10 pt-2">
                        <div class="text-xs uppercase text-gray-300 px-4">CRM</div>
                        <ResponsiveNavLink :href="route('crm.managers.index')" :active="route().current('crm.managers.*')">Менеджеры</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('crm.brands.index')" :active="route().current('crm.brands.index')">Марки</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('crm.models.index')" :active="route().current('crm.models.index')">Модели</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('crm.equipments.index')" :active="route().current('crm.equipments.index')">Комплектации</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('crm.cars.index')" :active="route().current('crm.cars.index')">Авто</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('crm.engines.index')" :active="route().current('crm.engines.index')">Двигатели</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('crm.countries.index')" :active="route().current('crm.countries.index')">Страны</ResponsiveNavLink>
                        <ResponsiveNavLink v-if="!isManager" :href="route('crm.team.index')" :active="route().current('crm.team.index')">Команда</ResponsiveNavLink>
                    </div>

                    <div v-if="$page.props.auth.user" class="pt-4 pb-1 border-t border-white/10">
                        <div class="flex items-center px-4">
                            <div>
                                <div class="font-medium text-base text-white">{{ $page.props.auth.user.name }}</div>
                                <div class="font-medium text-sm text-gray-300">{{ $page.props.auth.user.email }}</div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">Профиль</ResponsiveNavLink>
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">Выйти</ResponsiveNavLink>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="flex-1">
                <div class="flex min-h-[calc(100vh-96px)]">
                    <aside class="hidden lg:block w-[260px] shrink-0 border-r border-slate-200 bg-white">
                        <div class="sticky top-0 p-4">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Админка</div>

                            <Link :href="route('crm.cars.create')" class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                <i class="pi pi-plus text-xs" />
                                Добавить авто
                            </Link>

                            <div class="mt-3 space-y-1">
                                <Link :href="route('crm.brands.index')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.brands.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Марки</Link>
                                <Link :href="route('crm.models.index')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.models.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Модели</Link>
                                <Link :href="route('crm.engines.index')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.engines.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Двигатели</Link>
                                <Link :href="route('crm.equipments.index')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.equipments.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Комплектации</Link>
                                <Link :href="route('crm.cars.index')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.cars.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Авто</Link>
                                <Link :href="route('crm.countries.index')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.countries.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Страны</Link>
                            </div>

                            <div v-if="!isManager" class="mt-6">
                                <div class="px-3 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Отзывы</div>
                                <div class="mt-2 space-y-1">
                                    <Link :href="route('crm.testimonials.index')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.testimonials.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Список отзывов</Link>
                                    <Link :href="route('crm.testimonials.create')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.testimonials.create') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Создать отзыв</Link>
                                </div>

                                <div class="mt-6 px-3 text-[11px] font-semibold uppercase tracking-wide text-slate-500">Команда</div>
                                <div class="mt-2 space-y-1">
                                    <Link :href="route('crm.team.index')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.team.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Команда</Link>
                                    <Link :href="route('crm.managers.index')" class="block rounded-lg px-3 py-2 text-sm font-medium transition" :class="route().current('crm.managers.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'">Менеджеры</Link>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <div class="flex-1 min-w-0">
                        <slot />
                    </div>
                </div>
            </main>

            <footer class="bg-slate-900 text-white py-6 mt-8">
                <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-300">
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <ApplicationMark class="h-7 w-2" />
                        <span>© {{ new Date().getFullYear() }} БазаАвто</span>
                    </div>
                    <div class="flex gap-4">
                        <Link :href="route('catalog.index')" class="hover:text-white">Каталог</Link>
                        <Link :href="route('catalog.calculator')" class="hover:text-white">Доставка</Link>
                        <Link :href="route('about')" class="hover:text-white">О нас</Link>
                        <Link :href="route('testimonials.index')" class="hover:text-white">Отзывы</Link>
                        <Link :href="route('faq')" class="hover:text-white">FAQ</Link>
                    </div>
                </div>
            </footer>
        </div>

        <!-- ========================= PUBLIC AREA (premium redesign) ========================= -->
        <div v-else class="min-h-screen flex flex-col bg-bg-base text-ink-primary">
            <PageLoader />

            <SiteHeader />

            <main class="flex-1">
                <slot />
            </main>

            <SiteFooter />
        </div>
    </div>
</template>
