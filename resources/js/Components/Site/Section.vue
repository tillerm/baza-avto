<script setup>
defineProps({
    eyebrow: { type: String, default: '' },
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    align: { type: String, default: 'left' }, // left | center
    /**
     * Spacing variant: 'default' (py-20 lg:py-32), 'tight' (py-12 lg:py-20), 'loose' (py-28 lg:py-40)
     */
    spacing: { type: String, default: 'default' },
    /** Apply max-width container (default true). */
    contained: { type: Boolean, default: true },
    /** Render as a tag — section by default. */
    as: { type: String, default: 'section' },
});
</script>

<template>
    <component
        :is="as"
        class="site-section"
        :class="[
            spacing === 'tight' ? 'py-12 lg:py-20' : spacing === 'loose' ? 'py-28 lg:py-40' : 'py-20 lg:py-32',
        ]"
    >
        <div :class="contained ? 'max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8' : ''">
            <header
                v-if="eyebrow || title || subtitle"
                class="site-section__head"
                :class="align === 'center' ? 'site-section__head--center' : ''"
            >
                <div v-if="eyebrow" class="section-eyebrow">{{ eyebrow }}</div>
                <h2
                    v-if="title"
                    class="site-section__title font-display"
                >
                    <slot name="title">{{ title }}</slot>
                </h2>
                <p v-if="subtitle" class="site-section__subtitle">
                    <slot name="subtitle">{{ subtitle }}</slot>
                </p>
                <slot name="head-extra" />
            </header>

            <slot />
        </div>
    </component>
</template>

<style scoped>
.site-section__head {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.875rem;
    margin-bottom: 3rem;
    max-width: 56rem;
}

.site-section__head--center {
    align-items: center;
    text-align: center;
    margin-left: auto;
    margin-right: auto;
}

.site-section__title {
    font-weight: 800;
    font-size: clamp(1.875rem, 4vw, 3rem);
    line-height: 1.05;
    letter-spacing: -0.03em;
    color: rgb(var(--text-primary));
    text-wrap: balance;
}

.site-section__subtitle {
    font-size: clamp(1rem, 1.5vw, 1.125rem);
    color: rgb(var(--text-secondary));
    line-height: 1.55;
    max-width: 42rem;
    text-wrap: balance;
}

@media (min-width: 1024px) {
    .site-section__head {
        margin-bottom: 4rem;
    }
}
</style>
