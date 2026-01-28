<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const seo = computed(() => page.props.seo as any);
</script>

<template>
    <Head :title="seo?.title">
        <meta name="description" :content="seo?.description" />
        <meta name="keywords" :content="seo?.keywords" />
        
        <!-- Canonical -->
        <link rel="canonical" :href="seo?.url" />

        <!-- Open Graph -->
        <meta property="og:type" content="website" />
        <meta property="og:url" :content="seo?.url" />
        <meta property="og:title" :content="seo?.full_title" />
        <meta property="og:description" :content="seo?.description" />
        <meta property="og:image" :content="seo?.image" />

        <!-- Twitter -->
        <meta name="twitter:card" :content="seo?.twitter_card" />
        <meta name="twitter:title" :content="seo?.full_title" />
        <meta name="twitter:description" :content="seo?.description" />
        <meta name="twitter:image" :content="seo?.image" />
    </Head>
</template>
