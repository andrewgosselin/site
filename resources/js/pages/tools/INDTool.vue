<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useClipboard, useInfiniteScroll } from '@vueuse/core';
import axios from 'axios';
import { computed, onMounted, ref, watch } from 'vue';

interface Sponsor {
    name: string;
    kvk_number: string;
}

const sponsors = ref<Sponsor[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const search = ref('');
const { copy, copied } = useClipboard();
const copiedId = ref<string | null>(null);

// Infinite Scroll State
const scrollContainer = ref<HTMLElement | null>(null);
const visibleCount = ref(50);
const itemsPerLoad = 50;

const fetchSponsors = async () => {
    loading.value = true;
    error.value = null;
    try {
        const response = await axios.get('/api/tools/ind-register');
        sponsors.value = response.data;
    } catch (e) {
        error.value = 'Failed to load IND register data.';
        console.error(e);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchSponsors();
});

const filteredSponsors = computed(() => {
    if (!search.value) return sponsors.value;
    const q = search.value.toLowerCase();
    return sponsors.value.filter((s) => s.name.toLowerCase().includes(q) || s.kvk_number.includes(q));
});

const visibleSponsors = computed(() => {
    return filteredSponsors.value.slice(0, visibleCount.value);
});

// Reset visible count when search changes
watch(search, () => {
    visibleCount.value = itemsPerLoad;
    window.scrollTo({ top: 0, behavior: 'instant' });
});

const loadMore = () => {
    if (visibleCount.value < filteredSponsors.value.length) {
        visibleCount.value += itemsPerLoad;
    }
};

useInfiniteScroll(
    window,
    () => {
        loadMore();
    },
    { distance: 10 },
);

const handleCopy = (text: string, id: string) => {
    copy(text);
    copiedId.value = id;
    setTimeout(() => {
        copiedId.value = null;
    }, 2000);
};
</script>

<template>
    <SeoHead />
    <PortfolioLayout :fullWidth="true">
        <div class="min-h-screen bg-white pb-20 text-black dark:bg-black dark:text-white" ref="scrollContainer">
            <!-- Sticky Header & Search -->
            <div
                class="sticky top-0 z-30 border-b border-gray-200 bg-white/80 backdrop-blur-md transition-all duration-300 dark:border-zinc-800 dark:bg-black/80"
            >
                <div class="mx-auto max-w-7xl px-4 py-4 md:px-8">
                    <div class="flex flex-col gap-4">
                        <!-- Top Bar -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Link
                                    href="/tools"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition-colors hover:bg-gray-200 dark:bg-zinc-800 dark:text-gray-400 dark:hover:bg-zinc-700"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        class="h-4 w-4"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                    </svg>
                                </Link>
                                <div>
                                    <h1 class="text-lg font-bold tracking-tight md:text-xl">IND Register</h1>
                                </div>
                            </div>
                            <div class="hidden text-xs text-gray-500 md:block dark:text-gray-400">
                                Search recognised sponsors for regular labour and highly skilled migrants.
                            </div>
                        </div>

                        <!-- Search Field -->
                        <div class="group relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="h-5 w-5 text-gray-400 transition-colors group-focus-within:text-blue-500"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                                    />
                                </svg>
                            </div>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search organisation or KvK..."
                                class="w-full rounded-xl border-none bg-gray-100 py-3 pr-4 pl-10 text-base placeholder-gray-500 transition-all focus:ring-2 focus:ring-blue-500/50 md:py-2.5 md:text-sm dark:bg-zinc-900 dark:placeholder-zinc-500"
                            />
                            <div v-if="search" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <button
                                    @click="search = ''"
                                    class="rounded-full p-1 text-gray-400 transition-colors hover:bg-gray-200 dark:hover:bg-zinc-800"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        class="h-4 w-4"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="mx-auto max-w-7xl px-2 py-4 md:px-8 md:py-6">
                <!-- Loading State -->
                <div v-if="loading" class="space-y-4">
                    <div
                        v-for="i in 8"
                        :key="i"
                        class="animate-pulse rounded-xl border border-gray-100 bg-gray-50 p-4 md:p-6 dark:border-zinc-800/50 dark:bg-zinc-900/50"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="w-full space-y-3">
                                <div class="h-5 w-3/4 rounded bg-gray-200 dark:bg-zinc-800"></div>
                                <div class="h-4 w-1/3 rounded bg-gray-200 dark:bg-zinc-800"></div>
                            </div>
                            <div class="h-8 w-8 rounded-lg bg-gray-200 dark:bg-zinc-800"></div>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="mb-4 rounded-full bg-red-50 p-4 text-red-500 dark:bg-red-900/10">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-8 w-8"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
                            />
                        </svg>
                    </div>
                    <p class="mb-2 text-lg font-medium text-gray-900 dark:text-white">{{ error }}</p>
                    <button
                        @click="fetchSponsors"
                        class="rounded-lg bg-black px-6 py-2.5 text-sm font-medium text-white transition-opacity hover:opacity-80 dark:bg-white dark:text-black"
                    >
                        Try Again
                    </button>
                </div>

                <!-- Empty Search Results -->
                <div v-else-if="filteredSponsors.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="mb-4 rounded-full bg-gray-50 p-6 dark:bg-zinc-900">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke-width="1.5"
                            stroke="currentColor"
                            class="h-8 w-8 text-gray-400"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"
                            />
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-900 dark:text-white">No sponsors found</p>
                    <p class="mt-1 text-gray-500 dark:text-gray-400">We couldn't find anything matching "{{ search }}"</p>
                    <button @click="search = ''" class="mt-6 font-medium text-blue-500 hover:underline">Clear search</button>
                </div>

                <!-- Data List -->
                <div v-else>
                    <div class="mb-4 flex items-center justify-between px-1">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Found <span class="font-medium text-black dark:text-white">{{ filteredSponsors.length }}</span> results
                        </p>
                    </div>

                    <!-- Infinite Scroll Grid -->
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2 md:gap-4 lg:grid-cols-3">
                        <div
                            v-for="(sponsor, index) in visibleSponsors"
                            :key="index"
                            class="group relative rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition-all hover:border-blue-500/30 hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-blue-500/30"
                        >
                            <div class="flex justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base leading-snug font-semibold break-words text-gray-900 dark:text-gray-100">
                                        {{ sponsor.name }}
                                    </h3>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-500">KvK</span>
                                        <span
                                            class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-sm text-gray-600 dark:bg-zinc-800 dark:text-gray-300"
                                        >
                                            {{ sponsor.kvk_number }}
                                        </span>
                                    </div>
                                </div>
                                <button
                                    @click.stop="handleCopy(sponsor.kvk_number, `${index}`)"
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-50 text-gray-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:bg-zinc-800 dark:text-gray-400 dark:hover:bg-blue-900/20 dark:hover:text-blue-400"
                                    :title="copiedId === `${index}` ? 'Copied!' : 'Copy KvK'"
                                >
                                    <svg
                                        v-if="copiedId === `${index}`"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="2"
                                        stroke="currentColor"
                                        class="h-5 w-5 text-green-500"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                    <svg
                                        v-else
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="h-5 w-5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.386H9.375"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Loading Indicator for Infinite Scroll -->
                    <div v-if="visibleSponsors.length < filteredSponsors.length" class="mt-8 flex justify-center py-4">
                        <div
                            class="h-8 w-8 animate-spin rounded-full border-2 border-gray-300 border-t-blue-500 dark:border-zinc-700 dark:border-t-white"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>
