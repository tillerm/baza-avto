<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PhotoSlider from '@/Components/PhotoSlider.vue';
import CarCard from '@/Components/Site/CarCard.vue';
import { computed, ref } from 'vue';
import moment from 'moment/moment';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    car: Object,
    fuels: Object,
    bodies: Object,
});

const photos = computed(() => Object.values(props.car.photos || []));
const activeIndex = ref(0);
const mainPhoto = computed(() => photos.value[activeIndex.value]?.photo ?? null);
const isSliderOpen = ref(false);
const carYear = computed(() => (props.car.release_date ? moment(props.car.release_date).format('YYYY') : '—'));
const metaChips = computed(() => [
    { icon: 'pi pi-calendar', label: 'Год', value: carYear.value },
    { icon: 'pi pi-compass', label: 'Пробег', value: props.car.mileage ? `${props.car.mileage} км` : 'Без пробега' },
    { icon: 'pi pi-palette', label: 'Цвет', value: props.car.color ?? '—' },
]);
const similarCars = computed(() => props.car.similar ?? []);
const manager = computed(() => props.car.manager ?? null);
const managerInitial = computed(() => (manager.value?.name?.[0] ?? 'M').toUpperCase());
const managerTelegramUsername = computed(() => (manager.value?.telegram_username ?? '').replace(/^@/, ''));
const managerTelegramLink = computed(() => (managerTelegramUsername.value ? `https://t.me/${managerTelegramUsername.value}` : null));

const formatCurrency = (value) =>
    value || value === 0
        ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(value)
        : '';

const specs = computed(() => [
    {
        icon: 'pi pi-bolt',
        label: 'Двигатель',
        value: `${props.car.supply.equipment.engine.fuel ? props.fuels[props.car.supply.equipment.engine.fuel] : ''}, ${props.car.supply.equipment.engine.capacity} см³`,
    },
    { icon: 'pi pi-palette', label: 'Цвет', value: props.car.color ?? '—' },
    { icon: 'pi pi-compass', label: 'Пробег', value: props.car.mileage ? `${props.car.mileage} км` : 'Без пробега' },
    { icon: 'pi pi-star', label: 'Комплектация', value: props.car.supply.equipment.name },
]);

const user = computed(() => usePage().props.auth?.user ?? null);
const pinLoading = ref(false);

function pinCar() {
    pinLoading.value = true;
    router.post(route('catalog.pin', { id: props.car.id }), {}, {
        preserveScroll: true,
        onFinish: () => { pinLoading.value = false; },
    });
}
</script>

