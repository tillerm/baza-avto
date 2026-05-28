<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { useForm, usePage } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import InputNumber from 'primevue/inputnumber';
import ProgressBar from 'primevue/progressbar';
import { computed, watch } from 'vue';
import Toast from 'primevue/toast';
import moment from 'moment/moment';
import SelectButton from 'primevue/selectbutton';
import Calendar from 'primevue/calendar';
import InputSwitch from 'primevue/inputswitch';

const props = defineProps({
    car: Object,
    statuses: Object,
});

const toast = useToast();
const page = usePage();
const isManager = computed(() => Boolean(page.props.access?.isManager));

const statusesOptions = computed(() =>
    Object.entries(props.statuses || {}).map(([value, name]) => ({
        name,
        value,
    })),
);

const form = useForm({
    // keep backward compatibility: if `car_price` not set, fallback to `price`
    car_price: props.car.car_price ? Number(props.car.car_price) : (props.car.price ? Number(props.car.price) : null),
    customs: props.car.customs ? Number(props.car.customs) : null,
    status: { name: props.statuses[props.car.status], value: props.car.status },
    pinned: props.car.pinned ?? false,
    vin: props.car.vin,
    chassis_number: props.car.chassis_number,
    body_number: props.car.body_number,
    mileage: props.car.mileage,
    color: props.car.color,
    state_number: props.car.state_number,
    pts_series: props.car.pts_series,
    pts_number: props.car.pts_number,
    pts_issued_by: props.car.pts_issued_by,
    pts_issued_at: props.car.pts_issued_at,
    sts_series: props.car.sts_series,
    sts_number: props.car.sts_number,
    sts_issued_by: props.car.sts_issued_by,
    sts_issued_at: props.car.sts_issued_at,
    release_date: props.car.release_date,
});

watch(
    () => props.car.status,
    (value) => {
        form.status = { name: props.statuses[value], value };
    },
);

const update = () => {
    form
        .transform((data) => ({
            ...data,
            release_date: data.release_date ? moment(data.release_date).format('YYYY-MM-DD') : null,
            pts_issued_at: data.pts_issued_at ? moment(data.pts_issued_at).format('YYYY-MM-DD') : null,
            sts_issued_at: data.sts_issued_at ? moment(data.sts_issued_at).format('YYYY-MM-DD') : null,
            status: data.status?.value ?? props.car.status,
            ...(isManager.value ? {} : { pinned: Boolean(data.pinned) }),
        }))
        .put(route('crm.cars.update', [props.car.id]), {
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Успешно', detail: 'Авто обновлено', life: 3000 });
            },
            onError: () => {
                toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Попробуйте снова', life: 3000 });
            },
        });
};
</script>

<template>
    <AppLayout title="Авто">
        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <Card>
                        <template #title>
                            Редактирование авто
                            <Toast />
                        </template>
                        <template #content>
                            <div class="space-y-8">
                                <div class="card flex flex-column md:flex-row sm:gap-3 gap-5">
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.car_price : ''">
                                            <InputNumber
                                                :max="9999999999.99"
                                                mode="currency"
                                                currency="RUB"
                                                locale="ru-RU"
                                                id="car_price"
                                                v-model.trim="form.car_price"
                                                :disabled="form.processing"
                                                :class="{'p-invalid': form.hasErrors && form.errors.car_price}"
                                            />
                                            <label>Стоимость авто</label>
                                        </span>
                                    </div>
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.customs : ''">
                                            <InputNumber
                                                :max="9999999999.99"
                                                mode="currency"
                                                currency="RUB"
                                                locale="ru-RU"
                                                id="customs"
                                                v-model.trim="form.customs"
                                                :disabled="form.processing"
                                                :class="{'p-invalid': form.hasErrors && form.errors.customs}"
                                            />
                                            <label>Таможенные пошлины</label>
                                        </span>
                                    </div>
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.color : ''">
                                            <InputText
                                                v-model.trim="form.color"
                                                :disabled="form.processing"
                                                :class="{'p-invalid': form.hasErrors && form.errors.color}"
                                            />
                                            <label>Цвет</label>
                                        </span>
                                    </div>
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.mileage : ''">
                                            <InputNumber
                                                :useGrouping="false"
                                                suffix=" км"
                                                v-model.trim="form.mileage"
                                                :disabled="form.processing"
                                                :class="{'p-invalid': form.hasErrors && form.errors.mileage}"
                                            />
                                            <label>Пробег</label>
                                        </span>
                                    </div>
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.release_date : ''">
                                            <Calendar
                                                v-model="form.release_date"
                                                :disabled="form.processing"
                                                :class="{'p-invalid': form.hasErrors && form.errors.release_date}"
                                                view="year"
                                                dateFormat="yy"
                                            />
                                            <label>Год выпуска</label>
                                        </span>
                                    </div>
                                </div>

                                <div class="card flex flex-column md:flex-row sm:gap-3 gap-5">
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.vin : ''">
                                            <InputText
                                                v-model.trim="form.vin"
                                                :disabled="form.processing"
                                                :class="{'p-invalid': form.hasErrors && form.errors.vin}"
                                            />
                                            <label>VIN</label>
                                        </span>
                                    </div>
                                </div>


                                <div class="card flex flex-column md:flex-row sm:gap-3 gap-5">
                                    <div class="flex">
                                        <SelectButton
                                            :disabled="form.processing"
                                            v-model="form.status"
                                            :options="statusesOptions"
                                            optionLabel="name"
                                            aria-labelledby="basic"
                                        />
                                    </div>
                                    <div v-if="!isManager" class="flex items-center gap-3">
                                        <InputSwitch v-model="form.pinned" :disabled="form.processing" />
                                        <span class="text-sm text-slate-700">Закрепить в каталоге</span>
                                    </div>
                                </div>

                                <div class="card flex justify-end mx-4">
                                    <Button :disabled="form.processing" @click="update" label="Сохранить" />
                                </div>
                            </div>
                            <ProgressBar v-if="form.processing" class="mt-8" mode="indeterminate" style="height: 3px" />
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
