<script setup lang="ts">
import { ref, computed } from 'vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { v4 as uuidv4 } from 'uuid';

const mode = ref<'uuid' | 'password' | 'hash'>('uuid');
const output = ref('');

// UUID Options
const uuidCount = ref(1);

// Password Options
const passwordLength = ref(16);
const useUppercase = ref(true);
const useLowercase = ref(true);
const useNumbers = ref(true);
const useSymbols = ref(true);

// Hash Options
const hashBytes = ref(32);
const hashFormat = ref<'hex' | 'base64'>('hex');

const generate = () => {
    switch (mode.value) {
        case 'uuid':
            const uuids = Array.from({ length: uuidCount.value }, () => uuidv4());
            output.value = uuids.join('\n');
            break;
        case 'password':
            const charset = [
                useUppercase.value ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '',
                useLowercase.value ? 'abcdefghijklmnopqrstuvwxyz' : '',
                useNumbers.value ? '0123456789' : '',
                useSymbols.value ? '!@#$%^&*()_+~`|}{[]:;?><,./-=' : ''
            ].join('');
            
            if (!charset) {
                output.value = 'Please select at least one character set.';
                return;
            }

            let password = '';
            const array = new Uint32Array(passwordLength.value);
            crypto.getRandomValues(array);
            for (let i = 0; i < passwordLength.value; i++) {
                password += charset[array[i] % charset.length];
            }
            output.value = password;
            break;
        case 'hash':
            const bytes = new Uint8Array(hashBytes.value);
            crypto.getRandomValues(bytes);
            if (hashFormat.value === 'hex') {
                output.value = Array.from(bytes).map(b => b.toString(16).padStart(2, '0')).join('');
            } else {
                let binary = '';
                const len = bytes.byteLength;
                for (let i = 0; i < len; i++) {
                    binary += String.fromCharCode(bytes[i]);
                }
                output.value = btoa(binary);
            }
            break;
    }
};

const copy = async () => {
    if (output.value) {
        await navigator.clipboard.writeText(output.value);
    }
};

// Initial generation
generate();
</script>

<template>
    <Head title="Generator Tool" />
    <PortfolioLayout :fullWidth="true">
        <div class="p-4 md:p-8 md:pt-2 h-[calc(100vh-140px)] flex flex-col">
            <div class="mb-4 flex items-center gap-2">
                 <Link href="/tools" class="text-gray-500 hover:text-black dark:text-gray-400 dark:hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                 </Link>
                <h1 class="text-2xl font-bold dark:text-white">Generator</h1>
            </div>

            <div class="flex-1 flex flex-col lg:flex-row gap-4 overflow-hidden">
                <!-- Controls Column -->
                <div class="w-full lg:w-80 flex-shrink-0 flex flex-col gap-4 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6 overflow-y-auto custom-scrollbar">
                    
                    <!-- Mode Selector -->
                    <div class="flex flex-col gap-2">
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Type</label>
                        <div class="grid grid-cols-1 gap-2">
                            <button 
                                v-for="m in ['uuid', 'password', 'hash']" 
                                :key="m"
                                @click="mode = m as any; generate()"
                                class="px-4 py-3 text-sm font-bold text-left rounded-xl transition-all border-2"
                                :class="mode === m ? 'border-black bg-black text-white dark:border-white dark:bg-white dark:text-black' : 'border-transparent bg-white dark:bg-black/20 dark:text-gray-300 hover:border-gray-200 dark:hover:border-white/20'"
                            >
                                {{ m === 'uuid' ? 'UUID / GUID' : m === 'password' ? 'Password' : 'Secrets & Hashes' }}
                            </button>
                        </div>
                    </div>

                    <hr class="border-gray-200 dark:border-white/10 my-1" />

                    <!-- UUID Options -->
                    <div v-if="mode === 'uuid'" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Quantity: {{ uuidCount }}</label>
                            <input type="range" v-model.number="uuidCount" min="1" max="50" class="w-full accent-black dark:accent-white" @input="generate" />
                        </div>
                    </div>

                    <!-- Password Options -->
                    <div v-if="mode === 'password'" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                             <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Length: {{ passwordLength }}</label>
                            <input type="range" v-model.number="passwordLength" min="8" max="64" class="w-full accent-black dark:accent-white" @input="generate" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="useUppercase" @change="generate" class="rounded border-gray-300 text-black focus:ring-black dark:border-white/20 dark:bg-white/5" />
                                <span class="text-sm dark:text-gray-300">Uppercase (A-Z)</span>
                            </label>
                             <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="useLowercase" @change="generate" class="rounded border-gray-300 text-black focus:ring-black dark:border-white/20 dark:bg-white/5" />
                                <span class="text-sm dark:text-gray-300">Lowercase (a-z)</span>
                            </label>
                             <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="useNumbers" @change="generate" class="rounded border-gray-300 text-black focus:ring-black dark:border-white/20 dark:bg-white/5" />
                                <span class="text-sm dark:text-gray-300">Numbers (0-9)</span>
                            </label>
                             <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="useSymbols" @change="generate" class="rounded border-gray-300 text-black focus:ring-black dark:border-white/20 dark:bg-white/5" />
                                <span class="text-sm dark:text-gray-300">Symbols (!@#$)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Hash Options -->
                    <div v-if="mode === 'hash'" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                             <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Bytes: {{ hashBytes }}</label>
                            <input type="range" v-model.number="hashBytes" min="8" max="64" step="8" class="w-full accent-black dark:accent-white" @input="generate" />
                        </div>
                         <div class="flex flex-col gap-2">
                             <label class="text-xs font-bold uppercase tracking-wider text-gray-400">Format</label>
                            <div class="flex gap-2">
                                <button 
                                    v-for="f in ['hex', 'base64']" 
                                    :key="f"
                                    @click="hashFormat = f as any; generate()"
                                    class="flex-1 py-1 text-xs font-bold uppercase rounded-md border"
                                    :class="hashFormat === f ? 'bg-black text-white border-black dark:bg-white dark:text-black dark:border-white' : 'border-gray-200 dark:border-white/20 text-gray-500 hover:border-black dark:hover:border-white'"
                                >
                                    {{ f }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <button @click="generate" class="mt-auto py-3 bg-black text-white dark:bg-white dark:text-black rounded-xl font-bold hover:opacity-90 transition-opacity">
                        Regenerate
                    </button>
                </div>

                <!-- Output Column -->
                <div class="flex-1 flex flex-col gap-2 h-full min-h-[300px]">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-sm font-medium uppercase tracking-wider text-gray-500">Result</label>
                        <button 
                            @click="copy" 
                            class="text-xs text-gray-500 hover:text-black dark:hover:text-white transition-colors"
                        >
                            Copy
                        </button>
                    </div>

                    <div class="flex-1 relative bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl overflow-hidden p-6 custom-scrollbar overflow-auto">
                        <pre class="font-mono text-xl md:text-2xl leading-relaxed whitespace-pre-wrap break-all dark:text-[#EDEDEC]">{{ output }}</pre>
                    </div>
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>
