<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { defineAsyncComponent } from 'vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import GameGrid from '@/components/portfolio/GameGrid.vue';
import type { Game } from '@/data/games';

const props = defineProps<{
    game: Game;
}>();

// Async import components if they exist
const gameComponents: Record<string, any> = {
    'DinoGame': defineAsyncComponent(() => import('@/components/portfolio/DinoGame.vue')),
};

const CurrentGameComponent = props.game.component ? gameComponents[props.game.component] : null;
</script>

<template>
    <Head :title="game.title" />

    <PortfolioLayout mainContainerClass="p-0">
        <div class="flex flex-col min-h-screen">
            <!-- Game Playing Area -->
            <div class="w-full bg-white dark:bg-[#1C1C1E] border-b border-black/10 dark:border-white/10 overflow-hidden relative" style="height: 480px;">
                <component 
                    v-if="CurrentGameComponent" 
                    :is="CurrentGameComponent" 
                />
                <div v-else class="w-full h-full flex flex-col items-center justify-center space-y-4 p-8 text-center">
                    <div :class="['w-32 h-32 rounded-2xl flex items-center justify-center p-4', game.bg]">
                         <img :src="game.image" :alt="game.title" class="w-full h-full object-contain drop-shadow-lg opacity-50 grayscale">
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold dark:text-white">{{ game.title }}</h2>
                        <p class="text-gray-500 dark:text-gray-400">Coming soon to the arcade.</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="p-8 space-y-12">
                <div class="max-w-4xl">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ game.title }}</h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400">
                        {{ game.description }}
                    </p>
                </div>

                <!-- More Games Section -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">More Games</h2>
                    </div>
                    <GameGrid :excludeSlug="game.slug" />
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>
