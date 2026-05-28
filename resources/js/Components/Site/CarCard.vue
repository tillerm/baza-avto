<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    car: { type: Object, required: true },
    /**
     * Layout variant: 'grid' (default tile) | 'list' (horizontal) | 'compact' (slimmer for sidebars)
     */
    variant: { type: String, default: 'grid' },
});

const formatPrice = (value) => {
    if (value === null || value === undefined) return null;
    try {
        return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(value);
    } catch (_) {
        return `${value} ₽`;
    }
};

const formatNumber = (value) => {
    if (value === null || value === undefined) return null;
    try {
        return new Intl.NumberFormat('ru-RU').format(value);
    } catch (_) {
        return String(value);
    }
};

const brandName = computed(() => props.car?.brand?.name || props.car?.model?.brand?.name || '');
const modelName = computed(() => props.car?.model?.name || props.car?.name || '');

const image = computed(() => {
    const photos = props.car?.photos;
    if (Array.isArray(photos) && photos.length) return photos[0]?.url || photos[0];
    return props.car?.thumbnail_url || props.car?.image || props.car?.photo || null;
});

const priceText = computed(() => formatPrice(props.car?.price));
const yearText = computed(() => props.car?.year ?? null);
const mileageText = computed(() => {
    if (props.car?.mileage == null) return null;
    return `${formatNumber(props.car.mileage)} км`;
});
const transmissionText = computed(() => props.car?.transmission || null);
const engineText = computed(() => {
    const eng = props.car?.engine;
    if (!eng) return null;
    if (typeof eng === 'string') return eng;
    if (eng.name) return eng.name;
    const parts = [];
    if (eng.volume) parts.push(`${eng.volume} л`);
    if (eng.power) parts.push(`${eng.power} л.с.`);
    if (eng.fuel) parts.push(eng.fuel);
    return parts.join(' • ');
});

const href = computed(() => {
    if (props.car?.slug) {
        try { return route('catalog.show', props.car.slug); } catch (_) {}
    }
    if (props.car?.id) {
        try { return route('catalog.show', props.car.id); } catch (_) {}
    }
    return '#';
});

const badge = computed(() => props.car?.badge || (props.car?.is_in_stock ? 'В наличии' : 'Под заказ'));
</script>

<template>
    <Link :href="href" class="car-card" :class="`car-card--${variant}`">
        <div class="car-card__media">
            <img
                v-if="image"
                :src="image"
                :alt="`${brandName} ${modelName}`"
                loading="lazy"
                class="car-card__img"
            />
            <div v-else class="car-card__img car-card__img--placeholder">
                <i class="pi pi-image"></i>
            </div>

            <div class="car-card__badges">
                <span v-if="badge" class="car-card__badge">
                    <span class="car-card__badge-dot" aria-hidden="true"></span>
                    {{ badge }}
                </span>
                <span v-if="yearText" class="car-card__chip">{{ yearText }}</span>
            </div>
        </div>

        <div class="car-card__body">
            <div v-if="brandName" class="car-card__eyebrow">{{ brandName }}</div>
            <h3 class="car-card__title">{{ modelName }}</h3>

            <ul class="car-card__specs">
                <li v-if="engineText"><i class="pi pi-bolt"></i>{{ engineText }}</li>
                <li v-if="transmissionText"><i class="pi pi-cog"></i>{{ transmissionText }}</li>
                <li v-if="mileageText"><i class="pi pi-compass"></i>{{ mileageText }}</li>
            </ul>

            <div class="car-card__footer">
                <div class="car-card__price">
                    <span v-if="priceText">{{ priceText }}</span>
                    <span v-else class="car-card__price-soft">Цена по запросу</span>
                </div>
                <span class="car-card__cta">
                    <i class="pi pi-arrow-right"></i>
                </span>
            </div>
        </div>
    </Link>
</template>

<style scoped>
.car-card {
    position: relative;
    display: flex;
    flex-direction: column;
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    overflow: hidden;
    color: rgb(var(--text-primary));
    transition: transform var(--dur-base) cubic-bezier(0.16, 1, 0.3, 1), border-color var(--dur-base) ease, box-shadow var(--dur-base) ease;
}

.car-card:hover {
    transform: translateY(-4px);
    border-color: var(--border-strong);
    box-shadow: var(--shadow-lg);
}

.car-card:hover .car-card__img {
    transform: scale(1.04);
}

.car-card:hover .car-card__cta {
    background: rgb(var(--accent));
    color: #fff;
    transform: translateX(2px);
}

.car-card--list {
    flex-direction: column;
}

@media (min-width: 768px) {
    .car-card--list {
        flex-direction: row;
    }
    .car-card--list .car-card__media {
        width: 320px;
        flex-shrink: 0;
    }
}

.car-card__media {
    position: relative;
    aspect-ratio: 16 / 11;
    overflow: hidden;
    background: rgb(var(--surface-1));
}

.car-card__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform var(--dur-slow) cubic-bezier(0.16, 1, 0.3, 1);
}

.car-card__img--placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgb(var(--text-muted));
    font-size: 2rem;
}

.car-card__badges {
    position: absolute;
    top: 0.875rem;
    left: 0.875rem;
    right: 0.875rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

.car-card__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: rgb(var(--text-primary));
    background: var(--bg-glass);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
}

.car-card__badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 9999px;
    background: #34d399;
    box-shadow: 0 0 0 3px rgba(52, 211, 153, 0.18);
}

.car-card__chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: rgb(var(--text-primary));
    background: var(--bg-glass);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
}

.car-card__body {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1.25rem 1.25rem 1.5rem;
    flex: 1;
}

.car-card__eyebrow {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgb(var(--text-muted));
}

.car-card__title {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.375rem;
    letter-spacing: -0.02em;
    line-height: 1.15;
    color: rgb(var(--text-primary));
}

.car-card__specs {
    display: flex;
    flex-wrap: wrap;
    gap: 0.875rem;
    color: rgb(var(--text-secondary));
    font-size: 0.875rem;
    margin-top: auto;
}

.car-card__specs li {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.car-card__specs i {
    color: rgb(var(--text-muted));
    font-size: 0.85rem;
}

.car-card__footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    margin-top: 0.5rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-subtle);
}

.car-card__price {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.25rem;
    letter-spacing: -0.02em;
    color: rgb(var(--text-primary));
}

.car-card__price-soft {
    font-size: 0.9375rem;
    font-weight: 500;
    color: rgb(var(--text-secondary));
}

.car-card__cta {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 9999px;
    background: var(--bg-glass);
    color: rgb(var(--text-primary));
    border: 1px solid var(--border-subtle);
    transition: background-color var(--dur-base) ease, color var(--dur-base) ease, transform var(--dur-base) ease, border-color var(--dur-base) ease;
}

/* Compact variant */
.car-card--compact .car-card__media {
    aspect-ratio: 4 / 3;
}
.car-card--compact .car-card__body {
    padding: 1rem;
    gap: 0.5rem;
}
.car-card--compact .car-card__title {
    font-size: 1.125rem;
}
.car-card--compact .car-card__specs {
    font-size: 0.8125rem;
}
.car-card--compact .car-card__price {
    font-size: 1.0625rem;
}
</style>
