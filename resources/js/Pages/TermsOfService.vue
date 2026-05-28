<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    terms: String,
});
</script>

<template>
    <AppLayout title="Условия использования">
        <article class="legal-page">
            <div class="legal-page__bg" aria-hidden="true">
                <div class="bg-grid absolute inset-0 opacity-50"></div>
            </div>

            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 relative pt-16 lg:pt-24 pb-20 lg:pb-28">
                <header class="space-y-4 mb-12">
                    <span class="section-eyebrow">Документ</span>
                    <h1 class="display-heading text-4xl sm:text-5xl text-ink-primary">Условия использования</h1>
                </header>

                <div class="hairline mb-10"></div>

                <div class="legal-content prose prose-lg max-w-none" v-html="terms"></div>
            </div>
        </article>
    </AppLayout>
</template>

<style scoped>
.legal-page {
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    background: rgb(var(--bg-base));
}

.legal-page__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.legal-content :deep(h1),
.legal-content :deep(h2),
.legal-content :deep(h3) {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: rgb(var(--text-primary));
}

.legal-content :deep(h2) {
    font-size: 1.625rem;
    margin-top: 2.5rem;
    margin-bottom: 1rem;
}

.legal-content :deep(h3) {
    font-size: 1.25rem;
    margin-top: 2rem;
    margin-bottom: 0.75rem;
}

.legal-content :deep(p),
.legal-content :deep(li) {
    color: rgb(var(--text-secondary));
    line-height: 1.7;
}

.legal-content :deep(p) { margin-bottom: 1rem; }

.legal-content :deep(a) {
    color: rgb(var(--accent));
    text-decoration: underline;
    text-underline-offset: 3px;
}

.legal-content :deep(strong) { color: rgb(var(--text-primary)); }

.legal-content :deep(ul),
.legal-content :deep(ol) {
    padding-left: 1.5rem;
    margin-bottom: 1.25rem;
}

.legal-content :deep(li) { margin-bottom: 0.5rem; }
</style>
