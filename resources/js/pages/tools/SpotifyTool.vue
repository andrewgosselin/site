<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const props = defineProps<{
    isConnected: boolean;
}>();

const pagePlaylists = ref<any[]>([]);
const addedPlaylists = ref<any[]>([]);
const playlists = computed(() => {
    const unique = new Map();
    [...addedPlaylists.value, ...pagePlaylists.value].forEach((p) => unique.set(p.id, p));
    return Array.from(unique.values());
});
const selectedPlaylist = ref<any>(null);
const tracks = ref<any[]>([]);
const isLoadingPlaylists = ref(false);
const isLoadingTracks = ref(false);
const showAddByUrl = ref(false);
const playlistUrl = ref('');

const login = () => {
    window.location.href = '/tools/spotify/auth/redirect';
};

const fetchPlaylists = async () => {
    if (!props.isConnected) return;

    isLoadingPlaylists.value = true;
    try {
        const response = await axios.get('/api/tools/spotify/playlists');
        pagePlaylists.value = response.data.items;
    } catch (e) {
        console.error('Failed to fetch playlists', e);
    } finally {
        isLoadingPlaylists.value = false;
    }
};

const downloadStatus = ref<any>(null);
let pollInterval: any = null;

const isProcessing = computed(() => downloadStatus.value?.status === 'processing');
const hasSuccesses = computed(() => tracks.value.some((t) => t.status === 'completed'));
const hasFailures = computed(() => tracks.value.some((t) => t.status === 'failed'));

const checkStatus = async () => {
    if (!selectedPlaylist.value) return;
    try {
        const response = await axios.get(`/api/tools/spotify/playlist/${selectedPlaylist.value.id}/status`);
        downloadStatus.value = response.data;

        if (response.data.status === 'completed' && response.data.playlist?.tracks) {
            tracks.value = response.data.playlist.tracks;
            clearInterval(pollInterval);
            pollInterval = null;
        } else if (response.data.status === 'processing' && response.data.playlist?.tracks) {
            // Update tracks to show live progress
            tracks.value = response.data.playlist.tracks;
        }
    } catch (e) {
        // Ignore 404
    }
};

const downloadPlaylist = async () => {
    if (!selectedPlaylist.value) return;

    try {
        const type = selectedPlaylist.value.type || 'playlist';
        const response = await axios.post(`/api/tools/spotify/playlist/${selectedPlaylist.value.id}/download?type=${type}`);
        downloadStatus.value = response.data;

        if (!pollInterval) {
            pollInterval = setInterval(checkStatus, 2000);
        }
    } catch (e) {
        console.error('Failed to start download', e);
        alert('Failed to start download');
    }
};

const selectPlaylist = async (playlist: any) => {
    selectedPlaylist.value = playlist;
    tracks.value = [];
    isLoadingTracks.value = true;
    downloadStatus.value = null;
    if (pollInterval) clearInterval(pollInterval);

    // Check if we have a local copy first or status
    try {
        const statusResponse = await axios.get(`/api/tools/spotify/playlist/${playlist.id}/status`);
        downloadStatus.value = statusResponse.data;

        if (statusResponse.data.status === 'completed' && statusResponse.data.playlist?.tracks) {
            tracks.value = statusResponse.data.playlist.tracks;
            isLoadingTracks.value = false;
            return;
        } else if (statusResponse.data.status === 'processing') {
            pollInterval = setInterval(checkStatus, 2000);
        }
    } catch (e) {
        // Not downloaded yet, proceed to fetch from Spotify API directly for preview
    }

    try {
        const type = playlist.type || 'playlist';
        const response = await axios.get(`/api/tools/spotify/playlists/${playlist.id}/tracks?type=${type}`);
        tracks.value = response.data.items;
    } catch (e) {
        console.error('Failed to fetch tracks', e);
    } finally {
        isLoadingTracks.value = false;
    }
};

