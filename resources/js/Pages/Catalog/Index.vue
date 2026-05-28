<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DataView from 'primevue/dataview';
import moment from 'moment/moment';
import SearchField from '@/Components/SearchField.vue';
import Dropdown from 'primevue/dropdown';
import { computed, reactive, ref, watch } from 'vue';
import { debounce } from 'lodash';
import { router } from '@inertiajs/vue3';
import Calendar from 'primevue/calendar';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';
import FilterChip from '@/Components/Site/FilterChip.vue';
import { reachGoal } from '@/lib/metrika';

const props = defineProps({
    cars: Object,
    brands: Object,
    models: Object,
});

const loading = ref(false);
const filter = ref({
    brand: null,
    model: null,
    yearFrom: null,
    yearTo: null,
    priceFrom: null,
    priceTo: null,
});

const showAllBrands = ref(false);
const visibleBrands = computed(() => {
    const list = props.brands ?? [];
    return showAllBrands.value ? list : list.slice(0, 12);
});

const brokenBrandLogos = reactive(new Set());

function resolveBrandLogoSrc(logo) {
    if (!logo) return null;
    const normalized = String(logo).trim();
    if (!normalized) return null;
    if (normalized.startsWith('http://') || normalized.startsWith('https://')) return normalized;
    if (normalized.startsWith('/')) return normalized;
    if (normalized.startsWith('storage/')) return `/${normalized}`;
    return `/storage/${normalized}`;
}

function onBrandLogoError(brandId) {
    if (brandId === null || brandId === undefined) return;
    brokenBrandLogos.add(brandId);
}

function selectBrand(brand) {
    filter.value.brand = brand;
    filter.value.model = null;
    reachGoal('catalog_brand_select', { brand: brand?.name ?? 'reset' });
    initSearch();
}

function getUrlParams() {
    const result = {};
    for (const [key, value] of new URLSearchParams(window.location.search)) result[key] = value;
    return result;
}

const initSearch = debounce(() => {
    loading.value = true;
    let params = getUrlParams();
    router.get(
        route(route().current()),
        {
            ...params,
            brand: filter.value.brand?.id,
            model: filter.value.model?.id,
            yearFrom: filter.value.yearFrom ? moment(filter.value.yearFrom).format('YYYY-MM-DD') : null,
            yearTo: filter.value.yearTo ? moment(filter.value.yearTo).format('YYYY-MM-DD') : null,
            priceFrom: filter.value.priceFrom,
            priceTo: filter.value.priceTo,
        },
        { preserveState: true, onSuccess: () => { loading.value = false; } },
    );
}, 400);

watch(filter, () => initSearch(), { deep: true });

const resetAllFilters = () => {
    filter.value = { brand: null, model: null, yearFrom: null, yearTo: null, priceFrom: null, priceTo: null };
};

const formatPrice = (value) => {
    try {
        return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(value);
    } catch (_) { return `${value} ₽`; }
};

const activeChips = computed(() => {
    const chips = [];
    if (filter.value.brand) chips.push({ key: 'brand', label: 'Марка', value: filter.value.brand.name, clear: () => { filter.value.brand = null; filter.value.model = null; } });
    if (filter.value.model) chips.push({ key: 'model', label: 'Модель', value: filter.value.model.name, clear: () => { filter.value.model = null; } });
    if (filter.value.yearFrom) chips.push({ key: 'yearFrom', label: 'Год от', value: moment(filter.value.yearFrom).format('YYYY'), clear: () => { filter.value.yearFrom = null; } });
    if (filter.value.yearTo) chips.push({ key: 'yearTo', label: 'Год до', value: moment(filter.value.yearTo).format('YYYY'), clear: () => { filter.value.yearTo = null; } });
    if (filter.value.priceFrom) chips.push({ key: 'priceFrom', label: 'Цена от', value: formatPrice(filter.value.priceFrom), clear: () => { filter.value.priceFrom = null; } });
    if (filter.value.priceTo) chips.push({ key: 'priceTo', label: 'Цена до', value: formatPrice(filter.value.priceTo), clear: () => { filter.value.priceTo = null; } });
    return chips;
});

const totalCount = computed(() => Array.isArray(props.cars) ? props.cars.length : (props.cars?.length ?? 0));

const fuelMap = { DIESEL: 'Дизель', PETROL: 'Бензин', OTHER: 'Другое' };
</script>

