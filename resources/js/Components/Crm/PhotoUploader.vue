<script setup>
import { computed, onBeforeUnmount, ref } from 'vue';

const props = defineProps({
    /** Required uploader function (file) => Promise. Resolved on success, rejected on failure. */
    uploader: { type: Function, required: true },
    /** Auto-upload immediately when files are selected (vs. confirm with button). */
    autoUpload: { type: Boolean, default: false },
    /**
     * Max file size in MB BEFORE compression. Generous limit because we resize/recompress
     * client-side. Files larger than this are rejected with a toast.
     */
    maxFileSizeMb: { type: Number, default: 30 },
    /** Accepted MIME types (comma-separated) — passed to the native input. */
    accept: { type: String, default: 'image/jpeg,image/jpg,image/png,image/webp,image/gif' },
    /** When true, the uploader is in compact (button-only) mode without the dropzone. */
    compact: { type: Boolean, default: false },
    /** Auto-resize + JPEG re-encode images before upload. Disable for non-image flows. */
    compress: { type: Boolean, default: true },
    /** Max dimension (px, longest side) for compressed images. */
    maxDimension: { type: Number, default: 1920 },
    /** JPEG quality 0..1 used when re-encoding. */
    quality: { type: Number, default: 0.85 },
    /** Target max file size in KB after compression (used to decide if compression is needed). */
    targetSizeKb: { type: Number, default: 1800 },
});

const emit = defineEmits([
    /** (uploaded: number, total: number) — reports completion. */
    'success',
    /** (message: string) — emits user-friendly error. */
    'error',
]);

const fileInput = ref(null);
const dragActive = ref(false);
const queue = ref([]);          // [{ id, file, name, size, preview, status, error }]
const uploading = ref(false);
const totalToUpload = ref(0);
const completedCount = ref(0);

const supportedTypes = computed(() => props.accept.split(',').map(t => t.trim()));
const maxBytes = computed(() => props.maxFileSizeMb * 1024 * 1024);

const formatBytes = (n) => {
    if (n < 1024) return `${n} B`;
    if (n < 1024 * 1024) return `${(n / 1024).toFixed(0)} KB`;
    return `${(n / 1024 / 1024).toFixed(1)} MB`;
};

let nextId = 0;
const makeId = () => ++nextId;

/**
 * Resize+recompress an image File to keep it under the backend size limit.
 * Falls back to the original file on any failure (so we still try the upload).
 */
async function compressImage(file, { maxDimension, quality, targetSizeKb }) {
    if (!file || !file.type || !file.type.startsWith('image/')) return file;
    if (file.type === 'image/gif') return file; // animated GIFs would lose frames

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                let w = img.width;
                let h = img.height;

                // Skip work entirely if the file is already small AND not oversized.
                const maxSide = Math.max(w, h);
                if (maxSide <= maxDimension && file.size <= targetSizeKb * 1024) {
                    resolve(file);
                    return;
                }

                if (maxSide > maxDimension) {
                    if (w >= h) {
                        h = Math.round(h * (maxDimension / w));
                        w = maxDimension;
                    } else {
                        w = Math.round(w * (maxDimension / h));
                        h = maxDimension;
                    }
                }

                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = w;
                    canvas.height = h;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, w, h);

                    // Re-encode as JPEG (smaller than PNG for photos). PNG without
                    // transparency benefit becomes JPEG. Keep WEBP as-is.
                    const outputType = file.type === 'image/webp' ? 'image/webp' : 'image/jpeg';

                    // Try increasingly aggressive quality steps until we hit target size.
                    const tryEncode = (q) => new Promise((r) => canvas.toBlob(r, outputType, q));
                    (async () => {
                        let q = quality;
                        let blob = await tryEncode(q);
                        while (blob && blob.size > targetSizeKb * 1024 && q > 0.4) {
                            q = +(q - 0.1).toFixed(2);
                            blob = await tryEncode(q);
                        }
                        if (!blob) {
                            resolve(file);
                            return;
                        }
                        // If our compressed result is somehow larger than the original (rare for tiny files), keep original.
                        if (blob.size >= file.size) {
                            resolve(file);
                            return;
                        }
                        const ext = outputType === 'image/webp' ? '.webp' : '.jpg';
                        const newName = file.name.replace(/\.[^.]+$/, ext);
                        const newFile = new File([blob], newName, { type: outputType, lastModified: Date.now() });
                        resolve(newFile);
                    })();
                } catch (_) {
                    resolve(file);
                }
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}

