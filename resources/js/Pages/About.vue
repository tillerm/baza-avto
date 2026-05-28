<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Site/Section.vue';
import { computed } from 'vue';

const highlights = [
    { title: 'Прозрачность', text: 'Все данные по авто открыты: комплектация, пробег, история, фото в высоком качестве.' },
    { title: 'Скорость', text: 'Быстрый подбор по брендам, моделям и бюджету. Фильтры работают мгновенно.' },
    { title: 'Поддержка', text: 'Помогаем выбрать вариант, организуем осмотр, доставку и оформление.' },
    { title: 'Экспертиза', text: 'Работаем с проверенными поставщиками, используем единые стандарты проверки.' },
];

const principles = [
    { title: 'Подбор и проверка', desc: 'Проверяем авто по ключевым параметрам: пробег, состояние, документы.' },
    { title: 'Оформление', desc: 'Готовим договор, страхование, доставку и передачу ключей.' },
    { title: 'Поддержка', desc: 'Остаёмся на связи после сделки и помогаем с дальнейшим обслуживанием.' },
];

const props = defineProps({
    cars: { type: [Array, Number], default: () => [] },
});

const totalCars = computed(() => {
    if (Array.isArray(props.cars)) return props.cars.length;
    if (typeof props.cars === 'number') return props.cars;
    return 0;
});
</script>

<template>
    <AppLayout title="О нас">
        <!-- HERO -->
        <section class="about-hero">
            <div class="about-hero__bg" aria-hidden="true">
                <div class="bg-grid absolute inset-0 opacity-50"></div>
                <div class="absolute -top-1/3 -left-1/4 w-[60vw] h-[60vw] rounded-full opacity-20" style="background: radial-gradient(circle, rgba(239,68,68,0.30), transparent 60%);"></div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative pt-20 lg:pt-28 pb-16 lg:pb-20">
                <div class="space-y-6 max-w-4xl">
                    <span class="section-eyebrow">О компании</span>
                    <h1 class="display-heading text-4xl sm:text-6xl lg:text-7xl text-ink-primary">
                        Помогаем выбрать <br class="hidden sm:block" />и купить <span class="text-accent">авто без сложностей</span>
                    </h1>
                    <p class="text-lg lg:text-xl text-ink-secondary leading-relaxed max-w-3xl">
                        Каталог с актуальными предложениями, удобной системой фильтров и прозрачной информацией.
                        Команда сопровождает сделку от подбора до передачи ключей.
                    </p>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <span class="about-chip">Работаем по всей России</span>
                        <span class="about-chip">Проверенные поставщики</span>
                        <span class="about-chip">Гарантия честных условий</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- HOW WE WORK -->
        <Section eyebrow="Как мы работаем" title="От подбора до передачи ключей" subtitle="Вы выбираете авто в каталоге — мы подтверждаем наличие, организуем осмотр, подготовим документы и доставку.">
            <div class="grid lg:grid-cols-2 gap-12 items-start">
                <div class="space-y-8">
                    <div v-for="(p, i) in principles" :key="p.title" class="principle">
                        <div class="principle__num">{{ String(i + 1).padStart(2, '0') }}</div>
                        <div class="principle__body">
                            <h3 class="principle__title">{{ p.title }}</h3>
                            <p class="principle__desc">{{ p.desc }}</p>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="kpi-card kpi-card--primary col-span-full">
                        <span class="section-eyebrow text-white/70">Каталог</span>
                        <div class="kpi-card__value">{{ totalCars }}+</div>
                        <p class="kpi-card__desc text-white/85">Предложений: марки, модели, фильтры по цене и году.</p>
                    </div>
                    <div class="kpi-card">
                        <span class="section-eyebrow">Сделка</span>
                        <div class="kpi-card__title">Документы и доставка</div>
                        <p class="kpi-card__desc">Готовим договор, страхование, логистику.</p>
                    </div>
                    <div class="kpi-card">
                        <span class="section-eyebrow">Контроль</span>
                        <div class="kpi-card__title">Проверки состояния</div>
                        <p class="kpi-card__desc">Фотографии, ТТХ, прозрачные данные по авто.</p>
                    </div>
                </div>
            </div>
        </Section>

        <!-- HIGHLIGHTS -->
        <Section eyebrow="Принципы" title="На чём строим работу">
            <div class="highlight-grid">
                <article v-for="item in highlights" :key="item.title" class="highlight-tile">
                    <span class="section-eyebrow">{{ item.title }}</span>
                    <p class="highlight-tile__text">{{ item.text }}</p>
                </article>
            </div>
        </Section>

        <!-- CTA -->
        <Section spacing="tight">
            <div class="about-cta">
                <div class="about-cta__bg" aria-hidden="true"></div>
                <div class="relative grid md:grid-cols-[1.2fr_auto] gap-8 items-center">
                    <div class="space-y-3">
                        <span class="section-eyebrow">Свяжитесь с нами</span>
                        <h2 class="display-heading text-3xl sm:text-4xl text-ink-primary">Нужна помощь с подбором?</h2>
                        <p class="text-ink-secondary leading-relaxed max-w-xl">
                            Оставьте заявку или напишите — ответим и подберём варианты под ваш запрос.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="https://t.me/+uXvETOFXQ99iMjUy" target="_blank" rel="noopener" class="premium-btn-secondary">
                            <i class="pi pi-telegram"></i>
                            Telegram
                        </a>
                    </div>
                </div>
            </div>
        </Section>
    </AppLayout>
