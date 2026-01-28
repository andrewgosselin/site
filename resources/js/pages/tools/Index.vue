<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import SeoHead from '@/components/SeoHead.vue';

const tools = [
    {
        name: 'JSON Tool',
        description: 'Format, validate, and explore JSON data with a tree view.',
        href: '/tools/json',
        icon: 'pi pi-code',
        color: 'bg-emerald-500'
    },
    {
        name: 'Encoder / Decoder',
        description: 'Encode and decode URL, Base64, JWT, and HTML entities.',
        href: '/tools/encoder',
        icon: 'pi pi-arrow-right-arrow-left',
        color: 'bg-blue-500'
    },
    {
        name: 'Generator',
        description: 'Generate UUIDs, secure passwords, and cryptographic hashes.',
        href: '/tools/generator',
        icon: 'pi pi-key',
        color: 'bg-neutral-900 dark:bg-white dark:!text-black'
    },
    {
        name: 'SEO Checker',
        description: 'Preview how URLs appear in search results and social media.',
        href: '/tools/seo-checker',
        icon: 'pi pi-search',
        color: 'bg-purple-500'
    },
    {
        name: 'Image Tool',
        description: 'Convert, resize, crop, and transform images.',
        href: '/tools/image',
        icon: 'pi pi-image',
        color: 'bg-pink-500'
    },
    {
        name: 'IND Register',
        description: 'Search recognised sponsors for regular labour and highly skilled migrants.',
        href: '/tools/ind',
        icon: 'pi pi-building',
        color: 'bg-orange-500'
    }
];
</script>

<template>
    <SeoHead />
    <PortfolioLayout>
        <div class="p-4 md:p-8 md:pt-12 max-w-7xl mx-auto">
            <div class="mb-12">
                <h1 class="text-4xl md:text-5xl font-black tracking-tighter mb-4 dark:text-white">
                    DEVELOPER TOOLS
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl">
                    A collection of utilities I built to help with my daily workflow.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link 
                    v-for="tool in tools" 
                    :key="tool.name"
                    :href="tool.href"
                    class="block group relative overflow-hidden bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl p-6 transition-all duration-300 hover:border-black dark:hover:border-white hover:shadow-lg select-none"
                >
                    <div class="flex items-start justify-between mb-4">
                        <div :class="[tool.color, 'w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg']">
                            <i :class="[tool.icon, 'text-xl']"></i>
                        </div>
                        <div class="bg-white dark:bg-white/10 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider dark:text-white group-hover:bg-black group-hover:text-white dark:group-hover:bg-white dark:group-hover:text-black transition-colors">
                            Open
                        </div>
                    </div>
                    
                    <h3 class="text-xl font-bold mb-2 dark:text-white group-hover:translate-x-1 transition-transform">
                        {{ tool.name }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                        {{ tool.description }}
                    </p>
                </Link>
            </div>
        </div>
    </PortfolioLayout>
</template>
