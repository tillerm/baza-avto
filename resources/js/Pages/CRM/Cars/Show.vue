<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import {onMounted, ref} from 'vue';
import Toolbar from 'primevue/toolbar';
import {Link, useForm} from "@inertiajs/vue3";
import {useConfirm} from "primevue/useconfirm";
import {useToast} from "primevue/usetoast";
import ConfirmDialog from "primevue/confirmdialog";
import Toast from 'primevue/toast';
import Galleria from 'primevue/galleria';
import PhotoUploader from '@/Components/Crm/PhotoUploader.vue';
import moment from "moment";
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    car: Object,
    statuses: Object,
});

const toast = useToast();
const confirm = useConfirm();
let images = ref([]);
const activeIndex = ref(0);
const fullscreenOpen = ref(false);
const fullscreenIndex = ref(0);

const openFullscreen = (idx) => {
    fullscreenIndex.value = idx;
    fullscreenOpen.value = true;
};

const form = useForm({
    photos: [],
    car_id: props.car.id
});

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const pinLoading = ref(false);

onMounted(() => {
    updateImages();

    const flashToast = page.props.jetstream?.flash?.toast;
    if (flashToast) {
        toast.add(flashToast);
    }
});

const updateImages = () => {
    images.value = [];
    Object.entries(props.car.photos || {}).forEach(entry => {
        const [key, value] = entry;
        images.value.push({
            id: value.id,
            is_primary: !!value.is_primary,
            itemImageSrc: '/storage/' + value.photo,
            thumbnailImageSrc: '/storage/' + value.photo
        });
    });
};

const resolveUploadError = (errors) => {
    if (!errors) {
        return 'Произошла ошибка';
    }

    return errors['photos.0'] || errors.photos || errors.photo || 'Произошла ошибка';
};

const fileToBase64 = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(new Error('Failed to read selected file'));
        reader.readAsDataURL(file);
    });
};

const store = () => {
    return new Promise((resolve, reject) => {
        form.post(route('crm.photos.store'), {
            preserveScroll: true,
            onSuccess: () => {
                updateImages();
                resolve();
            },
            onError: (e) => {
                reject(e);
            },
            onFinish: () => {
                form.photos = [];
            },
        });
    });
};

/**
 * Single-file uploader passed to <PhotoUploader>. Returns a Promise that resolves on
 * success and rejects with a human-readable message on failure — letting the uploader
 * component drive its own per-file UI (queue / progress / per-item error state).
 */
const uploadOnePhoto = async (file) => {
    const encodedPhoto = await fileToBase64(file);
    form.photos = [encodedPhoto];
    try {
        await store();
    } catch (errors) {
        throw new Error(resolveUploadError(errors));
    } finally {
        form.photos = [];
    }
};

const onUploadSuccess = (uploaded, total) => {
    if (uploaded === 0) return;
    toast.add({
        severity: 'success',
        summary: 'Успешно',
        detail: uploaded === total ? 'Фото загружены' : `Загружено ${uploaded} из ${total} фото`,
        life: 4000,
    });
};

const onUploadError = (message) => {
    toast.add({ severity: 'error', summary: 'Ошибка', detail: message, life: 6000 });
};

const confirmDelete = () => {
    confirm.require({
        message: 'Вы действительно хотите удалить фото?',
        header: 'Подтвердите удаление',
        icon: 'pi pi-info-circle',
        acceptClass: 'p-button-danger',
        accept: () => {
            destroy()
        },
    });
};

const destroy = () => {
    router.visit(route('crm.photos.destroy', [images.value[activeIndex.value].id]), {
        method: 'delete',
        onSuccess: page => {
            toast.add({ severity: 'success', summary: 'Успешно', detail: 'Фото удалено', life: 3000 });
            updateImages()
        },
        onError: errors => {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Произошла ошибка', life: 3000 });
        },
    });
};

const makePrimary = () => {
    const currentImage = images.value[activeIndex.value];

    if (!currentImage || currentImage.is_primary) {
        return;
    }

    router.visit(route('crm.photos.primary', [currentImage.id]), {
        method: 'put',
        preserveScroll: true,
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Успешно', detail: 'Главное фото обновлено', life: 3000 });
            updateImages();
        },
        onError: () => {
            toast.add({ severity: 'error', summary: 'Ошибка', detail: 'Не удалось обновить главное фото', life: 3000 });
        },
    });
};

