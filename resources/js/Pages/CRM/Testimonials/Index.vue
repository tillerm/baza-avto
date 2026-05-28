<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    testimonials: {
        type: Array,
        default: () => [],
    },
});

const destroyItem = (id) => {
    if (!confirm('Удалить отзыв?')) return;
    router.delete(route('crm.testimonials.destroy', id));
};

const truncate = (value, max = 90) => {
    if (!value) return '';
    return value.length > max ? `${value.slice(0, max)}…` : value;
};
</script>

<template>
    <AppLayout title="Отзывы">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Отзывы клиентов</h1>
                        <p class="text-slate-600 text-sm">Текстовые и видео-отзывы, которые показываются на публичной странице.</p>
                    </div>
                    <Link
                        :href="route('crm.testimonials.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition"
                    >
                        + Добавить отзыв
                    </Link>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Тип</th>
                                <th class="px-4 py-3">Автор / Заголовок</th>
                                <th class="px-4 py-3">Содержание</th>
                                <th class="px-4 py-3">Позиция</th>
                                <th class="px-4 py-3">Статус</th>
                                <th class="px-4 py-3 text-right">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="item in props.testimonials" :key="item.id" class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="item.type === 'video' ? 'bg-rose-50 text-rose-700' : 'bg-sky-50 text-sky-700'"
                                    >
                                        {{ item.type === 'video' ? 'Видео' : 'Текст' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    <template v-if="item.type === 'video'">{{ item.title }}</template>
                                    <template v-else>
                                        <div>{{ item.author_name }}</div>
                                        <div class="text-xs font-normal text-slate-500">
                                            {{ item.car_model }}<template v-if="item.car_model && item.city"> · </template>{{ item.city }}
                                        </div>
                                    </template>
                                </td>
                                <td class="px-4 py-3 text-slate-600 max-w-md">
                                    <template v-if="item.type === 'video'">
                                        <a :href="item.video_url" target="_blank" rel="noopener" class="text-slate-700 underline break-all">
                                            {{ truncate(item.video_url, 60) }}
                                        </a>
                                    </template>
                                    <template v-else>
                                        {{ truncate(item.text) }}
                                    </template>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ item.position ?? 0 }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold"
                                        :class="item.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full"
                                            :class="item.is_active ? 'bg-emerald-500' : 'bg-slate-400'"
                                        ></span>
                                        {{ item.is_active ? 'Активен' : 'Скрыт' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <Link
                                            :href="route('crm.testimonials.edit', item.id)"
                                            class="text-slate-700 hover:text-slate-900 text-sm font-semibold"
                                        >
                                            Редактировать
                                        </Link>
                                        <button
                                            type="button"
                                            class="text-red-600 hover:text-red-700 text-sm font-semibold"
                                            @click="destroyItem(item.id)"
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!props.testimonials.length">
                                <td colspan="6" class="px-4 py-5 text-center text-slate-500 text-sm">Отзывов пока нет.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