<template>
    <AppLayout title="Каталог">
        <!-- ============== HERO ============== -->
        <section class="catalog-hero">
            <div class="catalog-hero__bg" aria-hidden="true">
                <div class="bg-grid absolute inset-0 opacity-50"></div>
                <div class="absolute -top-1/2 -right-1/3 w-[60vw] h-[60vw] rounded-full opacity-30" style="background: radial-gradient(circle, rgba(239,68,68,0.25), transparent 60%);"></div>
            </div>
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="grid lg:grid-cols-[1.05fr_0.95fr] gap-10 items-end pt-20 lg:pt-28 pb-10">
                    <div class="space-y-5 max-w-2xl">
                        <span class="section-eyebrow">Каталог</span>
                        <h1 class="display-heading text-4xl sm:text-5xl lg:text-6xl text-ink-primary">
                            Найдите машину, которая <span class="text-accent">вам подходит</span>
                        </h1>
                        <p class="text-lg text-ink-secondary leading-relaxed">
                            Выбирайте по бренду, модели, году или цене. Прозрачные карточки с ключевыми характеристиками.
                        </p>
                        <div class="flex flex-wrap gap-2 pt-2">
                            <span class="catalog-hero__chip">
                                <i class="pi pi-bolt text-xs"></i>
                                {{ totalCount }} предложений
                            </span>
                            <span class="catalog-hero__chip">
                                <i class="pi pi-clock text-xs"></i>
                                Обновлено {{ moment().format('DD.MM.YYYY') }}
                            </span>
                        </div>
                    </div>

                    <div class="brand-quickpick">
                        <div class="flex items-start justify-between gap-3 mb-5">
                            <div>
                                <div class="section-eyebrow">Марки</div>
                                <h2 class="font-display font-bold text-lg text-ink-primary mt-2">Быстрый выбор</h2>
                            </div>
                            <button
                                type="button"
                                class="brand-quickpick__reset"
                                @click="selectBrand(null)"
                            >
                                Сбросить
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-2">
                            <button
                                v-for="brand in visibleBrands"
                                :key="brand.id"
                                type="button"
                                class="brand-pill"
                                :class="{ 'is-active': filter.brand?.id === brand.id }"
                                @click="selectBrand(brand)"
                            >
                                <span class="brand-pill__icon">
                                    <img
                                        v-if="resolveBrandLogoSrc(brand.logo) && !brokenBrandLogos.has(brand.id)"
                                        :src="resolveBrandLogoSrc(brand.logo)"
                                        :alt="brand.name"
                                        loading="lazy"
                                        @error="onBrandLogoError(brand.id)"
                                    />
                                    <span v-else class="brand-pill__letter">{{ brand.name?.[0] }}</span>
                                </span>
                                <span class="brand-pill__name">{{ brand.name }}</span>
                            </button>
                        </div>
                        <button
                            v-if="(props.brands?.length ?? 0) > visibleBrands.length"
                            type="button"
                            class="brand-quickpick__more"
                            @click="showAllBrands = true"
                        >
                            Показать все марки
                            <i class="pi pi-angle-down text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============== CATALOG MAIN ============== -->
        <section class="py-12 lg:py-16">
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="catalog-layout">
                    <!-- ============ FILTERS SIDEBAR ============ -->
                    <aside class="catalog-filters filter-panel">
                        <div class="flex items-center justify-between">
                            <h2 class="font-display font-bold text-lg text-ink-primary flex items-center gap-2">
                                <i class="pi pi-sliders-h text-accent"></i>
                                Фильтры
                            </h2>
                            <button
                                type="button"
                                class="text-xs font-semibold text-ink-muted hover:text-accent transition uppercase tracking-wide"
                                aria-label="Сбросить все фильтры"
                                @click="resetAllFilters"
                            >
                                Сбросить
                            </button>
                        </div>

                        <SearchField class="w-full" />

                        <div class="space-y-3">
                            <div class="p-inputgroup">
                                <Dropdown
                                    :disabled="loading"
                                    v-model="filter.brand"
                                    :options="brands"
                                    filter
                                    optionLabel="name"
                                    placeholder="Марка"
                                    @change="filter.model = null"
                                />
                                <Button v-if="filter.brand" icon="pi pi-times" @click="filter.brand = null; filter.model = null" />
                            </div>
                            <div class="p-inputgroup">
                                <Dropdown
                                    :disabled="!filter.brand || models.length === 0 || loading"
                                    v-model="filter.model"
                                    :options="models"
                                    filter
                                    optionLabel="name"
                                    placeholder="Модель"
                                />
                                <Button v-if="filter.model" icon="pi pi-times" @click="filter.model = null" />
                            </div>

                            <div class="hairline"></div>

                            <div class="p-inputgroup">
                                <Calendar v-model="filter.yearFrom" :disabled="loading" :maxDate="filter.yearTo" view="year" dateFormat="yy" placeholder="Год от" />
                                <Button v-if="filter.yearFrom" icon="pi pi-times" @click="filter.yearFrom = null" />
                            </div>
                            <div class="p-inputgroup">
                                <Calendar v-model="filter.yearTo" :disabled="loading" :minDate="filter.yearFrom" view="year" dateFormat="yy" placeholder="Год до" />
                                <Button v-if="filter.yearTo" icon="pi pi-times" @click="filter.yearTo = null" />
                            </div>

                            <div class="hairline"></div>

                            <div class="p-inputgroup">
                                <InputNumber v-model="filter.priceFrom" :disabled="loading" :max="9999999999.99" mode="currency" currency="RUB" locale="ru-RU" placeholder="Цена от" />
                                <Button v-if="filter.priceFrom" icon="pi pi-times" @click="filter.priceFrom = null" />
                            </div>
                            <div class="p-inputgroup">
                                <InputNumber v-model="filter.priceTo" :disabled="loading" :max="9999999999.99" mode="currency" currency="RUB" locale="ru-RU" placeholder="Цена до" />
                                <Button v-if="filter.priceTo" icon="pi pi-times" @click="filter.priceTo = null" />
                            </div>
                        </div>
                    </aside>

                    <!-- ============ RESULTS ============ -->
                    <div class="space-y-5 min-w-0 w-full">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div>
                                <span class="section-eyebrow">Результаты</span>
                                <h2 class="font-display font-bold text-2xl text-ink-primary mt-1">Доступные авто</h2>
                            </div>
                            <span v-if="loading" class="text-sm text-ink-muted inline-flex items-center gap-2">
                                <i class="pi pi-spin pi-spinner"></i>
                                Обновляем
                            </span>
                        </div>

                        <div v-if="activeChips.length" class="flex flex-wrap gap-2">
                            <FilterChip
                                v-for="chip in activeChips"
                                :key="chip.key"
                                :label="chip.label"
                                :value="chip.value"
                                @remove="chip.clear"
                            />
                            <button
                                type="button"
                                class="text-xs font-semibold text-ink-muted hover:text-accent transition uppercase tracking-wide self-center ml-2"
                                @click="resetAllFilters"
                            >
                                Очистить всё
                            </button>
                        </div>

                        <div class="catalog-results">
                            <DataView
                                :value="cars"
                                paginator
                                :rows="5"
                                layout="list"
                                :paginatorTemplate="`PageLinks NextPageLink`"
                                :pageLinkSize="5"
                                class="custom-pagination"
                            >
                                <template #empty>
                                    <div class="empty-state">
                                        <i class="pi pi-search text-3xl text-ink-muted"></i>
                                        <h3 class="font-display font-semibold text-lg text-ink-primary mt-3">Ничего не нашли</h3>
                                        <p class="text-sm text-ink-secondary max-w-md mx-auto mt-2">Попробуйте изменить фильтры или сбросить их полностью — мы можем подобрать авто под заказ.</p>
                                    </div>
                                </template>
                                <template #list="slotProps">
                                    <a
                                        :href="route('catalog.show', [slotProps.data.id])"
                                        class="result-card group block w-full"
                                        data-metrika-goal="catalog_car_open"
                                        :data-metrika-car-id="slotProps.data.id"
                                        :data-metrika-brand="slotProps.data.supply.equipment.name"
                                        :data-metrika-model="slotProps.data.supply.equipment.engine?.name ?? ''"
                                    >
                                        <div class="result-card__media">
                                            <img
                                                v-if="slotProps.data.photos && slotProps.data.photos.length"
                                                :src="'/storage/' + slotProps.data.photos[0].photo"
                                                :alt="slotProps.data.supply.equipment.name"
                                                class="result-card__img"
                                                loading="lazy"
                                            />
                                            <div v-else class="result-card__placeholder">
                                                <i class="pi pi-image"></i>
                                                <span>Фото скоро будет</span>
                                            </div>

                                            <span v-if="slotProps.data.pinned && slotProps.data.status !== 'SOLD'" class="result-card__pin">
                                                <i class="pi pi-star-fill text-[10px]"></i>
                                                Топ
                                            </span>

                                            <div v-if="slotProps.data.status === 'SOLD'" class="result-card__sold">
                                                <span>Продано</span>
                                            </div>
                                        </div>

                                        <div class="result-card__body">
                                            <div class="space-y-2 flex-1">
                                                <div class="result-card__eyebrow">
                                                    {{ slotProps.data.supply.equipment.model?.brand?.name ?? 'Авто' }}
                                                </div>
                                                <h3 class="result-card__title">
                                                    {{ slotProps.data.supply.equipment.model?.name ?? slotProps.data.supply.equipment.name }}
                                                </h3>
                                                <ul class="result-card__specs">
                                                    <li>
                                                        <i class="pi pi-calendar"></i>
                                                        {{ slotProps.data.release_date ? moment(slotProps.data.release_date).format('YYYY') : '—' }}
                                                    </li>
                                                    <li v-if="slotProps.data.supply.equipment.engine?.fuel">
                                                        <i class="pi pi-bolt"></i>
                                                        {{ fuelMap[slotProps.data.supply.equipment.engine?.fuel] }}
                                                    </li>
                                                    <li v-if="slotProps.data.mileage">
                                                        <i class="pi pi-compass"></i>
                                                        {{ slotProps.data.mileage }} км
                                                    </li>
                                                </ul>
                                                <p v-if="slotProps.data.supply.equipment.engine?.name" class="result-card__engine">
                                                    {{ slotProps.data.supply.equipment.engine.name }}
                                                </p>
                                            </div>

                                            <div class="result-card__price-block">
                                                <div v-if="slotProps.data.price" class="result-card__price-wrap">
                                                    <span class="result-card__price-label">Цена</span>
                                                    <span class="result-card__price">{{ formatPrice(slotProps.data.price) }}</span>
                                                </div>
                                                <div v-else class="result-card__price-soft">по запросу</div>
                                                <span class="result-card__cta">
                                                    Подробнее
                                                    <i class="pi pi-arrow-right"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </DataView>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
