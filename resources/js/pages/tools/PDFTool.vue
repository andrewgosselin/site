<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import { Link } from '@inertiajs/vue3';
import { PDFDocument } from 'pdf-lib';
import { GlobalWorkerOptions, getDocument } from 'pdfjs-dist';
import pdfWorker from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { computed, onBeforeUnmount, ref } from 'vue';

GlobalWorkerOptions.workerSrc = pdfWorker;

type PreviewPage = {
    pageNumber: number;
    previewUrl: string;
    customSplitRatio: number | null;
};

const uploadedFile = ref<File | null>(null);
const pdfObjectUrl = ref('');
const previewPages = ref<PreviewPage[]>([]);
const isDragging = ref(false);
const isLoading = ref(false);
const isProcessing = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const operationMode = ref<'splice' | 'translate'>('splice');

const splitDirection = ref<'vertical' | 'horizontal'>('vertical');
const globalSplitRatio = ref(0.5);
const linkGlobalLine = ref(true);
const dragContext = ref<{ pageNumber: number; container: HTMLElement } | null>(null);

const sourceLanguage = ref<'auto' | 'en' | 'nl' | 'de' | 'fr' | 'es'>('auto');
const targetLanguage = ref<'en' | 'nl' | 'de' | 'fr' | 'es'>('nl');
const translatedText = ref('');

const hasPdf = computed(() => uploadedFile.value !== null);
const totalOutputPages = computed(() => previewPages.value.length * 2);
const modeActionLabel = computed(() => (operationMode.value === 'splice' ? 'Splice PDF' : 'Translate PDF'));

const clearMessages = () => {
    errorMessage.value = '';
    successMessage.value = '';
};

const clampSplitRatio = (value: number) => Math.min(0.95, Math.max(0.05, value));
const getCurrentBytes = async () => (uploadedFile.value ? new Uint8Array(await uploadedFile.value.arrayBuffer()) : null);

const getEffectiveSplitRatio = (page: PreviewPage) => clampSplitRatio(page.customSplitRatio ?? globalSplitRatio.value);

const setSplitForPage = (pageNumber: number, nextRatio: number) => {
    const clamped = clampSplitRatio(nextRatio);
    const page = previewPages.value.find((candidate) => candidate.pageNumber === pageNumber);
    if (!page) return;
    if (linkGlobalLine.value && page.customSplitRatio === null) {
        globalSplitRatio.value = clamped;
        return;
    }
    page.customSplitRatio = clamped;
};

const removePreviewUrls = () => {
    for (const page of previewPages.value) URL.revokeObjectURL(page.previewUrl);
};

const clear = () => {
    clearMessages();
    uploadedFile.value = null;
    if (pdfObjectUrl.value) {
        URL.revokeObjectURL(pdfObjectUrl.value);
        pdfObjectUrl.value = '';
    }
    removePreviewUrls();
    previewPages.value = [];
    operationMode.value = 'splice';
    splitDirection.value = 'vertical';
    globalSplitRatio.value = 0.5;
    linkGlobalLine.value = true;
    sourceLanguage.value = 'auto';
    targetLanguage.value = 'nl';
    translatedText.value = '';
};

const setFile = async (file: File) => {
    clearMessages();
    if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
        errorMessage.value = 'Please upload a PDF file.';
        return;
    }

    clear();
    uploadedFile.value = file;
    pdfObjectUrl.value = URL.createObjectURL(file);
    const bytes = new Uint8Array(await file.arrayBuffer());

    isLoading.value = true;
    try {
        const loadingTask = getDocument({ data: bytes });
        const pdfDocument = await loadingTask.promise;
        const renderedPages: PreviewPage[] = [];

        for (let pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber++) {
            const page = await pdfDocument.getPage(pageNumber);
            const viewport = page.getViewport({ scale: 1.15 });
            const canvas = document.createElement('canvas');
            canvas.width = viewport.width;
            canvas.height = viewport.height;
            const context = canvas.getContext('2d');
            if (!context) continue;

            await page.render({ canvasContext: context, viewport }).promise;
            renderedPages.push({ pageNumber, previewUrl: canvas.toDataURL('image/png'), customSplitRatio: null });
        }

        previewPages.value = renderedPages;
    } catch {
        errorMessage.value = 'Unable to preview this PDF. Please try another file.';
        uploadedFile.value = null;
        if (pdfObjectUrl.value) {
            URL.revokeObjectURL(pdfObjectUrl.value);
            pdfObjectUrl.value = '';
        }
        removePreviewUrls();
        previewPages.value = [];
    } finally {
        isLoading.value = false;
    }
};