const isAcceptable = (file) => {
    if (!supportedTypes.value.length) return true;
    return supportedTypes.value.some(t => {
        if (t === '*' || t === '*/*') return true;
        if (t.endsWith('/*')) return file.type.startsWith(t.slice(0, -1));
        return file.type === t;
    });
};

const addFiles = (rawList) => {
    const incoming = Array.from(rawList || []);
    for (const file of incoming) {
        if (!isAcceptable(file)) {
            emit('error', `Файл «${file.name}» — неподдерживаемый формат`);
            continue;
        }
        if (file.size > maxBytes.value) {
            emit('error', `Файл «${file.name}» больше ${props.maxFileSizeMb} MB`);
            continue;
        }
        const item = {
            id: makeId(),
            file,
            name: file.name,
            size: file.size,
            preview: URL.createObjectURL(file),
            status: 'pending', // pending | uploading | done | error
            error: null,
        };
        queue.value.push(item);
    }

    if (props.autoUpload && queue.value.some(i => i.status === 'pending')) {
        startUpload();
    }
};

const removeFromQueue = (id) => {
    const idx = queue.value.findIndex(i => i.id === id);
    if (idx === -1) return;
    URL.revokeObjectURL(queue.value[idx].preview);
    queue.value.splice(idx, 1);
};

const clearAll = () => {
    queue.value.forEach(i => URL.revokeObjectURL(i.preview));
    queue.value = [];
    completedCount.value = 0;
    totalToUpload.value = 0;
};

const startUpload = async () => {
    if (uploading.value) return;
    const pending = queue.value.filter(i => i.status === 'pending' || i.status === 'error');
    if (!pending.length) return;

    uploading.value = true;
    totalToUpload.value = pending.length;
    completedCount.value = 0;

    let succeeded = 0;
    for (const item of pending) {
        item.error = null;
        try {
            let fileToSend = item.file;
            if (props.compress) {
                item.status = 'compressing';
                fileToSend = await compressImage(item.file, {
                    maxDimension: props.maxDimension,
                    quality: props.quality,
                    targetSizeKb: props.targetSizeKb,
                });
                item.compressedSize = fileToSend.size;
            }
            item.status = 'uploading';
            await props.uploader(fileToSend);
            item.status = 'done';
            succeeded += 1;
        } catch (err) {
            item.status = 'error';
            item.error = (err && err.message) ? err.message : (typeof err === 'string' ? err : 'Ошибка загрузки');
        }
        completedCount.value += 1;
    }

    uploading.value = false;

    // Remove successfully uploaded items after a short delay so the user sees the green tick.
    setTimeout(() => {
        queue.value = queue.value.filter(i => i.status !== 'done');
        // revoke the URL of removed items - they were already cleared in filter, but URL.revokeObjectURL still safe
    }, 800);

    emit('success', succeeded, totalToUpload.value);
};

/* ----------- Drag & drop handlers ----------- */
const onDragEnter = (e) => { e.preventDefault(); dragActive.value = true; };
const onDragOver = (e) => { e.preventDefault(); dragActive.value = true; };
const onDragLeave = (e) => {
    e.preventDefault();
    if (e.currentTarget.contains(e.relatedTarget)) return;
    dragActive.value = false;
};
const onDrop = (e) => {
    e.preventDefault();
    dragActive.value = false;
    const items = e.dataTransfer?.files;
    if (items && items.length) addFiles(items);
};

