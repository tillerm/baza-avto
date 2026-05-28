<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    news: Object,
});

const form = useForm({
    title: props.news.title ?? '',
    content: props.news.content ?? '',
    published_at: props.news.published_at ?? '',
    approved: props.news.approved ?? false,
});

const updateNews = () => {
    form.put(route('crm.news.update', [props.news.id]), {
        onSuccess: () => form.reset('approved'),
    });
};
</script>

<template>
    <AppLayout title="Редактировать новость">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Новости / Редактирование
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-screen-lg mx-auto sm:px-6 lg:px-8 space-y-6">
                <Card>
                    <template #title>Редактировать новость</template>
                    <template #content>
                        <div class="flex flex-col gap-3">
                            <input
                                v-model="form.title"
                                placeholder="Заголовок"
                                class="border rounded px-3 py-2"
                            />
                            <textarea
                                v-model="form.content"
                                placeholder="Содержимое (HTML поддерживается)"
                                rows="8"
                                class="border rounded px-3 py-2"
                            ></textarea>
                            <input
                                v-model="form.published_at"
                                type="datetime-local"
                                class="border rounded px-3 py-2"
                            />
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input type="checkbox" v-model="form.approved" />
                                Опубликовано
                            </label>
                            <div class="flex justify-end">
                                <Button label="Сохранить" icon="pi pi-check" @click="updateNews" />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
