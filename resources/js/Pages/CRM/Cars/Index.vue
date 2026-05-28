<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Card from 'primevue/card';
import moment from "moment";
import SearchField from "../../../Components/SearchField.vue";
import Dropdown from 'primevue/dropdown';
import {onMounted, ref, watch} from "vue";
import {debounce} from "lodash";
import {router} from "@inertiajs/vue3";
import Calendar from 'primevue/calendar';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';

const props = defineProps({
    cars: Object,
    statuses: Object,
    brands: Object,
    models: Object,
});

const loading = ref(false);
let dropdownStatuses = ref();
const filter = ref({
    brand: null,
    model: null,
    yearFrom: null,
    yearTo: null,
    priceFrom: null,
    priceTo: null,
    status: null
});
onMounted(() => {
    dropdownStatuses.value = [];
    Object.entries(props.statuses).forEach(entry => {
        const [key, value] = entry;
        dropdownStatuses.value.push({option: value, value: key})
    })
});

function getUrlParams() {
    const result = {}
    for(const [key, value] of new URLSearchParams(window.location.search)) {
        result[key] = value;
    }
    return result;
}
const initSearch = debounce(() => {
    loading.value = true;
    let params = getUrlParams();
    router.get(
        route(route().current()),
        {
            ...params,
            brand: filter.value.brand?.id,
            model: filter.value.model?.id,
            yearFrom: filter.value.yearFrom ? moment(filter.value.yearFrom).format("YYYY-MM-DD") : null,
            yearTo: filter.value.yearTo ? moment(filter.value.yearTo).format("YYYY-MM-DD") : null,
            priceFrom: filter.value.priceFrom,
            priceTo: filter.value.priceTo,
            status: filter.value.status,
        },
        {
            preserveState: true,
            onSuccess: params => {
                loading.value = false;
            }
        },
    );
}, 500)

watch([filter.value], () =>
    initSearch()
)
</script>

<template>
    <AppLayout title="Авто">
        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <Card>
                        <template #content>
                            <div class="card pt-4">
                                <DataTable paginator :rows="15" :value="cars" removableSort sortMode="multiple" tableStyle="min-width: 50rem">
                                    <template #empty>
                                        <div class="text-base text-center">По вашему запросу ничего не нашлось</div>
                                    </template>
                                    <Column field="id" header="ID" sortable>
                                        <template #body="slotProps">
                                            <a class="text-blue-600" :href="route('crm.cars.show', [slotProps.data.id])" v-text="slotProps.data.id" />
                                        </template>
                                    </Column>
                                    <Column field="equipment" header="Комплектация">
                                        <template #body="slotProps">
                                            <a class="text-blue-600" :href="route('crm.equipments.show', [slotProps.data.supply.equipment.id])" v-text="slotProps.data.supply.equipment.name" />
                                        </template>
                                    </Column>
                                    <Column field="equipment.engine.name" header="Двигатель">
                                        <template #body="slotProps">
                                            <a
                                                v-if="slotProps.data.supply.equipment.engine"
                                                class="text-blue-600"
                                                :href="route('crm.engines.show', [slotProps.data.supply.equipment.engine.id])"
                                                v-text="slotProps.data.supply.equipment.engine.name"
                                            />
                                            <span v-else class="text-gray-500">Не указан</span>
                                        </template>
                                    </Column>
                                    <Column field="manager.name" header="Менеджер">
                                        <template #body="slotProps">
                                            {{ slotProps.data.manager?.name ?? slotProps.data.supply.user?.name }}
                                        </template>
                                    </Column>
                                    <Column field="price" header="Цена" sortable>
                                        <template #body="slotProps">
                                            {{slotProps.data.price ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(slotProps.data.price) : ''}}
                                        </template>
                                    </Column>
                                    <Column field="release_date" header="Год выпуска" sortable>
                                        <template #body="slotProps">
                                            {{slotProps.data.release_date ? moment(slotProps.data.release_date).format("YYYY") : ''}}
                                        </template>
                                    </Column>
                                    <Column field="status" header="Статус">
                                        <template #body="slotProps">
                                            <i v-if="slotProps.data.status === 'PRESENT'" style="color: dodgerblue;" class="pi pi-eye-slash" v-tooltip="'В наличии: не отображается в каталоге'"></i>
                                            <i v-else-if="slotProps.data.status === 'SELLING'" style="color: dodgerblue;" class="pi pi-eye" v-tooltip="'В продаже: отображается в каталоге'"></i>
                                            <i v-else-if="slotProps.data.status === 'SOLD'" style="color: dodgerblue;" class="pi pi-thumbs-up" v-tooltip="'Продано'"></i>
                                        </template>
                                    </Column>
                                </DataTable>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