const onFileInputChange = (e) => {
    addFiles(e.target.files);
    if (fileInput.value) fileInput.value.value = '';
};

const openPicker = () => {
    fileInput.value?.click();
};

onBeforeUnmount(() => {
    queue.value.forEach(i => URL.revokeObjectURL(i.preview));
});

const progressPercent = computed(() => {
    if (!totalToUpload.value) return 0;
    return Math.round((completedCount.value / totalToUpload.value) * 100);
});

defineExpose({
    addFiles,
    startUpload,
    clearAll,
    openPicker,
});
</script>

<template>
    <div class="photo-uploader" :class="{ 'is-compact': compact }">
        <!-- Hidden native input -->
        <input
            ref="fileInput"
            type="file"
            :accept="accept"
            multiple
            class="sr-only"
            @change="onFileInputChange"
        />

        <!-- Dropzone (full mode) -->
        <div
            v-if="!compact"
            class="photo-uploader__zone"
            :class="{ 'is-drag': dragActive }"
            @dragenter="onDragEnter"
            @dragover="onDragOver"
            @dragleave="onDragLeave"
            @drop="onDrop"
            @click="openPicker"
        >
            <div class="photo-uploader__zone-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
            </div>
            <div class="photo-uploader__zone-title">
                Перетащите фото сюда или
                <button type="button" class="photo-uploader__zone-cta" @click.stop="openPicker">выберите файлы</button>
            </div>
            <div class="photo-uploader__zone-hint">
                JPG, PNG, WEBP, GIF · до {{ maxFileSizeMb }} MB · можно несколько сразу
            </div>
        </div>

        <!-- Compact mode: just a button -->
        <div v-else class="photo-uploader__compact">
            <button
                type="button"
                class="photo-uploader__btn"
                :disabled="uploading"
                @click="openPicker"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                <span>Добавить фото</span>
            </button>
        </div>

        <!-- Queue preview -->
        <div v-if="queue.length" class="photo-uploader__queue">
            <div class="photo-uploader__queue-head">
                <div>
                    <span class="photo-uploader__queue-count">{{ queue.length }}</span>
                    <span class="photo-uploader__queue-label">{{ queue.length === 1 ? 'файл' : (queue.length < 5 ? 'файла' : 'файлов') }}</span>
                    <span v-if="uploading" class="photo-uploader__queue-progress">
                        — загружено {{ completedCount }} из {{ totalToUpload }} ({{ progressPercent }}%)
                    </span>
                </div>
                <div class="photo-uploader__queue-actions">
                    <button
                        v-if="!autoUpload && queue.some(i => i.status === 'pending' || i.status === 'error')"
                        type="button"
                        class="photo-uploader__btn photo-uploader__btn--primary"
                        :disabled="uploading"
                        @click="startUpload"
                    >
                        <svg v-if="!uploading" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="17 8 12 3 7 8" />
                            <line x1="12" y1="3" x2="12" y2="15" />
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7" />
                        </svg>
                        <svg v-else class="animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                        </svg>
                        <span>{{ uploading ? `${completedCount}/${totalToUpload}` : `Загрузить (${queue.length})` }}</span>
                    </button>
                    <button
                        type="button"
                        class="photo-uploader__btn photo-uploader__btn--ghost"
                        :disabled="uploading"
                        @click="clearAll"
                    >
                        Очистить
                    </button>
                </div>
            </div>

            <div v-if="uploading" class="photo-uploader__bar">
                <div class="photo-uploader__bar-fill" :style="{ width: `${progressPercent}%` }"></div>
            </div>

            <ul class="photo-uploader__items">
                <li
                    v-for="item in queue"
                    :key="item.id"
                    class="photo-uploader__item"
                    :class="`is-${item.status}`"
                >
                    <div class="photo-uploader__thumb">
                        <img :src="item.preview" :alt="item.name" />
                        <span v-if="item.status === 'compressing'" class="photo-uploader__thumb-overlay">
                            <svg class="animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                            </svg>
                        </span>
                        <span v-else-if="item.status === 'uploading'" class="photo-uploader__thumb-overlay">
                            <svg class="animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M21 12a9 9 0 1 1-6.219-8.56" />
                            </svg>
                        </span>
                        <span v-else-if="item.status === 'done'" class="photo-uploader__thumb-overlay is-success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </span>
                        <span v-else-if="item.status === 'error'" class="photo-uploader__thumb-overlay is-error">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </span>
                    </div>
                    <div class="photo-uploader__meta">
                        <div class="photo-uploader__name" :title="item.name">{{ item.name }}</div>
                        <div class="photo-uploader__sub">
                            <span>
                                {{ formatBytes(item.size) }}
                                <span v-if="item.compressedSize && item.compressedSize < item.size" class="photo-uploader__compressed">
                                    → {{ formatBytes(item.compressedSize) }}
                                </span>
                            </span>
                            <span v-if="item.status === 'pending'" class="photo-uploader__status">в очереди</span>
                            <span v-if="item.status === 'compressing'" class="photo-uploader__status photo-uploader__status--up">сжимаем…</span>
                            <span v-if="item.status === 'uploading'" class="photo-uploader__status photo-uploader__status--up">загружается…</span>
                            <span v-if="item.status === 'done'" class="photo-uploader__status photo-uploader__status--ok">готово</span>
                            <span v-if="item.status === 'error'" class="photo-uploader__status photo-uploader__status--err">{{ item.error || 'ошибка' }}</span>
                        </div>
                    </div>
                    <button
                        v-if="!uploading && item.status !== 'done'"
                        type="button"
                        class="photo-uploader__remove"
                        aria-label="Убрать из очереди"
                        @click="removeFromQueue(item.id)"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<style scoped>
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