const handleFileSelect = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (!target.files?.[0]) return;
    await setFile(target.files[0]);
    target.value = '';
};

const handleDrop = async (event: DragEvent) => {
    isDragging.value = false;
    if (!event.dataTransfer?.files?.[0]) return;
    await setFile(event.dataTransfer.files[0]);
};

const beginLineDrag = (pageNumber: number, event: MouseEvent) => {
    if (operationMode.value !== 'splice') return;
    const container = event.currentTarget as HTMLElement | null;
    if (!container) return;
    dragContext.value = { pageNumber, container };
    updateLineFromPointer(event.clientX, event.clientY);
    window.addEventListener('mousemove', onPointerMove);
    window.addEventListener('mouseup', stopLineDrag);
};

const updateLineFromPointer = (clientX: number, clientY: number) => {
    if (!dragContext.value || operationMode.value !== 'splice') return;
    const { container, pageNumber } = dragContext.value;
    const bounds = container.getBoundingClientRect();
    const ratio = splitDirection.value === 'vertical' ? (clientX - bounds.left) / bounds.width : (clientY - bounds.top) / bounds.height;
    setSplitForPage(pageNumber, ratio);
};

const onPointerMove = (event: MouseEvent) => updateLineFromPointer(event.clientX, event.clientY);
const stopLineDrag = () => {
    dragContext.value = null;
    window.removeEventListener('mousemove', onPointerMove);
    window.removeEventListener('mouseup', stopLineDrag);
};

const applyGlobalToAllPages = () => {
    for (const page of previewPages.value) page.customSplitRatio = null;
    successMessage.value = 'Global split position applied to all pages.';
};

const downloadBytes = (bytes: Uint8Array, suffix: string) => {
    const blob = new Blob([bytes], { type: 'application/pdf' });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    const originalName = uploadedFile.value?.name.replace(/\.pdf$/i, '') || 'document';
    anchor.href = url;
    anchor.download = `${originalName}-${suffix}.pdf`;
    anchor.click();
    URL.revokeObjectURL(url);
};

const downloadText = (text: string, suffix: string) => {
    const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const anchor = document.createElement('a');
    const originalName = uploadedFile.value?.name.replace(/\.pdf$/i, '') || 'document';
    anchor.href = url;
    anchor.download = `${originalName}-${suffix}.txt`;
    anchor.click();
    URL.revokeObjectURL(url);
};

const splicePdf = async () => {
    const bytes = await getCurrentBytes();
    if (!bytes || previewPages.value.length === 0) return;

    const sourcePdf = await PDFDocument.load(bytes);
    const outputPdf = await PDFDocument.create();

    for (let index = 0; index < sourcePdf.getPageCount(); index++) {
        const sourcePage = sourcePdf.getPage(index);
        const targetPreview = previewPages.value[index];
        const splitRatio = targetPreview ? getEffectiveSplitRatio(targetPreview) : globalSplitRatio.value;
        const width = sourcePage.getWidth();
        const height = sourcePage.getHeight();
        const mediaBox = sourcePage.getMediaBox();
        const minX = mediaBox.x;
        const minY = mediaBox.y;

        if (splitDirection.value === 'vertical') {
            const safeSplit = Math.min(width - 1, Math.max(1, width * splitRatio));
            const [leftPage, rightPage] = await outputPdf.copyPages(sourcePdf, [index, index]);
            leftPage.setCropBox(minX, minY, safeSplit, height);
            rightPage.setCropBox(minX + safeSplit, minY, width - safeSplit, height);
            outputPdf.addPage(leftPage);
            outputPdf.addPage(rightPage);
        } else {
            const safeSplit = Math.min(height - 1, Math.max(1, height * (1 - splitRatio)));
            const [topPage, bottomPage] = await outputPdf.copyPages(sourcePdf, [index, index]);
            topPage.setCropBox(minX, minY + safeSplit, width, height - safeSplit);
            bottomPage.setCropBox(minX, minY, width, safeSplit);
            outputPdf.addPage(topPage);
            outputPdf.addPage(bottomPage);
        }
    }

    downloadBytes(await outputPdf.save(), 'spliced');
    successMessage.value = `Spliced PDF created with ${totalOutputPages.value} pages (${splitDirection.value} split).`;
};

