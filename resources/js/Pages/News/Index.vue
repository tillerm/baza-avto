<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Site/Section.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    news: Array,
});

const items = computed(() => props.news || []);
const featured = computed(() => items.value[0] ?? null);
const rest = computed(() => items.value.slice(1));

const formatDate = (date) => {
    if (!date) return '';
    try { return new Date(date).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }); }
    catch (_) { return ''; }
};
</script>

<template>
    <AppLayout title="Новости">
        <!-- HERO -->
        <section class="news-hero">
            <div class="news-hero__bg" aria-hidden="true">
                <div class="bg-grid absolute inset-0 opacity-50"></div>
            </div>
            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative pt-20 lg:pt-28 pb-12">
                <span class="section-eyebrow">Новости</span>
                <h1 class="display-heading text-4xl sm:text-5xl lg:text-6xl text-ink-primary mt-4 max-w-3xl">
                    Актуальные новости и <span class="text-accent">обновления</span>
                </h1>
                <p class="text-lg text-ink-secondary leading-relaxed mt-5 max-w-2xl">
                    Публикуем только подтверждённые материалы — обновления каталога, отзывы, разборы рынка.
                </p>
            </div>
        </section>

        <Section spacing="default">
            <div v-if="!items.length" class="text-center py-16 text-ink-muted">
                <i class="pi pi-inbox text-3xl"></i>
                <p class="mt-3">Новостей пока нет.</p>
            </div>

            <div v-else class="space-y-12">
                <!-- Featured -->
                <Link v-if="featured" :href="route('news.show', [featured.id])" class="news-featured group">
                    <div class="news-featured__inner">
                        <span class="section-eyebrow">Главное</span>
                        <h2 class="news-featured__title">{{ featured.title }}</h2>
                        <p v-if="featured.summary" class="news-featured__summary">{{ featured.summary }}</p>
                        <div class="news-featured__meta">
                            <span>{{ formatDate(featured.published_at) }}</span>
                            <span class="news-featured__cta">
                                Читать <i class="pi pi-arrow-right text-[10px]"></i>
                            </span>
                        </div>
                    </div>
                </Link>

                <!-- Grid -->
                <div v-if="rest.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <Link
                        v-for="item in rest"
                        :key="item.id"
                        :href="route('news.show', [item.id])"
                        class="news-card group"
                    >
                        <div class="news-card__inner">
                            <div class="news-card__date">{{ formatDate(item.published_at) }}</div>
                            <h3 class="news-card__title">{{ item.title }}</h3>
                            <p v-if="item.summary" class="news-card__summary">{{ item.summary }}</p>
                            <div class="news-card__cta">
                                Читать
                                <i class="pi pi-arrow-right text-[10px]"></i>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </Section>
    </AppLayout>
</template>

<style scoped>
.news-hero {
    position: relative;
    overflow: hidden;
    background: rgb(var(--bg-base));
    border-bottom: 1px solid var(--border-subtle);
}

.news-hero__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.news-featured {
    display: block;
    position: relative;
    overflow: hidden;
    border-radius: var(--radius-xl);
    background: linear-gradient(135deg, rgb(var(--bg-elevated)), rgb(var(--surface-1)));
    border: 1px solid var(--border-subtle);
    transition: border-color var(--dur-base) ease, transform var(--dur-base) ease;
}

.news-featured::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 60% 40% at 80% 20%, var(--accent-soft), transparent 60%);
    pointer-events: none;
}

.news-featured:hover {
    border-color: rgb(var(--accent) / 0.4);
    transform: translateY(-2px);
}

.news-featured__inner {
    position: relative;
    z-index: 1;
    padding: 3rem 2.5rem;
    max-width: 56rem;
}

.news-featured__title {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: clamp(1.875rem, 4vw, 3rem);
    letter-spacing: -0.03em;
    line-height: 1.05;
    color: rgb(var(--text-primary));
    margin: 1rem 0;
    text-wrap: balance;
}

.news-featured__summary {
    font-size: 1.0625rem;
    color: rgb(var(--text-secondary));
    line-height: 1.55;
    max-width: 44rem;
}

.news-featured__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-top: 1.75rem;
    font-size: 0.875rem;
    color: rgb(var(--text-muted));
}

.news-featured__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: rgb(var(--accent));
    font-weight: 600;
    transition: gap var(--dur-base) ease;
}

.news-featured:hover .news-featured__cta { gap: 0.75rem; }

.news-card {
    display: block;
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    transition: border-color var(--dur-base) ease, transform var(--dur-base) ease;
}

.news-card:hover {
    border-color: var(--border-strong);
    transform: translateY(-2px);
}

.news-card__inner {
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    height: 100%;
    min-height: 220px;
}

.news-card__date {
    font-size: 0.6875rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: rgb(var(--text-muted));
}

.news-card__title {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.25rem;
    letter-spacing: -0.02em;
    line-height: 1.2;
    color: rgb(var(--text-primary));
    transition: color var(--dur-base) ease;
}

.news-card:hover .news-card__title { color: rgb(var(--accent)); }

.news-card__summary {
    font-size: 0.9375rem;
    color: rgb(var(--text-secondary));
    line-height: 1.55;
    flex: 1;
}

.news-card__cta {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    font-weight: 600;
    color: rgb(var(--accent));
    margin-top: auto;
    transition: gap var(--dur-base) ease;
}

.news-card:hover .news-card__cta { gap: 0.75rem; }
</style>
