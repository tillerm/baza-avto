<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stats: { type: Object, default: () => ({}) },
});

const widgets = [
    { label: 'Авто (всего)', key: 'carsTotal' },
    { label: 'В продаже', key: 'carsSelling' },
    { label: 'В наличии', key: 'carsPresent' },
    { label: 'Продано', key: 'carsSold' },
    { label: 'Заявки', key: 'requestsTotal' },
    { label: 'Заказы', key: 'ordersTotal' },
    { label: 'Марки', key: 'brandsTotal' },
    { label: 'Модели', key: 'modelsTotal' },
    { label: 'Двигатели', key: 'enginesTotal' },
    { label: 'Комплектации', key: 'equipmentsTotal' },
];
</script>

<template>
    <AppLayout title="CRM">
        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <div class="text-lg font-semibold text-slate-900">
                            Добро пожаловать, {{ $page.props.auth.user.name }}
                        </div>
                        <div class="mt-1 text-sm text-slate-500">
                            Базовая статистика по CRM
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div
                            v-for="w in widgets"
                            :key="w.key"
                            class="bg-white border border-slate-200 rounded-xl p-4"
                        >
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                {{ w.label }}
                            </div>
                            <div class="mt-2 text-3xl font-bold text-slate-900">
                                {{ props.stats?.[w.key] ?? 0 }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
