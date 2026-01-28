<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import { Link } from '@inertiajs/vue3';
import SeoHead from '@/components/SeoHead.vue';
import axios from 'axios';

interface Sponsor {
    name: string;
    kvk_number: string;
}

const sponsors = ref<Sponsor[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const search = ref('');
const currentPage = ref(1);
const itemsPerPage = 50;

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
    return sponsors.value.filter(s => 
        s.name.toLowerCase().includes(q) || 
        s.kvk_number.includes(q)
    );
});

const totalPages = computed(() => Math.ceil(filteredSponsors.value.length / itemsPerPage));

const paginatedSponsors = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredSponsors.value.slice(start, end);
});

// Reset to page 1 when search changes
watch(search, () => {
    currentPage.value = 1;
});

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

// Generate page numbers to show
const visiblePages = computed(() => {
    const pages: (number | string)[] = [];
    const total = totalPages.value;
    const current = currentPage.value;
    const delta = 2; // Number of pages to show around current

    if (total <= 1) return [];

    pages.push(1);
    
    if (current - delta > 2) {
        pages.push('...');
    }

    for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
        pages.push(i);
    }

    if (current + delta < total - 1) {
        pages.push('...');
    }

    if (total > 1) {
        pages.push(total);
    }

    return pages;
});
</script>

<template>
    <SeoHead 
        title="IND Public Register Search" 
        description="Search recognised sponsors in the Netherlands IND Public Register for regular labour and highly skilled migrants."
        keywords="IND Register, Recognised Sponsors, Netherlands Skilled Migrants, Dutch Visa Sponsors, IND Search Tool"
    />
    <PortfolioLayout :fullWidth="true">
        <div class="min-h-screen bg-white dark:bg-black text-black dark:text-white pb-20">
            <!-- Header -->
            <div class="bg-gray-50 dark:bg-zinc-900 border-b border-gray-200 dark:border-zinc-800 sticky top-0 z-20">
                <div class="max-w-7xl mx-auto px-4 md:px-8 py-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <Link href="/tools" class="flex items-center justify-center w-8 h-8 rounded-full bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 hover:border-blue-500 dark:hover:border-blue-500 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                                </svg>
                            </Link>
                            <div>
                                <h1 class="text-xl font-bold tracking-tight">IND Public Register</h1>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                    Search recognised sponsors for regular labour and highly skilled migrants.
                                    <span class="block mt-1 text-xs opacity-75">
                                        Data accessible via <Link href="/docs" class="underline hover:text-blue-500">API</Link>.
                                    </span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Search Bar -->
                        <div class="w-full md:w-96 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </div>
                            <input 
                                v-model="search"
                                type="text" 
                                placeholder="Search by name or KvK..." 
                                class="w-full pl-10 pr-4 py-2 bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all text-sm"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="max-w-7xl mx-auto px-4 md:px-8 py-8">
                <!-- Loading State -->
                <div v-if="loading" class="flex flex-col gap-4">
                    <div v-for="i in 10" :key="i" class="animate-pulse flex items-center gap-4 p-4 border border-gray-100 dark:border-zinc-800 rounded-lg bg-gray-50 dark:bg-zinc-900/50">
                        <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-1/3"></div>
                        <div class="h-4 bg-gray-200 dark:bg-zinc-800 rounded w-24"></div>
                    </div>
                </div>

                <!-- Error State -->
                <div v-else-if="error" class="flex flex-col items-center justify-center py-20 text-center">
                    <div class="text-red-500 mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 mx-auto">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                    <p class="font-medium text-lg">{{ error }}</p>
                    <button @click="fetchSponsors" class="mt-4 px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm font-medium hover:opacity-80 transition-opacity">
                        Try Again
                    </button>
                </div>

                <!-- Empty Search Results -->
                <div v-else-if="filteredSponsors.length === 0" class="flex flex-col items-center justify-center py-20 text-center text-gray-500 dark:text-gray-400">
                    <p class="text-lg">No sponsors found matching "{{ search }}"</p>
                </div>

                <!-- Data Table -->
                <div v-else>
                    <div class="space-y-4">
                        <!-- Desktop Table -->
                        <div class="hidden md:block overflow-hidden rounded-xl border border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 shadow-sm">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-zinc-800/50 border-b border-gray-200 dark:border-zinc-800">
                                        <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white w-2/3">Organisation</th>
                                        <th class="px-6 py-4 font-semibold text-gray-900 dark:text-white w-1/3 text-right tabular-nums">KvK Number</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-zinc-800">
                                    <tr 
                                        v-for="(sponsor, index) in paginatedSponsors" 
                                        :key="index"
                                        class="hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                                    >
                                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                            {{ sponsor.name }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400 text-right font-mono">
                                            {{ sponsor.kvk_number }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="md:hidden space-y-3">
                            <div 
                                v-for="(sponsor, index) in paginatedSponsors" 
                                :key="index"
                                class="bg-white dark:bg-zinc-900 rounded-lg p-4 border border-gray-200 dark:border-zinc-800 shadow-sm"
                            >
                                <div class="font-medium text-gray-900 dark:text-white text-base mb-2">
                                    {{ sponsor.name }}
                                </div>
                                <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                                    <span>KvK Number</span>
                                    <span class="font-mono bg-gray-50 dark:bg-zinc-800 px-2 py-1 rounded">
                                        {{ sponsor.kvk_number }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-4">
                        <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 text-center md:text-left order-2 md:order-1">
                            Showing <span class="font-medium text-black dark:text-white">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> to <span class="font-medium text-black dark:text-white">{{ Math.min(currentPage * itemsPerPage, filteredSponsors.length) }}</span> of <span class="font-medium text-black dark:text-white">{{ filteredSponsors.length }}</span> results
                        </p>
                        
                        <div class="flex items-center gap-1 order-1 md:order-2 w-full md:w-auto justify-center">
                            <button 
                                @click="goToPage(currentPage - 1)" 
                                :disabled="currentPage === 1"
                                class="flex-1 md:flex-none px-3 py-2 md:py-1 rounded-md text-sm font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors border md:border-0 border-gray-200 dark:border-zinc-800"
                            >
                                Previous
                            </button>
                            
                            <div class="hidden md:flex items-center gap-1">
                                <template v-for="(page, index) in visiblePages" :key="index">
                                    <span v-if="page === '...'" class="px-2 text-gray-400">...</span>
                                    <button 
                                        v-else 
                                        @click="goToPage(page as number)"
                                        class="min-w-8 h-8 flex items-center justify-center rounded-md text-sm font-medium transition-colors"
                                        :class="currentPage === page 
                                            ? 'bg-black dark:bg-white text-white dark:text-black' 
                                            : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-zinc-800'"
                                    >
                                        {{ page }}
                                    </button>
                                </template>
                            </div>

                            <!-- Mobile Page Indicator -->
                            <span class="md:hidden text-sm font-medium px-4">
                                {{ currentPage }} / {{ totalPages }}
                            </span>

                            <button 
                                @click="goToPage(currentPage + 1)" 
                                :disabled="currentPage === totalPages"
                                class="flex-1 md:flex-none px-3 py-2 md:py-1 rounded-md text-sm font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-zinc-800 disabled:opacity-50 disabled:cursor-not-allowed transition-colors border md:border-0 border-gray-200 dark:border-zinc-800"
                            >
                                Next
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>