function pinCar() {
    pinLoading.value = true;
    router.post(route('catalog.pin', { id: props.car.id }), {}, {
        preserveScroll: true,
        onFinish: () => { pinLoading.value = false; },
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Успешно', detail: 'Машина піднята в каталозі', life: 4000 });
        },
        onError: (errors) => {
            toast.add({ severity: 'error', summary: 'Помилка', detail: errors?.error || 'Не вдалося підняти', life: 4000 });
        }
    });
}
</script>

<template>
    <AppLayout title="Авто">
        <div class="py-12">
            <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-xl sm:rounded-lg">
                    <Card>
                        <template #title>
                            <Toolbar class="mb-4">
                                <template #start>
                                    <div class="space-x-2 flex">
                                        <Link :href="route('crm.cars.edit', [car.id])">
                                            <Button label="Редактировать" icon="pi pi-pencil"/>
                                        </Link>
                                        <PhotoUploader
                                            v-if="images.length === 0"
                                            compact
                                            :uploader="uploadOnePhoto"
                                            @success="onUploadSuccess"
                                            @error="onUploadError"
                                        />
                                        <Button
                                            v-if="car.status === 'SELLING'"
                                            @click="pinCar()"
                                            :loading="pinLoading"
                                            label="Поднять в каталоге"
                                            icon="pi pi-arrow-up"
                                            severity="secondary"
                                        />
                                        <Toast />
                                    </div>
                                </template>
                            </Toolbar>
                        </template>
                        <template #content>
                            <div class="card flex flex-col">
                                <div v-if="images.length > 0" class="crm-gallery-wrap m-4">
                                    <div class="crm-gallery">
                                        <Galleria
                                            v-model:activeIndex="activeIndex"
                                            :value="images"
                                            :numVisible="6"
                                            :circular="true"
                                            :showItemNavigators="images.length > 1"
                                            :showThumbnails="images.length > 1"
                                            :showItemNavigatorsOnHover="false"
                                            containerStyle="width: 100%;"
                                        >
                                            <template #item="slotProps">
                                                <div class="crm-gallery__main" @click="openFullscreen(activeIndex)">
                                                    <img
                                                        :src="slotProps.item.itemImageSrc"
                                                        :alt="slotProps.item.alt"
                                                        class="crm-gallery__img"
                                                    />
                                                    <span v-if="slotProps.item.is_primary" class="crm-gallery__primary-badge">
                                                        <i class="pi pi-star-fill"></i>
                                                        Главное
                                                    </span>
                                                    <span class="crm-gallery__counter">
                                                        {{ activeIndex + 1 }} / {{ images.length }}
                                                    </span>
                                                    <span class="crm-gallery__zoom">
                                                        <i class="pi pi-search-plus"></i>
                                                        Открыть
                                                    </span>
                                                </div>
                                            </template>
                                            <template #thumbnail="slotProps">
                                                <div class="crm-gallery__thumb">
                                                    <img :src="slotProps.item.thumbnailImageSrc" :alt="slotProps.item.alt" />
                                                    <span v-if="slotProps.item.is_primary" class="crm-gallery__thumb-pin">
                                                        <i class="pi pi-star-fill"></i>
                                                    </span>
                                                </div>
                                            </template>
                                        </Galleria>
                                    </div>

                                    <!-- Fullscreen lightbox -->
                                    <Galleria
                                        v-model:activeIndex="fullscreenIndex"
                                        v-model:visible="fullscreenOpen"
                                        :value="images"
                                        :numVisible="8"
                                        :circular="true"
                                        :fullScreen="true"
                                        :showItemNavigators="true"
                                        :showThumbnails="true"
                                        :showIndicators="false"
                                    >
                                        <template #item="slotProps">
                                            <img
                                                :src="slotProps.item.itemImageSrc"
                                                :alt="slotProps.item.alt"
                                                style="max-width: 100%; max-height: 85vh; object-fit: contain; display: block; margin: 0 auto;"
                                            />
                                        </template>
                                        <template #thumbnail="slotProps">
                                            <img
                                                :src="slotProps.item.thumbnailImageSrc"
                                                :alt="slotProps.item.alt"
                                                style="height: 64px; width: auto; display: block; border-radius: 6px;"
                                            />
                                        </template>
                                    </Galleria>
                                    <div class="m-3 flex flex-wrap items-start gap-2 justify-center">
                                        <PhotoUploader
                                            compact
                                            :uploader="uploadOnePhoto"
                                            @success="onUploadSuccess"
                                            @error="onUploadError"
                                        />
                                        <Button
                                            @click="makePrimary()"
                                            label="Сделать главным"
                                            icon="pi pi-star"
                                            severity="secondary"
                                            :disabled="!images.length || images[activeIndex]?.is_primary"
                                        />
                                        <Button @click="confirmDelete()" label="Удалить" severity="danger" icon="pi pi-trash" class="mr-2"/>
                                        <ConfirmDialog></ConfirmDialog>
                                    </div>

                                    <!-- Full drop-zone uploader below the gallery for batch uploads -->
                                    <div class="mx-3 mb-4">
                                        <PhotoUploader
                                            :uploader="uploadOnePhoto"
                                            @success="onUploadSuccess"
                                            @error="onUploadError"
                                        />
                                    </div>
                                    <div class="text-center text-sm text-gray-600">
                                        <span v-if="images[activeIndex]?.is_primary" class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                                            <i class="pi pi-star-fill"></i>
                                            Главное фото
                                        </span>
                                    </div>
                                </div>
                                <!-- Empty-state drop-zone (no photos yet) -->
                                <div v-if="images.length === 0" class="mx-3 my-4">
                                    <PhotoUploader
                                        :uploader="uploadOnePhoto"
                                        @success="onUploadSuccess"
                                        @error="onUploadError"
                                    />
                                </div>

                                <div class="relative overflow-x-auto">
                                    <table class="w-full text-sm text-left">
                                        <tbody>
                                            <tr class="border-b">
                                                <th scope="row" colspan="2" class="text-lg px-6 py-4 font-bold text-gray-900 whitespace-nowrap">
                                                    Основное
                                                    <i v-if="car.status === 'PRESENT'" style="color: dodgerblue;" class="pi pi-eye-slash" v-tooltip="'В наличии: не отображается в каталоге'"></i>
                                                    <i v-else-if="car.status === 'SELLING'" style="color: dodgerblue;" class="pi pi-eye" v-tooltip="'В продаже: отображается в каталоге'"></i>
                                                    <i v-else-if="car.status === 'SOLD'" style="color: dodgerblue;" class="pi pi-thumbs-up" v-tooltip="'Продано'"></i>
                                                </th>
                                            </tr>
                                            <tr class="border-b">
                                                <th scope="row" class="px-6 py-3 font-bold text-gray-900 whitespace-nowrap">
                                                    Комплектация
                                                </th>
                                                <td class="px-6 py-3">
                                                    <a class="text-blue-600"
                                                       :href="route('crm.equipments.show', [car.supply.equipment.id])"
                                                       v-text="car.supply.equipment.name + ' | ' + (car.supply.equipment.engine?.name ?? '')" />
                                                </td>
                                            </tr>
                                            <tr class="border-b">
                                                <th scope="row" class="px-6 py-3 font-bold text-gray-900 whitespace-nowrap">
                                                    Менеджер
                                                </th>
                                                <td class="px-6 py-3">
                                                    <div>{{ car.manager?.name ?? car.supply.user?.name }}</div>
                                                    <div v-if="car.manager?.telegram_username" class="text-xs text-gray-500">
                                                        {{ car.manager.telegram_username }}
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr class="border-b">
                                                <th scope="row" class="px-6 py-3 font-bold text-gray-900 whitespace-nowrap">
                                                    Стоимость авто
                                                </th>
                                                <td class="px-6 py-3">
                                                    {{car.car_price ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(car.car_price) : (car.price ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(car.price) : '')}}
                                                </td>
                                            </tr>
                                            <tr class="border-b">
                                                <th scope="row" class="px-6 py-3 font-bold text-gray-900 whitespace-nowrap">
                                                    Таможенные пошлины
                                                </th>
                                                <td class="px-6 py-3">
                                                    {{car.customs ? new Intl.NumberFormat('ru-RU', { style: 'currency', currency: 'RUB' }).format(car.customs) : ''}}
                                                </td>
                                            </tr>
                                            <tr class="border-b">
                                                <th scope="row" class="px-6 py-3 font-bold text-gray-900 whitespace-nowrap">
                                                    Цвет
                                                </th>
                                                <td class="px-6 py-3">
                                                    {{car.color}}
                                                </td>
                                            </tr>
                                            <tr class="border-b">
                                                <th scope="row" class="px-6 py-3 font-bold text-gray-900 whitespace-nowrap">
                                                    Пробег
                                                </th>
                                                <td class="px-6 py-3">
                                                    {{car.mileage ? car.mileage + ' км' : ''}}
                                                </td>
                                            </tr>
                                            <tr class="border-b">
                                                <th scope="row" class="px-6 py-3 font-bold text-gray-900 whitespace-nowrap">
                                                    Год выпуска
                                                </th>
                                                <td class="px-6 py-3">
                                                    {{car.release_date ? moment(car.release_date).format("YYYY") + ' г.' : ''}}
                                                </td>
                                            </tr>
                                            <tr class="border-b">
                                                <th scope="row" class="px-6 py-3 font-bold text-gray-900 whitespace-nowrap">
                                                    VIN
                                                </th>
                                                <td class="px-6 py-3">
                                                    {{car.vin}}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.crm-gallery-wrap {
    display: flex;
    flex-direction: column;
}

