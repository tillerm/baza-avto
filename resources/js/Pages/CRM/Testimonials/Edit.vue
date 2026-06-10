<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    testimonial: {
        type: Object,
        required: true,
    },
    isNew: {
        type: Boolean,
        default: false,
    },
});

const photoPreview = ref(null);
const photoInput = ref(null);

const form = useForm({
    type: props.testimonial.type ?? 'text',
    title: props.testimonial.title ?? '',
    text: props.testimonial.text ?? '',
    author_name: props.testimonial.author_name ?? '',
    car_model: props.testimonial.car_model ?? '',
    city: props.testimonial.city ?? '',
    rating: props.testimonial.rating ?? 5,
    video_url: props.testimonial.video_url ?? '',
    photo: null,
    position: props.testimonial.position ?? 0,
    is_active: props.testimonial.is_active ?? true,
});

const isVideo = computed(() => form.type === 'video');

const onPhotoChange = () => {
    const photo = photoInput.value?.files?.[0];
    if (!photo) return;

    form.photo = photo;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target?.result;
    };
    reader.readAsDataURL(photo);
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        type: form.type ?? 'text',
    }));

    if (props.isNew) {
        form.post(route('crm.testimonials.store'), { forceFormData: true });
    } else {
        form.put(route('crm.testimonials.update', props.testimonial.id), { forceFormData: true });
    }
};

const destroyItem = () => {
    if (!props.isNew && confirm('Удалить отзыв?')) {
        router.delete(route('crm.testimonials.destroy', props.testimonial.id));
    }
};
</script>

<template>
    <AppLayout :title="props.isNew ? 'Новый отзыв' : 'Редактировать отзыв'">
        <div class="py-8">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">
                            {{ props.isNew ? 'Новый отзыв' : 'Редактировать отзыв' }}
                        </h1>
                        <p class="text-slate-600 text-sm">Выберите тип отзыва и заполните поля. Отзыв сразу попадёт на публичную страницу.</p>
                    </div>
                    <div class="flex items-center gap-3" v-if="!props.isNew">
                        <button
                            type="button"
                            class="text-red-600 hover:text-red-700 text-sm font-semibold"
                            @click="destroyItem"
                        >
                            Удалить
                        </button>
                    </div>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-800">Тип отзыва</label>
                        <input type="hidden" name="type" :value="form.type" />
                        <div class="inline-flex p-1 bg-slate-100 rounded-lg">
                            <button
                                type="button"
                                class="px-4 py-1.5 text-sm font-semibold rounded-md transition"
                                :class="form.type === 'text' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                @click="form.type = 'text'"
                            >
                                Текст
                            </button>
                            <button
                                type="button"
                                class="px-4 py-1.5 text-sm font-semibold rounded-md transition"
                                :class="form.type === 'video' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                @click="form.type = 'video'"
                            >
                                Видео (YouTube Shorts)
                            </button>
                        </div>
                        <p v-if="form.errors.type" class="text-sm text-red-600">{{ form.errors.type }}</p>
                    </div>

                    <template v-if="isVideo">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Название</label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-slate-100"
                                placeholder="VOYAH FREE · отзыв Геннадия"
                            />
                            <p v-if="form.errors.title" class="text-sm text-red-600">{{ form.errors.title }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">YouTube Shorts URL</label>
                            <input
                                v-model="form.video_url"
                                type="url"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-slate-100"
                                placeholder="https://www.youtube.com/embed/shorts/xxxx"
                            />
                            <p v-if="form.errors.video_url" class="text-sm text-red-600">{{ form.errors.video_url }}</p>
                        </div>
                    </template>

                    <template v-else>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-800">Имя клиента</label>
                                <input
                                    v-model="form.author_name"
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-slate-100"
                                    placeholder="Игорь"
                                />
                                <p v-if="form.errors.author_name" class="text-sm text-red-600">{{ form.errors.author_name }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-800">Город</label>
                                <input
                                    v-model="form.city"
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-slate-100"
                                    placeholder="Москва"
                                />
                                <p v-if="form.errors.city" class="text-sm text-red-600">{{ form.errors.city }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-800">Модель авто</label>
                                <input
                                    v-model="form.car_model"
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-slate-100"
                                    placeholder="Audi A6"
                                />
                                <p v-if="form.errors.car_model" class="text-sm text-red-600">{{ form.errors.car_model }}</p>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-slate-800">Оценка (1–5)</label>
                                <input
                                    v-model.number="form.rating"
                                    type="number"
                                    min="1"
                                    max="5"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-slate-100"
                                />
                                <p v-if="form.errors.rating" class="text-sm text-red-600">{{ form.errors.rating }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Текст отзыва</label>
                            <textarea
                                v-model="form.text"
                                rows="6"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-slate-100"
                                placeholder="Расскажите историю клиента..."
                            ></textarea>
                            <p v-if="form.errors.text" class="text-sm text-red-600">{{ form.errors.text }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Фото (опционально)</label>
                            <input
                                ref="photoInput"
                                type="file"
                                accept="image/*"
                                @change="onPhotoChange"
                                :disabled="form.processing"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-slate-800 focus:border-slate-400 focus:ring focus:ring-slate-100"
                            />
                            <p v-if="form.errors.photo" class="text-sm text-red-600">{{ form.errors.photo }}</p>
                            <div v-if="props.testimonial.photo && !photoPreview" class="mt-3">
                                <div class="text-sm text-slate-600 mb-2">Текущее фото:</div>
                                <img :src="props.testimonial.photo.startsWith('http') ? props.testimonial.photo : '/storage/' + props.testimonial.photo" alt="Фото отзыва" class="max-h-48 rounded-lg object-cover" />
                            </div>
                            <div v-if="photoPreview" class="mt-3">
                                <div class="text-sm text-slate-600 mb-2">Выбрано фото:</div>
                                <img :src="photoPreview" alt="Новый файл" class="max-h-48 rounded-lg object-cover" />
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Позиция (сортировка)</label>
                            <input
                                v-model.number="form.position"
                                type="number"
                                min="0"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-slate-100"
                            />
                            <p v-if="form.errors.position" class="text-sm text-red-600">{{ form.errors.position }}</p>
                        </div>

                        <div class="flex items-center gap-3 pt-6">
                            <input
                                id="is_active"
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 text-slate-900 border-slate-300 rounded"
                            />
                            <label for="is_active" class="text-sm font-semibold text-slate-800">Показывать</label>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition disabled:opacity-70"
                            :disabled="form.processing"
                            @click="submit"
                        >
                            Сохранить
                        </button>
                        <button
                            type="button"
                            class="text-slate-600 hover:text-slate-800 text-sm font-semibold"
                            :disabled="form.processing"
                            @click="router.visit(route('crm.testimonials.index'))"
                        >
                            Отмена
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
