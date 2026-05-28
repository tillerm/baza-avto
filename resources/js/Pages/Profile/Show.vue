<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import { Link, router } from '@inertiajs/vue3';
import moment from 'moment';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
    profileCars: Array,
});

const formatMoney = (value) => {
    if (value === null || value === undefined || value === '') {
        return '';
    }

    return new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(value);
};

const statusLabels = {
    PRESENT: 'В наличии',
    SELLING: 'В продаже',
    SOLD: 'Продано',
};

const markAsSold = (car) => {
    if (!car?.id || car.status === 'SOLD') {
        return;
    }

    router.put(route('crm.cars.sold', [car.id]), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Профиль
            </h2>
        </template>

        <div>
            <div class="max-w-8xl mx-auto py-10 sm:px-6 lg:px-8">
                <div class="sm:mt-0">
                    <h3 class="text-lg font-medium text-gray-900">Мои авто</h3>
                    <p class="mt-1 text-sm text-gray-600">Список автомобилей, закрепленных за вашим аккаунтом.</p>

                    <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">ID</th>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">Авто</th>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">Комплектация</th>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">Статус</th>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">VIN</th>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">Год</th>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">Цвет</th>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">Пробег</th>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">Стоимость</th>
                                    <th class="px-2 py-2 text-left font-semibold text-gray-700">Дії</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-if="!profileCars?.length">
                                    <td colspan="10" class="px-4 py-6 text-center text-gray-500">Автомобили не найдены</td>
                                </tr>
                                <tr v-for="car in profileCars" :key="car.id">
                                    <td class="px-2 py-2 font-medium text-gray-900">{{ car.id }}</td>
                                    <td class="px-2 py-2 text-gray-700">{{ [car.brand, car.model].filter(Boolean).join(' ') }}</td>
                                    <td class="px-2 py-2 text-gray-700">{{ car.equipment || '' }}</td>
                                    <td class="px-2 py-2 text-gray-700">{{ statusLabels[car.status] || car.status }}</td>
                                    <td class="px-2 py-2 text-gray-700">{{ car.vin || '' }}</td>
                                    <td class="px-2 py-2 text-gray-700">{{ car.release_date ? moment(car.release_date).format('YYYY') : '' }}</td>
                                    <td class="px-2 py-2 text-gray-700">{{ car.color || '' }}</td>
                                    <td class="px-2 py-2 text-gray-700">{{ car.mileage ? `${car.mileage} км` : '' }}</td>
                                    <td class="px-2 py-2 text-gray-700">{{ formatMoney(car.car_price ?? car.price) }}</td>
                                    <td class="px-2 py-2 text-gray-700">
                                        <div class="flex flex-wrap gap-2">
                                            <Link :href="route('crm.cars.show', [car.id])" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                Открыть
                                            </Link>
                                            <Link :href="route('crm.cars.edit', [car.id])" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                                Редактировать
                                            </Link>
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-emerald-300 px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 disabled:opacity-50"
                                                :disabled="car.status === 'SOLD'"
                                                @click="markAsSold(car)"
                                            >
                                                Продать
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <SectionBorder />

                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <details class="mt-10 sm:mt-0 rounded-lg border border-gray-200 bg-white">
                        <summary class="cursor-pointer list-none px-4 py-3 text-base font-semibold text-gray-900">Обновить пароль</summary>
                        <div class="px-4 pb-4">
                            <UpdatePasswordForm class="sm:mt-0" />
                        </div>
                    </details>
                </div>

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                    <TwoFactorAuthenticationForm
                        :requires-confirmation="confirmsTwoFactorAuthentication"
                        class="mt-10 sm:mt-0"
                    />
                </div>

                <details class="mt-10 sm:mt-5 rounded-lg border border-gray-200 bg-white">
                    <summary class="cursor-pointer list-none px-4 py-3 text-base font-semibold text-gray-900">Сеансы</summary>
                    <div class="px-4 pb-4">
                        <LogoutOtherBrowserSessionsForm :sessions="sessions" class="sm:mt-0" />
                    </div>
                </details>

                <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                    <SectionBorder />

                    <DeleteUserForm class="mt-10 sm:mt-0" />
                </template>
            </div>
        </div>
    </AppLayout>
</template>
