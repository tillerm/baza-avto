<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { useForm } from '@inertiajs/vue3';

defineProps({
    news: Array,
});

const approveNews = (id) => {
    useForm({}).put(route('crm.news.approve', [id]));
};

const goEdit = (id) => {
    window.location = route('crm.news.edit', [id]);
};

const deleteNews = (id) => {
    useForm({}).delete(route('crm.news.destroy', [id]));
};
</script>

<template>
    <AppLayout title="Новости">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Новости / Список
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <Card>
                    <template #title>Список новостей</template>
                    <template #content>
                        <div v-if="news.length" class="space-y-3">
                            <div
                                v-for="item in news"
                                :key="item.id"
                                class="flex flex-col gap-2 border rounded px-3 py-2 sm:flex-row sm:items-start sm:justify-between"
                            >
                                <div class="max-w-4xl space-y-1">
                                    <div class="font-semibold flex gap-3">
                                        {{ item.title }}

                                        <span
                                            class="text-xs px-2 py-1 rounded"
                                            :class="item.approved ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                        >
                                            {{ item.approved ? 'Опубликовано' : 'Черновик' }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600">{{ item.published_at }}</div>
                                    <div class="text-sm text-gray-700 line-clamp-2" v-html="item.content"></div>
                                </div>
                                <div class="flex items-center gap-2 self-end sm:self-start">
                                    <Button
                                        label="Редактировать"
                                        size="small"
                                        severity="secondary"
                                        icon="pi pi-pencil"
                                        @click="goEdit(item.id)"
                                    />
                                    <Button
                                        v-if="!item.approved"
                                        label="Опубликовать"
                                        size="small"
                                        icon="pi pi-send"
                                        @click="approveNews(item.id)"
                                    />
                                    <Button
                                        v-else
                                        label="Удалить"
                                        size="small"
                                        severity="danger"
                                        icon="pi pi-trash"
                                        @click="deleteNews(item.id)"
                                    />
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-600">Новостей пока нет.</div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
