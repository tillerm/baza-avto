<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import { Link } from '@inertiajs/vue3';
import Toast from 'primevue/toast';
import SearchField from '../../../Components/SearchField.vue';

defineProps({
    supplies: Object,
});
</script>

<template>
    <AppLayout title="Поступления">
        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <Card>
                        <template #title>
                            <Toolbar class="mb-4">
                                <template #start>
                                    <div class="space-x-2 flex">
                                        <SearchField />
                                        <Link :href="route('crm.supplies.create')">
                                            <Button label="Создать" icon="pi pi-plus" />
                                        </Link>
                                        <Toast />
                                    </div>
                                </template>
                            </Toolbar>
                        </template>
                        <template #content>
                            <div class="card">
                                <DataTable paginator :rows="15" :value="supplies" removableSort sortMode="multiple" tableStyle="min-width: 50rem">
                                    <template #empty>
                                        <div class="text-base text-center">По вашему запросу ничего не нашлось</div>
                                    </template>
                                    <Column field="id" header="ID" sortable>
                                        <template #body="slotProps">
                                            <a class="text-blue-600" :href="route('crm.supplies.show', [slotProps.data.id])" v-text="slotProps.data.id" />
                                        </template>
                                    </Column>
                                    <Column field="model_id" header="Комплектация">
                                        <template #body="slotProps">
                                            <a class="text-blue-600" :href="route('crm.equipments.show', [slotProps.data.equipment_id])" v-text="slotProps.data.equipment.name" />
                                        </template>
                                    </Column>
                                    <Column field="user_id" header="Сотрудник">
                                        <template #body="slotProps">
                                            {{ slotProps.data.user.name }}
                                        </template>
                                    </Column>
                                    <Column field="price" header="Цена">
                                        <template #body="slotProps">
                                            {{ slotProps.data.price ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(slotProps.data.price) : '' }}
                                        </template>
                                    </Column>
                                    <Column field="status" header="Статус">
                                        <template #body="slotProps">
                                            <i v-if="slotProps.data.supplied_at" style="color: green" class="pi pi-check-circle" v-tooltip="'Автомобиль прибыл'" />
                                            <i v-else style="color: orange" class="pi pi-exclamation-triangle" v-tooltip="'Статус прибытия не указан'" />
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
