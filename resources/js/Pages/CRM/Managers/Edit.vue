<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    manager: {
        type: Object,
        required: true,
    },
    isNew: {
        type: Boolean,
        default: false,
    },
});

const form = useForm({
    name: props.manager.name ?? '',
    email: props.manager.email ?? '',
    telegram_username: props.manager.telegram_username ?? '',
    phone: props.manager.phone ?? '',
    password: '',
});

const submit = () => {
    if (props.isNew) {
        form.post(route('crm.managers.store'));
        return;
    }

    form
        .transform((data) => ({
            ...data,
            _method: 'put',
        }))
        .post(route('crm.managers.update', props.manager.id));
};

const destroyItem = () => {
    if (!props.isNew && confirm('Удалить менеджера?')) {
        router.delete(route('crm.managers.destroy', props.manager.id));
    }
};
</script>

<template>
    <AppLayout :title="props.isNew ? 'Создать менеджера' : 'Редактировать менеджера'">
        <div class="py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">
                            {{ props.isNew ? 'Создать менеджера' : 'Редактировать менеджера' }}
                        </h1>
                        <p class="text-slate-600 text-sm">Аккаунт менеджера для входа в CRM и связи из каталога.</p>
                    </div>
                    <button
                        v-if="!props.isNew"
                        type="button"
                        class="text-red-600 hover:text-red-700 text-sm font-semibold"
                        @click="destroyItem"
                    >
                        Удалить
                    </button>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-800">ФИО</label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800"
                            placeholder="Иван Петров"
                        />
                        <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Email</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800"
                                placeholder="manager@example.com"
                            />
                            <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Телефон</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800"
                                placeholder="+380 00 000 00 00"
                            />
                            <p v-if="form.errors.phone" class="text-sm text-red-600">{{ form.errors.phone }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Telegram username</label>
                            <input
                                v-model="form.telegram_username"
                                type="text"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800"
                                placeholder="@manager_username"
                            />
                            <p v-if="form.errors.telegram_username" class="text-sm text-red-600">{{ form.errors.telegram_username }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-semibold text-slate-800">
                            {{ props.isNew ? 'Пароль' : 'Новый пароль' }}
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800"
                            :placeholder="props.isNew ? 'Минимум 8 символов' : 'Оставьте пустым, если менять не нужно'"
                        />
                        <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <div class="pt-2 flex flex-wrap items-center gap-3">
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
                            @click="router.visit(route('crm.managers.index'))"
                        >
                            Назад
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