const addByUrl = async () => {
    // Regex to extract type and ID
    // Supports:
    // https://open.spotify.com/playlist/37i9dQZF1DXcBWIGoYBM5M
    // https://open.spotify.com/album/1ATIiX12d80cbe1aF0066d
    const match = playlistUrl.value.match(/(playlist|album)\/([a-zA-Z0-9]+)/);

    if (!match) {
        alert('Invalid Spotify URL');
        return;
    }

    const type = match[1];
    const id = match[2];

    // Check if already in list
    const existing = playlists.value.find((p) => p.id === id);
    if (existing) {
        selectPlaylist(existing);
        showAddByUrl.value = false;
        playlistUrl.value = '';
        return;
    }

    try {
        const response = await axios.get(`/api/tools/spotify/playlist/${id}?type=${type}`);
        const playlist = response.data;
        // Ensure type is stored
        playlist.type = type;

        // Add to list and select
        const exists = addedPlaylists.value.find((p) => p.id === playlist.id);
        if (!exists) {
            addedPlaylists.value.unshift(playlist);
            localStorage.setItem('added_playlists', JSON.stringify(addedPlaylists.value));
        }

        selectPlaylist(playlist);

        showAddByUrl.value = false;
        playlistUrl.value = '';
    } catch (e: any) {
        console.error('Failed to add playlist', e);
        const errorMsg = e.response?.data?.error || e.message || 'Unknown error';
        const details = e.response?.data?.details ? JSON.stringify(e.response.data.details) : '';
        alert(`Failed to fetch ${type}: ${errorMsg} ${details}`);
    }
};

const exportPlaylist = () => {
    if (!selectedPlaylist.value) return;
    window.location.href = `/api/tools/spotify/playlist/${selectedPlaylist.value.id}/export`;
};

onMounted(() => {
    const stored = localStorage.getItem('added_playlists');
    if (stored) {
        try {
            addedPlaylists.value = JSON.parse(stored);
        } catch (e) {
            console.error('Failed to parse added playlists', e);
        }
    }

    if (props.isConnected) {
        fetchPlaylists();
    }
});
</script>