const translatePdfLanguage = async () => {
    const bytes = await getCurrentBytes();
    if (!bytes) return;

    const loadingTask = getDocument({ data: bytes });
    const pdfDocument = await loadingTask.promise;
    const textParts: string[] = [];

    for (let pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber++) {
        const page = await pdfDocument.getPage(pageNumber);
        const textContent = await page.getTextContent();
        const pageText = textContent.items
            .map((item) => ('str' in item ? item.str : ''))
            .filter((item) => typeof item === 'string' && item.length > 0)
            .join(' ')
            .trim();

        if (pageText.length > 0) {
            textParts.push(pageText);
        }
    }

    const extractedText = textParts.join('\n\n').trim();
    if (!extractedText) {
        throw new Error('No selectable text found in this PDF. OCR is not supported yet');
    }

    const response = await fetch('/api/tools/pdf/translate-text', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
        body: JSON.stringify({
            text: extractedText.slice(0, 20000),
            source_language: sourceLanguage.value === 'auto' ? null : sourceLanguage.value,
            target_language: targetLanguage.value,
        }),
    });

    const payload = await response.json();
    if (!response.ok) {
        throw new Error(payload?.message || 'Translation request failed');
    }

    translatedText.value = payload.translated_text || '';
    if (!translatedText.value) {
        throw new Error('Translation returned empty content');
    }

    downloadText(translatedText.value, `translated-${targetLanguage.value}`);
    successMessage.value = `Translated text exported in ${targetLanguage.value.toUpperCase()}.`;
};

const runCurrentOperation = async () => {
    if (!hasPdf.value) return;
    clearMessages();
    isProcessing.value = true;
    try {
        if (operationMode.value === 'splice') await splicePdf();
        if (operationMode.value === 'translate') await translatePdfLanguage();
    } catch (error) {
        const details = error instanceof Error ? ` (${error.message})` : '';
        errorMessage.value = `Failed to process this PDF${details}.`;
    } finally {
        isProcessing.value = false;
    }
};

onBeforeUnmount(() => {
    stopLineDrag();
    if (pdfObjectUrl.value) URL.revokeObjectURL(pdfObjectUrl.value);
    removePreviewUrls();
});
</script>