.crm-gallery {
    border-radius: 16px;
    overflow: hidden;
    background: #0b1220;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06), 0 16px 48px rgba(15, 23, 42, 0.18);
    border: 1px solid #e6ecf3;
}

.crm-gallery__main {
    position: relative;
    background: linear-gradient(180deg, #0f172a 0%, #020617 100%);
    height: 360px;
    cursor: zoom-in;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

@media (min-width: 640px) { .crm-gallery__main { height: 460px; } }
@media (min-width: 1024px) { .crm-gallery__main { height: 540px; } }
@media (min-width: 1536px) { .crm-gallery__main { height: 620px; } }

.crm-gallery__img {
    width: 100%;
    height: 100%;
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
    transition: transform 400ms cubic-bezier(0.16, 1, 0.3, 1);
}

.crm-gallery__main:hover .crm-gallery__img {
    transform: scale(1.02);
}

.crm-gallery__counter {
    position: absolute;
    top: 1rem;
    right: 1rem;
    padding: 0.4rem 0.875rem;
    border-radius: 9999px;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(8px);
    color: #fff;
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    pointer-events: none;
}

.crm-gallery__primary-badge {
    position: absolute;
    top: 1rem;
    left: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    border-radius: 9999px;
    background: rgba(245, 158, 11, 0.18);
    backdrop-filter: blur(8px);
    color: #fcd34d;
    border: 1px solid rgba(252, 211, 77, 0.35);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    pointer-events: none;
}

.crm-gallery__primary-badge i { font-size: 0.6875rem; }

.crm-gallery__zoom {
    position: absolute;
    bottom: 1rem;
    right: 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 0.875rem;
    border-radius: 9999px;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(8px);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 600;
    opacity: 0;
    transition: opacity 200ms ease;
    pointer-events: none;
}

.crm-gallery__main:hover .crm-gallery__zoom { opacity: 1; }

/* Thumbnails */
.crm-gallery__thumb {
    position: relative;
    width: 100%;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    background: #1e293b;
}

.crm-gallery__thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.crm-gallery__thumb-pin {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 18px;
    height: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    background: rgba(245, 158, 11, 0.85);
    color: #fff;
    font-size: 0.5rem;
}

/* PrimeVue Galleria deep overrides — premium navigation arrows + thumb strip */
.crm-gallery :deep(.p-galleria) {
    background: transparent;
    border: none;
    display: flex;
    flex-direction: column;
}

.crm-gallery :deep(.p-galleria-content) {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
}

.crm-gallery :deep(.p-galleria-item-wrapper) {
    background: #0b1220;
    display: flex;
    align-items: stretch;
    justify-content: center;
    flex: 1;
    min-height: 0;
    position: relative;
}

.crm-gallery :deep(.p-galleria-item-container) {
    background: #0b1220;
    display: flex;
    align-items: stretch;
    justify-content: center;
    flex: 1;
    min-height: 0;
    width: 100%;
    height: 100%;
}

.crm-gallery :deep(.p-galleria-item) {
    flex: 1;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: stretch;
    justify-content: center;
}

.crm-gallery :deep(.p-galleria-item-prev),
.crm-gallery :deep(.p-galleria-item-next) {
    width: 48px;
    height: 48px;
    margin: 0 1rem;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.14) !important;
    backdrop-filter: blur(12px);
    color: #fff !important;
    border: 1px solid rgba(255, 255, 255, 0.22) !important;
    transition: background-color 200ms ease, transform 200ms ease, border-color 200ms ease;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.30);
}

