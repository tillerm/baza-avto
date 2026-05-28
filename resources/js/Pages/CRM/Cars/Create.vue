<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import ProgressBar from 'primevue/progressbar';
import Toast from 'primevue/toast';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useToast } from 'primevue/usetoast';
import moment from 'moment/moment';

const props = defineProps({
    brands: { type: Array, default: () => [] },
    models: { type: Array, default: () => [] },
    engines: { type: Array, default: () => [] },
    equipments: { type: Array, default: () => [] },
    defaultCountryId: { type: Number, default: null },
    fuels: { type: Object, default: () => ({}) },
    types: { type: Object, default: () => ({}) },
    bodies: { type: Object, default: () => ({}) },
    wizard: { type: Object, default: () => ({}) },
});

const toast = useToast();

const steps = [
    { label: 'Марка' },
    { label: 'Модель' },
    { label: 'Двигатель' },
    { label: 'Комплектация' },
    { label: 'Детали' },
    { label: 'Подтверждение' },
];

const activeStep = ref(0);

const form = useForm({
    wizard: 1,
    brand_id: null,
    model_id: null,
    engine_id: null,
    equipment_id: null,

    vin: null,
    state_number: null,
    color: null,
    mileage: null,
    release_date: null,

    supply_price: null,
    car_price: null,
    customs: null,
});

const showAddBrand = ref(false);
const showAddModel = ref(false);
const showAddEngine = ref(false);
const showAddEquipment = ref(false);

const brandForm = useForm({
    wizard: 1,
    name: null,
    country_id: props.defaultCountryId,
});

const modelForm = useForm({
    wizard: 1,
    name: null,
    brand_id: null,
});

const engineForm = useForm({
    wizard: 1,
    name: null,
    capacity: null,
    fuel: null,
});

const equipmentForm = useForm({
    wizard: 1,
    name: null,
    type: null,
    doors_count: null,
    seats_count: null,
    body: null,
    model_id: null,
    engine_id: null,
});

const fuelOptions = computed(() =>
    Object.entries(props.fuels || {}).map(([key, value]) => ({
        label: value,
        value: key,
    })),
);

const typeOptions = computed(() =>
    Object.entries(props.types || {}).map(([key, value]) => ({
        label: value,
        value: key,
    })),
);

const bodyOptions = computed(() =>
    Object.entries(props.bodies || {}).map(([key, value]) => ({
        label: value,
        value: key,
    })),
);

const createdCarId = computed(() => props.wizard?.createdCarId ?? null);

const selectedBrand = computed(() => props.brands.find((b) => b.id === form.brand_id) ?? null);
const selectedModel = computed(() => props.models.find((m) => m.id === form.model_id) ?? null);
const selectedEngine = computed(() => props.engines.find((e) => e.id === form.engine_id) ?? null);
const selectedEquipment = computed(() => props.equipments.find((e) => e.id === form.equipment_id) ?? null);

const filteredModels = computed(() =>
    props.models.filter((m) => m.brand_id === form.brand_id),
);

const filteredEquipments = computed(() =>
    props.equipments.filter((e) => e.model_id === form.model_id && e.engine_id === form.engine_id),
);

const canGoNext = computed(() => {
    switch (activeStep.value) {
        case 0:
            return Boolean(form.brand_id);
        case 1:
            return Boolean(form.model_id);
        case 2:
            return Boolean(form.engine_id);
        case 3:
            return Boolean(form.equipment_id);
        case 4:
            return true;
        default:
            return false;
    }
});

const canSubmit = computed(() => Boolean(form.equipment_id) && !form.processing);

function brandLogoSrc(brand) {
    if (!brand?.logo) return null;
    return brand.logo.startsWith('http') ? brand.logo : '/storage/' + brand.logo;
}

function nextStep() {
    if (!canGoNext.value) return;
    if (activeStep.value < steps.length - 1) activeStep.value += 1;
}

function prevStep() {
    if (activeStep.value > 0) activeStep.value -= 1;
}

function goToStep(stepIndex) {
    if (stepIndex < 0 || stepIndex > steps.length - 1) return;
    if (stepIndex > activeStep.value) return;
    activeStep.value = stepIndex;
}

