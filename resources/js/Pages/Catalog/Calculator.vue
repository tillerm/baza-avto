<script setup>
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

const destination = ref('');
const mailingAgree = ref(false);
const isLoading = ref(false);
const result = ref(null);
const errorMessage = ref('');
const successMessage = ref('');

const formatCurrency = (value) =>
    value || value === 0
        ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB', maximumFractionDigits: 0 }).format(value)
        : '';

const formatDistance = (value) =>
    value || value === 0 ? new Intl.NumberFormat('ru-RU', { maximumFractionDigits: 0 }).format(value) : '';

const reset = () => {
    destination.value = '';
    mailingAgree.value = false;
    result.value = null;
    errorMessage.value = '';
    successMessage.value = '';
};

const calculateDelivery = async () => {
    errorMessage.value = '';
    successMessage.value = '';
    result.value = null;

    if (!destination.value.trim()) {
        errorMessage.value = 'Укажите город или точный адрес доставки.';
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.post(route('catalog.calculator.delivery-estimate'), {
            destination: destination.value.trim(),
        });
        result.value = response.data;
        // Lead is stored silently for analytics — no banner shown to the user.
        await axios.post(route('leads.delivery-calculator.store'), {
            destination: destination.value.trim(),
            mailing_agree: mailingAgree.value,
            distance_km: response.data.distance_km,
            price: response.data.price,
        });
    } catch (error) {
        errorMessage.value = error.response?.data?.errors?.destination?.[0]
            ?? error.response?.data?.message
            ?? 'Не удалось рассчитать доставку. Попробуйте ещё раз.';
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <AppLayout title="Калькулятор доставки">
        <section class="calc-page">
            <div class="calc-page__bg" aria-hidden="true">
                <div class="bg-grid absolute inset-0 opacity-50"></div>
                <div class="absolute -top-1/3 -right-1/4 w-[60vw] h-[60vw] rounded-full opacity-20" style="background: radial-gradient(circle, rgba(239,68,68,0.30), transparent 60%);"></div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24 relative">
                <header class="space-y-5 max-w-3xl mb-12 lg:mb-16">
                    <span class="section-eyebrow">Доставка по России</span>
                    <h1 class="display-heading text-4xl sm:text-5xl lg:text-6xl text-ink-primary">
                        Калькулятор <span class="text-accent">доставки</span><br />из Таганрога
                    </h1>
                    <p class="text-lg text-ink-secondary leading-relaxed">
                        Рассчитываем стоимость по формуле <span class="font-mono text-ink-primary">100 000 ₽ + 25 ₽/км</span>.
                        Дистанция строится от Таганрога до указанного города или адреса.
                    </p>
                </header>

                <div class="calc-grid">
                    <!-- Form column -->
                    <div class="calc-card glass-card">
                        <div class="grid sm:grid-cols-2 gap-3 mb-6">
                            <div class="calc-tile">
                                <span class="section-eyebrow text-[0.6875rem]">Точка отправления</span>
                                <div class="calc-tile__value">Таганрог</div>
                            </div>
                            <div class="calc-tile">
                                <span class="section-eyebrow text-[0.6875rem]">Тариф</span>
                                <div class="calc-tile__value">100 000 ₽<span class="calc-tile__unit"> до 1000 км</span></div>
                                <div class="text-xs text-ink-secondary">+ 25 ₽/км далее</div>
                            </div>
                        </div>

                        <form class="space-y-5" @submit.prevent="calculateDelivery">
                            <div class="space-y-2">
                                <label for="destination" class="calc-label">Куда нужна доставка</label>
                                <input
                                    id="destination"
                                    v-model.trim="destination"
                                    type="text"
                                    placeholder="Москва, Краснодар или точный адрес"
                                    class="public-form-input"
                                    :disabled="isLoading"
                                />
                                <p class="text-xs text-ink-muted">Если адрес не находится, укажите город и область.</p>
                            </div>

                            <p v-if="errorMessage" class="text-sm text-accent">{{ errorMessage }}</p>

                            <div class="flex flex-wrap gap-3 pt-2">
                                <button type="submit" class="premium-btn" :disabled="isLoading">
                                    <i v-if="!isLoading" class="pi pi-map-marker"></i>
                                    <i v-else class="pi pi-spin pi-spinner"></i>
                                    {{ isLoading ? 'Считаем…' : 'Рассчитать доставку' }}
                                </button>
                                <button type="button" class="premium-btn-secondary" :disabled="isLoading" @click="reset">
                                    <i class="pi pi-refresh"></i>
                                    Сбросить
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Result column -->
                    <div class="calc-result glass-card">
                        <template v-if="result">
                            <div class="space-y-5">
                                <div>
                                    <span class="section-eyebrow">Маршрут</span>
                                    <div class="font-display font-bold text-xl text-ink-primary mt-2">
                                        {{ result.origin }} → {{ result.destination }}
                                    </div>
                                </div>

                                <dl class="calc-result__rows">
                                    <div class="calc-result__row">
                                        <dt>Расстояние</dt>
                                        <dd>{{ formatDistance(result.distance_km) }} км</dd>
                                    </div>
                                    <div class="calc-result__row">
                                        <dt>Базовая ставка</dt>
                                        <dd>{{ formatCurrency(result.pricing.base_price) }}</dd>
                                    </div>
                                    <div class="calc-result__row">
                                        <dt>Пробег × тариф</dt>
                                        <dd>{{ formatCurrency(result.pricing.distance_price) }}</dd>
                                    </div>
                                </dl>

                                <div class="calc-result__total">
                                    <span class="calc-result__total-eyebrow">Итого</span>
                                    <div class="calc-result__total-value">{{ formatCurrency(result.price) }}</div>
                                </div>

                                <p v-if="result.is_approximate" class="text-sm text-amber-500">
                                    Маршрутный сервис временно недоступен — стоимость рассчитана ориентировочно.
                                </p>
                            </div>
                        </template>
                        <template v-else>
                            <div class="space-y-5">
                                <span class="section-eyebrow">Как это работает</span>
                                <h3 class="font-display font-bold text-2xl text-ink-primary text-balance">
                                    Введите пункт назначения и получите стоимость доставки
                                </h3>
                                <p class="text-ink-secondary leading-relaxed">
                                    Калькулятор определит расстояние от Таганрога и сразу посчитает сумму по вашему тарифу.
                                </p>
                                <div class="calc-example">
                                    <i class="pi pi-info-circle text-accent text-base"></i>
                                    <span>Пример: 300 км → 100 000 ₽ + 7 500 ₽ = 107 500 ₽</span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </section>
    </AppLayout>
</template>

<style scoped>
.calc-page {
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    background: rgb(var(--bg-base));
}

.calc-page__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.calc-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    gap: 1.5rem;
    align-items: start;
}

@media (min-width: 1024px) {
    .calc-grid {
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        gap: 2rem;
    }
}

.calc-card,
.calc-result {
    padding: 2rem;
    border-radius: var(--radius-xl);
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    min-width: 0;
}

.calc-tile {
    padding: 1.25rem;
    border-radius: var(--radius-md);
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.calc-tile__value {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.0625rem;
    letter-spacing: -0.02em;
    color: rgb(var(--text-primary));
}

.calc-tile__unit {
    color: rgb(var(--text-muted));
    font-weight: 400;
    font-size: 0.875rem;
}

.calc-label {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgb(var(--text-secondary));
}

.calc-result__rows {
    display: flex;
    flex-direction: column;
    border-top: 1px solid var(--border-subtle);
}

.calc-result__row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.875rem 0;
    border-bottom: 1px solid var(--border-subtle);
    font-size: 0.9375rem;
}

.calc-result__row dt { color: rgb(var(--text-muted)); }
.calc-result__row dd { color: rgb(var(--text-primary)); font-weight: 600; }

.calc-result__total {
    padding: 1.25rem 1.5rem;
    border-radius: var(--radius-lg);
    color: #fff;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: var(--shadow-glow);
}

.calc-result__total-eyebrow {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.75);
}

.calc-result__total-value {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: clamp(1.875rem, 4vw, 2.5rem);
    letter-spacing: -0.03em;
    line-height: 1;
    margin-top: 0.5rem;
}

.calc-example {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.875rem 1rem;
    border-radius: var(--radius-md);
    background: var(--bg-glass);
    border: 1px dashed var(--border-strong);
    color: rgb(var(--text-secondary));
    font-size: 0.875rem;
}
</style>
