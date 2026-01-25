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
            <div v-if="activeTab === 'portfolio'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="project in projects" :key="project.id" class="group flex flex-col">
                    <div class="aspect-video mb-4 overflow-hidden rounded-lg bg-gray-100 dark:bg-[#1C1C1E] border border-black/10 dark:border-white/10 relative">
                        <img 
                            v-if="project.image" 
                            :src="project.image" 
                            :alt="project.title" 
                            class="w-full h-full object-cover grayscale group-hover:grayscale-0 group-hover:scale-105 transition-all duration-500"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-700">
                             <!-- Placeholder icon -->
                             <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex gap-2">
                            <span v-for="tag in project.tags" :key="tag" class="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                                {{ tag }}
                            </span>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                            {{ project.title }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                            {{ project.description }}
                        </p>
                    </div>
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
