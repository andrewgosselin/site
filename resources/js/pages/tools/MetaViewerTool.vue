<script setup lang="ts">
import { ref, computed } from 'vue';
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

// --- Computed Previews with Fallbacks ---

// Generic Helper
const getMeta = (key: string, type: 'twitter' | 'openGraph' | 'root' = 'root') => {
    if (!metadata.value) return null;
    if (type === 'root') return metadata.value[key];
    return metadata.value[type]?.[key];
};

const displayDomain = computed(() => getDomain(url.value));

const twitterPreview = computed(() => {
    if (!metadata.value) return null;
    
    const t = metadata.value.twitter || {};
    const og = metadata.value.openGraph || {};
    const m = metadata.value;

    return {
        title: t.title || og.title || m.title,
        description: t.description || og.description || m.description,
        image: t.image || og.image,
        card: t.card || (t.image || og.image ? 'summary_large_image' : 'summary'),
        domain: displayDomain.value
    };
});

const linkedinPreview = computed(() => {
    if (!metadata.value) return null;
    
    const og = metadata.value.openGraph || {};
    const m = metadata.value;

    return {
        title: og.title || m.title,
        description: og.description || m.description, // LinkedIn often truncates this heavily
        image: og.image || m.twitter?.image,
        domain: displayDomain.value
    };
});

