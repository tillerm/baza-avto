<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    news: Object,
});

const formattedDate = computed(() => {
    if (!props.news?.published_at) return '';
    try { return new Date(props.news.published_at).toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }); }
    catch (_) { return ''; }
});
</script>

<template>
    <AppLayout :title="news?.title || 'Новость'">
        <article class="news-show">
            <div class="news-show__bg" aria-hidden="true">
                <div class="bg-grid absolute inset-0 opacity-50"></div>
            </div>

            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 relative pt-16 lg:pt-24 pb-20 lg:pb-28">
                <Link :href="route('news.index')" class="back-link">
                    <i class="pi pi-arrow-left text-xs"></i>
                    Все новости
                </Link>

                <header class="space-y-5 mt-8 mb-12">
                    <span class="section-eyebrow">{{ formattedDate || 'Новость' }}</span>
                    <h1 class="display-heading text-4xl sm:text-5xl text-ink-primary text-balance">
                        {{ news?.title }}
                    </h1>
                </header>

                <div class="hairline mb-10"></div>

                <div class="news-content prose prose-lg max-w-none" v-html="news?.content"></div>
            </div>
        </article>
    </AppLayout>
</template>

<style scoped>
.news-show {
    position: relative;
    overflow: hidden;
    background: rgb(var(--bg-base));
    min-height: 100vh;
}

.news-show__bg {
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

.news-content :deep(h2) {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.875rem;
    letter-spacing: -0.02em;
    color: rgb(var(--text-primary));
    margin-top: 2.5rem;
    margin-bottom: 1rem;
}

.news-content :deep(h3) {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    font-size: 1.375rem;
    letter-spacing: -0.01em;
    color: rgb(var(--text-primary));
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}

.news-content :deep(p) {
    color: rgb(var(--text-secondary));
    line-height: 1.7;
    margin-bottom: 1.25rem;
}

.news-content :deep(p:first-of-type)::first-letter {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 800;
    font-size: 3.5rem;
    line-height: 1;
    float: left;
    margin: 0.25rem 0.75rem 0 0;
    color: rgb(var(--accent));
}

.news-content :deep(a) {
    color: rgb(var(--accent));
    text-decoration: underline;
    text-underline-offset: 3px;
    text-decoration-thickness: 1px;
}

.news-content :deep(a:hover) { color: rgb(var(--accent-hover)); }

.news-content :deep(blockquote) {
    border-left: 2px solid rgb(var(--accent));
    padding-left: 1.5rem;
    margin: 2rem 0;
    font-size: 1.125rem;
    color: rgb(var(--text-primary));
    font-style: italic;
}

.news-content :deep(ul),
.news-content :deep(ol) {
    color: rgb(var(--text-secondary));
    line-height: 1.7;
    margin-bottom: 1.25rem;
    padding-left: 1.5rem;
}

.news-content :deep(li) { margin-bottom: 0.5rem; }

.news-content :deep(img) {
    border-radius: var(--radius-lg);
    margin: 2rem 0;
    width: 100%;
    height: auto;
    border: 1px solid var(--border-subtle);
}

.news-content :deep(code) {
    background: var(--bg-glass);
    padding: 0.125rem 0.375rem;
    border-radius: var(--radius-sm);
    font-size: 0.875em;
    color: rgb(var(--accent));
}

.news-content :deep(strong) { color: rgb(var(--text-primary)); }
</style>