</template>

<style scoped>
.about-hero {
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid var(--border-subtle);
    background: rgb(var(--bg-base));
}

.about-hero__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.about-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 0.875rem;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
    font-size: 0.8125rem;
    color: rgb(var(--text-secondary));
}

.principle {
    display: flex;
    gap: 1.25rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border-subtle);
}

.principle__num {
    flex-shrink: 0;
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: 1.75rem;
    letter-spacing: -0.04em;
    line-height: 1;
    color: rgb(var(--accent));
    width: 56px;
}

.principle__title {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.25rem;
    letter-spacing: -0.02em;
    color: rgb(var(--text-primary));
    margin-bottom: 0.5rem;
}

.principle__desc {
    color: rgb(var(--text-secondary));
    line-height: 1.6;
}

.kpi-card {
    padding: 1.75rem 1.5rem;
    border-radius: var(--radius-lg);
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.kpi-card--primary {
    color: #fff;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    border: none;
    box-shadow: var(--shadow-glow);
}

.kpi-card__value {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: clamp(2.5rem, 5vw, 4rem);
    letter-spacing: -0.04em;
    line-height: 1;
    margin: 0.5rem 0;
}

.kpi-card__title {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.125rem;
    letter-spacing: -0.01em;
    color: rgb(var(--text-primary));
    margin-top: 0.5rem;
}

.kpi-card__desc {
    font-size: 0.875rem;
    color: rgb(var(--text-secondary));
    line-height: 1.55;
}

.highlight-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1px;
    background: var(--border-subtle);
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    overflow: hidden;
}

@media (min-width: 640px) {
    .highlight-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

.highlight-tile {
    padding: 2rem 1.75rem;
    background: rgb(var(--bg-elevated));
    display: flex;
    flex-direction: column;
    gap: 0.875rem;
    transition: background-color var(--dur-base) ease;
}

.highlight-tile:hover { background: rgb(var(--surface-1)); }

.highlight-tile__text {
    color: rgb(var(--text-secondary));
    line-height: 1.6;
    font-size: 0.9375rem;
}

.about-cta {
    position: relative;
    overflow: hidden;
    padding: 2.5rem;
    border-radius: var(--radius-xl);
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
}

.about-cta__bg {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 50% 50% at 100% 50%, var(--accent-soft), transparent 60%);
    pointer-events: none;
}
</style>
