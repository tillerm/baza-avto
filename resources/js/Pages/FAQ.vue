<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Section from '@/Components/Site/Section.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    faqs: { type: Array, default: () => [] },
});

const search = ref('');

const filteredFaqs = computed(() => {
    const q = search.value.trim().toLowerCase();
    if (!q) return props.faqs;
    return (props.faqs || []).filter(f =>
        (f.question || '').toLowerCase().includes(q) || (f.answer || '').toLowerCase().includes(q)
    );
});
</script>

<template>
    <AppLayout title="FAQ">
        <!-- HERO -->
        <section class="faq-hero">
            <div class="faq-hero__bg" aria-hidden="true">
                <div class="bg-grid absolute inset-0 opacity-50"></div>
                <div class="absolute -top-1/3 -right-1/4 w-[60vw] h-[60vw] rounded-full opacity-20" style="background: radial-gradient(circle, rgba(239,68,68,0.30), transparent 60%);"></div>
            </div>

            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 relative pt-20 lg:pt-28 pb-12 text-center">
                <span class="section-eyebrow">Частые вопросы</span>
                <h1 class="display-heading text-4xl sm:text-5xl lg:text-6xl text-ink-primary mt-4">
                    Как мы работаем и чем <span class="text-accent">поможем</span>
                </h1>
                <p class="text-lg text-ink-secondary leading-relaxed mt-5">
                    Ответы на основные вопросы о подборе, проверках и сделках. Не нашли своего — напишите нам.
                </p>

                <div class="faq-search mt-8">
                    <i class="pi pi-search text-ink-muted"></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Поиск по вопросам…"
                        class="bg-transparent border-0 flex-1 text-ink-primary outline-none placeholder:text-ink-muted"
                    />
                    <button
                        v-if="search"
                        type="button"
                        class="text-ink-muted hover:text-accent transition"
                        @click="search = ''"
                        aria-label="Очистить"
                    >
                        <i class="pi pi-times text-sm"></i>
                    </button>
                </div>
            </div>
        </section>

        <!-- FAQ list -->
        <Section spacing="default">
            <div class="max-w-3xl mx-auto">
                <div v-if="filteredFaqs.length" class="faq-list">
                    <details
                        v-for="(item, index) in filteredFaqs"
                        :key="index"
                        class="faq-item"
                    >
                        <summary class="faq-summary">
                            <span class="faq-question">{{ item.question }}</span>
                            <span class="faq-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                            </span>
                        </summary>
                        <div class="faq-answer">{{ item.answer }}</div>
                    </details>
                </div>
                <div v-else class="text-center py-12">
                    <i class="pi pi-search text-3xl text-ink-muted"></i>
                    <p class="text-ink-secondary mt-3">По запросу «{{ search }}» ничего не найдено.</p>
                </div>
            </div>
        </Section>

        <!-- CTA -->
        <Section spacing="tight">
            <div class="faq-cta">
                <div class="faq-cta__bg" aria-hidden="true"></div>
                <div class="relative grid md:grid-cols-[1.2fr_auto] gap-8 items-center">
                    <div class="space-y-3">
                        <span class="section-eyebrow">Свяжитесь с нами</span>
                        <h2 class="display-heading text-3xl sm:text-4xl text-ink-primary">Нужна помощь?</h2>
                        <p class="text-ink-secondary leading-relaxed max-w-xl">
                            Напишите менеджеру — ответим в течение нескольких минут.
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
.faq-hero {
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid var(--border-subtle);
    background: rgb(var(--bg-base));
}

.faq-hero__bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.faq-search {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.875rem 1.25rem;
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
    transition: border-color var(--dur-base) ease;
}

.faq-search:focus-within {
    border-color: rgb(var(--accent));
    box-shadow: 0 0 0 4px var(--accent-soft);
}

.faq-list {
    border-top: 1px solid var(--border-subtle);
}

.faq-item {
    border-bottom: 1px solid var(--border-subtle);
}

.faq-summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 1.5rem 0.5rem 1.5rem 0;
    cursor: pointer;
    list-style: none;
}

.faq-summary::-webkit-details-marker { display: none; }

.faq-question {
    font-family: 'Manrope', 'Inter', sans-serif;
    font-weight: 600;
    font-size: 1.0625rem;
    letter-spacing: -0.01em;
    color: rgb(var(--text-primary));
}

.faq-icon {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 9999px;
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    color: rgb(var(--text-secondary));
    transition: transform var(--dur-base) cubic-bezier(0.34, 1.56, 0.64, 1), background-color var(--dur-base) ease, color var(--dur-base) ease;
}

.faq-icon svg { width: 14px; height: 14px; }

.faq-item[open] .faq-icon {
    transform: rotate(45deg);
    background: rgb(var(--accent));
    color: #fff;
    border-color: rgb(var(--accent));
}

.faq-answer {
    padding: 0 0.5rem 1.75rem 0;
    font-size: 0.9375rem;
    color: rgb(var(--text-secondary));
    line-height: 1.65;
}

.faq-cta {
    position: relative;
    overflow: hidden;
    padding: 2.5rem;
    border-radius: var(--radius-xl);
    background: rgb(var(--bg-elevated));
    border: 1px solid var(--border-subtle);
}

.faq-cta__bg {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 50% 50% at 100% 50%, var(--accent-soft), transparent 60%);
    pointer-events: none;
}
</style>