<template>
    <SeoHead />
    <PortfolioLayout :fullWidth="true">
        <div class="flex h-[calc(100vh-140px)] flex-col p-4 md:p-8 md:pt-2">
            <div class="mb-4 flex items-center gap-2">
                <Link href="/tools" class="text-gray-500 transition-colors hover:text-black dark:text-gray-400 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold dark:text-white">PDF Tool</h1>
            </div>

            <div class="flex min-h-0 flex-1 flex-col gap-4 lg:flex-row">
                <div class="flex w-full shrink-0 flex-col gap-4 lg:w-96">
                    <div
                        @drop.prevent="handleDrop"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        :class="[
                            'min-h-[220px] rounded-2xl border-2 border-dashed p-6 transition-colors',
                            isDragging ? 'border-black bg-gray-50 dark:border-white dark:bg-white/5' : 'border-gray-300 dark:border-white/10',
                        ]"
                    >
                        <div class="flex h-full flex-col items-center justify-center text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mb-4 h-14 w-14 text-gray-400">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125V5.625a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3V10.5M2.25 12.75V6.375c0-1.864 1.511-3.375 3.375-3.375h6.75c.895 0 1.753.356 2.386.989l4.5 4.5a3.375 3.375 0 01.989 2.386v5.625"
                                />
                            </svg>
                            <p class="mb-2 text-gray-600 dark:text-gray-400">Drag and drop a PDF here</p>
                            <p class="mb-4 text-sm text-gray-400">or</p>
                            <label class="cursor-pointer rounded-xl bg-black px-4 py-2 font-bold text-white transition-opacity hover:opacity-90 dark:bg-white dark:text-black">
                                Choose PDF
                                <input type="file" accept="application/pdf,.pdf" class="hidden" @change="handleFileSelect" />
                            </label>
                        </div>
                    </div>

                    <div v-if="hasPdf" class="space-y-4 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="space-y-3">
                            <h2 class="text-sm font-bold tracking-wider uppercase dark:text-white">Tool</h2>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" class="rounded-lg border px-2 py-2 text-xs font-medium" :class="operationMode === 'splice' ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' : 'border-gray-200 dark:border-white/20 dark:text-white'" @click="operationMode = 'splice'">Splice</button>
                                <button type="button" class="rounded-lg border px-2 py-2 text-xs font-medium" :class="operationMode === 'translate' ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' : 'border-gray-200 dark:border-white/20 dark:text-white'" @click="operationMode = 'translate'">Translate</button>
                            </div>
                        </div>

                        <div v-if="operationMode === 'splice'" class="space-y-3">
                            <h3 class="text-sm font-bold tracking-wider uppercase dark:text-white">Splice Settings</h3>
                            <div>
                                <label class="mb-2 block text-xs font-bold tracking-wider text-gray-400 uppercase">Split Direction</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" @click="splitDirection = 'vertical'" class="rounded-lg border px-3 py-2 text-sm font-medium" :class="splitDirection === 'vertical' ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' : 'border-gray-200 dark:border-white/20 dark:text-white'">Vertical</button>
                                    <button type="button" @click="splitDirection = 'horizontal'" class="rounded-lg border px-3 py-2 text-sm font-medium" :class="splitDirection === 'horizontal' ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' : 'border-gray-200 dark:border-white/20 dark:text-white'">Horizontal</button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold tracking-wider text-gray-400 uppercase">Global {{ splitDirection === 'vertical' ? 'line from left' : 'line from top' }}: {{ Math.round(globalSplitRatio * 100) }}%</label>
                                <input v-model.number="globalSplitRatio" type="range" min="0.05" max="0.95" step="0.01" class="w-full accent-black dark:accent-white" />
                            </div>
                            <label class="flex cursor-pointer items-center gap-2">
                                <input v-model="linkGlobalLine" type="checkbox" class="rounded border-gray-300 text-black focus:ring-black dark:border-white/20 dark:bg-white/5" />
                                <span class="text-sm dark:text-gray-300">Drag on shared pages updates global line</span>
                            </label>
                            <button type="button" @click="applyGlobalToAllPages" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm transition-colors hover:bg-white dark:border-white/20 dark:text-white dark:hover:bg-white/5">Apply global line to all pages</button>
                        </div>

                        <div v-if="operationMode === 'translate'" class="space-y-3">
                            <h3 class="text-sm font-bold tracking-wider uppercase dark:text-white">Language Translate</h3>
                            <div>
                                <label class="mb-2 block text-xs font-bold tracking-wider text-gray-400 uppercase">From</label>
                                <select v-model="sourceLanguage" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-white/20 dark:bg-black/20 dark:text-white">
                                    <option value="auto">Auto detect</option>
                                    <option value="en">English</option>
                                    <option value="nl">Dutch</option>
                                    <option value="de">German</option>
                                    <option value="fr">French</option>
                                    <option value="es">Spanish</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-2 block text-xs font-bold tracking-wider text-gray-400 uppercase">To</label>
                                <select v-model="targetLanguage" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-white/20 dark:bg-black/20 dark:text-white">
                                    <option value="en">English</option>
                                    <option value="nl">Dutch</option>
                                    <option value="de">German</option>
                                    <option value="fr">French</option>
                                    <option value="es">Spanish</option>
                                </select>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Extracts selectable text from the PDF and exports translated text as a `.txt` file.
                            </p>
                            <textarea
                                v-if="translatedText"
                                :value="translatedText"
                                rows="5"
                                readonly
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs dark:border-white/20 dark:bg-black/20 dark:text-white"
                            />
                            <button
                                v-if="translatedText"
                                type="button"
                                class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm transition-colors hover:bg-white dark:border-white/20 dark:text-white dark:hover:bg-white/5"
                                @click="downloadText(translatedText, `translated-${targetLanguage}`)"
                            >
                                Download translated text again
                            </button>
                            <p v-if="!translatedText" class="text-xs text-gray-500 dark:text-gray-400">
                                Run Translate PDF to extract + translate.
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Note: scanned/image-only PDFs need OCR support first.
                            </p>
                        </div>

                        <div v-if="operationMode === 'translate'" class="space-y-2 border-t border-gray-200 pt-3 text-xs text-gray-500 dark:border-white/10 dark:text-gray-400">
                            <p>Language translation currently outputs translated text only (not rewritten PDF layout).</p>
                            <p>Max text sent per request: 20,000 characters.</p>
                        </div>

                        <div class="space-y-2 border-t border-gray-200 pt-4 text-sm dark:border-white/10">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Pages loaded</span>
                                <span class="font-medium dark:text-white">{{ previewPages.length }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Output pages</span>
                                <span class="font-medium dark:text-white">{{ totalOutputPages }}</span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button type="button" @click="runCurrentOperation" :disabled="isProcessing || isLoading" class="flex-1 rounded-xl bg-black px-4 py-2 font-bold text-white transition-opacity hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white dark:text-black">
                                {{ isProcessing ? 'Processing...' : modeActionLabel }}
                            </button>
                            <button type="button" @click="clear" class="rounded-xl border border-gray-200 px-4 py-2 text-sm transition-colors hover:bg-white dark:border-white/20 dark:text-white dark:hover:bg-white/5">Clear</button>
                        </div>
                    </div>

                    <p v-if="errorMessage" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        {{ errorMessage }}
                    </p>
                    <p
                        v-if="successMessage"
                        class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300"
                    >
                        {{ successMessage }}
                    </p>
                </div>

                <div class="min-h-0 min-w-0 flex-1 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                    <div v-if="!hasPdf" class="flex h-full items-center justify-center text-center text-gray-400">
                        <div>
                            <p class="text-lg font-medium">Upload a PDF to preview and splice pages</p>
                            <p class="mt-1 text-sm">You can set one global line and override specific pages.</p>
                        </div>
                    </div>

                    <div v-else-if="isLoading" class="flex h-full items-center justify-center text-gray-500 dark:text-gray-400">Rendering page previews...</div>

                    <div v-else class="grid h-full grid-cols-1 gap-4 overflow-y-auto pr-1 md:grid-cols-2 xl:grid-cols-3 custom-scrollbar">
                        <div v-for="page in previewPages" :key="page.pageNumber" class="rounded-xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-black/20">
                            <div class="mb-2 flex items-center justify-between">
                                <p class="text-sm font-bold dark:text-white">Page {{ page.pageNumber }}</p>
                                <label class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                    <input
                                        :checked="page.customSplitRatio !== null"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-black focus:ring-black dark:border-white/20 dark:bg-white/5"
                                        @change="(event) => (page.customSplitRatio = (event.target as HTMLInputElement).checked ? getEffectiveSplitRatio(page) : null)"
                                    />
                                    Custom
                                </label>
                            </div>

                            <div class="group relative overflow-hidden rounded-lg border border-gray-200 bg-gray-100 dark:border-white/10 dark:bg-black/40" :class="operationMode === 'splice' ? splitDirection === 'vertical' ? 'cursor-col-resize' : 'cursor-row-resize' : 'cursor-default'" @mousedown="(event) => beginLineDrag(page.pageNumber, event)">
                                <img :src="page.previewUrl" :alt="`PDF page ${page.pageNumber}`" class="w-full select-none" draggable="false" />
                                <div v-if="operationMode === 'splice'" class="pointer-events-none absolute border-red-500" :class="splitDirection === 'vertical' ? 'top-0 bottom-0 border-l-2' : 'left-0 right-0 border-t-2'" :style="splitDirection === 'vertical' ? { left: `${getEffectiveSplitRatio(page) * 100}%` } : { top: `${getEffectiveSplitRatio(page) * 100}%` }">
                                    <span class="absolute rounded bg-red-500 px-1.5 py-0.5 text-[10px] font-bold text-white" :class="splitDirection === 'vertical' ? 'top-2 left-2' : '-top-6 right-2'">{{ Math.round(getEffectiveSplitRatio(page) * 100) }}%</span>
                                </div>
                            </div>

                            <div v-if="operationMode === 'splice'" class="mt-3">
                                <label class="mb-1 block text-xs font-bold tracking-wider text-gray-400 uppercase">
                                    {{
                                        page.customSplitRatio === null
                                            ? 'Inherited from global'
                                            : `Custom line from ${splitDirection === 'vertical' ? 'left' : 'top'}`
                                    }}
                                </label>
                                <input
                                    :value="getEffectiveSplitRatio(page)"
                                    type="range"
                                    min="0.05"
                                    max="0.95"
                                    step="0.01"
                                    class="w-full accent-black dark:accent-white"
                                    @input="(event) => setSplitForPage(page.pageNumber, Number((event.target as HTMLInputElement).value))"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>
