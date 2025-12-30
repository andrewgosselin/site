<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';

const time = ref('');

const updateTime = () => {
    const now = new Date();
    time.value = now.toLocaleTimeString('en-US', { 
        hour12: false, 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit',
        timeZone: 'Europe/Amsterdam'
    });
};

onMounted(() => {
    updateTime();
    setInterval(updateTime, 1000);
});

const showcase = [
    { name: 'Portfolio', tech: 'Laravel / Vue', link: '/projects' },
    { name: 'Test project', tech: 'Next.js', link: '/projects' },
    { name: 'Test project', tech: 'PHP / JS', link: '/projects' },
];
</script>

<template>
    <Head title="Home" />

    <PortfolioLayout mainContainerClass="p-0 overflow-hidden">
        <div class="h-full flex flex-col bg-[#F5F5F3] dark:bg-[#101010] selection:bg-black selection:text-white transition-colors duration-500">
            
            <!-- Header -->
            <div class="border-b border-black dark:border-white/10 px-8 py-6 flex flex-row items-center justify-between gap-4 bg-white dark:bg-[#161615]">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-bold tracking-[0.2em] uppercase">Open to work</span>
                    </div>
                    <div class="h-4 w-[1px] bg-black/10 dark:bg-white/10"></div>
                    <div class="text-xs font-bold tracking-[0.2em] uppercase text-gray-400">The Hague, NL</div>
                </div>
                <div class="text-xs font-mono font-bold">{{ time }}</div>
            </div>

            <!-- Bento Grid -->
            <div class="flex-1 p-4 md:p-8 overflow-y-auto">
                <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    
                    <!-- Hero -->
                    <!-- <div class="md:col-span-4 lg:col-span-4 bg-white dark:bg-[#161615] rounded-3xl border border-black dark:border-white/10 p-10 flex flex-col justify-between hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] dark:hover:shadow-[8px_8px_0px_0px_rgba(255,255,255,0.1)] transition-all">
                        <div class="space-y-4">
                            <div class="w-12 h-1 bg-black dark:bg-white"></div>
                        </div>
                    </div> -->

                    <!-- Showcase Projects -->
                    <div class="md:col-span-2 lg:col-span-2 bg-white dark:bg-[#161615] rounded-3xl border border-black dark:border-white/10 p-8 flex flex-col">
                        <h3 class="text-xs font-bold uppercase tracking-[0.3em] text-gray-400 mb-6 underline decoration-black/10 dark:decoration-white/10 underline-offset-8">Showcase</h3>
                        <div class="space-y-4 flex-1">
                            <Link v-for="item in showcase" :key="item.name" :href="item.link" class="block p-4 rounded-2xl border border-transparent hover:border-black/10 dark:hover:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-bold">{{ item.name }}</span>
                                    <span class="text-[10px] font-bold uppercase py-1 px-2 bg-black text-white dark:bg-white dark:text-black rounded">{{ item.tech }}</span>
                                </div>
                            </Link>
                        </div>
                    </div>

                    <!-- All Projects Link -->
                    <Link href="/projects" class="md:col-span-2 bg-black text-white dark:bg-white dark:text-black rounded-3xl p-8 flex flex-col justify-between group transition-all hover:translate-y-[-4px]">
                        <h3 class="text-4xl font-bold tracking-tighter">VIEW ALL<br>PROJECTS</h3>
                        <div class="flex justify-end">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-10 h-10 transform group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                            </svg>
                        </div>
                    </Link>

                    <!-- Technologies -->
                    <div class="md:col-span-2 bg-[#EDEDEC] dark:bg-[#1C1C1E] rounded-3xl p-8 border border-black/5 dark:border-white/5">
                        <h3 class="text-xs font-bold uppercase tracking-[0.3em] text-gray-400 mb-6">Stack</h3>
                        <div class="flex flex-wrap gap-2 text-xs font-bold tracking-widest uppercase">
                            <span v-for="t in ['Laravel', 'Vue.js', 'Next.js', 'Tailwind', 'TypeScript', 'MySQL', 'Redis']" :key="t" class="p-3 bg-white dark:bg-black/20 border border-black/5 dark:border-white/5 rounded-xl">
                                {{ t }}
                            </span>
                        </div>
                    </div>

                    <!-- Arcade & Bio -->
                    <Link href="/arcade" class="md:col-span-2 bg-[#E5F55F] rounded-3xl p-8 border border-black text-black flex flex-col justify-between group hover:scale-[1.01] transition-all">
                        <div class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-[#E5F55F] mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 2.25c-3.354 0-6.458 1.104-8.967 2.977l-1.637 1.25L3.93 10l3.053 3.053 4.253-1.637z" />
                            </svg>
                        </div>
                        <div class="text-4xl font-bold tracking-tighter">ARCADE</div>
                    </Link>

                    <!-- Resume -->
                    <Link href="/resume" class="md:col-span-1 bg-blue-600 text-white rounded-3xl p-8 flex items-center justify-center hover:bg-blue-500 transition-all">
                        <span class="text-xs font-bold uppercase tracking-[0.4em] rotate-90 md:rotate-0">CV</span>
                    </Link>

                    <!-- Documentation -->
                    <Link href="/docs" class="md:col-span-1 bg-white dark:bg-[#161615] border border-black dark:border-white/10 rounded-3xl p-8 flex items-center justify-center group hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-all">
                        <div class="text-center">
                            <div class="text-xs font-bold uppercase tracking-widest whitespace-nowrap">API Docs</div>
                        </div>
                    </Link>

                </div>
            </div>

            <!-- Footer -->
            <div class="px-8 py-4 border-t border-black dark:border-white/10 flex flex-col md:flex-row justify-between items-center gap-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                <div class="flex items-center gap-4">
                    <span>©2025 Andrew Gosselin</span>
                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                    <span>High Fidelity Craft</span>
                </div>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-black dark:hover:text-white transition-colors">GitHub</a>
                    <a href="#" class="hover:text-black dark:hover:text-white transition-colors">LinkedIn</a>
                </div>
            </div>

        </div>
    </PortfolioLayout>
</template>

<style scoped>
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.1);
    border-radius: 10px;
}
.dark ::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
}
</style>
