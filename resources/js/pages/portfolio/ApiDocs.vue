<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
</script>

<template>
    <SeoHead />
    
    <PortfolioLayout mainContainerClass="p-0 overflow-hidden">
        <div class="w-full h-full flex flex-col bg-white dark:bg-[#161615]">
            <iframe 
                src="/docs/api" 
                class="w-full h-full border-none"
                style="height: calc(100vh - 70px);"
            ></iframe>
        </div>
    </PortfolioLayout>
</template>

<style scoped>
/* Ensure the iframe container takes up available space */
iframe {
    background: transparent;
}
</style>

