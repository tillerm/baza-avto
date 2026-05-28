<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { useForm } from '@inertiajs/vue3';

defineProps({
    sources: Array,
});

const sourceForm = useForm({
    name: '',
    url: '',
});

const createSource = () => {
    sourceForm.post(route('crm.news.sources.store'), {
        onSuccess: () => sourceForm.reset(),
    });
};

const toggleSource = (id) => {
    useForm({}).put(route('crm.news.sources.toggle', [id]));
};
</script>

<template>
    <AppLayout title="Источники новостей">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Новости / Подписки
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-screen-lg mx-auto sm:px-6 lg:px-8 space-y-6">
                <Card>
                    <template #title>Добавить RSS</template>
                    <template #content>
                        <div class="flex flex-col gap-3">
                            <input
                                v-model="sourceForm.name"
                                placeholder="Название"
                                class="border rounded px-3 py-2"
                            />
                            <input
                                v-model="sourceForm.url"
                                placeholder="RSS URL"
                                class="border rounded px-3 py-2"
                            />
                            <div class="flex justify-end">
                                <Button label="Добавить источник" icon="pi pi-plus" @click="createSource" />
                            </div>
                        </div>
                    </template>
                </Card>

                <Card>
                    <template #title>Источники</template>
                    <template #content>
                        <div v-if="sources.length" class="space-y-2">
                            <div
                                v-for="source in sources"
                                :key="source.id"
                                class="flex items-center justify-between border rounded px-3 py-2"
                            >
                                <div>
                                    <div class="font-semibold">{{ source.name }}</div>
                                    <div class="text-sm text-gray-600">{{ source.url }}</div>
                                </div>
                                <Button
                                    :label="source.active ? 'Выключить' : 'Включить'"
                                    size="small"
                                    severity="warning"
                                    @click="toggleSource(source.id)"
                                />
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-600">Подписок пока нет.</div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
