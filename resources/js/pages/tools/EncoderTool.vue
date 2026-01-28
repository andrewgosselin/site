<script setup lang="ts">
import { ref, computed } from 'vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import SeoHead from '@/components/SeoHead.vue';
import { Head, Link } from '@inertiajs/vue3';
import { jwtDecode } from "jwt-decode";

const input = ref('');
const mode = ref<'encode' | 'decode'>('encode');
const format = ref<'url' | 'base64' | 'jwt'>('url');
const error = ref<string | null>(null);

const output = computed(() => {
    if (!input.value) return '';
    error.value = null;

    try {
        switch (format.value) {
            case 'url':
                return mode.value === 'encode' 
                    ? encodeURIComponent(input.value) 
                    : decodeURIComponent(input.value);
            case 'base64':
                if (mode.value === 'encode') {
                    return btoa(input.value);
                } else {
                    return atob(input.value);
                }
            case 'jwt':
                if (mode.value === 'encode') {
                    error.value = 'JWT signing requires a secret key and is not supported in this client-side tool.';
                    return '';
                } else {
                    try {
                        const header = jwtDecode(input.value, { header: true });
                        const payload = jwtDecode(input.value);
                        return JSON.stringify({ header, payload }, null, 4);
                    } catch (e) {
                        throw new Error('Invalid JWT format');
                    }
                }
            default:
                return '';
        }
    } catch (e: any) {
        error.value = e.message;
        return '';
    }
});

const copy = async () => {
    if (output.value) {
        await navigator.clipboard.writeText(output.value);
    }
};

const labels = {
    url: 'URL',
    base64: 'Base64',
    jwt: 'JWT'
};
</script>

<template>
    <SeoHead />
    <PortfolioLayout :fullWidth="true">
        <div class="p-4 md:p-8 md:pt-2 h-[calc(100vh-140px)] flex flex-col">
            <div class="mb-4 flex items-center gap-2">
                 <Link href="/tools" class="text-gray-500 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                 </Link>
                <h1 class="text-2xl font-bold dark:text-white">Encoder / Decoder</h1>
            </div>

            <div class="flex-1 flex flex-col lg:flex-row gap-4 overflow-hidden">
                <!-- Input Column -->
                <div class="flex-1 flex flex-col gap-2 h-full min-h-[300px]">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-sm font-medium uppercase tracking-wider text-gray-500">Input</label>
                        <div class="flex gap-2 bg-gray-100 dark:bg-white/10 p-1 rounded-lg">
                            <button 
                                v-for="m in ['encode', 'decode']" 
                                :key="m"
                                @click="mode = m as any"
                                class="px-3 py-1 text-xs font-bold uppercase rounded-md transition-shadow"
                                :class="mode === m ? 'bg-white dark:bg-black text-black dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-white'"
                            >
                                {{ m }}
                            </button>
                        </div>
                    </div>
                    <textarea 
                        v-model="input"
                        class="flex-1 w-full p-4 font-mono text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white resize-none"
                        placeholder="Paste content here..."
                    ></textarea>
                </div>

                <!-- Output Column -->
                <div class="flex-1 flex flex-col gap-2 h-full min-h-[300px]">
                    <div class="flex justify-between items-center px-1">
                         <div class="flex items-center gap-2 overflow-x-auto no-scrollbar">
                            <button 
                                v-for="(label, key) in labels" 
                                :key="key"
                                @click="format = key as any"
                                class="px-3 py-1 text-xs font-bold uppercase rounded-full border transition-colors whitespace-nowrap"
                                :class="format === key ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' : 'border-gray-200 dark:border-white/10 text-gray-500 hover:border-gray-400 dark:hover:border-white/30'"
                            >
                                {{ label }}
                            </button>
                        </div>
                        <button 
                            @click="copy" 
                            class="text-xs text-gray-500 hover:text-black dark:hover:text-white transition-colors"
                        >
                            Copy
                        </button>
                    </div>

                    <div class="flex-1 relative bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden">
                        <!-- Error Overlay -->
                        <div v-if="error" class="absolute inset-x-0 bottom-0 bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-2 text-xs font-mono border-t border-red-200 dark:border-red-900/50 z-10">
                            {{ error }}
                        </div>

                        <!-- Content -->
                        <div class="absolute inset-0 overflow-auto p-4 custom-scrollbar">
                             <div v-if="!input" class="h-full flex items-center justify-center text-gray-400 italic">
                                Result will appear here...
                            </div>
                            <pre v-else class="font-mono text-sm leading-6 dark:text-[#EDEDEC] whitespace-pre-wrap break-all">{{ output }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>
