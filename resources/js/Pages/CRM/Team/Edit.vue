<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    teamMember: {
        type: Object,
        required: true,
    },
    isNew: {
        type: Boolean,
        default: false,
    },
    managers: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    user_id: props.teamMember.user_id ?? props.teamMember.user?.id ?? null,
    name: props.teamMember.name ?? '',
    role: props.teamMember.role ?? '',
    city: props.teamMember.city ?? '',
    phone: props.teamMember.phone ?? '',
    telegram_username: props.teamMember.telegram_username ?? '',
    description: props.teamMember.description ?? '',
    photo: null,
    photo_focus_x: props.teamMember.photo_focus_x ?? 50,
    photo_focus_y: props.teamMember.photo_focus_y ?? 50,
    position: props.teamMember.position ?? 0,
    is_active: props.teamMember.is_active ?? true,
});

const normalizedUserId = computed(() => (form.user_id ? Number(form.user_id) : null));
const selectedManager = computed(() => props.managers.find((manager) => manager.id === normalizedUserId.value) ?? null);
const previewName = computed(() => selectedManager.value?.name || form.name || "Ім'я Прізвище");
const previewPhone = computed(() => selectedManager.value?.phone || form.phone || '');
const previewTelegram = computed(() => selectedManager.value?.telegram_username || form.telegram_username || '');

const onPhotoChange = (event) => {
    form.photo = event.target.files?.[0] ?? null;
};

const photoPreview = computed(() => {
    if (form.photo instanceof File) {
        return URL.createObjectURL(form.photo);
    }
    if (props.teamMember.photo) {
        return `/storage/${props.teamMember.photo}`;
    }
    return null;
});

const submit = () => {
    if (props.isNew) {
        form
            .transform((data) => ({
                ...data,
                user_id: data.user_id || null,
            }))
            .post(route('crm.team.store'), { forceFormData: true });
    } else {
        form
            .transform((data) => ({
                ...data,
                user_id: data.user_id || null,
                _method: 'put',
            }))
            .post(route('crm.team.update', props.teamMember.id), { forceFormData: true });
    }
};

const destroyItem = () => {
    if (!props.isNew && confirm('Удалить участника команды?')) {
        router.delete(route('crm.team.destroy', props.teamMember.id));
    }
};
</script>