.photo-uploader { display: flex; flex-direction: column; gap: 1rem; }

/* Dropzone */
.photo-uploader__zone {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 2.5rem 1.5rem;
    border: 2px dashed #cbd5e1;
    border-radius: 16px;
    background: #f8fafc;
    color: #475569;
    cursor: pointer;
    transition: border-color 200ms ease, background-color 200ms ease, color 200ms ease;
}

.photo-uploader__zone:hover {
    border-color: #ef4444;
    background: #fef2f2;
    color: #b91c1c;
}

.photo-uploader__zone.is-drag {
    border-color: #ef4444;
    border-style: solid;
    background: #fee2e2;
    color: #b91c1c;
}

.photo-uploader__zone-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 9999px;
    background: #fff;
    border: 1px solid #e2e8f0;
    color: #ef4444;
    margin-bottom: 0.25rem;
}

.photo-uploader__zone-icon svg { width: 24px; height: 24px; }

.photo-uploader__zone-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #0f172a;
}

.photo-uploader__zone:hover .photo-uploader__zone-title { color: #b91c1c; }
.photo-uploader__zone.is-drag .photo-uploader__zone-title { color: #b91c1c; }

.photo-uploader__zone-cta {
    color: #ef4444;
    font-weight: 700;
    text-decoration: underline;
    text-underline-offset: 3px;
    background: none;
    border: 0;
    cursor: pointer;
    padding: 0;
    font-size: inherit;
}

.photo-uploader__zone-hint {
    font-size: 0.8125rem;
    color: #64748b;
}

/* Compact mode */
.photo-uploader__compact { display: flex; }

.photo-uploader__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    font-weight: 600;
    font-size: 0.875rem;
    color: #0f172a;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    cursor: pointer;
    transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease, transform 150ms ease;
}

.photo-uploader__btn:hover:not(:disabled) {
    background: #f1f5f9;
    border-color: #94a3b8;
}

.photo-uploader__btn svg { width: 16px; height: 16px; }

.photo-uploader__btn--primary {
    background: #ef4444;
    color: #fff;
    border-color: #ef4444;
}

.photo-uploader__btn--primary:hover:not(:disabled) {
    background: #dc2626;
    border-color: #dc2626;
    transform: translateY(-1px);
    box-shadow: 0 8px 24px rgba(239, 68, 68, 0.32);
}

.photo-uploader__btn--ghost {
    background: transparent;
    border-color: transparent;
    color: #64748b;
}
.photo-uploader__btn--ghost:hover:not(:disabled) {
    background: #f1f5f9;
    color: #0f172a;
}

.photo-uploader__btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

@keyframes pu-spin { to { transform: rotate(360deg); } }
.animate-spin { animation: pu-spin 900ms linear infinite; transform-origin: center; }

/* Queue */
.photo-uploader__queue {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
}

.photo-uploader__queue-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
    font-size: 0.875rem;
    color: #475569;
}

