<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, defineAsyncComponent, computed } from 'vue';
import PortfolioLayout from '@/layouts/PortfolioLayout.vue';
import gsap from 'gsap';

const page = usePage();
const games = ref<any[]>(page.props.games_list as any[]);

const isInserting = ref(false);
const monitorOn = ref(false);
const activeGame = ref<any | null>(null);

// Async component map
const gameComponents: Record<string, any> = {
    'DinoGame': defineAsyncComponent(() => import('@/components/portfolio/DinoGame.vue')),
};

const CurrentGameComponent = computed(() => {
    return activeGame.value?.component ? gameComponents[activeGame.value.component] : null;
});

const insertDisk = (index: number, event: MouseEvent) => {
    if (isInserting.value || activeGame.value) return;
    
    isInserting.value = true;
    const disk = event.currentTarget as HTMLElement;
    const slot = document.querySelector('.crt-slot-internal') as HTMLElement;
    
    if (!disk || !slot) return;

    // Get positions relative to the viewport
    const diskRect = disk.getBoundingClientRect();
    const slotRect = slot.getBoundingClientRect();
    
    // Calculate translation
    const deltaX = slotRect.left + (slotRect.width / 2) - (diskRect.left + (diskRect.width / 2));
    const deltaY = slotRect.top + (slotRect.height / 2) - (diskRect.top + (diskRect.height / 2));

    const tl = gsap.timeline({
        onComplete: () => {
            monitorOn.value = true;
            activeGame.value = games.value[index];
            isInserting.value = false;
        }
    });

    // Disable interactions
    disk.style.pointerEvents = 'none';

    tl.to(disk, {
        y: -30,
        scale: 1.05,
        duration: 0.2,
        ease: "power2.out"
    })
    .to(disk, {
        x: deltaX,
        y: deltaY,
        rotation: 0,
        scale: 0.4,
        duration: 0.5,
        ease: "power2.inOut"
    })
    .to(disk, {
        y: deltaY + 20, // Move "into" the slot
        opacity: 0,
        scale: 0.3,
        duration: 0.2,
        ease: "power2.in"
    });
};

const ejectDisk = () => {
    monitorOn.value = false;
    activeGame.value = null;
};

onMounted(() => {
    gsap.from('.disk-card', {
        y: 50,
        opacity: 0,
        stagger: 0.1,
        duration: 0.6,
        ease: "power3.out"
    });
});
</script>

