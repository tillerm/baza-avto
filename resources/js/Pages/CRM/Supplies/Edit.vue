<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import ProgressBar from 'primevue/progressbar';
import Toast from 'primevue/toast';

const props = defineProps({
    supply: Object,
    equipments: Object,
});

const toast = useToast();

const form = useForm({
    price: props.supply.price === null ? null : Number(props.supply.price),
    equipment_id: props.supply.equipment,
});

const update = () => {
    form.transform((data) => ({
        ...data,
        equipment_id: data.equipment_id ? data.equipment_id.id : null,
    })).put(route('crm.supplies.update', [props.supply.id]), {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Успешно', detail: 'Поступление обновлено', life: 3000 });
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Произошла ошибка', life: 3000 });
        },
    });
};
</script>

<template>
    <AppLayout title="Поступления">
        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <Card>
                        <template #title>
                            Редактирование поступления
                            <Toast />
                        </template>
                        <template #content>
                            <div class="space-y-8">
                                <div class="card flex flex-column md:flex-row sm:gap-3 gap-5">
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.equipment_id : ''">
                                            <Dropdown
                                                v-model="form.equipment_id"
                                                :disabled="form.processing"
                                                :class="{ 'p-invalid': form.hasErrors && form.errors.equipment_id }"
                                                :options="equipments"
                                                filter
                                                :filterFields="['name', 'engine.name', 'model.name', 'model.brand.name']"
                                            >
                                                <template #value="slotProps">
                                                    <div v-if="slotProps.value" class="flex align-items-center">
                                                        <div>{{ slotProps.value.name }} | {{ slotProps.value.model?.name ?? 'Без модели' }} | {{ slotProps.value.model?.brand?.name ?? 'Без марки' }} | {{ slotProps.value.engine?.name ?? 'Без двигателя' }}</div>
                                                    </div>
                                                </template>
                                                <template #option="slotProps">
                                                    <div class="flex align-items-center">
                                                        <div>{{ slotProps.option.name }} | {{ slotProps.option.model?.name ?? 'Без модели' }} | {{ slotProps.option.model?.brand?.name ?? 'Без марки' }} | {{ slotProps.option.engine?.name ?? 'Без двигателя' }}</div>
                                                    </div>
                                                </template>
                                            </Dropdown>
                                            <label>Комплектация</label>
                                        </span>
                                    </div>
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.price : ''">
                                            <InputNumber
                                                id="price"
                                                v-model.trim="form.price"
                                                :disabled="form.processing"
                                                :class="{ 'p-invalid': form.hasErrors && form.errors.price }"
                                                :max="9999999999.99"
                                                mode="currency"
                                                currency="RUB"
                                                locale="ru-RU"
                                            />
                                            <label>Цена</label>
                                        </span>
                                    </div>
                                </div>
                                <div class="card flex justify-end mx-4">
                                    <Button :disabled="form.processing" @click="update" label="Обновить" />
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