<template>
    <SeoHead />
    <PortfolioLayout :fullWidth="true">
        <div class="flex h-[calc(100vh-140px)] flex-col p-4 md:p-8 md:pt-2">
            <div class="mb-4 flex items-center gap-2">
                <Link href="/tools" class="text-gray-500 transition-colors hover:text-black dark:text-gray-400 dark:hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold dark:text-white">Spotify Downloader</h1>
            </div>

            <div class="flex flex-1 flex-col gap-4 overflow-hidden lg:flex-row">
                <!-- Left Column: Playlists -->
                <div
                    class="flex h-full min-h-[300px] w-full flex-col gap-2 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 lg:w-1/3 dark:border-white/10 dark:bg-white/5"
                >
                    <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-white/10">
                        <h2 class="text-lg font-bold dark:text-white">Playlists</h2>
                        <div class="flex items-center gap-2">
                            <a
                                v-if="!isConnected && playlists.length > 0"
                                href="/tools/spotify/login"
                                class="flex items-center gap-2 rounded-full bg-[#1DB954] px-3 py-1 text-xs font-bold text-white transition-opacity hover:opacity-80"
                            >
                                <i class="pi pi-spotify"></i>
                                Login
                            </a>
                            <button
                                @click="showAddByUrl = !showAddByUrl"
                                class="rounded-full bg-black px-3 py-1 text-xs text-white transition-opacity hover:opacity-80 dark:bg-white dark:text-black"
                            >
                                {{ showAddByUrl ? 'Cancel' : 'Add by URL' }}
                            </button>
                        </div>
                    </div>

                    <div v-if="showAddByUrl" class="border-b border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="flex gap-2">
                            <input
                                v-model="playlistUrl"
                                type="text"
                                placeholder="Spotify Playlist URL"
                                class="flex-1 rounded-md border border-gray-300 bg-transparent px-3 py-1 text-sm dark:border-white/20 dark:text-white"
                            />
                            <button
                                @click="addByUrl"
                                class="rounded-md bg-green-500 px-3 py-1 text-xs font-bold text-white uppercase transition-colors hover:bg-green-600"
                            >
                                Add
                            </button>
                        </div>
                    </div>

                    <div class="custom-scrollbar flex-1 overflow-y-auto p-2">
                        <!-- Loading State -->
                        <div v-if="isLoadingPlaylists" class="flex justify-center p-8">
                            <i class="pi pi-spin pi-spinner text-2xl text-gray-400"></i>
                        </div>

                        <!-- Empty State -->
                        <div
                            v-else-if="playlists.length === 0"
                            class="flex h-full flex-col items-center justify-center gap-8 p-8 text-center text-gray-500"
                        >
                            <!-- Add by URL (Empty State) -->
                            <div class="w-full max-w-xs">
                                <label class="mb-2 block text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                    Add by Playlist/Album URL
                                </label>
                                <div class="flex gap-2">
                                    <input
                                        v-model="playlistUrl"
                                        type="text"
                                        placeholder="https://open.spotify.com/playlist/... or /album/..."
                                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-blue-500 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"
                                        @keyup.enter="addByUrl"
                                    />
                                    <button
                                        @click="addByUrl"
                                        :disabled="!playlistUrl"
                                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-700 disabled:opacity-50"
                                    >
                                        Add
                                    </button>
                                </div>
                            </div>

                            <div class="w-full max-w-xs border-t border-gray-200 dark:border-white/10"></div>

                            <!-- Login (Empty State) -->
                            <div v-if="!isConnected" class="flex flex-col items-center gap-2">
                                <button
                                    @click="login"
                                    class="flex items-center gap-2 rounded-full bg-[#1DB954] px-6 py-2 font-bold text-white transition-transform hover:scale-105"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.38 4.2-1.32 11.28-1.08 15.42 1.44.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.299z"
                                        />
                                    </svg>
                                    Login with Spotify
                                </button>
                                <div class="mt-4 max-w-xs text-[10px] text-gray-500 dark:text-gray-400">
                                    <i class="pi pi-exclamation-triangle mr-1 text-yellow-500"></i>
                                    Login is currently restricted to internal testing accounts due to
                                    <a
                                        href="https://developer.spotify.com/blog/2026-02-06-update-on-developer-access-and-platform-security"
                                        target="_blank"
                                        class="underline hover:text-blue-500"
                                    >
                                        Spotify platform security updates.
                                    </a>
                                    Please use the URL input above.
                                </div>
                            </div>
                        </div>

                        <!-- List of Playlists -->
                        <div v-else class="flex flex-col gap-2">
                            <div
                                v-for="playlist in playlists"
                                :key="playlist.id"
                                @click="selectPlaylist(playlist)"
                                class="flex cursor-pointer items-center gap-3 rounded-lg p-2 transition-colors"
                                :class="
                                    selectedPlaylist?.id === playlist.id
                                        ? 'bg-black text-white dark:bg-white dark:text-black'
                                        : 'hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-white/10'
                                "
                            >
                                <img
                                    v-if="playlist.images?.[0]?.url"
                                    :src="playlist.images[0].url"
                                    class="h-12 w-12 rounded bg-gray-300 object-cover"
                                />
                                <div class="flex h-12 w-12 items-center justify-center rounded bg-gray-300 text-gray-500" v-else>
                                    <i class="pi pi-music"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-sm font-bold">{{ playlist.name }}</h3>
                                    <p class="truncate text-xs opacity-70">
                                        {{ playlist.tracks.total }} tracks • {{ playlist.owner?.display_name || playlist.type }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Tracks -->
                <div
                    class="flex h-full min-h-[300px] w-full flex-col gap-2 overflow-hidden rounded-lg border border-gray-200 bg-white lg:w-2/3 dark:border-white/10 dark:bg-black"
                >
                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="flex items-center gap-3" v-if="selectedPlaylist">
                            <img
                                v-if="selectedPlaylist.images?.[0]?.url"
                                :src="selectedPlaylist.images[0].url"
                                class="h-10 w-10 rounded object-cover"
                            />
                            <div>
                                <h2 class="text-lg leading-tight font-bold dark:text-white">{{ selectedPlaylist.name }}</h2>
                                <p class="text-xs text-gray-500">{{ selectedPlaylist.tracks.total }} tracks</p>
                            </div>
                        </div>
                        <h2 v-else class="text-lg font-bold dark:text-white">Tracks</h2>

                        <div class="flex gap-2">
                            <button
                                v-if="selectedPlaylist && !isProcessing && hasSuccesses"
                                @click="exportPlaylist"
                                class="flex items-center gap-2 rounded-full bg-green-600 px-4 py-2 text-xs font-bold tracking-wider text-white uppercase shadow-lg transition-colors hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <i class="pi pi-download"></i>
                                Save to Computer
                            </button>

                            <button
                                v-if="selectedPlaylist && !isProcessing && !hasFailures && hasSuccesses"
                                @click="downloadPlaylist"
                                class="flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-xs font-bold tracking-wider text-white uppercase shadow-lg transition-colors hover:bg-blue-700"
                            >
                                <i class="pi pi-refresh"></i>
                                Redownload
                            </button>

                            <button
                                v-if="selectedPlaylist && !isProcessing && hasFailures"
                                @click="downloadPlaylist"
                                class="flex items-center gap-2 rounded-full bg-red-600 px-4 py-2 text-xs font-bold tracking-wider text-white uppercase shadow-lg transition-colors hover:bg-red-700"
                            >
                                <i class="pi pi-refresh"></i>
                                Retry Download
                            </button>

                            <button
                                v-if="selectedPlaylist && !isProcessing && !hasSuccesses && !hasFailures"
                                @click="downloadPlaylist"
                                :disabled="downloadStatus && downloadStatus.status === 'processing'"
                                class="flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-xs font-bold tracking-wider text-white uppercase shadow-lg transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <i class="pi" :class="downloadStatus?.status === 'processing' ? 'pi-spin pi-spinner' : 'pi-cloud-download'"></i>
                                {{ downloadStatus?.status === 'processing' ? 'Downloading...' : 'Download' }}
                            </button>
                        </div>
                    </div>

                    <div class="custom-scrollbar flex-1 overflow-y-auto p-0">
                        <div v-if="!selectedPlaylist" class="flex h-full items-center justify-center text-gray-400 italic">
                            Select a playlist to view tracks...
                        </div>
                        <div v-else-if="isLoadingTracks" class="flex justify-center p-8">
                            <i class="pi pi-spin pi-spinner text-2xl text-gray-400"></i>
                        </div>
                        <div v-else class="flex flex-col">
                            <!-- Download/Progress Header -->
                            <div
                                v-if="downloadStatus && downloadStatus.status === 'processing'"
                                class="mb-2 border-b border-blue-100 bg-blue-50 p-3 dark:border-blue-900/20 dark:bg-blue-900/10"
                            >
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="text-xs font-bold tracking-wider text-blue-800 uppercase dark:text-blue-300">
                                        Downloading... {{ downloadStatus.processed_tracks }} / {{ downloadStatus.total_tracks }}
                                    </span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-blue-200 dark:bg-blue-900/40">
                                    <div
                                        class="h-full bg-blue-500 transition-all duration-300 ease-out"
                                        :style="{ width: `${(downloadStatus.processed_tracks / downloadStatus.total_tracks) * 100}%` }"
                                    ></div>
                                </div>
                            </div>

                            <div
                                v-if="downloadStatus && downloadStatus.status === 'failed'"
                                class="mb-2 border-b border-red-100 bg-red-50 p-3 dark:border-red-900/20 dark:bg-red-900/10"
                            >
                                <div class="flex items-center gap-2 text-xs font-bold tracking-wider text-red-800 uppercase dark:text-red-300">
                                    <i class="pi pi-exclamation-circle"></i>
                                    Download Failed
                                </div>
                            </div>

                            <div
                                v-for="(item, index) in tracks"
                                :key="item.track?.id || item.id"
                                class="group flex items-center gap-3 border-b border-gray-100 p-3 transition-colors hover:bg-gray-50 dark:border-white/5 dark:hover:bg-white/5"
                            >
                                <span class="w-6 text-center text-xs text-gray-400">{{ index + 1 }}</span>
                                <img
                                    v-if="item.track?.album?.images?.[0]?.url || item.image"
                                    :src="item.track?.album?.images?.[0]?.url || item.image"
                                    class="h-10 w-10 rounded object-cover"
                                />
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate text-sm font-bold dark:text-gray-200">{{ item.track?.name || item.name }}</h3>
                                    <p class="truncate text-xs text-gray-500">
                                        {{
                                            (item.track?.artists || item.artists || [])
                                                .map((a: any) => (typeof a === 'string' ? a : a.name))
                                                .join(', ')
                                        }}
                                        •
                                        {{ item.track?.album?.name || item.album }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i v-if="item.status === 'completed'" class="pi pi-check-circle text-green-500"></i>
                                    <i v-else-if="item.status === 'failed'" class="pi pi-times-circle text-red-500"></i>
                                    <i v-else-if="item.status === 'downloading'" class="pi pi-spin pi-spinner text-blue-500"></i>
                                    <i v-else class="pi pi-circle text-gray-200 dark:text-gray-700"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PortfolioLayout>
</template>

<style>
/* Indeterminate progress bar animation */
@keyframes progress-indeterminate {
    0% {
        transform: translateX(-100%);
        width: 100%;
    }
    50% {
        transform: translateX(0%);
        width: 100%;
    }
    100% {
        transform: translateX(100%);
        width: 100%;
    }
}
.animate-progress-indeterminate {
    animation: progress-indeterminate 1.5s infinite linear;
}
</style>
