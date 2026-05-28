<script setup>
defineProps({
    label: { type: String, required: true },
    /** Optional value subtext shown after the label, e.g. "Toyota Camry" */
    value: { type: String, default: '' },
    /** Show a remove (×) action on the right. */
    removable: { type: Boolean, default: true },
});

defineEmits(['remove']);
</script>

<template>
    <span class="filter-chip" :class="{ 'is-static': !removable }">
        <span class="filter-chip__label">
            <span class="filter-chip__key">{{ label }}</span>
            <span v-if="value" class="filter-chip__value">{{ value }}</span>
        </span>
        <button
            v-if="removable"
            type="button"
            class="filter-chip__close"
            @click.prevent="$emit('remove')"
            aria-label="Удалить фильтр"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>
    </span>
</template>

<style scoped>
.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.5rem 0.375rem 0.875rem;
    font-size: 0.8125rem;
    color: rgb(var(--text-primary));
    background: var(--bg-glass);
    border: 1px solid var(--border-subtle);
    border-radius: 9999px;
    transition: border-color var(--dur-base) ease;
}

.filter-chip:hover {
    border-color: var(--border-strong);
}

.filter-chip.is-static {
    padding-right: 0.875rem;
}

.filter-chip__label {
    display: inline-flex;
    align-items: baseline;
    gap: 0.375rem;
}

.filter-chip__key {
    font-weight: 600;
    color: rgb(var(--text-secondary));
    font-size: 0.6875rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.filter-chip__value {
    color: rgb(var(--text-primary));
    font-weight: 500;
}

.filter-chip__close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 9999px;
    background: transparent;
    color: rgb(var(--text-muted));
    border: none;
    cursor: pointer;
    transition: color var(--dur-base) ease, background-color var(--dur-base) ease;
}

.filter-chip__close:hover {
    color: #fff;
    background: rgb(var(--accent));
}

.filter-chip__close svg {
    width: 12px;
    height: 12px;
}
</style>
