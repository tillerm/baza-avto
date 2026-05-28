<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    photos: { type: Array, default: () => [] },
    startIndex: { type: Number, default: 0 },
});

const emit = defineEmits(['close']);

const current = ref(props.startIndex || 0);

watch(() => props.startIndex, (v) => {
    current.value = v || 0;
});

watch(() => props.photos, () => {
    if (current.value >= props.photos.length) current.value = 0;
});

watch(() => props.show, (v) => {
    if (typeof document === 'undefined') return;
    document.body.style.overflow = v ? 'hidden' : '';
});

const close = () => emit('close');

const prev = () => {
    if (!props.photos.length) return;
    current.value = (current.value - 1 + props.photos.length) % props.photos.length;
};

const next = () => {
    if (!props.photos.length) return;
    current.value = (current.value + 1) % props.photos.length;
};

const goTo = (i) => {
    current.value = i;
};

const keyHandler = (e) => {
    if (!props.show) return;
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'ArrowRight') next();
    if (e.key === 'Escape') close();
};

onMounted(() => document.addEventListener('keydown', keyHandler));
onUnmounted(() => {
    document.removeEventListener('keydown', keyHandler);
    if (typeof document !== 'undefined') document.body.style.overflow = '';
});

const currentPhoto = computed(() => props.photos[current.value] ?? null);
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-[100] bg-slate-950 flex flex-col"
                @click.self="close"
            >
                <!-- Top bar -->
                <div class="relative flex items-center px-4 sm:px-6 py-4 z-10">
                    <div v-if="photos.length" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 text-white text-xs font-semibold">
                        <i class="pi pi-images text-[11px]"></i>
                        {{ current + 1 }} / {{ photos.length }}
                    </div>
                    <button
                        type="button"
                        class="lightbox-btn ml-auto"
                        aria-label="Закрыть"
                        @click="close"
                    >
                        <i class="pi pi-times text-base"></i>
                    </button>
                </div>

                <!-- Main image area -->
                <div class="flex-1 relative flex items-center justify-center px-16 sm:px-20 min-h-0" @click.self="close">
                    <button
                        v-if="photos.length > 1"
                        type="button"
                        class="lightbox-nav-btn lightbox-nav-btn--left"
                        aria-label="Предыдущее фото"
                        @click.stop="prev"
                    >
                        <i class="pi pi-chevron-left text-lg"></i>
                    </button>

                    <img
                        v-if="currentPhoto"
                        :src="'/storage/' + currentPhoto.photo"
                        :key="currentPhoto.id ?? currentPhoto.photo"
                        alt="Фото автомобиля"
                        class="max-h-full max-w-full object-contain rounded-xl shadow-2xl select-none"
                        @click.stop
                    />
                    <div v-else class="text-white/70 text-sm">Фото отсутствуют</div>

                    <button
                        v-if="photos.length > 1"
                        type="button"
                        class="lightbox-nav-btn lightbox-nav-btn--right"
                        aria-label="Следующее фото"
                        @click.stop="next"
                    >
                        <i class="pi pi-chevron-right text-lg"></i>
                    </button>
                </div>

                <!-- Thumbnails -->
                <div v-if="photos.length > 1" class="px-4 sm:px-6 pb-5 pt-3">
                    <div class="flex items-center justify-center gap-2 overflow-x-auto pb-2">
                        <button
                            v-for="(p, i) in photos"
                            :key="p.id ?? p.photo"
                            type="button"
                            class="lightbox-thumb"
                            :class="{ 'is-active': current === i }"
                            @click="goTo(i)"
                        >
                            <img :src="'/storage/' + p.photo" alt="Миниатюра" />
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