function stepCircleClass(stepIndex) {
    if (stepIndex < activeStep.value) return 'bg-emerald-600 text-white border-emerald-600';
    if (stepIndex === activeStep.value) return 'bg-white text-emerald-700 border-emerald-600';
    return 'bg-white text-gray-500 border-gray-300';
}

function stepLabelClass(stepIndex) {
    if (stepIndex === activeStep.value) return 'text-gray-900';
    if (stepIndex < activeStep.value) return 'text-gray-700';
    return 'text-gray-500';
}

function stepLineClass(stepIndex) {
    return stepIndex < activeStep.value ? 'bg-emerald-600' : 'bg-gray-200';
}

function selectBrand(brandId) {
    form.brand_id = brandId;
    nextStep();
}

function selectModel(modelId) {
    form.model_id = modelId;
    nextStep();
}

function selectEngine(engineId) {
    form.engine_id = engineId;
    nextStep();
}

function selectEquipment(equipmentId) {
    form.equipment_id = equipmentId;
    nextStep();
}

const createBrand = () => {
    if (!props.defaultCountryId) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Сначала добавьте страну', life: 3000 });
        return;
    }

    brandForm.post(route('crm.brands.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Успешно', detail: 'Марка создана', life: 3000 });
            brandForm.reset();
            brandForm.country_id = props.defaultCountryId;
            showAddBrand.value = false;
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Проверьте данные', life: 3000 });
        },
    });
};

const createModel = () => {
    if (!form.brand_id) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Сначала выберите марку', life: 3000 });
        return;
    }

    modelForm
        .transform((data) => ({
            ...data,
            brand_id: form.brand_id,
        }))
        .post(route('crm.models.store'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Успешно', detail: 'Модель создана', life: 3000 });
                modelForm.reset();
                showAddModel.value = false;
            },
            onError: () => {
                toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Проверьте данные', life: 3000 });
            },
        });
};

const createEngine = () => {
    engineForm.post(route('crm.engines.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Успешно', detail: 'Двигатель создан', life: 3000 });
            engineForm.reset();
            showAddEngine.value = false;
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Проверьте данные', life: 3000 });
        },
    });
};

const createEquipment = () => {
    if (!form.model_id || !form.engine_id) {
        toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Сначала выберите модель и двигатель', life: 3000 });
        return;
    }

    equipmentForm
        .transform((data) => ({
            ...data,
            model_id: form.model_id,
            engine_id: form.engine_id,
        }))
        .post(route('crm.equipments.store'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.add({ severity: 'success', summary: 'Успешно', detail: 'Комплектация создана', life: 3000 });
                equipmentForm.reset();
                showAddEquipment.value = false;
            },
            onError: () => {
                toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Проверьте данные', life: 3000 });
            },
        });
};

const submit = () => {
    form
        .transform((data) => ({
            wizard: 1,
            equipment_id: data.equipment_id,
            supply_price: data.supply_price,
            car_price: data.car_price,
            customs: data.customs,
            vin: data.vin,
            state_number: data.state_number,
            color: data.color,
            mileage: data.mileage,
            release_date: data.release_date ? moment(data.release_date).format('YYYY-MM-DD') : null,
        }))
        .post(route('crm.cars.store'), {
            preserveScroll: true,
            preserveState: true,
            onError: () => {
                toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Проверьте данные', life: 3000 });
            },
        });
};

watch(
    () => form.brand_id,
    (value, oldValue) => {
        if (value === oldValue) return;
        form.model_id = null;
        form.engine_id = null;
        form.equipment_id = null;
    },
);

watch(
    () => form.model_id,
    (value, oldValue) => {
        if (value === oldValue) return;
        form.engine_id = null;
        form.equipment_id = null;
    },
);

watch(
    () => form.engine_id,
    (value, oldValue) => {
        if (value === oldValue) return;
        form.equipment_id = null;
    },
);

watch(
    () => props.wizard?.createdBrandId,
    (id) => {
        if (!id) return;
        form.brand_id = id;
        showAddBrand.value = false;
        activeStep.value = 1;
    },
);

watch(
    () => props.wizard?.createdModelId,
    (id) => {
        if (!id) return;
        form.model_id = id;
        showAddModel.value = false;
        activeStep.value = 2;
    },
);

watch(
    () => props.wizard?.createdEngineId,
    (id) => {
        if (!id) return;
        form.engine_id = id;
        showAddEngine.value = false;
        activeStep.value = 3;
    },
);

