<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import { Link } from '@inertiajs/vue3';

const tools = [
    {
        name: 'JSON Tool',
        description: 'Format, validate, and explore JSON data with a tree view.',
        href: '/tools/json',
        icon: 'pi pi-code',
        color: 'bg-emerald-500',
    },
    {
        name: 'Encoder / Decoder',
        description: 'Encode and decode URL, Base64, JWT, and HTML entities.',
        href: '/tools/encoder',
        icon: 'pi pi-arrow-right-arrow-left',
        color: 'bg-blue-500',
    },
    {
        name: 'Generator',
        description: 'Generate UUIDs, secure passwords, and cryptographic hashes.',
        href: '/tools/generator',
        icon: 'pi pi-key',
        color: 'bg-neutral-900 dark:bg-white dark:!text-black',
    },
    {
        name: 'SEO Checker',
        description: 'Preview how URLs appear in search results and social media.',
        href: '/tools/seo-checker',
        icon: 'pi pi-search',
        color: 'bg-purple-500',
    },
    {
        name: 'Image Tool',
        description: 'Convert, resize, crop, and transform images.',
        href: '/tools/image',
        icon: 'pi pi-image',
        color: 'bg-pink-500',
    },
    {
        name: 'PDF Tool',
        description: 'View PDFs and splice each page with global or per-page lines.',
        href: '/tools/pdf',
        icon: 'pi pi-file-pdf',
        color: 'bg-red-500',
    },
    {
        name: 'Spotify Downloader',
        description: 'Download your favorite playlists and tracks from Spotify.',
        href: '/tools/spotify',
        icon: 'pi pi-spotify', // Note: Need to make sure this icon class exists or use a generic one if not
        color: 'bg-green-500',
    },
    {
        name: 'IND Register',
        description: 'Search recognised sponsors for regular labour and highly skilled migrants.',
        href: '/tools/ind',
        icon: 'pi pi-building',
        color: 'bg-orange-500',
    },
];
</script>

<template>
    <SeoHead />
    <PortfolioLayout>
        <div class="mx-auto max-w-7xl p-4 md:p-8 md:pt-12">
            <div class="mb-12">
                <h1 class="mb-4 text-4xl font-black tracking-tighter md:text-5xl dark:text-white">DEVELOPER TOOLS</h1>
                <p class="max-w-2xl text-xl text-gray-600 dark:text-gray-400">A collection of utilities I built to help with my daily workflow.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="tool in tools"
                    :key="tool.name"
                    :href="tool.href"
                    class="group relative block overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 p-6 transition-all duration-300 select-none hover:border-black hover:shadow-lg dark:border-white/10 dark:bg-white/5 dark:hover:border-white"
                >
                    <div class="mb-4 flex items-start justify-between">
                        <div :class="[tool.color, 'flex h-12 w-12 items-center justify-center rounded-xl text-white shadow-lg']">
                            <i :class="[tool.icon, 'text-xl']"></i>
                        </div>
                        <div
                            class="rounded-full bg-white px-3 py-1 text-xs font-bold tracking-wider uppercase transition-colors group-hover:bg-black group-hover:text-white dark:bg-white/10 dark:text-white dark:group-hover:bg-white dark:group-hover:text-black"
                        >
                            Open
                        </div>
                    </div>

                    <h3 class="mb-2 text-xl font-bold transition-transform group-hover:translate-x-1 dark:text-white">
                        {{ tool.name }}
                    </h3>
                    <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                        {{ tool.description }}
                    </p>
                </Link>
            </div>
        </div>
    </PortfolioLayout>
</template>