<template>
    <AppLayout :title="`${car.supply.equipment.name}`">
        <div class="catalog-show">
            <div class="catalog-show__bg" aria-hidden="true">
                <div class="absolute -top-1/3 -right-1/4 w-[60vw] h-[60vw] rounded-full opacity-20" style="background: radial-gradient(circle, rgba(239,68,68,0.30), transparent 60%);"></div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 pb-20 lg:pb-28 relative space-y-10">
                <Link :href="route('catalog.index')" class="back-link">
                    <i class="pi pi-arrow-left text-xs"></i>
                    Назад к каталогу
                </Link>

                <!-- Title block -->
                <header class="space-y-4">
                    <span class="section-eyebrow">Каталог · БазаАвто</span>
                    <h1 class="display-heading text-4xl sm:text-5xl lg:text-6xl text-ink-primary">
                        {{ car.supply.equipment.name }}<span v-if="carYear !== '—'">, <span class="text-accent">{{ carYear }}</span></span>
                    </h1>
                    <div class="flex flex-wrap gap-2">
                        <span v-for="chip in metaChips" :key="chip.label" class="show-chip">
                            <i :class="chip.icon" class="text-accent text-[11px]"></i>
                            <span class="show-chip__label">{{ chip.label }}</span>
                            <span class="show-chip__value">{{ chip.value }}</span>
                        </span>
                    </div>
                </header>

                <!-- Gallery + sticky right column -->
                <div class="grid lg:grid-cols-[1.4fr_0.95fr] gap-8">
                    <div class="space-y-4 min-w-0">
                        <div class="show-gallery group" @click="mainPhoto && (isSliderOpen = true)">
                            <div class="show-gallery__top-row">
                                <span class="show-gallery__pill">
                                    <i class="pi pi-images text-[11px]"></i>
                                    Галерея
                                </span>
                                <span v-if="photos.length > 1" class="show-gallery__counter">
                                    {{ activeIndex + 1 }} / {{ photos.length }}
                                </span>
                            </div>
                            <img
                                v-if="mainPhoto"
                                :src="'/storage/' + mainPhoto"
                                alt="Фото автомобиля"
                                class="show-gallery__img"
                            />
                            <div v-else class="show-gallery__placeholder">
                                <i class="pi pi-image"></i>
                                <span>Фото отсутствуют</span>
                            </div>
                            <div v-if="mainPhoto" class="show-gallery__zoom">
                                <i class="pi pi-search-plus text-[11px]"></i>
                                Увеличить
                            </div>
                        </div>

                        <div v-if="photos.length > 1" class="flex flex-wrap gap-2">
                            <button
                                v-for="(photo, index) in photos"
                                :key="photo.id ?? photo.photo"
                                @click="activeIndex = index"
                                class="show-thumb"
                                :class="{ 'is-active': activeIndex === index }"
                                aria-label="Открыть фото"
                            >
                                <img :src="'/storage/' + photo.photo" alt="Миниатюра" />
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4 lg:sticky lg:top-[100px] lg:self-start">
                        <!-- Pricing card -->
                        <div class="price-card">
                            <div class="price-card__bg" aria-hidden="true"></div>
                            <div class="relative space-y-5">
                                <div class="flex items-center justify-between">
                                    <span class="price-card__eyebrow">Цена</span>
                                    <span class="price-card__badge">
                                        <span class="price-card__badge-dot"></span>
                                        В наличии
                                    </span>
                                </div>
                                <div class="price-card__value">
                                    {{ formatCurrency(car.car_price ? car.car_price : car.price) || 'По запросу' }}
                                </div>
                                <div v-if="car.customs" class="price-card__row">
                                    <span>Таможенные пошлины</span>
                                    <span class="font-semibold">{{ formatCurrency(car.customs) }}</span>
                                </div>
                                <p class="price-card__note">
                                    Поможем с доставкой, таможней и постановкой на учёт.
                                </p>

                                <div v-if="manager" class="price-card__manager">
                                    <span class="price-card__manager-avatar">{{ managerInitial }}</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="price-card__manager-label">Ваш менеджер</div>
                                        <div class="price-card__manager-name">{{ manager.name ?? 'Admin' }}</div>
                                    </div>
                                </div>

                                <a
                                    v-if="managerTelegramLink"
                                    :href="managerTelegramLink"
                                    target="_blank"
                                    rel="noopener"
                                    class="premium-btn w-full !justify-center"
                                    data-metrika-goal="car_telegram_click"
                                    :data-metrika-car-id="car.id"
                                    :data-metrika-brand="car.supply.equipment.name"
                                    :data-metrika-model="car.supply.equipment.engine?.name ?? ''"
                                >
                                    <i class="pi pi-telegram"></i>
                                    Написать в Telegram
                                </a>
                                <div v-else class="price-card__no-tg">
                                    <i class="pi pi-info-circle text-xs"></i>
                                    У менеджера не указан Telegram
                                </div>
                            </div>
                        </div>

                        <!-- Specs (Porsche-like dl with hairlines) -->
                        <div class="specs-card">
                            <div class="flex items-center justify-between">
                                <h3 class="font-display font-bold text-base text-ink-primary">Характеристики</h3>
                                <span class="specs-card__badge">
                                    <span class="specs-card__badge-dot"></span>
                                    Актуально
                                </span>
                            </div>
                            <dl class="specs-dl">
                                <div v-for="item in specs" :key="item.label" class="specs-row">
                                    <dt>
                                        <i :class="item.icon" class="text-accent text-sm"></i>
                                        <span>{{ item.label }}</span>
                                    </dt>
                                    <dd>{{ item.value }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Calculator CTA -->
                        <div class="cta-card">
                            <div class="flex items-start gap-3">
                                <span class="cta-card__icon"><i class="pi pi-calculator"></i></span>
                                <div>
                                    <h3 class="font-display font-bold text-base text-ink-primary">Нужен расчёт доставки?</h3>
                                    <p class="text-sm text-ink-secondary mt-1 leading-relaxed">
                                        Подскажем точные сроки и стоимость, подготовим договор и возьмём на себя оформление.
                                    </p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 mt-4">
                                <Link
                                    :href="route('catalog.calculator')"
                                    class="premium-btn !py-2.5 !px-5 !text-sm"
                                    data-metrika-goal="calculator_open"
                                    data-metrika-location="catalog_show"
                                    :data-metrika-car-id="car.id"
                                >
                                    Рассчитать стоимость
                                    <i class="pi pi-arrow-right text-[10px]"></i>
                                </Link>
                                <button
                                    v-if="user && user.id === car.manager_id"
                                    @click="pinCar"
                                    :disabled="pinLoading"
                                    class="premium-btn-secondary !py-2.5 !px-5 !text-sm"
                                >
                                    <i class="pi pi-arrow-up text-[10px]"></i>
                                    Підняти
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Similar cars -->
                <section v-if="similarCars.length" class="space-y-6 pt-6">
                    <div class="flex items-end justify-between gap-4 flex-wrap">
                        <div>
                            <span class="section-eyebrow">Похожие предложения</span>
                            <h2 class="display-heading text-3xl sm:text-4xl text-ink-primary mt-2">Возможно, вам понравится</h2>
                        </div>
                        <Link :href="route('catalog.index')" class="premium-btn-secondary !py-2.5 !px-5 !text-sm">
                            Весь каталог
                            <i class="pi pi-arrow-right text-[10px]"></i>
                        </Link>
                    </div>
                    <div class="similar-grid">
                        <CarCard
                            v-for="similar in similarCars"
                            :key="similar.id"
                            :car="{
                                id: similar.id,
                                slug: similar.id,
                                thumbnail_url: similar.photo ? '/storage/' + similar.photo : null,
                                model: { name: similar.name },
                                brand: { name: similar.brand?.name || '' },
                                year: similar.release_date ? String(similar.release_date).substring(0, 4) : null,
                                transmission: similar.transmission || null,
                                price: similar.price ?? null,
                                badge: 'Под заказ',
                            }"
                        />
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>

    <PhotoSlider :show="isSliderOpen" :photos="photos" :start-index="activeIndex" @close="isSliderOpen = false" />