const discordPreview = computed(() => {
    if (!metadata.value) return null;
    
    const og = metadata.value.openGraph || {};
    const m = metadata.value;

    return {
        title: og.title || m.title,
        description: og.description || m.description,
        image: og.image || m.twitter?.image,
        siteName: og.site_name || displayDomain.value,
        themeColor: '#2B2D31' // Default Discord embed background
    };
});

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
            <div v-if="metadata" class="space-y-8">
                
                <!-- 1. Search Results -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Google Search Result Preview -->
                    <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                        <h2 class="text-lg font-bold mb-4 dark:text-white flex items-center gap-2">
                            <span class="text-xl">🔍</span> Google Search
                        </h2>
                        <div class="bg-white dark:bg-[#1f1f1f] p-4 rounded-lg border border-gray-200 dark:border-white/5">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="bg-gray-100 dark:bg-gray-700 w-7 h-7 rounded-full flex items-center justify-center overflow-hidden">
                                    <img 
                                        v-if="metadata.favicon" 
                                        :src="metadata.favicon" 
                                        alt="Favicon"
                                        class="w-4 h-4 object-contain"
                                        @error="(e) => (e.target as HTMLImageElement).style.display = 'none'"
                                    />
                                    <span v-else class="text-[10px] text-gray-500">?</span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm text-gray-900 dark:text-[#dadce0] leading-tight">{{ metadata.title || 'Untitled' }}</span>
                                    <span class="text-xs text-gray-500 dark:text-[#bdc1c6] leading-tight">{{ url }}</span>
                                </div>
                            </div>
                            <h3 class="text-xl text-[#1a0dab] dark:text-[#8ab4f8] hover:underline cursor-pointer mb-1 truncate">
                                {{ metadata.title || 'Untitled Page' }}
                            </h3>
                            <p class="text-sm text-[#4d5156] dark:text-[#bdc1c6] line-clamp-2">
                                {{ metadata.description || 'No description available for this page.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Browser Tab Preview -->
                    <div class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                         <h2 class="text-lg font-bold mb-4 dark:text-white flex items-center gap-2">
                            <span class="text-xl">🌐</span> Browser Tab
                        </h2>
                        <div class="bg-gray-200 dark:bg-gray-800 p-2 rounded-t-lg flex gap-2">
                            <div class="bg-white dark:bg-[#1f1f1f] pl-3 pr-8 py-2 rounded-lg flex items-center gap-2 max-w-[240px] border-t border-x border-gray-300 dark:border-black/50 shadow-sm">
                                <img 
                                    v-if="metadata.favicon" 
                                    :src="metadata.favicon" 
                                    alt="Favicon"
                                    class="w-4 h-4"
                                    @error="(e) => (e.target as HTMLImageElement).style.display = 'none'"
                                />
                                <div v-else class="w-4 h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
                                <span class="text-xs font-medium dark:text-gray-300 truncate">{{ metadata.title || 'Untitled' }}</span>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-[#1f1f1f] h-[100px] border-x border-b border-gray-200 dark:border-white/10 rounded-b-lg flex items-center justify-center text-gray-400 text-sm">
                            Page Content Info...
                        </div>
                    </div>
                </div>

                <!-- 2. Social Previews Grid -->
                <div>
                     <h2 class="text-xl font-bold mb-4 dark:text-white border-b dark:border-white/10 pb-2">Social Media Previews</h2>
                     <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        
                        <!-- Twitter Card -->
                        <div class="space-y-2">
                            <h3 class="font-bold text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wider">Twitter / X</h3>
                            <div class="bg-black/5 dark:bg-white/5 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 max-w-sm">
                                <!-- Image at Top (Large Card) -->
                                <div v-if="twitterPreview?.image" class="aspect-[2/1] w-full bg-gray-200 dark:bg-gray-800 relative overflow-hidden">
                                     <img 
                                        :src="twitterPreview.image" 
                                        class="w-full h-full object-cover"
                                        alt="Twitter Preview"
                                        @error="(e) => (e.target as HTMLImageElement).style.display = 'none'"
                                    />
                                    <div class="absolute bottom-2 left-2 bg-black/60 text-white text-[10px] px-1 rounded">
                                        {{ displayDomain }}
                                    </div>
                                </div>
                                <div class="p-3 bg-white dark:bg-black font-sans">
                                    <!-- Domain usually shows if no image, but with large image it's sometimes different. Let's simplify -->
                                    <div v-if="!twitterPreview?.image" class="text-gray-500 text-sm mb-1">{{ displayDomain }}</div>
                                    <h4 class="font-bold text-black dark:text-white leading-tight mb-1 line-clamp-2">
                                        {{ twitterPreview?.title || 'No Title' }}
                                    </h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm line-clamp-2 leading-snug">
                                        {{ twitterPreview?.description || 'No description available' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Facebook / LinkedIn -->
                        <div class="space-y-2">
                            <h3 class="font-bold text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wider">LinkedIn / Facebook</h3>
                            <div class="bg-white dark:bg-[#1f1f1f] border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden max-w-sm shadow-sm cursor-pointer">
                                 <div v-if="linkedinPreview?.image" class="aspect-[1.91/1] w-full bg-gray-200 dark:bg-gray-700 relative">
                                    <img 
                                        :src="linkedinPreview.image" 
                                        class="w-full h-full object-cover"
                                        alt="LinkedIn Preview"
                                        @error="(e) => (e.target as HTMLImageElement).style.display = 'none'"
                                    />
                                </div>
                                <div class="p-3 bg-[#fdfdfd] dark:bg-[#2b2b2b] border-t border-gray-100 dark:border-white/5">
                                    <div class="text-xs font-bold text-gray-900 dark:text-[#dadce0] truncate mb-1">
                                        {{ linkedinPreview?.title || 'No Title' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-[#bdc1c6] truncate">
                                        {{ displayDomain }}
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Discord / Slack -->
                        <div class="space-y-2">
                            <h3 class="font-bold text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wider">Discord / Slack</h3>
                            <div class="bg-[#313338] rounded flex max-w-sm pl-1 py-0 overflow-hidden text-left" style="border-left: 4px solid #1e1f22;">
                                <div class="p-3 md:pr-4 flex-1 min-w-0">
                                    <div class="text-xs font-medium text-[#b5bac1] mb-1.5">{{ discordPreview?.siteName || displayDomain }}</div>
                                    <div class="text-[#00b0f4] font-semibold text-sm hover:underline cursor-pointer mb-1.5 line-clamp-2">
                                        {{ discordPreview?.title || 'No Title' }}
                                    </div>
                                    <div class="text-[#dbdee1] text-xs leading-relaxed line-clamp-4 mb-2">
                                        {{ discordPreview?.description || 'No description available' }}
                                    </div>
                                    <img v-if="discordPreview?.image" 
                                        :src="discordPreview.image" 
                                        class="rounded-lg max-w-full max-h-[200px] object-cover border border-[#1e1f22]"
                                        alt="Discord Image"
                                    />
                                </div>
                            </div>
                        </div>

                     </div>
                </div>

                <!-- 3. Technical Data -->
                 <div>
                    <h2 class="text-xl font-bold mb-4 dark:text-white border-b dark:border-white/10 pb-2">Technical Details</h2>
                    <div class="space-y-4">
                        <!-- Structured Data -->
                        <div v-if="metadata.structuredData && metadata.structuredData.length > 0" class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                            <h2 class="text-lg font-bold mb-4 dark:text-white">Structured Data (JSON-LD)</h2>
                            <div class="space-y-3">
                                <div v-for="(data, index) in metadata.structuredData" :key="index" class="bg-white dark:bg-black/20 p-4 rounded-lg border border-gray-200 dark:border-white/10">
                                    <div class="text-xs font-mono text-gray-500 dark:text-gray-400 mb-2">
                                        Type: {{ data['@type'] || 'Unknown' }}
                                    </div>
                                    <pre class="text-xs font-mono overflow-x-auto dark:text-gray-300 max-h-64">{{ JSON.stringify(data, null, 2) }}</pre>
                                </div>
                            </div>
                        </div>

                        <!-- All Meta Tags -->
                        <details class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6">
                            <summary class="text-lg font-bold cursor-pointer dark:text-white">Raw Meta Tags ({{ metadata.allMeta?.length || 0 }})</summary>
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-2">
                                <div v-for="(meta, index) in metadata.allMeta" :key="index" class="bg-white dark:bg-black/20 p-3 rounded-lg border border-gray-200 dark:border-white/10">
                                    <div class="text-xs font-bold text-blue-600 dark:text-blue-400 mb-1 break-all">{{ meta.name }}</div>
                                    <div class="text-sm dark:text-gray-300 break-words font-mono text-xs">{{ meta.content }}</div>
                                </div>
                            </div>
                        </details>
                    </div>
                 </div>

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
