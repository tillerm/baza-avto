<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Site/Section.vue';
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    videoTestimonials: {
        type: Array,
        default: () => [],
    },
    textTestimonials: {
        type: Array,
        default: () => [],
    },
});

const ITEMS_PER_PAGE = 9;

const currentPage = ref(1);
const totalPages = computed(() => Math.max(1, Math.ceil(props.textTestimonials.length / ITEMS_PER_PAGE)));
const visibleTestimonials = computed(() => {
    const start = (currentPage.value - 1) * ITEMS_PER_PAGE;
    return props.textTestimonials.slice(start, start + ITEMS_PER_PAGE);
});
const pages = computed(() => Array.from({ length: totalPages.value }, (_, idx) => idx + 1));

function goToPage(page) {
    if (page < 1 || page > totalPages.value || page === currentPage.value) return;
    currentPage.value = page;
    if (typeof window !== 'undefined') window.scrollTo({ top: 0, behavior: 'smooth' });
}

function toEmbedUrl(url) {
    if (!url) return '';
    if (url.includes('/embed/')) return url;
    const shortMatch = url.match(/shorts\/([\w-]+)/);
    if (shortMatch) return `https://www.youtube.com/embed/${shortMatch[1]}`;
    const watchMatch = url.match(/[?&]v=([\w-]+)/);
    if (watchMatch) return `https://www.youtube.com/embed/${watchMatch[1]}`;
    const youtuMatch = url.match(/youtu\.be\/([\w-]+)/);
    if (youtuMatch) return `https://www.youtube.com/embed/${youtuMatch[1]}`;
    return url;
}
</script>

<template>
    <AppLayout title="Отзывы">
        <!-- HERO -->
        <section class="testimonials-hero">
            <div class="testimonials-hero__bg" aria-hidden="true">
                <div class="bg-grid absolute inset-0 opacity-50"></div>
                <div class="absolute -top-1/3 -left-1/4 w-[60vw] h-[60vw] rounded-full opacity-20" style="background: radial-gradient(circle, rgba(239,68,68,0.30), transparent 60%);"></div>
            </div>

            <div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 relative pt-20 lg:pt-28 pb-12">
                <div class="space-y-5 max-w-4xl">
                    <span class="section-eyebrow">Реальные клиенты</span>
                    <h1 class="display-heading text-4xl sm:text-5xl lg:text-6xl text-ink-primary">
                        Отзывы клиентов <br class="hidden sm:block" />
                        <span class="text-accent">о нашей работе</span>
                    </h1>
                    <p class="text-lg text-ink-secondary leading-relaxed max-w-2xl">
                        Реальные истории людей, которые купили авто через нас — от подбора до выдачи. Без постановок и лишних слов.
                    </p>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <Link :href="route('catalog.index')" class="premium-btn">
                            Перейти в каталог
                            <i class="pi pi-arrow-right text-[10px]"></i>
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- VIDEO TESTIMONIALS -->
        <Section v-if="props.videoTestimonials.length">
            <div class="flex items-end justify-between mb-8 flex-wrap gap-3">
                <div>
                    <span class="section-eyebrow">Видео-отзывы</span>
                    <h2 class="display-heading text-2xl sm:text-3xl text-ink-primary mt-2">Истории от первого лица</h2>
                </div>
                <div class="text-sm text-ink-muted">{{ props.videoTestimonials.length }} видео</div>
            </div>

            <div class="video-grid">
                <article v-for="v in props.videoTestimonials" :key="`v-${v.id}`" class="video-card">
                    <div class="video-card__frame">
                        <iframe
                            :src="toEmbedUrl(v.video_url)"
                            :title="v.title"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                        ></iframe>
                    </div>
                    <div class="video-card__title">{{ v.title }}</div>
                </article>
            </div>
        </Section>

        <!-- TEXT TESTIMONIALS GRID -->
        <Section v-if="props.textTestimonials.length">
            <div class="flex items-end justify-between mb-8 flex-wrap gap-3">
                <div>
                    <span class="section-eyebrow">Текстовые отзывы</span>
                    <h2 class="display-heading text-2xl sm:text-3xl text-ink-primary mt-2">Что говорят клиенты</h2>
                </div>
                <div class="text-sm text-ink-muted">{{ props.textTestimonials.length }} отзывов</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6 auto-rows-fr">
                <article
                    v-for="(t, i) in visibleTestimonials"
                    :key="t.id"
                    class="testimonial"
                    :class="i % 5 === 0 ? 'testimonial--featured' : ''"
                >
                    <div class="testimonial__quote-mark">“</div>
                    <p class="testimonial__text">{{ t.text }}</p>
                    <div v-if="t.photo" class="testimonial__image-wrapper">
                        <img :src="t.photo.startsWith('http') ? t.photo : '/storage/' + t.photo" alt="Фото отзыва" class="testimonial__image" />
                    </div>
                    <div class="testimonial__footer">
                        <div class="testimonial__avatar">{{ t.author_name?.[0] }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="testimonial__name">{{ t.author_name }}</div>
                            <div class="testimonial__meta">
                                <span v-if="t.car_model">{{ t.car_model }}</span>
                                <span v-if="t.car_model && t.city">·</span>
                                <span v-if="t.city">{{ t.city }}</span>
                            </div>
                        </div>
                        <div class="testimonial__stars">
                            <i v-for="n in (t.rating || 5)" :key="n" class="pi pi-star-fill"></i>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex flex-wrap items-center justify-center gap-2 mt-12">
                <button
                    type="button"
                    class="page-btn"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                    aria-label="Предыдущая страница"
                >
                    <i class="pi pi-arrow-left text-xs"></i>
                </button>

                <button
                    v-for="page in pages"
                    :key="page"
                    type="button"
                    class="page-btn"
                    :class="{ 'is-active': page === currentPage }"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>

                <button
                    type="button"
                    class="page-btn"
                    :disabled="currentPage === totalPages"
                    @click="goToPage(currentPage + 1)"
                    aria-label="Следующая страница"
                >
                    <i class="pi pi-arrow-right text-xs"></i>
                </button>
            </div>
        </Section>

        <Section v-if="!props.videoTestimonials.length && !props.textTestimonials.length">
            <div class="text-center py-12 text-ink-secondary">
                Отзывы скоро появятся.
            </div>
        </Section>
    </AppLayout>
</template>

<style scoped>
.testimonials-hero {
    position: relative;
    overflow: hidden;
    background: rgb(var(--bg-base));
    border-bottom: 1px solid var(--border-subtle);
}

.testimonials-hero__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

/* Video grid */
.video-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(0, 280px));
    justify-content: center;
    gap: 1.5rem;
    align-items: stretch;
}