</template>

<style scoped>
.similar-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1.5rem;
}

@media (min-width: 640px) {
    .similar-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (min-width: 1024px) {
    .similar-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}

.catalog-show {
    position: relative;
    min-height: 100vh;
    overflow: hidden;
    background: rgb(var(--bg-base));
}

.catalog-show__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(var(--text-secondary));
    transition: color var(--dur-base) ease, gap var(--dur-base) ease;
}

.back-link:hover {
    color: rgb(var(--accent));
    gap: 0.75rem;
}

.show-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
    font-size: 0.8125rem;
}

.show-chip__label {
    color: rgb(var(--text-muted));
    font-weight: 500;
}

.show-chip__value {
    color: rgb(var(--text-primary));
    font-weight: 600;
}

/* Gallery */
.show-gallery {
    position: relative;
    aspect-ratio: 16 / 11;
    border-radius: var(--radius-xl);
    overflow: hidden;
    background: rgb(var(--surface-1));
    border: 1px solid var(--border-subtle);
    cursor: zoom-in;
}

.show-gallery__top-row {
    position: absolute;
    top: 1rem;
    left: 1rem;
    right: 1rem;
    z-index: 5;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.show-gallery__pill {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    background: rgb(var(--bg-elevated) / 0.9);
    backdrop-filter: blur(12px);
    border: 1px solid var(--border-subtle);
    color: rgb(var(--text-primary));
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.04em;
}

.show-gallery__counter {
    display: inline-flex;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(12px);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 600;
}

.show-gallery__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--dur-slow) cubic-bezier(0.16, 1, 0.3, 1);
}