/* ============ HERO ============ */
.catalog-hero {
    position: relative;
    overflow: hidden;
    background: rgb(var(--bg-base));
    border-bottom: 1px solid var(--border-subtle);
}

.catalog-hero__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.catalog-hero__chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.875rem;
    font-size: 0.8125rem;
    color: rgb(var(--text-secondary));
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
}

/* Brand quickpick */
.brand-quickpick {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-xl);
    padding: 1.5rem;
}

.brand-quickpick__reset {
    padding: 0.5rem 0.875rem;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgb(var(--text-secondary));
    background: transparent;
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
    transition: color var(--dur-base) ease, border-color var(--dur-base) ease;
    cursor: pointer;
}

.brand-quickpick__reset:hover {
    color: rgb(var(--accent));
    border-color: rgb(var(--accent) / 0.5);
}

.brand-quickpick__more {
    margin-top: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(var(--text-secondary));
    background: transparent;
    border: none;
    cursor: pointer;
    transition: color var(--dur-base) ease;
}

.brand-quickpick__more:hover { color: rgb(var(--accent)); }

.brand-pill {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.625rem 0.75rem;
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-md);
    color: rgb(var(--text-primary));
    cursor: pointer;
    transition: border-color var(--dur-base) ease, background-color var(--dur-base) ease, transform var(--dur-base) ease;
    text-align: left;
    min-width: 0;
}

