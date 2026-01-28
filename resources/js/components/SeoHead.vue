<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(defineProps<{
    title: string;
    description: string;
    keywords?: string;
    image?: string;
    canonicalUrl?: string; // Optional: Override automatic canonical
}>(), {
    keywords: 'Andrew Gosselin, Full Stack Developer, Laravel, Vue, PHP, Web Development, The Hague',
    image: 'https://gosselin.dev/assets/branding/banner.webp'
});

const page = usePage();

// Compute canonical URL automatically if not provided
const computedCanonical = computed(() => {
    if (props.canonicalUrl) return props.canonicalUrl;
    
    // Fallback: Construct from current path. 
    // Assumes production domain. Modify if needing dynamic domain from props/env.
    const baseUrl = 'https://gosselin.dev';
    const path = page.url; // e.g., '/tools/seo-checker'
    return `${baseUrl}${path}`;
});

const defaultTitle = "Andrew Gosselin | Full Stack Developer";

</script>

<template>
    <Head :title="title">
        <meta name="description" :content="description" />
        <meta name="keywords" :content="keywords" />
        
        <!-- Canonical -->
        <link rel="canonical" :href="computedCanonical" />

        <!-- Open Graph -->
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="computedCanonical" />
        <meta property="og:title" :content="title ? `${title} - Andrew Gosselin` : defaultTitle" />
        <meta property="og:description" :content="description" />
        <meta property="og:image" :content="image" />

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="title ? `${title} - Andrew Gosselin` : defaultTitle" />
        <meta name="twitter:description" :content="description" />
        <meta name="twitter:image" :content="image" />
    </Head>
</template>