.photo-uploader__queue-count {
    font-weight: 800;
    color: #0f172a;
    font-size: 0.9375rem;
}

.photo-uploader__queue-label {
    margin-left: 0.25rem;
    color: #64748b;
}

.photo-uploader__queue-progress {
    margin-left: 0.5rem;
    color: #0f172a;
}

.photo-uploader__queue-actions { display: inline-flex; gap: 0.5rem; }

.photo-uploader__bar {
    width: 100%;
    height: 4px;
    border-radius: 9999px;
    background: #f1f5f9;
    overflow: hidden;
}

.photo-uploader__bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #ef4444, #f97316);
    border-radius: 9999px;
    transition: width 200ms ease;
}

.photo-uploader__items {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
    list-style: none;
    padding: 0;
    margin: 0;
}

@media (min-width: 768px) {
    .photo-uploader__items { grid-template-columns: 1fr 1fr; }
}

.photo-uploader__item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.625rem;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #f8fafc;
    transition: border-color 150ms ease, background-color 150ms ease;
}

.photo-uploader__item.is-compressing { border-color: #bfdbfe; background: #eff6ff; }
.photo-uploader__item.is-uploading { border-color: #fde68a; background: #fffbeb; }
.photo-uploader__item.is-done { border-color: #bbf7d0; background: #f0fdf4; }
.photo-uploader__item.is-error { border-color: #fecaca; background: #fef2f2; }

.photo-uploader__compressed {
    color: #15803d;
    font-weight: 600;
    margin-left: 0.25rem;
}

.photo-uploader__thumb {
    position: relative;
    flex-shrink: 0;
    width: 56px;
    height: 56px;
    border-radius: 10px;
    overflow: hidden;
    background: #e2e8f0;
}

.photo-uploader__thumb img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}

.photo-uploader__thumb-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(15, 23, 42, 0.55);
    color: #fff;
}
.photo-uploader__thumb-overlay svg { width: 22px; height: 22px; }
.photo-uploader__thumb-overlay.is-success { background: rgba(22, 163, 74, 0.85); }
.photo-uploader__thumb-overlay.is-error { background: rgba(220, 38, 38, 0.85); }

.photo-uploader__meta {
    flex: 1;
    min-width: 0;
}

.photo-uploader__name {
    font-weight: 600;
    color: #0f172a;
    font-size: 0.875rem;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.photo-uploader__sub {
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    color: #64748b;
}

.photo-uploader__status { font-weight: 600; }
.photo-uploader__status--up { color: #b45309; }
.photo-uploader__status--ok { color: #15803d; }
.photo-uploader__status--err { color: #b91c1c; }

.photo-uploader__remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 9999px;
    background: #fff;
    color: #64748b;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: background-color 150ms ease, color 150ms ease, border-color 150ms ease;
}

.photo-uploader__remove:hover {
    background: #fef2f2;
    border-color: #fecaca;
    color: #b91c1c;
}

.photo-uploader__remove svg { width: 14px; height: 14px; }
</style>