.brand-pill:hover {
    border-color: var(--border-strong);
    transform: translateY(-1px);
}

.brand-pill.is-active {
    background: rgb(var(--accent));
    border-color: rgb(var(--accent));
    color: #fff;
}

.brand-pill.is-active .brand-pill__icon {
    background: rgba(255, 255, 255, 0.18);
    border-color: rgba(255, 255, 255, 0.3);
}

.brand-pill__icon {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    overflow: hidden;
}

.brand-pill__icon img {
    width: 22px;
    height: 22px;
    object-fit: contain;
}

.brand-pill__letter {
    font-weight: 700;
    font-size: 0.75rem;
}

.brand-pill__name {
    font-size: 0.875rem;
    font-weight: 500;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ============ LAYOUT ============ */
.catalog-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1.5rem;
}

@media (min-width: 1024px) {
    .catalog-layout {
        grid-template-columns: 300px minmax(0, 1fr);
        gap: 2rem;
        align-items: start;
    }
}

/* ============ FILTERS ============ */
.catalog-filters {
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    height: max-content;
    min-width: 0;
}

@media (min-width: 1024px) {
    .catalog-filters {
        position: sticky;
        top: 100px;
    }
}

/* ============ RESULTS ============ */
.catalog-results {
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    padding: 1rem;
}

