<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    teamMembers: {
        type: Array,
        default: () => [],
    },
});

const destroyItem = (id) => {
    if (!confirm('Удалить участника команды?')) return;
    router.delete(route('crm.team.destroy', id));
};
</script>

<template>
    <AppLayout title="Команда">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Команда</h1>
                        <p class="text-slate-600 text-sm">Управление командой на публичной странице.</p>
                    </div>
                    <Link
                        :href="route('crm.team.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition"
                    >
                        + Добавить участника
                    </Link>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Фото</th>
                                <th class="px-4 py-3">Имя</th>
                                <th class="px-4 py-3">Должность</th>
                                <th class="px-4 py-3">Телефон</th>
                                <th class="px-4 py-3">Город</th>
                                <th class="px-4 py-3">Позиция</th>
                                <th class="px-4 py-3">Активен</th>
                                <th class="px-4 py-3 text-right">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="item in props.teamMembers" :key="item.id" class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <div class="h-10 w-10 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center">
                                        <img
                                            v-if="item.photo"
                                            :src="'/storage/' + item.photo"
                                            alt="Фото участника"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else class="text-xs text-slate-400">Нет</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ item.name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ item.role ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ item.phone ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ item.city ?? '-' }}</td>
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
                                            :href="route('crm.team.edit', item.id)"
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
                            <tr v-if="!props.teamMembers.length">
                                <td colspan="8" class="px-4 py-5 text-center text-slate-500 text-sm">Участников команды пока нет.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
