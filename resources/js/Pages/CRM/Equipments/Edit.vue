<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import Button from 'primevue/button';
import {useForm} from "@inertiajs/vue3";
import {useToast} from "primevue/usetoast";
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import ProgressBar from 'primevue/progressbar';
import {onMounted, ref} from "vue";
import Toast from 'primevue/toast';

const props = defineProps({
    equipment: Object,
    engines: Object,
    models: Object,
    types: Object,
    bodies: Object,
    fuels: Object,
});

const toast = useToast();
let dropdownBodies = ref();
let dropdownModels = ref();
let dropdownTypes = ref();

const form = useForm({
    name: props.equipment.name,
    type: props.equipment.type,
    doors_count: props.equipment.doors_count,
    seats_count: props.equipment.seats_count,
    body: props.equipment.body,
    model_id: props.equipment.model_id,
    engine_id: props.equipment.engine
});

onMounted(() => {
    dropdownBodies.value = [];
    dropdownModels.value = [];
    dropdownTypes.value = [];
    Object.entries(props.bodies).forEach(entry => {
        const [key, value] = entry;
        dropdownBodies.value.push({option: value, value: key})
    })
    Object.entries(props.types).forEach(entry => {
        const [key, value] = entry;
        dropdownTypes.value.push({option: value, value: key})
    })
    props.models.forEach(model => {
        dropdownModels.value.push({
            option: `${model.name} | ${model.brand?.name ?? 'Без марки'}`,
            value: model.id,
        });
    });
});

const update = () => {
    form.transform((data) => {
        return {
            ...data,
            model_id: data.model_id,
            engine_id: data.engine_id ? data.engine_id.id : null,
        };
    })
        .put(route('crm.equipments.update', [props.equipment.id]), {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Успешно', detail: 'Комплектация обновлена', life: 3000 });
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Произошла ошибка', life: 3000 });
        },
    });
};
</script>

<template>
    <AppLayout title="Комплектации">
        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <Card>
                        <template #title>
                            Редактирование комплектации
                            <Toast />
                        </template>
                        <template #content>
                            <div class="space-y-10 pt-2">
                                <div class="space-y-4">
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.name : ''" >
                                            <InputText v-model.trim="form.name" v-bind:disabled="form.processing" autofocus :class="{'p-invalid': form.hasErrors && form.errors.name}"/>
                                            <label>Наименование</label>
                                        </span>
                                    </div>
                                    <div class="equipment-double-row">
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.type : ''">
                                            <Dropdown id="type" v-model="form.type" :options="dropdownTypes" v-bind:disabled="form.processing" :class="{'p-invalid': form.hasErrors && form.errors.type}" optionLabel="option" optionValue="value" class="w-full" />
                                            <label>Тип</label>
                                        </span>
                                    </div>
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.body : ''">
                                            <Dropdown id="type" v-model="form.body" :options="dropdownBodies" v-bind:disabled="form.processing" :class="{'p-invalid': form.hasErrors && form.errors.body}" optionLabel="option" optionValue="value" class="w-full" />
                                            <label>Кузов</label>
                                        </span>
                                    </div>
                                    </div>
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.engine_id : ''">
                                            <Dropdown v-bind:disabled="form.processing" :class="{'p-invalid': form.hasErrors && form.errors.engine_id}" v-model="form.engine_id" :options="engines" filter optionLabel="name">
                                                <template #value="slotProps">
                                                    <div v-if="slotProps.value" class="flex align-items-center">
                                                        <div>
                                                            {{ slotProps.value.name }}
                                                            <span v-if="slotProps.value.fuel" class="text-slate-500"> | {{ fuels?.[slotProps.value.fuel] ?? slotProps.value.fuel }}</span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template #option="slotProps">
                                                    <div class="flex align-items-center">
                                                        <div>
                                                            {{ slotProps.option.name }}
                                                        <span v-if="slotProps.option.fuel" class="text-slate-500"> | {{ fuels?.[slotProps.option.fuel] ?? slotProps.option.fuel }}</span>
                                                        </div>
                                                    </div>
                                                </template>
                                            </Dropdown>
                                            <label>Двигатель</label>
                                        </span>
                                    </div>
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.model_id : ''">
                                            <Dropdown
                                                id="model_id"
                                                v-model="form.model_id"
                                                :options="dropdownModels"
                                                v-bind:disabled="form.processing"
                                                :class="{'p-invalid': form.hasErrors && form.errors.model_id}"
                                                optionLabel="option"
                                                optionValue="value"
                                                filter
                                                class="w-full"
                                            />
                                            <label>Модель</label>
                                        </span>
                                    </div>
                                </div>
                                <div class="card flex flex-column md:flex-row sm:gap-3 gap-5">
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.doors_count : ''">
                                            <InputNumber :useGrouping="false" id="doors_count" v-model.trim="form.doors_count" v-bind:disabled="form.processing" :class="{'p-invalid': form.hasErrors && form.errors.doors_count}" />
                                            <label>Количество дверей</label>
                                        </span>
                                    </div>
                                    <div class="p-inputgroup">
                                        <span class="p-float-label" v-tooltip="form.hasErrors ? form.errors.seats_count : ''">
                                            <InputNumber :useGrouping="false" id="seats_count" v-model.trim="form.seats_count" v-bind:disabled="form.processing" :class="{'p-invalid': form.hasErrors && form.errors.seats_count}" />
                                            <label>Количество сидений</label>
                                        </span>
                                    </div>
                                </div>
                                <div class="card flex justify-end mx-4">
                                    <Button v-bind:disabled="form.processing" @click="update" label="Обновить" />
                                </div>
                            </div>
                            <ProgressBar v-if="form.processing" class="mt-8" mode="indeterminate" style="height: 3px"></ProgressBar>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.p-inputgroup {
    width: 100%;
    padding-top: 0.35rem;
}

.p-inputgroup :deep(.p-inputtext),
.p-inputgroup :deep(.p-inputnumber),
.p-inputgroup :deep(.p-dropdown) {
    width: 100%;
}

.equipment-double-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 768px) {
    .equipment-double-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
</style>
