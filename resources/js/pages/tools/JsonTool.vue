<script setup lang="ts">
import { ref, computed } from 'vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import JsonTreeView from '@/components/tools/JsonTreeView.vue';
import JsonVisualView from '@/components/tools/JsonVisualView.vue';
import { Head, Link } from '@inertiajs/vue3';
import yaml from 'js-yaml';
import { js2xml } from 'xml-js';

const jsonInput = ref('');
const activeTab = ref<'structured' | 'visual' | 'convert'>('visual');
const error = ref<string | null>(null);
const convertFormat = ref<'yaml' | 'xml'>('yaml');

const parsedJson = computed(() => {
    if (!jsonInput.value.trim()) return null;
    try {
        error.value = null;
        return JSON.parse(jsonInput.value);
    } catch (e: any) {
        error.value = e.message;
        return null;
    }
});

const beautifiedHtml = computed(() => {
    // We can keep a copy function that copies beautified JSON even if we don't show it
    if (parsedJson.value) {
        return JSON.stringify(parsedJson.value, null, 4);
    }
    return jsonInput.value;
});

const convertedOutput = computed(() => {
    if (!parsedJson.value) return '';
    try {
        switch (convertFormat.value) {
            case 'yaml':
                return yaml.dump(parsedJson.value);
            case 'xml':
                return js2xml(parsedJson.value, { compact: true, spaces: 4 });
            default:
                return '';
        }
    } catch (e: any) {
        return `Conversion Error: ${e.message}`;
    }
});

const copyToClipboard = async () => {
    const textToCopy = activeTab.value === 'convert' ? convertedOutput.value : beautifiedHtml.value;
    if (textToCopy) {
        await navigator.clipboard.writeText(textToCopy);
    }
};

const download = () => {
    let content = '';
    let extension = '';
    let mimeType = '';

    if (activeTab.value === 'convert') {
        content = convertedOutput.value;
        extension = convertFormat.value;
        if (extension === 'yaml') mimeType = 'text/yaml';
        if (extension === 'xml') mimeType = 'application/xml';
    } else {
        content = beautifiedHtml.value;
        extension = 'json';
        mimeType = 'application/json';
    }

    if (!content) return;

    const blob = new Blob([content], { type: mimeType });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `data.${extension}`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
};

const minify = () => {
    if (parsedJson.value) {
        jsonInput.value = JSON.stringify(parsedJson.value);
    }
};

const loadSample = () => {
    jsonInput.value = JSON.stringify({
        "project": "Super Tool",
        "version": 1.0,
        "active": true,
        "contributors": [
            { "name": "Alice", "role": "Dev" },
            { "name": "Bob", "role": "Designer" }
        ],
        "settings": {
            "theme": "dark",
            "notifications": {
                "email": true,
                "sms": false
            }
        }
    }, null, 4);
};

</script>

