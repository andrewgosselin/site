<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { Game } from '@/data/games';

const page = usePage();
// @ts-ignore
const gamesList = (page.props.games_list as Game[]) || [];

defineProps<{
    excludeSlug?: string;
}>();
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link 
            v-for="game in gamesList.filter(g => g.slug !== excludeSlug)" 
            :key="game.slug"
            :href="game.link"
            class="group relative aspect-video rounded-xl overflow-hidden border border-black/10 dark:border-white/10 hover:shadow-xl transition-all"
        >
            <!-- Background Image -->
            <div :class="['absolute inset-0', game.bg]">
                <img 
                    :src="game.image" 
                    :alt="game.title" 
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                >
            </div>

            <!-- Gradient Overlay & Content -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent flex flex-col justify-end p-6">
                <div class="translate-y-[calc(100%-2rem)] group-hover:translate-y-0 transition-transform duration-300">
                    <h3 class="text-2xl font-bold text-white mb-2">
                        {{ game.title }}
                    </h3>
                    <p class="text-gray-200 text-sm leading-relaxed opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-100">
                        {{ game.description }}
                    </p>
                    
                    <div class="mt-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 delay-150">
                         <span class="inline-flex items-center text-xs font-bold uppercase tracking-widest text-white border-b-2 border-white pb-1">
                            Play Now
                         </span>
                    </div>
                </div>
            </div>
        </Link>
    </div>
</template>
