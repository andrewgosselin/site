<script setup lang="ts">
import { ref, computed } from 'vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const uploadedFile = ref<File | null>(null);
const originalImage = ref<HTMLImageElement | null>(null);
const canvas = ref<HTMLCanvasElement | null>(null);
const previewUrl = ref<string>('');
const isDragging = ref(false);

// Image info
const imageInfo = ref({
    format: '',
    size: 0,
    width: 0,
    height: 0
});

// Operation states
const outputFormat = ref<'png' | 'jpeg' | 'webp'>('png');
const quality = ref(0.9);
const resizeWidth = ref<number | null>(null);
const resizeHeight = ref<number | null>(null);
const maintainAspectRatio = ref(true);
const rotation = ref(0);
const flipH = ref(false);
const flipV = ref(false);
const filename = ref('image');

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        loadImage(target.files[0]);
    }
};

const handleDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files && event.dataTransfer.files[0]) {
        loadImage(event.dataTransfer.files[0]);
    }
};

const loadImage = (file: File) => {
    uploadedFile.value = file;
    
    // Extract file info
    imageInfo.value.format = file.type.split('/')[1].toUpperCase();
    imageInfo.value.size = file.size;
    filename.value = file.name.split('.')[0];

    const reader = new FileReader();
    reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
            originalImage.value = img;
            imageInfo.value.width = img.width;
            imageInfo.value.height = img.height;
            resizeWidth.value = img.width;
            resizeHeight.value = img.height;
            renderCanvas();
        };
        img.src = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const renderCanvas = () => {
    if (!originalImage.value) return;

    const img = originalImage.value;
    const cnv = document.createElement('canvas');
    const ctx = cnv.getContext('2d');
    if (!ctx) return;

    // Calculate dimensions
    let width = resizeWidth.value || img.width;
    let height = resizeHeight.value || img.height;

    // Apply rotation
    const isRotated = rotation.value === 90 || rotation.value === 270;
    cnv.width = isRotated ? height : width;
    cnv.height = isRotated ? width : height;

    ctx.save();
    
    // Apply transformations
    ctx.translate(cnv.width / 2, cnv.height / 2);
    ctx.rotate((rotation.value * Math.PI) / 180);
    ctx.scale(flipH.value ? -1 : 1, flipV.value ? -1 : 1);
    ctx.drawImage(img, -width / 2, -height / 2, width, height);
    
    ctx.restore();

    canvas.value = cnv;
    previewUrl.value = cnv.toDataURL(`image/${outputFormat.value}`, quality.value);
};

const updateResize = () => {
    if (!originalImage.value) return;
    
    if (maintainAspectRatio.value && resizeWidth.value && resizeHeight.value) {
        const aspectRatio = originalImage.value.width / originalImage.value.height;
        // Update based on which field was changed last (simplified - always use width)
        resizeHeight.value = Math.round(resizeWidth.value / aspectRatio);
    }
    
    renderCanvas();
};

const rotate = (degrees: number) => {
    rotation.value = (rotation.value + degrees) % 360;
    renderCanvas();
};

const flip = (direction: 'h' | 'v') => {
    if (direction === 'h') {
        flipH.value = !flipH.value;
    } else {
        flipV.value = !flipV.value;
    }
    renderCanvas();
};

const reset = () => {
    if (!originalImage.value) return;
    rotation.value = 0;
    flipH.value = false;
    flipV.value = false;
    resizeWidth.value = originalImage.value.width;
    resizeHeight.value = originalImage.value.height;
    renderCanvas();
};

