<script setup lang="ts">
import { ref } from 'vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';

const url = ref('');
const loading = ref(false);
const error = ref<string | null>(null);
const metadata = ref<any>(null);

const fetchMetadata = async () => {
    if (!url.value) return;
    
    loading.value = true;
    error.value = null;
    metadata.value = null;

    try {
        const response = await axios.post('/api/tools/fetch-metadata', {
            url: url.value
        });
        metadata.value = response.data;
    } catch (e: any) {
        error.value = e.response?.data?.error || 'Failed to fetch metadata';
    } finally {
        loading.value = false;
    }
};

const getDomain = (urlString: string) => {
    try {
        const parsed = new URL(urlString);
        return parsed.hostname;
    } catch {
        return urlString;
    }
};
</script>

<template>
    <Head title="SEO Checker" />
    <PortfolioLayout :fullWidth="true">
        <div class="p-4 md:p-8 md:pt-2 min-h-[calc(100vh-140px)] flex flex-col">
            <div class="mb-4 flex items-center gap-2">
                <Link href="/tools" class="text-gray-500 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold dark:text-white">SEO Checker</h1>
            </div>

            <!-- Input Section -->
            <div class="mb-6 flex gap-2">
                <input 
                    v-model="url" 
                    type="url" 
                    placeholder="Enter URL (e.g., https://example.com)"
                    @keyup.enter="fetchMetadata"
                    class="flex-1 px-4 py-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 dark:text-white focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white"
                />
                <button 
                    @click="fetchMetadata"
                    :disabled="loading || !url"
                    class="px-6 py-3 bg-black text-white dark:bg-white dark:text-black rounded-xl font-bold hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ loading ? 'Loading...' : 'Fetch' }}
                </button>
            </div>

            <!-- Error State -->
            <div v-if="error" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                <p class="text-red-600 dark:text-red-400">{{ error }}</p>
            </div>

            <!-- Results -->
            <div v-if="metadata" class="space-y-6">
                <!-- Browser Tab Preview -->
                <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                    <h2 class="text-lg font-bold mb-4 dark:text-white">Browser Tab</h2>
                    <div class="flex items-center gap-3 bg-white dark:bg-black/20 p-3 rounded-lg border border-gray-200 dark:border-white/10">
                        <img 
                            v-if="metadata.favicon" 
                            :src="metadata.favicon" 
                            alt="Favicon"
                            class="w-4 h-4"
                            @error="(e) => (e.target as HTMLImageElement).style.display = 'none'"
                        />
                        <div v-else class="w-4 h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
                        <span class="text-sm font-medium dark:text-gray-300 truncate">{{ metadata.title || 'Untitled' }}</span>
                    </div>
                </div>

                <!-- Google Search Result Preview -->
                <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                    <h2 class="text-lg font-bold mb-4 dark:text-white">Google Search Result</h2>
                    <div class="bg-white dark:bg-black/20 p-4 rounded-lg border border-gray-200 dark:border-white/10">
                        <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">{{ getDomain(url) }}</div>
                        <h3 class="text-xl text-blue-600 dark:text-blue-400 mb-2 hover:underline cursor-pointer">
                            {{ metadata.title || 'Untitled Page' }}
                        </h3>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ metadata.description || 'No description available' }}
                        </p>
                    </div>
                </div>

                <!-- Open Graph / Facebook Preview -->
                <div v-if="metadata.openGraph && Object.keys(metadata.openGraph).length > 0" class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                    <h2 class="text-lg font-bold mb-4 dark:text-white">Open Graph (Facebook/LinkedIn)</h2>
                    <div class="bg-white dark:bg-black/20 rounded-lg border border-gray-200 dark:border-white/10 overflow-hidden">
                        <img 
                            v-if="metadata.openGraph.image" 
                            :src="metadata.openGraph.image" 
                            alt="OG Image"
                            class="w-full h-48 object-cover"
                            @error="(e) => (e.target as HTMLImageElement).style.display = 'none'"
                        />
                        <div class="p-4">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase">{{ getDomain(url) }}</div>
                            <h3 class="font-bold text-lg mb-1 dark:text-white">{{ metadata.openGraph.title || metadata.title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ metadata.openGraph.description || metadata.description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Twitter Card Preview -->
                <div v-if="metadata.twitter && Object.keys(metadata.twitter).length > 0" class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                    <h2 class="text-lg font-bold mb-4 dark:text-white">Twitter Card</h2>
                    <div class="bg-white dark:bg-black/20 rounded-lg border border-gray-200 dark:border-white/10 overflow-hidden">
                        <img 
                            v-if="metadata.twitter.image" 
                            :src="metadata.twitter.image" 
                            alt="Twitter Image"
                            class="w-full h-48 object-cover"
                            @error="(e) => (e.target as HTMLImageElement).style.display = 'none'"
                        />
                        <div class="p-4">
                            <h3 class="font-bold text-lg mb-1 dark:text-white">{{ metadata.twitter.title || metadata.title }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ metadata.twitter.description || metadata.description }}</p>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ getDomain(url) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Structured Data -->
                <div v-if="metadata.structuredData && metadata.structuredData.length > 0" class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                    <h2 class="text-lg font-bold mb-4 dark:text-white">Structured Data (JSON-LD)</h2>
                    <div class="space-y-3">
                        <div v-for="(data, index) in metadata.structuredData" :key="index" class="bg-white dark:bg-black/20 p-4 rounded-lg border border-gray-200 dark:border-white/10">
                            <div class="text-xs font-mono text-gray-500 dark:text-gray-400 mb-2">
                                Type: {{ data['@type'] || 'Unknown' }}
                            </div>
                            <pre class="text-xs font-mono overflow-x-auto dark:text-gray-300">{{ JSON.stringify(data, null, 2) }}</pre>
                        </div>
                    </div>
                </div>

                <!-- All Meta Tags -->
                <details class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                    <summary class="text-lg font-bold cursor-pointer dark:text-white">All Meta Tags ({{ metadata.allMeta?.length || 0 }})</summary>
                    <div class="mt-4 space-y-2">
                        <div v-for="(meta, index) in metadata.allMeta" :key="index" class="bg-white dark:bg-black/20 p-3 rounded-lg border border-gray-200 dark:border-white/10">
                            <div class="text-xs font-bold text-gray-600 dark:text-gray-400 mb-1">{{ meta.name }}</div>
                            <div class="text-sm dark:text-gray-300">{{ meta.content }}</div>
                        </div>
                    </div>
                </details>
            </div>

            <!-- Empty State -->
            <div v-if="!metadata && !loading && !error" class="flex-1 flex items-center justify-center">
                <div class="text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-4 opacity-50">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                    </svg>
                    <p class="text-lg font-medium">Enter a URL to view its metadata</p>
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>
