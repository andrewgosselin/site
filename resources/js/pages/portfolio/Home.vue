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

defineProps<{
    readme: string;
}>();
</script>

<template>

    <Head title="Home" />

    <PortfolioLayout mainContainerClass="p-0 h-full flex flex-col">
        <!-- Header -->
        <div
            class="shrink-0 hidden md:flex border-b border-black dark:border-white/10 px-8 py-6 flex-row items-center justify-between gap-4 bg-white dark:bg-[#161615]">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="text-xs font-bold tracking-[0.2em] uppercase">Open to work</span>
                </div>
                <div class="h-4 w-[1px] bg-black/10 dark:bg-white/10"></div>
                <div class="text-xs font-bold tracking-[0.2em] uppercase text-gray-400">The Hague, NL</div>
            </div>
            <div class="text-xs font-mono font-bold">{{ time }}</div>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto">
            <div class="h-full flex flex-col lg:flex-row">
                <!-- Left Column: README -->
                <div
                    class="lg:w-[400px] xl:w-[500px] lg:border-r border-black dark:border-white/10 bg-white dark:bg-[#161615]">
                    <div class="p-8">
                        <h2 class="text-xs font-bold uppercase tracking-[0.3em] text-gray-400 mb-6">About Me</h2>
                        <div v-html="readme" class="prose prose-sm dark:prose-invert max-w-none
                                prose-headings:font-bold prose-headings:tracking-tight
                                prose-h1:text-2xl prose-h2:text-xl prose-h3:text-lg
                                prose-p:text-sm prose-p:leading-relaxed
                                prose-a:text-blue-600 dark:prose-a:text-blue-400 prose-a:no-underline hover:prose-a:underline
                                prose-ul:text-sm prose-ol:text-sm
                                prose-li:my-1
                                prose-code:text-xs prose-code:bg-gray-100 dark:prose-code:bg-gray-800 prose-code:px-1 prose-code:py-0.5 prose-code:rounded
                                prose-pre:bg-gray-100 dark:prose-pre:bg-gray-800 prose-pre:text-xs"></div>
                    </div>
                </div>

                <!-- Right Column: Bento Grid -->
                <div class="flex-1 p-4 md:p-8 bg-[#F5F5F3] dark:bg-[#101010]">
                    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">

                        <!-- Hero -->
                        <!-- <div class="md:col-span-4 lg:col-span-4 bg-white dark:bg-[#161615] rounded-3xl border border-black dark:border-white/10 p-10 flex flex-col justify-between hover:shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] dark:hover:shadow-[8px_8px_0px_0px_rgba(255,255,255,0.1)] transition-all">
                        <div class="space-y-4">
                            <div class="w-12 h-1 bg-black dark:bg-white"></div>
                        </div>
                    </div> -->


                        <!-- All Projects Link -->
                        <Link href="/projects"
                            class="md:col-span-2 lg:col-span-3 bg-black text-white dark:bg-white dark:text-black rounded-3xl p-8 flex flex-col justify-between group transition-all hover:translate-y-[-4px]">
                            <h3 class="text-4xl font-bold tracking-tighter">VIEW ALL<br>PROJECTS</h3>
                            <div class="flex justify-end">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2.5" stroke="currentColor"
                                    class="w-10 h-10 transform group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25" />
                                </svg>
                            </div>
                        </Link>

                        <!-- Technologies -->
                        <div
                            class="md:col-span-2 lg:col-span-3 bg-[#EDEDEC] dark:bg-[#1C1C1E] rounded-3xl p-8 border border-black/5 dark:border-white/5">
                            <h3 class="text-xs font-bold uppercase tracking-[0.3em] text-gray-400 mb-6">Stack</h3>
                            <div class="flex flex-wrap gap-2 text-xs font-bold tracking-widest uppercase">
                                <span
                                    v-for="t in ['Laravel', 'Vue.js', 'Next.js', 'Tailwind', 'TypeScript', 'MySQL', 'Redis']"
                                    :key="t"
                                    class="p-3 bg-white dark:bg-black/20 border border-black/5 dark:border-white/5 rounded-xl">
                                    {{ t }}
                                </span>
                            </div>
                        </div>

                        <!-- Arcade & Bio -->
                        <Link href="/arcade"
                            class="md:col-span-2 lg:col-span-3 bg-[#E5F55F] rounded-3xl p-8 border border-black text-black flex flex-col justify-between group hover:scale-[1.01] transition-all">
                            <div
                                class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-[#E5F55F] mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 2.25c-3.354 0-6.458 1.104-8.967 2.977l-1.637 1.25L3.93 10l3.053 3.053 4.253-1.637z" />
                                </svg>
                            </div>
                            <div class="text-4xl font-bold tracking-tighter">ARCADE</div>
                        </Link>

                        <!-- Resume -->
                        <Link href="/resume"
                            class="md:col-span-1 lg:col-span-2 bg-blue-600 text-white rounded-3xl p-8 flex items-center justify-center hover:bg-blue-500 transition-all">
                            <span class="text-xs font-bold uppercase tracking-[0.4em] rotate-90 md:rotate-0">CV</span>
                        </Link>

                        <!-- Source Code -->
                        <a href="https://github.com/andrewgosselin/site" target="_blank"
                            class="md:col-span-1 lg:col-span-1 bg-white dark:bg-[#161615] border border-black dark:border-white/10 rounded-3xl p-8 flex items-center justify-center group hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-all">
                            <div class="text-center">
                                <div class="text-xs font-bold uppercase tracking-widest whitespace-nowrap">Source Code
                                </div>
                            </div>
                        </a>

                    </div>
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
    background: rgba(0, 0, 0, 0.1);
    border-radius: 10px;
}

.dark ::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
}

/* Markdown/README specific styles */
:deep(.prose) {

    /* Hide GitHub-only content */
    .github-only {
        display: none !important;
    }

    /* Center-aligned paragraphs */
    p[align="center"] {
        text-align: center;
        margin: 0.75rem 0;
    }

    /* Profile image styling */
    p[align="center"] img[width="175"] {
        border-radius: 50%;
        border: 3px solid currentColor;
        padding: 4px;
        transition: all 0.3s ease;
        margin: 1rem auto;
        display: block;
    }

    p[align="center"] img[width="175"]:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    }

    /* Badge images */
    p[align="center"] img[src*="shields.io"],
    p[align="center"] img[src*="hits.sh"] {
        display: inline-block;
        margin: 0.25rem;
        transition: transform 0.2s ease;
    }

    p[align="center"] img[src*="shields.io"]:hover {
        transform: translateY(-2px);
    }

    /* Horizontal rules */
    hr {
        border: none;
        border-top: 1px solid currentColor;
        opacity: 0.1;
        margin: 1.25rem 0;
    }

    /* Headings with centered paragraphs */
    h1 p[align="center"],
    h2 p[align="center"] {
        margin: 0.5rem 0;
        font-weight: bold;
    }

    /* Italic text */
    i {
        opacity: 0.8;
        font-style: italic;
    }

    /* Links in badges section */
    p[align="center"] a {
        display: inline-block;
        text-decoration: none;
    }

    /* First h1 - name */
    h1:first-of-type {
        margin-top: 0;
        margin-bottom: 0.75rem;
    }

    /* Emoji spacing in headings */
    h2 {
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
    }
}
</style>