@media (min-width: 640px) {
    .video-grid {
        grid-template-columns: repeat(auto-fit, 260px);
    }
}

.video-card {
    width: 100%;
    max-width: 280px;
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: border-color var(--dur-base) ease, transform var(--dur-base) ease;
}

.video-card:hover {
    border-color: var(--border-strong);
    transform: translateY(-3px);
}

.video-card__frame {
    position: relative;
    width: 100%;
    aspect-ratio: 9 / 16;
    background: #000;
    flex-shrink: 0;
}

.video-card__frame iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}

.video-card__title {
    padding: 0.875rem 1rem 1rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: rgb(var(--text-primary));
    line-height: 1.4;
    flex: 1;
    min-height: 3.5rem;
}

/* Text testimonial */
.testimonial {
    position: relative;
    padding: 2rem 1.75rem 1.75rem;
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
    border-radius: var(--radius-lg);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    transition: border-color var(--dur-base) ease, transform var(--dur-base) ease;
    overflow: hidden;
}

.testimonial:hover {
    border-color: var(--border-strong);
    transform: translateY(-3px);
}

.testimonial--featured {
    background: linear-gradient(135deg, rgb(var(--bg-elevated)), rgb(var(--surface-1)));
}

.testimonial--featured::after {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 60% 40% at 80% 20%, var(--accent-soft), transparent 60%);
    pointer-events: none;
}

.testimonial__quote-mark {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: 4rem;
    line-height: 0.6;
    color: rgb(var(--accent));
    opacity: 0.3;
}

.testimonial__text {
    font-size: 0.9375rem;
    color: rgb(var(--text-secondary));
    line-height: 1.65;
    flex: 1;
    position: relative;
    z-index: 1;
}

.testimonial__image-wrapper {
    width: 100%;
    margin-top: 1rem;
}

.testimonial__image {
    width: 100%;
    max-height: 240px;
    object-fit: cover;
    border-radius: 1rem;
    box-shadow: inset 0 0 0 1px rgba(15, 23, 42, 0.05);
}

.testimonial__footer {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-subtle);
    position: relative;
    z-index: 1;
}

.testimonial__avatar {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: var(--accent-soft);
    color: rgb(var(--accent));
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1rem;
}

.testimonial__name {
    font-weight: 600;
    font-size: 0.9375rem;
    color: rgb(var(--text-primary));
}

.testimonial__meta {
    font-size: 0.75rem;
    color: rgb(var(--text-muted));
    display: flex;
    gap: 0.375rem;
}

.testimonial__stars {
    flex-shrink: 0;
    display: inline-flex;
    gap: 2px;
    color: #facc15;
    font-size: 0.6875rem;
}

/* Pagination */
.page-btn {
    min-width: 40px;
    height: 40px;
    padding: 0 0.875rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
    color: rgb(var(--text-secondary));
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color var(--dur-base) ease, color var(--dur-base) ease, border-color var(--dur-base) ease;
}

.page-btn:hover:not(:disabled) {
    color: rgb(var(--accent));
    border-color: rgb(var(--accent) / 0.5);
}

.page-btn.is-active {
    background: rgb(var(--accent));
    color: #fff;
    border-color: rgb(var(--accent));
}

.page-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>