const download = () => {
    if (!canvas.value) return;
    
    canvas.value.toBlob((blob) => {
        if (!blob) return;
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${filename.value}.${outputFormat.value === 'jpeg' ? 'jpg' : outputFormat.value}`;
        a.click();
        URL.revokeObjectURL(url);
    }, `image/${outputFormat.value}`, quality.value);
};

const clear = () => {
    uploadedFile.value = null;
    originalImage.value = null;
    canvas.value = null;
    previewUrl.value = '';
    rotation.value = 0;
    flipH.value = false;
    flipV.value = false;
};

const formatBytes = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
    <Head title="Image Tool" />
    <PortfolioLayout :fullWidth="true">
        <div class="p-4 md:p-8 md:pt-2 h-[calc(100vh-140px)] flex flex-col">
            <div class="mb-4 flex items-center gap-2">
                <Link href="/tools" class="text-gray-500 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold dark:text-white">Image Tool</h1>
            </div>

            <div class="flex-1 flex flex-col lg:flex-row gap-4 overflow-hidden">
                <!-- Upload Column -->
                <div class="w-full lg:w-96 flex-shrink-0 flex flex-col gap-4">
                    <div 
                        @drop.prevent="handleDrop"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        :class="[
                            'border-2 border-dashed rounded-2xl p-8 flex flex-col items-center justify-center transition-colors min-h-[300px]',
                            isDragging ? 'border-black dark:border-white bg-gray-50 dark:bg-white/5' : 'border-gray-300 dark:border-white/10'
                        ]"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 text-gray-400 mb-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        <p class="text-gray-600 dark:text-gray-400 mb-2 text-center">Drag & drop an image here</p>
                        <p class="text-sm text-gray-400 mb-4">or</p>
                        <label class="px-4 py-2 bg-black text-white dark:bg-white dark:text-black rounded-xl font-bold cursor-pointer hover:opacity-90 transition-opacity">
                            Choose File
                            <input type="file" accept="image/*" @change="handleFileSelect" class="hidden" />
                        </label>
                    </div>

                    <!-- File Info -->
                    <div v-if="uploadedFile" class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-4">
                        <h3 class="font-bold mb-3 dark:text-white">Original Image</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Format:</span>
                                <span class="font-medium dark:text-white">{{ imageInfo.format }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Size:</span>
                                <span class="font-medium dark:text-white">{{ formatBytes(imageInfo.size) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Dimensions:</span>
                                <span class="font-medium dark:text-white">{{ imageInfo.width }} × {{ imageInfo.height }}</span>
                            </div>
                        </div>
                        <button @click="clear" class="mt-4 w-full py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Preview & Operations Column -->
                <div v-if="uploadedFile" class="flex-1 flex flex-col gap-4 min-w-0">
                    <!-- Operations Toolbar -->
                    <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-4 space-y-4">
                        <!-- Format & Quality -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Format</label>
                                <select v-model="outputFormat" @change="renderCanvas" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-black/20 dark:text-white">
                                    <option value="png">PNG</option>
                                    <option value="jpeg">JPEG</option>
                                    <option value="webp">WebP</option>
                                </select>
                            </div>
                            <div v-if="outputFormat !== 'png'">
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Quality: {{ Math.round(quality * 100) }}%</label>
                                <input type="range" v-model.number="quality" min="0.1" max="1" step="0.1" @input="renderCanvas" class="w-full accent-black dark:accent-white" />
                            </div>
                        </div>

                        <!-- Resize -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Resize</label>
                            <div class="flex gap-2 items-center">
                                <input type="number" v-model.number="resizeWidth" @input="updateResize" placeholder="Width" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-black/20 dark:text-white" />
                                <span class="text-gray-400">×</span>
                                <input type="number" v-model.number="resizeHeight" @input="updateResize" placeholder="Height" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-black/20 dark:text-white" />
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" v-model="maintainAspectRatio" class="rounded border-gray-300 text-black focus:ring-black dark:border-white/20 dark:bg-white/5" />
                                    <span class="text-sm dark:text-gray-300">Lock</span>
                                </label>
                            </div>
                        </div>

                        <!-- Transform -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Transform</label>
                            <div class="flex gap-2">
                                <button @click="rotate(90)" class="flex-1 py-2 px-3 bg-white dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm dark:text-white">
                                    Rotate 90°
                                </button>
                                <button @click="flip('h')" class="flex-1 py-2 px-3 bg-white dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm dark:text-white">
                                    Flip H
                                </button>
                                <button @click="flip('v')" class="flex-1 py-2 px-3 bg-white dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm dark:text-white">
                                    Flip V
                                </button>
                                <button @click="reset" class="py-2 px-3 bg-white dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-lg hover:bg-gray-50 dark:hover:bg-white/5 transition-colors text-sm dark:text-white">
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="flex-1 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6 flex items-center justify-center overflow-auto">
                        <img v-if="previewUrl" :src="previewUrl" alt="Preview" class="max-w-full max-h-full object-contain" />
                    </div>

                    <!-- Download -->
                    <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-4">
                        <div class="flex gap-2">
                            <input v-model="filename" type="text" placeholder="Filename" class="flex-1 px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-black/20 dark:text-white" />
                            <button @click="download" class="px-6 py-2 bg-black text-white dark:bg-white dark:text-black rounded-lg font-bold hover:opacity-90 transition-opacity">
                                Download
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="flex-1 flex items-center justify-center">
                    <div class="text-center text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-4 opacity-50">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <p class="text-lg font-medium">Upload an image to get started</p>
                    </div>
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>
