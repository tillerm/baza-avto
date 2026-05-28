<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { useForm } from '@inertiajs/vue3';

const newsForm = useForm({
    title: '',
    content: '',
    published_at: '',
});

const createNews = () => {
    newsForm.post(route('crm.news.store'), {
        onSuccess: () => newsForm.reset(),
    });
};
</script>

<template>
    <AppLayout title="Создать новость">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Новости / Создание
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-screen-lg mx-auto sm:px-6 lg:px-8 space-y-6">
                <Card>
                    <template #title>Создать новость вручную</template>
                    <template #content>
                        <div class="flex flex-col gap-3">
                            <input
                                v-model="newsForm.title"
                                placeholder="Заголовок"
                                class="border rounded px-3 py-2"
                            />
                            <textarea
                                v-model="newsForm.content"
                                placeholder="Содержимое"
                                rows="8"
                                class="border rounded px-3 py-2"
                            ></textarea>
                            <input
                                v-model="newsForm.published_at"
                                type="datetime-local"
                                class="border rounded px-3 py-2"
                            />
                            <div class="flex justify-end">
                                <Button label="Сохранить" icon="pi pi-check" @click="createNews" />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
