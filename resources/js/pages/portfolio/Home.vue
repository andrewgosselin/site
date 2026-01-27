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

const stackItems = [
    { 
        name: 'Laravel', 
        bg: 'hover:bg-red-500/10',
        color: '#FF2D20', 
        url: 'https://laravel.com',
        icon: '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M11.4 7.9c.8-.7 1.9-1.9 1.9-1.9.3-.3.1-.9-.3-1C9.6 4.3 8 3.5 5.2 3.1c-.2 0-.4.2-.4.4-.1 2.1-.2 4.1-.2 6.1 0 1.9.1 3.9.2 5.8 0 .1.1.2.2.2 2.8-.7 5.7-1.3 8.6-1.8.4-.1.6-.6.6-1 0-1.7-.1-3.4-.1-5 0-.2-.1-.5-.3-.6-.6-.4-1.6-1-2.4-1.3zM7.6 19.3c5.3 1.1 10.2 3.8 14.5 7.6.3.3.8.1.8-.4V9.8c0-.3-.2-.5-.4-.6-2.7 1.4-5.6 2.4-8.7 3-.4.1-.6.5-.6.9v3.7c0 .1-.2.2-.3.1-1.6-.8-3.3-1.4-5.1-1.8-.2-.1-.3 0-.3.2v3.8c0 .1.1.2.1.2z"/></svg>' 
    },
    { 
        name: 'Vue.js', 
        bg: 'hover:bg-emerald-500/10',
        color: '#42B883', 
        url: 'https://vuejs.org',
        icon: '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M24 1.615a1.6 1.6 0 0 0-1.615-1.615H1.615A1.615 1.615 0 0 0 0 1.615h24ZM12 21.015L3.486 6.319h3.692L12 14.652l4.822-8.333h3.692L12 21.015Zm0-4.665l-2.063-3.64-1.89-3.267-1.378-2.396h7.329L12 16.35Z"/></svg>' 
    },
    { 
        name: 'Next.js', 
        bg: 'hover:bg-gray-500/10',
        color: '#000000', 
        url: 'https://nextjs.org',
        icon: '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M24 12c0 6.627-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0s12 5.373 12 12zM8 2.5a9.5 9.5 0 0 0-3.6 17.2L16.2 3.1A9.5 9.5 0 0 0 8 2.5zm11.3 16.1L8.7 5.9A9.5 9.5 0 0 0 19.3 18.6z"/></svg>' 
    },
    { 
        name: 'React', 
        bg: 'hover:bg-blue-400/10',
        color: '#61DAFB', 
        url: 'https://react.dev',
        icon: '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 2.163c3.204 0 3.584.05 4.312.197 2.364.474 4.09 1.768 4.707 3.535.485 1.396.183 3.016-1.042 3.996-1.378 1.1-3.655 1.704-6.33 1.832 2.115-.49 4.322-1.258 5.768-2.614 1.554-1.458 1.96-3.754 1.4-5.462-.485-1.478-1.574-2.583-3.08-3.086-1.55-.518-3.32-.47-4.896-.03L12 2.162zm0 19.674c-3.204 0-3.584-.05-4.312-.197-2.364-.474-4.09-1.768-4.707-3.535-.485-1.396-.183-3.016 1.042-3.996 1.378-1.1 3.655-1.704 6.33-1.832-2.115.49-4.322 1.258-5.768 2.614-1.554 1.458-1.96 3.754-1.4 5.462.485 1.478 1.574 2.583 3.08 3.086 1.55.518 3.32.47 4.896.03l.836-1.632zM12 2.163c-3.204 0-3.584.05-4.312.197-2.364.474-4.09 1.768-4.707 3.535-.485 1.396-.183 3.016 1.042 3.996 1.378 1.1 3.655 1.704 6.33 1.832-2.115-.49-4.322-1.258-5.768-2.614-1.554-1.458-1.96-3.754-1.4-5.462.485-1.478-1.574-2.583-3.08-3.086-1.55-.518-3.32-.47-4.896-.03l.836-.838zM0 12c0 6.627 5.373 12 12 12s12-5.373 12-12S18.627 0 12 0 0 5.373 0 12zm12 2.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>' 
    },
    { 
        name: 'Tailwind', 
        bg: 'hover:bg-cyan-500/10',
        color: '#06B6D4', 
        url: 'https://tailwindcss.com',
        icon: '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12.001,4.8c-3.2,0-5.2,1.6-6,4.8c1.2-1.6,2.6-2.2,4.2-1.8c0.913,0.228,1.565,0.89,2.288,1.624 C13.666,10.618,15.027,12,18.001,12c3.2,0,5.2-1.6,6-4.8c-1.2,1.6-2.6,2.2-4.2,1.8c-0.913-0.228-1.565-0.89-2.288-1.624 C16.337,6.182,14.976,4.8,12.001,4.8z M6.001,12c-3.2,0-5.2,1.6-6,4.8c1.2-1.6,2.6-2.2,4.2-1.8c0.913,0.228,1.565,0.89,2.288,1.624 c1.177,1.194,2.538,2.576,5.512,2.576c3.2,0,5.2-1.6,6-4.8c-1.2,1.6-2.6,2.2-4.2,1.8c-0.913-0.228-1.565-0.89-2.288-1.624 C10.337,13.382,8.976,12,6.001,12z"/></svg>' 
    },
    { 
        name: 'TypeScript', 
        bg: 'hover:bg-blue-500/10',
        color: '#3178C6', 
        url: 'https://www.typescriptlang.org',
        icon: '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M1.125 0C.502 0 0 .502 0 1.125v21.75C0 23.498.502 24 1.125 24h21.75c.623 0 1.125-.502 1.125-1.125V1.125C24 .502 23.498 0 22.875 0H1.125zM11.523 9.452c1.7 0 2.502.836 2.502 2.51v3.253h-1.688v-2.923c0-.98-.242-1.424-1.04-1.424-.871 0-1.229.48-1.229 1.455v2.892H8.406V8.125h1.66v1.365c.421-.861 1.05-1.4 2.126-1.037.164.055.333-.001.333-.001zM18.846 13.9c0 1.456-1.134 2.306-2.458 2.306-1.5 0-2.316-.763-2.365-1.802h1.666c.036.386.41.6.877.6.536 0 .848-.22.848-.616 0-.348-.228-.553-1.04-.766-1.396-.347-2.09-.806-2.09-1.928 0-1.229.982-2.128 2.275-2.128 1.487 0 2.204.896 2.251 1.831h-1.65c-.06-.528-.42-.664-.783-.664-.47 0-.712.196-.712.553 0 .336.252.505.996.713 1.488.423 2.185.836 2.185 1.901z"/></svg>' 
    },
    { 
        name: 'MySQL', 
        bg: 'hover:bg-orange-500/10',
        color: '#4479A1', 
        url: 'https://www.mysql.com',
        icon: '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M12 12c.5 1.2-1.5 2.1-1.1 3.5 1 .3 2.5-.2 2.7 1.3.1.8-.4 1.4-.8 1.9-.3.3-.6.6-1 .8-1.6.7-3.7.8-5.3-.2-.5-.3-.9-.8-1.2-1.3-.1-.3-.2-.6-.2-1 0-.9 1.1-1.4 1.7-1.7 1.2-.5 2.5-.5 3.7.1 0 0 .1 0 .2-.1.1-.1.1-.3.2-.4.1-.3 0-.6-.1-.8-.3-.5-.9-.6-1.4-.4-.5.1-.9.4-1.3.7-.6.5-1.3 1-2.1 1.1-.9.1-1.7-.3-2.3-1-.5-.6-.7-1.3-.8-2.1 0-1.3.7-2.5 1.8-3.1 1.5-.9 3.4-.6 4.7.4.3.2.5.5.7.8.2.4.4.8.5 1.3zM16.9 16c0 .9-.7 1.6-1.6 1.6s-1.6-.7-1.6-1.6.7-1.6 1.6-1.6 1.6.7 1.6 1.6zm4.8 1.5c-.2 1.9-2.2 3.2-4.1 3.2-3.3 0-6.1-2.2-7.1-5.3-1-3.1.2-6.5 2.9-8.4 2.7-1.9 6.4-1.5 8.7.9.6.6 1 1.4 1.3 2.2.2.6.2 1.3.1 2-.1.6-.3 1.2-.6 1.7-.3.5-.7 1-1.2 1.4z"/></svg>' 
    },
    { 
        name: 'Redis', 
        bg: 'hover:bg-red-500/10',
        color: '#DC382D', 
        url: 'https://redis.io',
        icon: '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 5.86c-.51-.95-1.52-1.55-2.61-1.55H4.05c-1.09 0-2.1.6-2.61 1.55 1.53.51 3.5.95 5.87.95 3.39 0 6.06-.92 7.03-2.16.89 1.11 3.27 2.16 6.36 2.16 2.37 0 4.34-.44 5.86-.95ZM2.8 7.37c1.38 2.58 4.29 4.35 7.63 4.35 3.84 0 7.07-2.35 8.02-5.59-3.23.01-6.16 1.39-8.02 3.59-1.89-2.23-4.9-3.64-8.24-3.64-.53 0-1.05.04-1.56.09.05.41.11.81.18 1.2ZM19.5 20.3c.6-.68 1.1-1.45 1.48-2.29-.69.21-1.43.33-2.18.33-2.18 0-4.14-.88-5.58-2.31-1.43 1.43-3.39 2.31-5.58 2.31-.69 0-1.37-.09-2.02-.27.35.86.82 1.66 1.39 2.37 1.57 1.94 4.09 3.01 6.8 2.74 2.44.25 4.88-.73 6.38-2.67.11-.13.22-.27.31-.41Z"/></svg>' 
    }
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
            <div class="min-h-full flex flex-col lg:flex-row">
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
                    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-4">




                        <!-- CV (Row 1, Col 1) -->
                        <Link href="/resume"
                            class="md:col-span-1 bg-blue-600 text-white rounded-3xl p-8 flex flex-col items-center justify-center hover:bg-blue-500 transition-all gap-2 text-center group">
                             <i class="pi pi-file text-3xl group-hover:-translate-y-1 transition-transform"></i>
                            <span class="text-xs font-bold uppercase tracking-[0.4em]">CV</span>
                        </Link>

                        <!-- Linkedin (Row 1, Col 2) -->
                        <a href="https://www.linkedin.com/in/andrew-gosselin/" target="_blank"
                            class="md:col-span-1 bg-[#0077b5] text-white rounded-3xl p-8 flex flex-col items-center justify-center hover:bg-[#006399] transition-all gap-2 text-center group">
                            <i class="pi pi-linkedin text-3xl group-hover:-translate-y-1 transition-transform"></i>
                            <span class="text-xs font-bold uppercase tracking-[0.4em]">Linkedin</span>
                        </a>

                        <!-- Technologies (Top Right, spans 2 cols, 2 rows) -->
                        <div
                            class="md:col-span-2 md:row-span-2 bg-[#EDEDEC] dark:bg-[#1C1C1E] rounded-3xl p-8 border border-black/5 dark:border-white/5 flex flex-col">
                            <h3 class="text-xs font-bold uppercase tracking-[0.3em] text-gray-400 mb-6">Commonly Used</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-2 gap-2 text-xs font-bold tracking-widest uppercase flex-1 content-start">
                                <a v-for="t in stackItems" :key="t.name" :href="t.url" target="_blank"
                                    class="flex items-center gap-2 p-2 rounded-xl border border-black/5 dark:border-white/5 bg-white dark:bg-black/20 hover:scale-105 transition-transform cursor-pointer overflow-hidden"
                                    :class="t.bg"
                                >
                                    <div class="w-6 h-6 shrink-0 rounded-md flex items-center justify-center text-white" :style="{ backgroundColor: t.color }">
                                         <!-- eslint-disable-next-line vue/no-v-html -->
                                        <div v-html="t.icon" class="w-4 h-4 fill-current"></div>
                                    </div>
                                    <span class="text-[10px] sm:text-xs truncate min-w-0">{{ t.name }}</span>
                                </a>
                            </div>
                        </div>

                        <!-- Github (Row 2, Col 1) -->
                        <a href="https://github.com/andrewgosselin" target="_blank"
                            class="md:col-span-1 bg-[#181717] text-white rounded-3xl p-8 flex flex-col items-center justify-center hover:bg-[#333] transition-all gap-2 text-center group">
                            <i class="pi pi-github text-3xl group-hover:-translate-y-1 transition-transform"></i>
                            <span class="text-xs font-bold uppercase tracking-[0.4em]">Github</span>
                        </a>

                        <!-- Source (Row 2, Col 2) -->
                        <a href="https://github.com/andrewgosselin/site" target="_blank"
                            class="md:col-span-1 bg-[#2b3137] text-white rounded-3xl p-8 flex flex-col items-center justify-center hover:bg-[#24292e] transition-all gap-2 text-center group">
                            <i class="pi pi-code text-3xl group-hover:-translate-y-1 transition-transform"></i>
                            <span class="text-xs font-bold uppercase tracking-[0.4em]">Source</span>
                        </a>

                        <!-- All Projects Link (Bottom Left, 2 cols) -->
                        <Link href="/projects"
                            class="md:col-span-2 bg-black text-white dark:bg-white dark:text-black rounded-3xl p-8 flex flex-col justify-between group transition-all hover:translate-y-[-4px] relative overflow-hidden">
                            <div class="absolute right-[-20px] top-[-20px] opacity-10 rotate-12 transform scale-150 pointer-events-none">
                                <i class="pi pi-th-large text-[15rem]"></i>
                            </div>
                            <h3 class="text-4xl font-bold tracking-tighter relative z-10">VIEW ALL<br>PROJECTS</h3>
                            <div class="flex justify-end relative z-10">
                                <i class="pi pi-arrow-right text-4xl transform group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                            </div>
                        </Link>

                        <!-- Arcade (Row 3, 2 cols) -->
                        <Link href="/arcade"
                            class="md:col-span-2 bg-[#E5F55F] rounded-3xl p-8 border border-black text-black flex flex-col justify-between group hover:scale-[1.01] transition-all relative overflow-hidden">
                            <div class="absolute right-[-20px] bottom-[-40px] opacity-10 rotate-[-12deg] transform scale-150 pointer-events-none text-black">
                                 <i class="pi pi-bolt text-[15rem]"></i>
                            </div>
                            
                            <div
                                class="w-12 h-12 bg-black rounded-full flex items-center justify-center text-[#E5F55F] mb-4 relative z-10 transition-transform group-hover:rotate-12">
                                <i class="pi pi-bolt text-2xl"></i>
                            </div>
                            <div class="text-4xl font-bold tracking-tighter relative z-10">ARCADE</div>
                        </Link>

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