@media (min-width: 768px) {
    .catalog-results { padding: 1.25rem; }
}

/* Reset PrimeVue DataView native chrome — we do our own styling */
.catalog-results :deep(.p-dataview) {
    background: transparent;
    border: none;
    color: inherit;
}
.catalog-results :deep(.p-dataview-content) {
    background: transparent;
    border: none;
    padding: 0;
}
.catalog-results :deep(.p-dataview-header),
.catalog-results :deep(.p-dataview-footer) {
    background: transparent;
    border: none;
    padding: 0;
}
.catalog-results :deep(.p-grid),
.catalog-results :deep(.grid) {
    margin: 0;
    display: flex;
    flex-direction: column;
}
.catalog-results :deep(.p-col-12),
.catalog-results :deep(.col-12) {
    padding: 0;
    width: 100%;
    flex: 0 0 100%;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.result-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    width: 100%;
    padding: 1rem;
    margin-bottom: 0.875rem;
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    background: rgb(var(--bg-elevated));
    transition: border-color var(--dur-base) ease, transform var(--dur-base) ease, box-shadow var(--dur-base) ease;
    box-sizing: border-box;
}

@media (min-width: 640px) {
    .result-card {
        flex-direction: row;
        gap: 1.5rem;
        padding: 1.25rem;
        align-items: stretch;
    }
}

.result-card:hover {
    border-color: rgb(var(--accent) / 0.4);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.result-card__media {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 3;
    background: rgb(var(--surface-1));
    border-radius: var(--radius-md);
    overflow: hidden;
    flex-shrink: 0;
}

@media (min-width: 640px) {
    .result-card__media {
        width: 240px;
        min-width: 240px;
        max-width: 240px;
        aspect-ratio: 4 / 3;
        height: auto;
    }
}

@media (min-width: 1280px) {
    .result-card__media {
        width: 280px;
        min-width: 280px;
        max-width: 280px;
    }
}

.result-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--dur-slow) cubic-bezier(0.16, 1, 0.3, 1);
}

.result-card:hover .result-card__img { transform: scale(1.04); }

.result-card__placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: rgb(var(--text-muted));
    font-size: 0.8125rem;
}

.result-card__placeholder i { font-size: 1.5rem; }

.result-card__pin {
    position: absolute;
    left: 0.75rem;
    top: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.625rem;
    border-radius: 9999px;
    background: rgb(var(--bg-elevated) / 0.95);
    color: rgb(var(--accent));
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    border: 1px solid var(--border-subtle);
    backdrop-filter: blur(8px);
}

.result-card__sold {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
}

.result-card__sold span {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #fff;
    background: rgb(var(--accent));
    border-radius: 9999px;
}

.result-card__body {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-width: 0;
    gap: 1rem;
}

@media (min-width: 768px) {
    .result-card__body { flex-direction: row; }
}

.result-card__eyebrow {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgb(var(--text-muted));
}

.result-card__title {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.375rem;
    letter-spacing: -0.02em;
    line-height: 1.15;
    color: rgb(var(--text-primary));
    transition: color var(--dur-base) ease;
}

.result-card:hover .result-card__title { color: rgb(var(--accent)); }

.result-card__specs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.875rem;
    color: rgb(var(--text-secondary));
    font-size: 0.875rem;
}

.result-card__specs li {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.result-card__specs i {
    color: rgb(var(--text-muted));
    font-size: 0.85rem;
}

.result-card__engine {
    font-size: 0.875rem;
    color: rgb(var(--text-muted));
}

.result-card__price-block {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: space-between;
    gap: 0.75rem;
    flex-shrink: 0;
}

@media (max-width: 767px) {
    .result-card__price-block {
        flex-direction: row;
        align-items: center;
        width: 100%;
    }
}

.result-card__price-wrap { text-align: right; display: flex; flex-direction: column; gap: 0.25rem; }

.result-card__price-label {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgb(var(--text-muted));
}

.result-card__price {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: 1.5rem;
    letter-spacing: -0.02em;
    line-height: 1;
    color: rgb(var(--text-primary));
}

.result-card__price-soft {
    font-size: 0.9375rem;
    font-style: italic;
    color: rgb(var(--text-muted));
}

.result-card__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: rgb(var(--accent));
    transition: gap var(--dur-base) ease;
}

.result-card:hover .result-card__cta { gap: 0.625rem; }

@media (max-width: 767px) {
    .result-card__cta { display: none; }
}
</style>