.show-gallery:hover .show-gallery__img { transform: scale(1.03); }

.show-gallery__placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: rgb(var(--text-muted));
}

.show-gallery__placeholder i { font-size: 2rem; }

.show-gallery__zoom {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    border-radius: 9999px;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(12px);
    color: #fff;
    font-size: 0.75rem;
    opacity: 0;
    transition: opacity var(--dur-base) ease;
}

.show-gallery:hover .show-gallery__zoom { opacity: 1; }

.show-thumb {
    flex-shrink: 0;
    width: 92px;
    height: 64px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    border: 2px solid transparent;
    background: rgb(var(--surface-1));
    padding: 0;
    cursor: pointer;
    opacity: 0.6;
    transition: opacity var(--dur-base) ease, border-color var(--dur-base) ease;
}

.show-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.show-thumb:hover { opacity: 0.9; }

.show-thumb.is-active {
    opacity: 1;
    border-color: rgb(var(--accent));
}

@media (min-width: 640px) {
    .show-thumb { width: 108px; height: 76px; }
}

/* Pricing card */
.price-card {
    position: relative;
    overflow: hidden;
    padding: 1.75rem;
    border-radius: var(--radius-xl);
    color: #fff;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: var(--shadow-glow);
}

.price-card__bg {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 80% 50% at 100% 0%, rgba(255, 255, 255, 0.18), transparent 60%);
    pointer-events: none;
}

.price-card__eyebrow {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.75);
}

.price-card__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    background: rgba(255, 255, 255, 0.18);
    border-radius: 9999px;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.price-card__badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #34d399;
    box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.3);
}

.price-card__value {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: clamp(1.875rem, 4vw, 2.75rem);
    letter-spacing: -0.03em;
    line-height: 1;
}

.price-card__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 0.75rem;
    border-top: 1px solid rgba(255, 255, 255, 0.18);
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.85);
}

.price-card__note {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.85);
    line-height: 1.55;
}

.price-card__manager {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.18);
}

.price-card__manager-avatar {
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.18);
    border: 1px solid rgba(255, 255, 255, 0.24);
    font-weight: 700;
    font-size: 1rem;
}

.price-card__manager-label {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.75);
}

.price-card__manager-name {
    font-weight: 700;
    font-size: 0.9375rem;
    margin-top: 2px;
}

.price-card__no-tg {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.625rem 0.875rem;
    background: rgba(255, 255, 255, 0.10);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 9999px;
    font-size: 0.8125rem;
    color: rgba(255, 255, 255, 0.85);
}

/* Specs */
.specs-card {
    padding: 1.5rem;
    border-radius: var(--radius-xl);
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.specs-card__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    background: rgba(52, 211, 153, 0.12);
    color: #34d399;
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.specs-card__badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #34d399;
    box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.18);
}

.specs-dl {
    display: flex;
    flex-direction: column;
}

.specs-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.875rem 0;
    border-top: 1px solid var(--border-subtle);
}

.specs-row:first-child { border-top: none; padding-top: 0.5rem; }

.specs-row dt {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    color: rgb(var(--text-muted));
    font-weight: 500;
    letter-spacing: 0.02em;
}

.specs-row dd {
    text-align: right;
    font-weight: 600;
    font-size: 0.9375rem;
    color: rgb(var(--text-primary));
}

/* CTA card */
.cta-card {
    padding: 1.5rem;
    border-radius: var(--radius-xl);
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
}

.cta-card__icon {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-md);
    background: var(--accent-soft);
    color: rgb(var(--accent));
    font-size: 1.1rem;
}
</style>