.crm-gallery :deep(.p-galleria-item-prev:hover),
.crm-gallery :deep(.p-galleria-item-next:hover) {
    background: rgba(255, 255, 255, 0.26) !important;
    border-color: rgba(255, 255, 255, 0.40) !important;
    transform: scale(1.06);
}

.crm-gallery :deep(.p-galleria-item-prev-icon),
.crm-gallery :deep(.p-galleria-item-next-icon) {
    font-size: 1.125rem;
}

/* Thumbnail strip — softer, no harsh background */
.crm-gallery :deep(.p-galleria-thumbnail-wrapper) {
    background: #f8fafc;
    padding: 0.75rem;
    border-top: 1px solid #e6ecf3;
}

.crm-gallery :deep(.p-galleria-thumbnail-container) {
    background: transparent;
    padding: 0;
}

.crm-gallery :deep(.p-galleria-thumbnail-items-container) {
    padding: 0;
}

.crm-gallery :deep(.p-galleria-thumbnail-item) {
    padding: 0 4px;
}

.crm-gallery :deep(.p-galleria-thumbnail-item-content) {
    border-radius: 8px;
    overflow: hidden;
    transition: transform 200ms ease, box-shadow 200ms ease;
    cursor: pointer;
    border: 2px solid transparent;
}