<template>
    <Head title="Arcade" />

    <PortfolioLayout mainContainerClass="p-0 overflow-hidden h-full flex flex-col bg-[#F5F5F3] dark:bg-[#101010]">
        <div class="flex-1 overflow-y-auto overflow-x-hidden flex flex-col items-center py-12 px-6">
            
            <!-- Minimal CRT System -->
            <div class="w-full max-w-2xl px-4 lg:px-0">
                <div class="relative bg-[#E5E5E5] border border-black/5 dark:border-white/10 rounded-2xl md:rounded-3xl aspect-[4/3] flex flex-col p-2 md:p-4 shadow-2xl transition-all duration-500 text-black">
                    
                    <!-- Screen Area -->
                    <div class="flex-1 bg-black rounded-lg md:rounded-xl relative overflow-hidden flex flex-col items-center justify-center border-[6px] md:border-[12px] border-[#D1D1D1] group">
                        <!-- Scanline Effect -->
                        <div class="absolute inset-0 pointer-events-none z-10 scanlines opacity-20"></div>
                        <div class="absolute inset-0 pointer-events-none z-10 rounded-[20px] md:rounded-[30px] border-[15px] border-black opacity-40 blur-sm"></div>

                        <!-- Content -->
                        <div v-if="!monitorOn" class="text-center z-0">
                            <div class="text-[#E5F55F] font-mono text-xs md:text-sm animate-pulse tracking-[0.3em] uppercase mb-2">
                                System Ready
                            </div>
                            <div class="text-white/30 font-mono text-[9px] uppercase tracking-widest">
                                Insert Game Disk to Initialize
                            </div>
                        </div>

                        <!-- Game Display -->
                        <div v-else class="w-full h-full relative z-30">
                            <component 
                                v-if="CurrentGameComponent" 
                                :is="CurrentGameComponent" 
                                class="w-full h-full"
                                :showMoreLink="false"
                            />
                            
                            <!-- Static/Fallback if game is loading or no component -->
                            <div v-else class="absolute inset-0 bg-[#E5F55F] flex flex-col items-center justify-center p-8 z-20 animate-flicker">
                                <div class="text-black font-black text-4xl md:text-6xl tracking-tighter uppercase leading-none italic">
                                    Loading...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Controls / Slot -->
                    <div class="h-16 md:h-24 mt-4 flex items-center justify-between px-4 md:px-8 border-t border-black/5 dark:border-white/5">
                        <div class="flex items-center gap-4">
                            <div class="w-2 h-2 rounded-full" :class="monitorOn ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></div>
                            
                            <!-- Eject Button -->
                            <button 
                                v-if="activeGame"
                                @click="ejectDisk"
                                class="px-3 py-1 bg-black text-white dark:bg-white dark:text-black text-[10px] font-bold uppercase tracking-widest rounded hover:scale-105 active:scale-95 transition-all"
                            >
                                Eject
                            </button>
                        </div>
                        
                        <!-- THE SLOT -->
                        <div class="crt-slot-internal relative w-40 md:w-64 h-3 md:h-4 bg-black rounded-full overflow-hidden">
                            <div class="absolute inset-x-2 top-0.5 h-0.5 bg-white/20 rounded-full"></div>
                        </div>

                        <div class="hidden sm:flex flex-col items-end opacity-20 dark:opacity-40">
                            <div class="text-[8px] font-bold uppercase tracking-widest">{{ activeGame ? activeGame.title : 'Model AG-01' }}</div>
                            <div class="text-[8px] font-bold uppercase tracking-widest leading-none">High Fidelity</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Disk Library -->
            <div v-if="!activeGame" class="mt-16 w-full max-w-4xl animate-in fade-in slide-in-from-bottom-4 duration-700">
                <div class="flex flex-col items-center mb-8">
                    <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-gray-400">Disk Library</h2>
                    <div class="w-12 h-1 bg-black/10 dark:bg-white/10 mt-2"></div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 md:gap-8 justify-items-center">
                    <div 
                        v-for="(game, index) in games" 
                        :key="game.slug"
                        class="disk-card relative group cursor-pointer"
                        @click="insertDisk(index, $event)"
                    >
                        <!-- Physical Disk Mockup -->
                        <div class="w-28 h-28 md:w-36 md:h-36 bg-[#221F1E] dark:bg-[#2C2C2B] rounded-lg shadow-xl transform transition-all duration-300 group-hover:-translate-y-2 group-hover:rotate-1 group-active:scale-95 flex flex-col p-2">
                            <!-- Disk Label -->
                            <div class="bg-white rounded-sm flex-1 flex flex-col p-2 relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-8 h-8 bg-gray-100 rotate-45 translate-x-4 -translate-y-4"></div>
                                <div class="text-[8px] md:text-[10px] font-black uppercase tracking-tighter leading-none mb-1 text-black">
                                    {{ game.title }}
                                </div>
                                <div class="text-[6px] md:text-[8px] text-gray-400 font-bold tracking-widest uppercase">
                                    Format: 3.5"
                                </div>
                                <div class="mt-auto flex justify-between items-end">
                                    <div class="flex gap-0.5">
                                        <div v-for="i in 3" :key="i" class="w-3 h-1 bg-black/10"></div>
                                    </div>
                                    <div class="text-[8px] font-mono text-gray-300">#{{ index + 1 }}</div>
                                </div>
                            </div>
                            <!-- Metal Cover (Shutter) -->
                            <div class="h-10 md:h-14 mt-2 bg-[#8B8B8B] rounded-sm relative px-2 py-3">
                                <div class="w-full h-2 md:h-4 bg-black/20 rounded-full"></div>
                                <div class="absolute left-1/2 -translate-x-1/2 top-1 w-1 h-1 bg-black/10 rounded-full"></div>
                            </div>
                        </div>
                        
                        <!-- Dust shadow -->
                        <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-[80%] h-4 bg-black/10 blur-md rounded-full -z-10 group-hover:bg-black/20 transition-all"></div>
                    </div>
                </div>
            </div>

            <!-- Mobile Hint -->
            <div class="md:hidden mt-8 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                Tap a disk to play
            </div>

        </div>
    </PortfolioLayout>
</template>

<style scoped>
.scanlines {
    background: linear-gradient(
        to bottom,
        rgba(18, 16, 16, 0) 50%,
        rgba(0, 0, 0, 0.25) 50%
    );
    background-size: 100% 4px;
}

@keyframes flicker {
    0% { opacity: 0; }
    5% { opacity: 1; }
    10% { opacity: 0; }
    15% { opacity: 1; }
    20% { opacity: 0.2; }
    25% { opacity: 0.8; }
    30% { opacity: 0; }
    100% { opacity: 1; }
}

.animate-flicker {
    animation: flicker 0.6s step-end forwards;
}

::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: transparent;
}
::-webkit-scrollbar-thumb {
    background: rgba(0,0,0,0.1);
    border-radius: 10px;
}
.dark ::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
}
</style>