<template>
    <Head title="JSON Tool" />
    <PortfolioLayout :fullWidth="true">
        <div class="p-4 md:p-8 md:pt-2 h-[calc(100vh-140px)] flex flex-col">
            <div class="mb-4 flex items-center gap-2">
                 <Link href="/tools" class="text-gray-500 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                 </Link>
                <h1 class="text-2xl font-bold dark:text-white">JSON Tool</h1>
            </div>

            <div class="flex-1 flex flex-col lg:flex-row gap-4 overflow-hidden">
                <!-- Input Column -->
                <div class="flex-1 flex flex-col gap-2 h-full min-h-[300px]">
                    <div class="flex justify-between items-center">
                        <label class="text-sm font-medium uppercase tracking-wider text-gray-500">Input</label>
                        <div class="flex gap-3">
                            <button 
                                @click="loadSample" 
                                class="text-xs text-gray-400 hover:text-black dark:hover:text-white hover:underline transition-colors"
                            >
                                Sample
                            </button>
                            <button 
                                v-if="jsonInput" 
                                @click="minify" 
                                class="text-xs text-gray-400 hover:text-black dark:hover:text-white hover:underline transition-colors"
                            >
                                Minify
                            </button>
                            <button 
                                v-if="jsonInput" 
                                @click="jsonInput = ''" 
                                class="text-xs text-red-500 hover:underline"
                            >
                                Clear
                            </button>
                        </div>
                    </div>
                    <textarea 
                        v-model="jsonInput"
                        class="flex-1 w-full p-4 font-mono text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg focus:outline-none focus:ring-2 focus:ring-black dark:focus:ring-white resize-none"
                        placeholder="Paste your JSON here..."
                    ></textarea>
                </div>

                <!-- Output Column -->
                <div class="flex-1 flex flex-col gap-2 h-full min-h-[300px]">
                    <div class="flex justify-between items-center border-b border-gray-200 dark:border-white/10">
                        <div class="flex gap-4">
                             <button 
                                @click="activeTab = 'visual'"
                                class="pb-2 text-sm font-medium uppercase tracking-wider transition-colors border-b-2"
                                :class="activeTab === 'visual' ? 'border-black dark:border-white text-black dark:text-white' : 'border-transparent text-gray-500 hover:text-black dark:hover:text-white'"
                            >
                                Visual
                            </button>
                            <button 
                                @click="activeTab = 'structured'"
                                class="pb-2 text-sm font-medium uppercase tracking-wider transition-colors border-b-2"
                                :class="activeTab === 'structured' ? 'border-black dark:border-white text-black dark:text-white' : 'border-transparent text-gray-500 hover:text-black dark:hover:text-white'"
                            >
                                Structured
                            </button>
                            <button 
                                @click="activeTab = 'convert'"
                                class="pb-2 text-sm font-medium uppercase tracking-wider transition-colors border-b-2"
                                :class="activeTab === 'convert' ? 'border-black dark:border-white text-black dark:text-white' : 'border-transparent text-gray-500 hover:text-black dark:hover:text-white'"
                            >
                                Convert
                            </button>
                        </div>
                        <div class="flex gap-4">
                            <button 
                                v-if="parsedJson"
                                @click="download" 
                                class="text-xs text-gray-500 hover:text-black dark:hover:text-white transition-colors"
                            >
                                Download
                            </button>
                            <button @click="copyToClipboard" class="text-xs text-gray-500 hover:text-black dark:hover:text-white transition-colors">
                                Copy {{ activeTab === 'convert' ? 'Result' : 'JSON' }}
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 relative bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-lg overflow-hidden flex flex-col">
                        <!-- Convert Format Selector -->
                        <div v-if="activeTab === 'convert'" class="p-2 border-b border-gray-200 dark:border-white/10 flex gap-2">
                             <button 
                                v-for="fmt in ['yaml', 'xml']" 
                                :key="fmt"
                                @click="convertFormat = fmt as any"
                                class="px-3 py-1 text-xs font-bold uppercase rounded-md transition-colors"
                                :class="convertFormat === fmt ? 'bg-black text-white dark:bg-white dark:text-black' : 'hover:bg-gray-200 dark:hover:bg-white/10'"
                            >
                                {{ fmt }}
                            </button>
                        </div>

                        <!-- Error Overlay -->
                        <div v-if="error" class="absolute inset-x-0 bottom-0 bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-2 text-xs font-mono border-t border-red-200 dark:border-red-900/50 z-10">
                            {{ error }}
                        </div>

                        <!-- Content -->
                        <div class="flex-1 overflow-auto p-4 custom-scrollbar relative">
                            <div v-if="!jsonInput" class="h-full flex items-center justify-center text-gray-400 italic">
                                Result will appear here...
                            </div>
                            
                            <template v-else>
                                <JsonTreeView v-if="activeTab === 'structured' && parsedJson" :data="parsedJson" />
                                <JsonVisualView v-else-if="activeTab === 'visual' && parsedJson" :data="parsedJson" />
                                <pre v-else-if="activeTab === 'convert'" class="font-mono text-sm leading-6 dark:text-[#EDEDEC] whitespace-pre-wrap">{{ convertedOutput }}</pre>
                                <div v-else class="text-red-500">Invalid JSON</div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>