.crm-gallery :deep(.p-galleria-thumbnail-item:hover .p-galleria-thumbnail-item-content) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.14);
}

.crm-gallery :deep(.p-galleria-thumbnail-item-current .p-galleria-thumbnail-item-content) {
    border-color: #ef4444;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25);
}

.crm-gallery :deep(.p-galleria-thumbnail-prev),
.crm-gallery :deep(.p-galleria-thumbnail-next) {
    width: 32px;
    height: 32px;
    border-radius: 9999px;
    background: #fff;
    color: #64748b;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.08);
    transition: all 200ms ease;
}

.crm-gallery :deep(.p-galleria-thumbnail-prev:hover),
.crm-gallery :deep(.p-galleria-thumbnail-next:hover) {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
}

/* Fullscreen mask — darker, smoother */
:global(.p-galleria-mask.p-galleria-fullscreen) {
    background: rgba(0, 0, 0, 0.92);
    backdrop-filter: blur(8px);
}

:global(.p-galleria-fullscreen .p-galleria) {
    background: transparent;
    border: none;
}

:global(.p-galleria-fullscreen .p-galleria-item-prev),
:global(.p-galleria-fullscreen .p-galleria-item-next) {
    width: 56px;
    height: 56px;
    background: rgba(255, 255, 255, 0.08) !important;
    color: #fff !important;
    border: 1px solid rgba(255, 255, 255, 0.20) !important;
    backdrop-filter: blur(12px);
}

:global(.p-galleria-close) {
    width: 48px !important;
    height: 48px !important;
    background: rgba(255, 255, 255, 0.10) !important;
    color: #fff !important;
    border: 1px solid rgba(255, 255, 255, 0.20) !important;
    border-radius: 9999px !important;
    backdrop-filter: blur(8px);
}

:global(.p-galleria-close:hover) {
    background: rgba(239, 68, 68, 0.85) !important;
    border-color: rgba(239, 68, 68, 1) !important;
}
</style>
