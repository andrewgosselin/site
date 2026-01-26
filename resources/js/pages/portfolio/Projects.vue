<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import DinoGame from '@/components/portfolio/DinoGame.vue';
import { projects, ideas } from '@/data/projects';

const activeTab = ref<'portfolio' | 'lab'>('portfolio');
</script>

<template>
    <Head title="Projects" />

    <PortfolioLayout mainContainerClass="p-0">
        <!-- Header / Hero -->
        <div class="w-full h-[240px] bg-gray-100 dark:bg-[#1C1C1E] border-b border-black/10 dark:border-white/10 relative overflow-hidden group">
            <DinoGame />
            <!-- Dino Game Label -->
            <div class="absolute bottom-4 left-4 z-10 opacity-0 group-hover:opacity-100 transition-opacity">
                <span class="px-3 py-1 bg-black/50 backdrop-blur-md text-white text-[10px] uppercase tracking-widest font-bold border border-white/20">
                    Interactive Header: Play with Space / Click
                </span>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-y border-black dark:border-white/5 bg-white/50 dark:bg-black/20 backdrop-blur-sm sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-8 flex gap-8">
                <button 
                    @click="activeTab = 'portfolio'"
                    :class="[
                        'py-4 text-sm font-bold uppercase tracking-widest border-b-2 transition-all',
                        activeTab === 'portfolio' 
                            ? 'border-black dark:border-white text-black dark:text-white' 
                            : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-200'
                    ]"
                >
                    Portfolio
                </button>
                <!-- <button 
                    @click="activeTab = 'lab'"
                    :class="[
                        'py-4 text-sm font-bold uppercase tracking-widest border-b-2 transition-all',
                        activeTab === 'lab' 
                            ? 'border-black dark:border-white text-black dark:text-white' 
                            : 'border-transparent text-gray-400 hover:text-gray-600 dark:hover:text-gray-200'
                    ]"
                >
                    The Lab
                </button> -->
            </div>
        </div>

        <!-- Content Area -->
        <div class="max-w-7xl mx-auto p-8 min-h-[500px]">
            
            <!-- Portfolio Tab -->
            <div v-if="activeTab === 'portfolio'" class="flex flex-col items-center justify-center min-h-[400px]">
                <div class="border-2 border-dashed border-black/10 dark:border-white/10 rounded-3xl p-12 text-center max-w-lg w-full">
                    <div class="mb-6 inline-flex p-4 bg-gray-100 dark:bg-white/5 rounded-full">
                         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-gray-400">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m5.928 7.309l1.802 1.802" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold uppercase tracking-widest mb-2">Coming Soon</h2>
                    <p class="text-gray-500 text-sm">
                        Working on adding this list of projects, will be here soon.
                    </p>
                </div>
            </div>

            <!-- Lab Tab -->
            <div v-if="activeTab === 'lab'" class="max-w-4xl space-y-12">
                <div class="space-y-4">
                    <h2 class="text-2xl font-bold dark:text-white">Ideas & Experiments</h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        This is where I store concepts, half-finished prototypes, and "what if" scenarios. Some might become full projects, others just stay here as digital artifacts.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6">
                    <div v-for="idea in ideas" :key="idea.id" class="p-6 rounded-lg border border-black/5 dark:border-white/5 hover:border-black/20 dark:hover:border-white/20 transition-all bg-white/30 dark:bg-white/5 group">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    {{ idea.title }}
                                    <span class="px-2 py-0.5 text-[8px] bg-black/5 dark:bg-white/10 rounded uppercase tracking-tighter">Idea</span>
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl">
                                    {{ idea.description }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <span v-for="tag in idea.tags" :key="tag" class="px-2 py-1 text-[9px] font-bold uppercase tracking-widest border border-black/10 dark:border-white/10 text-gray-500">
                                    {{ tag }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </PortfolioLayout>
</template>
