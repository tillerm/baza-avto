<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    managers: {
        type: Array,
        default: () => [],
    },
});

const destroyItem = (id) => {
    if (!confirm('Удалить менеджера?')) return;
    router.delete(route('crm.managers.destroy', id));
};
</script>

<template>
    <AppLayout title="Менеджеры">
        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Менеджеры</h1>
                        <p class="text-slate-600 text-sm">Отдельные аккаунты менеджеров для CRM и каталога.</p>
                    </div>
                    <Link
                        :href="route('crm.managers.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition"
                    >
                        + Добавить менеджера
                    </Link>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Имя</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Телефон</th>
                                <th class="px-4 py-3">Telegram</th>
                                <th class="px-4 py-3">Авто</th>
                                <th class="px-4 py-3 text-right">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            <tr v-for="manager in props.managers" :key="manager.id" class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ manager.name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ manager.email }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ manager.phone ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ manager.telegram_username ?? '-' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ manager.cars_count ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <Link
                                            :href="route('crm.managers.edit', manager.id)"
                                            class="text-slate-700 hover:text-slate-900 text-sm font-semibold"
                                        >
                                            Редактировать
                                        </Link>
                                        <button
                                            type="button"
                                            class="text-red-600 hover:text-red-700 text-sm font-semibold"
                                            @click="destroyItem(manager.id)"
                                        >
                                            Удалить
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!props.managers.length">
                                <td colspan="6" class="px-4 py-5 text-center text-slate-500 text-sm">Менеджеров пока нет.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