watch(
    () => props.wizard?.createdEquipmentId,
    (id) => {
        if (!id) return;
        form.equipment_id = id;
        showAddEquipment.value = false;
        activeStep.value = 4;
    },
);

watch(
    () => props.wizard?.createdCarId,
    (id) => {
        if (!id) return;
        activeStep.value = 5;
    },
);
</script>

<template>
    <AppLayout title="Авто">
        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <div>
                    <Card>
                        <template #title>
                            Добавить автомобиль
                            <Toast />
                        </template>

                        <template #content>
                            <div class="mb-8">
                                <div class="flex items-center w-full">
                                    <div
                                        v-for="(step, idx) in steps"
                                        :key="idx"
                                        class="flex items-center min-w-0"
                                        :class="idx < steps.length - 1 ? 'flex-1' : ''"
                                    >
                                        <button
                                            type="button"
                                            class="flex items-center gap-2 min-w-0"
                                            :disabled="idx > activeStep"
                                            @click="goToStep(idx)"
                                        >
                                            <div
                                                class="h-[32px] w-[32px] flex-none rounded-full border flex items-center justify-center"
                                                :class="stepCircleClass(idx)"
                                            >
                                                <span v-if="idx < activeStep" class="pi pi-check text-[14px] leading-none" />
                                                <span v-else class="text-[14px] font-semibold leading-none">{{ idx + 1 }}</span>
                                            </div>
                                            <span class="text-sm font-medium truncate" :class="stepLabelClass(idx)">{{ step.label }}</span>
                                        </button>

                                        <div
                                            v-if="idx < steps.length - 1"
                                            class="h-px flex-1 mx-3"
                                            :class="stepLineClass(idx)"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Step 1: Brand -->
                            <div v-if="activeStep === 0" class="space-y-4">
                                <div class="text-xl font-semibold text-gray-900">Выберите марку</div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <button
                                        v-for="brand in props.brands"
                                        :key="brand.id"
                                        type="button"
                                        @click="selectBrand(brand.id)"
                                        class="p-4 border rounded-2xl hover:shadow-sm transition text-left"
                                        :class="form.brand_id === brand.id ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200 bg-white'"
                                    >
                                        <div class="flex items-center gap-3">
                                            <img
                                                v-if="brandLogoSrc(brand)"
                                                :src="brandLogoSrc(brand)"
                                                :alt="brand.name"
                                                class="h-[40px] w-[40px] object-contain"
                                            />
                                            <div class="font-medium text-gray-900">{{ brand.name }}</div>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="showAddBrand = true"
                                        class="p-4 border-2 border-dashed rounded-2xl hover:bg-gray-50 transition text-left"
                                        :class="showAddBrand ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200 bg-white'"
                                    >
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <span class="pi pi-plus" />
                                            <span class="font-medium">Добавить марку</span>
                                        </div>
                                    </button>
                                </div>

                                <div v-if="showAddBrand" class="border rounded-2xl p-4 bg-gray-50 max-w-xl">
                                    <div class="font-semibold text-gray-900 mb-3">Новая марка</div>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="text-sm text-gray-700">Название</label>
                                            <InputText
                                                v-model.trim="brandForm.name"
                                                :disabled="brandForm.processing"
                                                class="w-full"
                                                :class="{ 'p-invalid': brandForm.hasErrors && brandForm.errors.name }"
                                            />
                                            <small v-if="brandForm.hasErrors && brandForm.errors.name" class="p-error">{{ brandForm.errors.name }}</small>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 justify-end mt-4">
                                        <Button
                                            label="Отмена"
                                            severity="secondary"
                                            :disabled="brandForm.processing"
                                            @click="() => { showAddBrand = false; brandForm.reset(); brandForm.country_id = props.defaultCountryId; }"
                                        />
                                        <Button
                                            label="Сохранить"
                                            :disabled="brandForm.processing"
                                            @click="createBrand"
                                        />
                                    </div>
                                    <ProgressBar v-if="brandForm.processing" class="mt-4" mode="indeterminate" style="height: 3px" />
                                </div>
                            </div>

                            <!-- Step 2: Model -->
                            <div v-else-if="activeStep === 1" class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div v-if="selectedBrand" class="text-sm text-gray-600">
                                        Марка: <span class="font-medium text-gray-900">{{ selectedBrand.name }}</span>
                                    </div>
                                </div>

                                <div class="text-xl font-semibold text-gray-900">Выберите модель</div>

                                <div v-if="filteredModels.length" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <button
                                        v-for="model in filteredModels"
                                        :key="model.id"
                                        type="button"
                                        @click="selectModel(model.id)"
                                        class="p-4 border rounded-2xl hover:shadow-sm transition text-left"
                                        :class="form.model_id === model.id ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200 bg-white'"
                                    >
                                        <div class="font-medium text-gray-900">{{ model.name }}</div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="showAddModel = true"
                                        class="p-4 border-2 border-dashed rounded-2xl hover:bg-gray-50 transition text-left"
                                        :class="showAddModel ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200 bg-white'"
                                    >
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <span class="pi pi-plus" />
                                            <span class="font-medium">Добавить модель</span>
                                        </div>
                                    </button>
                                </div>
                                <div v-else class="border rounded-lg p-4 bg-gray-50">
                                    <div class="text-gray-700">Для выбранной марки пока нет моделей.</div>
                                    <div class="mt-3">
                                        <Button label="Добавить модель" @click="showAddModel = true" />
                                    </div>
                                </div>

                                <div v-if="showAddModel" class="border rounded-lg p-4 bg-gray-50 max-w-xl">
                                    <div class="font-semibold text-gray-900 mb-3">Новая модель</div>
                                    <div class="space-y-2">
                                        <div>
                                            <label class="text-sm text-gray-700">Название</label>
                                            <InputText
                                                v-model.trim="modelForm.name"
                                                :disabled="modelForm.processing"
                                                class="w-full"
                                                :class="{ 'p-invalid': modelForm.hasErrors && modelForm.errors.name }"
                                            />
                                            <small v-if="modelForm.hasErrors && modelForm.errors.name" class="p-error">{{ modelForm.errors.name }}</small>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 justify-end mt-4">
                                        <Button
                                            label="Отмена"
                                            severity="secondary"
                                            :disabled="modelForm.processing"
                                            @click="() => { showAddModel = false; modelForm.reset(); }"
                                        />
                                        <Button
                                            label="Сохранить"
                                            :disabled="modelForm.processing"
                                            @click="createModel"
                                        />
                                    </div>
                                    <ProgressBar v-if="modelForm.processing" class="mt-4" mode="indeterminate" style="height: 3px" />
                                </div>
                            </div>

                            <!-- Step 3: Engine -->
                            <div v-else-if="activeStep === 2" class="space-y-4">
                                <div class="text-xl font-semibold text-gray-900">Выберите двигатель</div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <button
                                        v-for="engine in props.engines"
                                        :key="engine.id"
                                        type="button"
                                        @click="selectEngine(engine.id)"
                                        class="p-4 border rounded-2xl hover:shadow-sm transition text-left"
                                        :class="form.engine_id === engine.id ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200 bg-white'"
                                    >
                                        <div class="font-medium text-gray-900">{{ engine.name }}</div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            {{ props.fuels?.[engine.fuel] ?? engine.fuel }}
                                            <span v-if="engine.capacity">• {{ engine.capacity }} см³</span>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="showAddEngine = true"
                                        class="p-4 border-2 border-dashed rounded-2xl hover:bg-gray-50 transition text-left"
                                        :class="showAddEngine ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200 bg-white'"
                                    >
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <span class="pi pi-plus" />
                                            <span class="font-medium">Добавить двигатель</span>
                                        </div>
                                    </button>
                                </div>

                                <div v-if="showAddEngine" class="border rounded-lg p-4 bg-gray-50 max-w-3xl">
                                    <div class="font-semibold text-gray-900 mb-3">Новый двигатель</div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="text-sm text-gray-700">Наименование</label>
                                            <InputText
                                                v-model.trim="engineForm.name"
                                                :disabled="engineForm.processing"
                                                class="w-full"
                                                :class="{ 'p-invalid': engineForm.hasErrors && engineForm.errors.name }"
                                            />
                                            <small v-if="engineForm.hasErrors && engineForm.errors.name" class="p-error">{{ engineForm.errors.name }}</small>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-700">Топливо</label>
                                            <Dropdown
                                                v-model="engineForm.fuel"
                                                :disabled="engineForm.processing"
                                                :options="fuelOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                class="w-full"
                                                :class="{ 'p-invalid': engineForm.hasErrors && engineForm.errors.fuel }"
                                            />
                                            <small v-if="engineForm.hasErrors && engineForm.errors.fuel" class="p-error">{{ engineForm.errors.fuel }}</small>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-700">Объем</label>
                                            <InputNumber
                                                v-model="engineForm.capacity"
                                                :useGrouping="false"
                                                suffix=" см³"
                                                :disabled="engineForm.processing"
                                                class="w-full"
                                                :class="{ 'p-invalid': engineForm.hasErrors && engineForm.errors.capacity }"
                                            />
                                            <small v-if="engineForm.hasErrors && engineForm.errors.capacity" class="p-error">{{ engineForm.errors.capacity }}</small>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 justify-end mt-4">
                                        <Button
                                            label="Отмена"
                                            severity="secondary"
                                            :disabled="engineForm.processing"
                                            @click="() => { showAddEngine = false; engineForm.reset(); }"
                                        />
                                        <Button
                                            label="Сохранить"
                                            :disabled="engineForm.processing"
                                            @click="createEngine"
                                        />
                                    </div>
                                    <ProgressBar v-if="engineForm.processing" class="mt-4" mode="indeterminate" style="height: 3px" />
                                </div>
                            </div>

                            <!-- Step 4: Equipment -->
                            <div v-else-if="activeStep === 3" class="space-y-4">
                                <div class="text-xl font-semibold text-gray-900">Выберите комплектацию</div>

                                <div v-if="selectedEngine" class="text-sm text-gray-600">
                                    Двигатель:
                                    <span class="font-medium text-gray-900">{{ selectedEngine.name }}</span>
                                </div>

                                <div v-if="filteredEquipments.length" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <button
                                        v-for="equipment in filteredEquipments"
                                        :key="equipment.id"
                                        type="button"
                                        @click="selectEquipment(equipment.id)"
                                        class="p-4 border rounded-2xl hover:shadow-sm transition text-left"
                                        :class="form.equipment_id === equipment.id ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200 bg-white'"
                                    >
                                        <div class="font-medium text-gray-900">{{ equipment.name }}</div>
                                        <div class="text-sm text-gray-600 mt-1">
                                            {{ props.bodies?.[equipment.body] ?? equipment.body }}
                                            <span v-if="equipment.doors_count">• {{ equipment.doors_count }} дв.</span>
                                            <span v-if="equipment.seats_count">• {{ equipment.seats_count }} мест</span>
                                            <span v-if="equipment.type">• {{ props.types?.[equipment.type] ?? equipment.type }}</span>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        @click="showAddEquipment = true"
                                        class="p-4 border-2 border-dashed rounded-2xl hover:bg-gray-50 transition text-left"
                                        :class="showAddEquipment ? 'border-emerald-600 bg-emerald-50' : 'border-gray-200 bg-white'"
                                    >
                                        <div class="flex items-center gap-2 text-gray-700">
                                            <span class="pi pi-plus" />
                                            <span class="font-medium">Добавить комплектацию</span>
                                        </div>
                                    </button>
                                </div>
                                <div v-else class="border rounded-lg p-4 bg-gray-50">
                                    <div class="text-gray-700">Для выбранных модели и двигателя пока нет комплектаций.</div>
                                    <div class="mt-3">
                                        <Button label="Добавить комплектацию" @click="showAddEquipment = true" />
                                    </div>
                                </div>

                                <div v-if="showAddEquipment" class="border rounded-lg p-4 bg-gray-50">
                                    <div class="font-semibold text-gray-900 mb-3">Новая комплектация</div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="text-sm text-gray-700">Название</label>
                                            <InputText
                                                v-model.trim="equipmentForm.name"
                                                :disabled="equipmentForm.processing"
                                                class="w-full"
                                                :class="{ 'p-invalid': equipmentForm.hasErrors && equipmentForm.errors.name }"
                                            />
                                            <small v-if="equipmentForm.hasErrors && equipmentForm.errors.name" class="p-error">{{ equipmentForm.errors.name }}</small>
                                        </div>

                                        <div>
                                            <label class="text-sm text-gray-700">Двигатель</label>
                                            <InputText
                                                :modelValue="selectedEngine?.name ?? ''"
                                                disabled
                                                class="w-full"
                                            />
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-700">Класс</label>
                                            <Dropdown
                                                v-model="equipmentForm.type"
                                                :disabled="equipmentForm.processing"
                                                :options="typeOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                class="w-full"
                                                :class="{ 'p-invalid': equipmentForm.hasErrors && equipmentForm.errors.type }"
                                            />
                                            <small v-if="equipmentForm.hasErrors && equipmentForm.errors.type" class="p-error">{{ equipmentForm.errors.type }}</small>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-700">Кузов</label>
                                            <Dropdown
                                                v-model="equipmentForm.body"
                                                :disabled="equipmentForm.processing"
                                                :options="bodyOptions"
                                                optionLabel="label"
                                                optionValue="value"
                                                class="w-full"
                                                :class="{ 'p-invalid': equipmentForm.hasErrors && equipmentForm.errors.body }"
                                            />
                                            <small v-if="equipmentForm.hasErrors && equipmentForm.errors.body" class="p-error">{{ equipmentForm.errors.body }}</small>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-700">Двери</label>
                                            <InputNumber
                                                v-model="equipmentForm.doors_count"
                                                :useGrouping="false"
                                                :disabled="equipmentForm.processing"
                                                class="w-full"
                                                :class="{ 'p-invalid': equipmentForm.hasErrors && equipmentForm.errors.doors_count }"
                                            />
                                            <small v-if="equipmentForm.hasErrors && equipmentForm.errors.doors_count" class="p-error">{{ equipmentForm.errors.doors_count }}</small>
                                        </div>
                                        <div>
                                            <label class="text-sm text-gray-700">Места</label>
                                            <InputNumber
                                                v-model="equipmentForm.seats_count"
                                                :useGrouping="false"
                                                :disabled="equipmentForm.processing"
                                                class="w-full"
                                                :class="{ 'p-invalid': equipmentForm.hasErrors && equipmentForm.errors.seats_count }"
                                            />
                                            <small v-if="equipmentForm.hasErrors && equipmentForm.errors.seats_count" class="p-error">{{ equipmentForm.errors.seats_count }}</small>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 justify-end mt-4">
                                        <Button
                                            label="Отмена"
                                            severity="secondary"
                                            :disabled="equipmentForm.processing"
                                            @click="() => { showAddEquipment = false; equipmentForm.reset(); }"
                                        />
                                        <Button
                                            label="Сохранить"
                                            :disabled="equipmentForm.processing"
                                            @click="createEquipment"
                                        />
                                    </div>
                                    <ProgressBar v-if="equipmentForm.processing" class="mt-4" mode="indeterminate" style="height: 3px" />
                                </div>
                            </div>

                            <!-- Step 5: Details -->
                            <div v-else-if="activeStep === 4" class="space-y-6">
                                <div class="text-xl font-semibold text-gray-900">Детали</div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="text-sm text-gray-700">VIN</label>
                                        <InputText
                                            v-model.trim="form.vin"
                                            :disabled="form.processing"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.hasErrors && form.errors.vin }"
                                        />
                                        <small v-if="form.hasErrors && form.errors.vin" class="p-error">{{ form.errors.vin }}</small>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-700">Гос. номер</label>
                                        <InputText
                                            v-model.trim="form.state_number"
                                            :disabled="form.processing"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.hasErrors && form.errors.state_number }"
                                        />
                                        <small v-if="form.hasErrors && form.errors.state_number" class="p-error">{{ form.errors.state_number }}</small>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-700">Цвет</label>
                                        <InputText
                                            v-model.trim="form.color"
                                            :disabled="form.processing"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.hasErrors && form.errors.color }"
                                        />
                                        <small v-if="form.hasErrors && form.errors.color" class="p-error">{{ form.errors.color }}</small>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-700">Пробег</label>
                                        <InputNumber
                                            v-model="form.mileage"
                                            :useGrouping="false"
                                            suffix=" км"
                                            :disabled="form.processing"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.hasErrors && form.errors.mileage }"
                                        />
                                        <small v-if="form.hasErrors && form.errors.mileage" class="p-error">{{ form.errors.mileage }}</small>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-700">Год выпуска</label>
                                        <Calendar
                                            v-model="form.release_date"
                                            view="year"
                                            dateFormat="yy"
                                            :disabled="form.processing"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.hasErrors && form.errors.release_date }"
                                        />
                                        <small v-if="form.hasErrors && form.errors.release_date" class="p-error">{{ form.errors.release_date }}</small>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-700">Цена поставки</label>
                                        <InputNumber
                                            v-model="form.supply_price"
                                            mode="currency"
                                            currency="RUB"
                                            locale="ru-RU"
                                            :disabled="form.processing"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.hasErrors && form.errors.supply_price }"
                                        />
                                        <small v-if="form.hasErrors && form.errors.supply_price" class="p-error">{{ form.errors.supply_price }}</small>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-700">Стоимость авто</label>
                                        <InputNumber
                                            v-model="form.car_price"
                                            mode="currency"
                                            currency="RUB"
                                            locale="ru-RU"
                                            :disabled="form.processing"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.hasErrors && form.errors.car_price }"
                                        />
                                        <small v-if="form.hasErrors && form.errors.car_price" class="p-error">{{ form.errors.car_price }}</small>
                                    </div>
                                    <div>
                                        <label class="text-sm text-gray-700">Таможенные пошлины</label>
                                        <InputNumber
                                            v-model="form.customs"
                                            mode="currency"
                                            currency="RUB"
                                            locale="ru-RU"
                                            :disabled="form.processing"
                                            class="w-full"
                                            :class="{ 'p-invalid': form.hasErrors && form.errors.customs }"
                                        />
                                        <small v-if="form.hasErrors && form.errors.customs" class="p-error">{{ form.errors.customs }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 6: Confirm -->
                            <div v-else-if="activeStep === 5" class="space-y-6">
                                <div v-if="createdCarId" class="space-y-4">
                                    <div class="text-xl font-semibold text-gray-900">Готово</div>
                                    <div class="border rounded-lg p-4 bg-emerald-50 text-emerald-800">
                                        Автомобиль создан.
                                    </div>
                                    <div class="flex justify-end">
                                        <Link :href="route('crm.cars.show', [createdCarId])">
                                            <Button label="Открыть авто" icon="pi pi-external-link" />
                                        </Link>
                                    </div>
                                </div>

                                <div v-else class="space-y-4">
                                    <div class="text-xl font-semibold text-gray-900">Подтверждение</div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="border rounded-lg p-4 bg-white">
                                            <div class="text-sm text-gray-600">Марка</div>
                                            <div class="font-medium text-gray-900">{{ selectedBrand?.name ?? '—' }}</div>
                                        </div>
                                        <div class="border rounded-lg p-4 bg-white">
                                            <div class="text-sm text-gray-600">Модель</div>
                                            <div class="font-medium text-gray-900">{{ selectedModel?.name ?? '—' }}</div>
                                        </div>
                                        <div class="border rounded-lg p-4 bg-white">
                                            <div class="text-sm text-gray-600">Двигатель</div>
                                            <div class="font-medium text-gray-900">{{ selectedEngine?.name ?? '—' }}</div>
                                        </div>
                                        <div class="border rounded-lg p-4 bg-white">
                                            <div class="text-sm text-gray-600">Комплектация</div>
                                            <div class="font-medium text-gray-900">{{ selectedEquipment?.name ?? '—' }}</div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <Button
                                            label="Создать автомобиль"
                                            icon="pi pi-check"
                                            :disabled="!canSubmit"
                                            @click="submit"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-between mt-10">
                                <Button
                                    label="Назад"
                                    icon="pi pi-arrow-left"
                                    severity="secondary"
                                    :disabled="activeStep === 0 || form.processing"
                                    @click="prevStep"
                                />
                                <Button
                                    v-if="activeStep < 5"
                                    label="Далее"
                                    icon="pi pi-arrow-right"
                                    iconPos="right"
                                    :disabled="!canGoNext || form.processing"
                                    @click="nextStep"
                                />
                            </div>

                            <ProgressBar v-if="form.processing" class="mt-6" mode="indeterminate" style="height: 3px" />
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Make the outer PrimeVue Card look like a plain container (no card chrome). */
:deep(.p-card) {
    background: transparent !important;
    box-shadow: none !important;
    border: none !important;
}

:deep(.p-card-body),
:deep(.p-card-content),
:deep(.p-card-header) {
    background: transparent !important;
}
</style>
