<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
</script>

<template>
    <Head title="Resume" />

    <PortfolioLayout mainContainerClass="h-full p-0 overflow-hidden relative">
        <div class="h-full w-full relative bg-gray-100 dark:bg-gray-900">
            <!-- Floating Download Button -->
            <a href="/assets/branding/resume.pdf" download
               class="absolute bottom-8 right-8 z-50 inline-flex items-center gap-2 px-6 py-3 text-sm font-medium text-white transition-all transform bg-blue-600 rounded-full shadow-lg hover:bg-blue-700 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download PDF
            </a>

            <!-- Full Screen PDF Viewer -->
            <iframe 
                src="/assets/branding/resume.pdf" 
                class="w-full h-full border-0"
                title="Resume PDF"
            ></iframe>
        </div>
    </PortfolioLayout>
</template>