<template>
    <AppLayout :title="props.isNew ? 'Створити учасника команди' : 'Редагувати учасника команди'">
        <div class="py-8">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">
                            {{ props.isNew ? 'Створити учасника команди' : 'Редагувати учасника команди' }}
                        </h1>
                        <p class="text-slate-600 text-sm">Оновлення блоку команди на головній сторінці.</p>
                    </div>
                    <div class="flex items-center gap-3" v-if="!props.isNew">
                        <button
                            type="button"
                            class="text-red-600 hover:text-red-700 text-sm font-semibold"
                            @click="destroyItem"
                        >
                            Видалити
                        </button>
                    </div>
                </div>

                <div class="flex items-start gap-6">
                    <div class="flex-1 min-w-0 bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-5">
                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Прив'язаний менеджер</label>
                            <select
                                v-model="form.user_id"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-inset focus:ring-slate-100"
                            >
                                <option value="">Без прив'язки</option>
                                <option v-for="manager in props.managers" :key="manager.id" :value="manager.id">
                                    {{ manager.name }}
                                </option>
                            </select>
                            <p class="text-xs text-slate-500">
                                Якщо менеджер вибраний, ім'я, телефон і Telegram на сайті будуть братись з його акаунта.
                            </p>
                            <p v-if="form.errors.user_id" class="text-sm text-red-600">{{ form.errors.user_id }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Ім'я</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-inset focus:ring-slate-100"
                                placeholder="Ім'я Прізвище"
                            />
                            <p v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2 min-w-0">
                                <label class="block text-sm font-semibold text-slate-800">Посада</label>
                                <input
                                    v-model="form.role"
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-inset focus:ring-slate-100"
                                    placeholder="Менеджер"
                                />
                                <p v-if="form.errors.role" class="text-sm text-red-600">{{ form.errors.role }}</p>
                            </div>
                            <div class="space-y-2 min-w-0">
                                <label class="block text-sm font-semibold text-slate-800">Місто</label>
                                <input
                                    v-model="form.city"
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-inset focus:ring-slate-100"
                                    placeholder="Київ"
                                />
                                <p v-if="form.errors.city" class="text-sm text-red-600">{{ form.errors.city }}</p>
                            </div>
                            <div class="space-y-2 min-w-0">
                                <label class="block text-sm font-semibold text-slate-800">Телефон</label>
                                <input
                                    v-model="form.phone"
                                    type="tel"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-inset focus:ring-slate-100"
                                    placeholder="+380 00 000 00 00"
                                />
                                <p v-if="form.errors.phone" class="text-sm text-red-600">{{ form.errors.phone }}</p>
                            </div>
                            <div class="space-y-2 min-w-0">
                                <label class="block text-sm font-semibold text-slate-800">Telegram username</label>
                                <input
                                    v-model="form.telegram_username"
                                    type="text"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-inset focus:ring-slate-100"
                                    placeholder="@manager_username"
                                />
                                <p v-if="form.errors.telegram_username" class="text-sm text-red-600">{{ form.errors.telegram_username }}</p>
                            </div>
                            <div class="space-y-2 min-w-0">
                                <label class="block text-sm font-semibold text-slate-800">Позиція (сортування)</label>
                                <input
                                    v-model.number="form.position"
                                    type="number"
                                    min="0"
                                    class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-inset focus:ring-slate-100"
                                />
                                <p v-if="form.errors.position" class="text-sm text-red-600">{{ form.errors.position }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Опис</label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="w-full rounded-lg border border-slate-200 px-3 py-3 text-slate-800 focus:border-slate-400 focus:ring focus:ring-inset focus:ring-slate-100"
                                placeholder="Коротко про співробітника"
                            ></textarea>
                            <p v-if="form.errors.description" class="text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-semibold text-slate-800">Фото</label>
                            <input
                                type="file"
                                accept="image/*"
                                @change="onPhotoChange"
                                class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:bg-slate-800"
                            />
                            <p v-if="form.errors.photo" class="text-sm text-red-600">{{ form.errors.photo }}</p>
                        </div>

                        <div class="space-y-3">
                            <div class="text-sm font-semibold text-slate-800">Photo position</div>
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-600">Horizontal: {{ form.photo_focus_x }}%</label>
                                <input
                                    v-model.number="form.photo_focus_x"
                                    type="range"
                                    min="0"
                                    max="100"
                                    class="w-full"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-600">Vertical: {{ form.photo_focus_y }}%</label>
                                <input
                                    v-model.number="form.photo_focus_y"
                                    type="range"
                                    min="0"
                                    max="100"
                                    class="w-full"
                                />
                            </div>
                            <p v-if="form.errors.photo_focus_x" class="text-sm text-red-600">{{ form.errors.photo_focus_x }}</p>
                            <p v-if="form.errors.photo_focus_y" class="text-sm text-red-600">{{ form.errors.photo_focus_y }}</p>
                        </div>

                        <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-4 py-3">
                            <div v-if="selectedManager" class="mb-3 text-sm text-slate-700">
                                Прив'язаний акаунт: <span class="font-semibold">{{ selectedManager.name }}</span>
                                <span v-if="selectedManager.phone"> · {{ selectedManager.phone }}</span>
                                <span v-if="selectedManager.telegram_username"> · {{ selectedManager.telegram_username }}</span>
                            </div>
                            <label for="is_active" class="inline-flex items-center gap-3">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 text-slate-900 border-slate-300 rounded"
                                />
                                <span class="text-sm font-semibold text-slate-800">Активний на сайті</span>
                            </label>
                        </div>

                        <div class="pt-2 flex flex-wrap items-center gap-3">
                            <button
                                type="button"
                                class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition disabled:opacity-70"
                                :disabled="form.processing"
                                @click="submit"
                            >
                                Зберегти
                            </button>
                            <button
                                type="button"
                                class="text-slate-600 hover:text-slate-800 text-sm font-semibold"
                                :disabled="form.processing"
                                @click="router.visit(route('crm.team.index'))"
                            >
                                Назад
                            </button>
                        </div>
                    </div>

                    <div class="w-[320px] shrink-0 space-y-3">
                        <div class="text-sm font-semibold text-slate-800">Предперегляд картки</div>
                        <div class="rounded-3xl bg-white shadow-sm border border-slate-200 overflow-hidden flex flex-col max-w-xs">
                            <div class="h-48 bg-gradient-to-br from-slate-300 via-slate-200 to-slate-300 flex items-center justify-center text-slate-600 text-sm">
                                <img
                                    v-if="photoPreview"
                                    :src="photoPreview"
                                    :alt="form.name || 'Фото співробітника'"
                                    class="h-full w-full object-cover"
                                    :style="{ objectPosition: `${form.photo_focus_x}% ${form.photo_focus_y}%` }"
                                />
                                <span v-else>Фото</span>
                            </div>
                            <div class="p-4 space-y-1">
                                <div class="text-lg font-bold text-slate-900 leading-snug">{{ previewName }}</div>
                                <div class="text-sm text-slate-600">{{ form.role || 'Посада' }}</div>
                                <div class="text-sm text-slate-600">{{ form.city || 'Місто' }}</div>
                                <div v-if="form.description" class="text-sm text-slate-600">
                                    {{ form.description }}
                                </div>
                                <div v-if="previewPhone" class="text-sm font-semibold text-slate-900">{{ previewPhone }}</div>
                                <a
                                    v-if="previewTelegram"
                                    :href="`https://t.me/${previewTelegram.replace(/^@/, '')}`"
                                    target="_blank"
                                    rel="noopener"
                                    class="inline-flex items-center gap-2 pt-2 text-sm font-semibold text-sky-600 hover:text-sky-700"
                                >
                                    Telegram
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
