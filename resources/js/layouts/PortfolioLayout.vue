<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, nextTick, computed } from 'vue';

const page = usePage();

const currentPageLabel = computed(() => {
    const navItems = page.props.nav_items as Record<string, string>;
    // Simple exact match logic
    const label = Object.keys(navItems).find(key => navItems[key] === page.url);
    
    // Fallback if no exact match (e.g. strict equality might fail with query params, but fine for now)
    return label || 'Portfolio';
});

const isDark = ref(false);
const isMobileMenuOpen = ref(false);

withDefaults(defineProps<{
    mainContainerClass?: string;
    fullWidth?: boolean;
}>(), {
    mainContainerClass: 'p-8',
    fullWidth: false
});

const toggleTheme = async () => {
    // Check if View Transitions API is supported
    if (!document.startViewTransition) {
        isDark.value = !isDark.value;
        updateTheme();
        return;
    }

    // If we are currently Dark, we are going to Light using Reverse animation
    if (isDark.value) {
        document.documentElement.classList.add('theme-reverse');
    }

    const transition = document.startViewTransition(async () => {
        isDark.value = !isDark.value;
        updateTheme();
    });

    try {
        await transition.finished;
    } finally {
        document.documentElement.classList.remove('theme-reverse');
    }
};

const updateTheme = () => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

// --- Branding Animation Logic ---

const firstRef = ref<HTMLElement | null>(null);
const lastRef = ref<HTMLElement | null>(null);
const subtitleRef = ref<HTMLElement | null>(null);
const titleRef = ref<HTMLElement | null>(null);

class TextScramble {
  el: HTMLElement;
  chars: string;
  queue: Array<{ from: string; to: string; start: number; end: number; char?: string }>;
  frame: number;
  frameRequest: number;
  resolve: (value?: void | PromiseLike<void>) => void;

  constructor(el: HTMLElement) {
    this.el = el;
    this.chars = '!<>-_\\/[]{}—=+*^?#________';
    this.update = this.update.bind(this);
    this.queue = [];
    this.frame = 0;
    this.frameRequest = 0;
    this.resolve = () => {};
  }

  setText(newText: string) {
    const oldText = this.el.innerText;
    const length = Math.max(oldText.length, newText.length);
    const promise = new Promise<void>((resolve) => (this.resolve = resolve));
    this.queue = [];
    for (let i = 0; i < length; i++) {
        const from = oldText[i] || '';
        const to = newText[i] || '';
        const start = Math.floor(Math.random() * 40);
        const end = start + Math.floor(Math.random() * 40);
        this.queue.push({ from, to, start, end });
    }
    cancelAnimationFrame(this.frameRequest);
    this.frame = 0;
    this.update();
    return promise;
  }

  update() {
    let output = '';
    let complete = 0;
    for (let i = 0, n = this.queue.length; i < n; i++) {
        let { from, to, start, end, char } = this.queue[i];
        if (this.frame >= end) {
            complete++;
            output += to;
        } else if (this.frame >= start) {
            if (!char || Math.random() < 0.28) {
                char = this.randomChar();
                this.queue[i].char = char;
            }
            output += `<span class="dud">${char}</span>`;
        } else {
            output += from;
        }
    }

    this.el.innerHTML = output;
    if (complete === this.queue.length) {
        this.resolve();
    } else {
        this.frameRequest = requestAnimationFrame(this.update);
        this.frame++;
    }
  }

  randomChar() {
    return this.chars[Math.floor(Math.random() * this.chars.length)];
  }
}

// Use window object to strictly ensure persistence across component unmounts/remounts
// This only resets on full page reload.
declare global {
    interface Window {
        hasPlayedBrandingAnimation?: boolean;
    }
}

onMounted(() => {
    // Theme Init
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        isDark.value = true;
        document.documentElement.classList.add('dark');
    } else {
        isDark.value = false;
        document.documentElement.classList.remove('dark');
    }

    // Branding Animation Sequence
    if (!window.hasPlayedBrandingAnimation) {
        // First Load (Refresh/Hard Nav): Run Animation
        window.hasPlayedBrandingAnimation = true;
        
        setTimeout(() => {
            if (firstRef.value) firstRef.value.style.marginLeft = '0';
            if (lastRef.value) lastRef.value.style.marginLeft = '0';
            
            setTimeout(() => {
                if (titleRef.value) titleRef.value.style.overflow = 'visible';
                if (subtitleRef.value) subtitleRef.value.style.opacity = '1';
            }, 1000);

            setTimeout(() => {
                if (firstRef.value) {
                    const fx = new TextScramble(firstRef.value);
                    fx.setText("ANDREW");
                }
                if (lastRef.value) {
                    const fx = new TextScramble(lastRef.value);
                    fx.setText("GOSSELIN");
                }
                if (subtitleRef.value) {
                    const fx = new TextScramble(subtitleRef.value);
                    fx.setText("FULL STACK DEVELOPER");
                }
            }, 600);
        }, 100); // Small delay to ensure render
    } else {
        // Subsequent Visits (SPA Nav): Show Instantly
        nextTick(() => {
            if (firstRef.value) {
                firstRef.value.style.transition = 'none';
                firstRef.value.style.marginLeft = '0';
                firstRef.value.innerHTML = "ANDREW";
            } 
            if (lastRef.value) {
                lastRef.value.style.transition = 'none';
                lastRef.value.style.marginLeft = '0';
                lastRef.value.innerHTML = "GOSSELIN";
            }
            if (titleRef.value) titleRef.value.style.overflow = 'visible';
            if (subtitleRef.value) {
                subtitleRef.value.style.transition = 'none';
                subtitleRef.value.style.opacity = '1';
                subtitleRef.value.innerHTML = "FULL STACK DEVELOPER";
            }
        });
    }
});
</script>

<template>
    <div class="main-container">
        <!-- Branding Element -->
        <div id="sidebar" :class="['hidden absolute left-0 top-0 z-10', fullWidth ? 'hidden' : 'lg:block']">
            <Link href="/" class="brandingContainer block cursor-pointer">
                <div class="logoContainer">
                    <img src="/assets/branding/logo.webp" alt="Andrew Gosselin Logo" class="object-contain w-full h-full">
                </div>
                <!-- Added Ref Binding here -->
                <div class="title" ref="titleRef">
                    <div class="subtitle1" ref="subtitleRef">$@!&(#(@&!#$$%*@!&*(!</div>
                    <div class="first" ref="firstRef">#*@)($*%%</div>
                    <div class="last" ref="lastRef">*%#*)!*@(%</div>
                </div>
            </Link>
        </div>

        <!-- Right Panel -->
        <div :class="['h-full w-full flex flex-col bg-white dark:bg-[#161615] relative', fullWidth ? 'w-full' : 'lg:w-[80%]']">
            
            <!-- Top Navigation -->
            <nav class="layout-navbar h-[70px] w-full border-b border-black dark:border-white/10 flex items-center px-4 md:px-8 shrink-0 dark:text-[#EDEDEC] bg-white dark:bg-[#161615]">
                <!-- Left: Current Page -->
                <div class="text-xl font-bold">
                    {{ currentPageLabel }}
                </div>

                <div class="flex-1"></div>

                <!-- Right: Navigation Links -->
                <div class="hidden lg:flex items-center h-full">
                    <Link 
                        v-for="(url, label) in $page.props.nav_items" 
                        :key="url"
                        :href="url" 
                        prefetch
                        class="px-6 h-full flex items-center transition-colors uppercase text-sm font-medium tracking-wide"
                        :class="[
                            currentPageLabel === label 
                                ? 'bg-gray-100 dark:bg-white/10 text-black dark:text-white font-bold box-border border-b-2 border-black dark:border-white' 
                                : 'hover:bg-gray-50 dark:hover:bg-white/5 text-gray-500 dark:text-gray-400 hover:text-black dark:hover:text-[#EDEDEC]'
                        ]"
                    >
                        {{ label }}
                    </Link>
                    
                    <!-- Icon Buttons with separators -->
                    <div class="flex h-full border-l border-black dark:border-white/20">
                        <button 
                            @click="toggleTheme"
                            class="w-[100px] h-full flex items-center justify-center hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            aria-label="Toggle Theme"
                        >
                            <!-- Moon Icon (Show when Light) -->
                            <svg v-if="!isDark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                            <!-- Sun Icon (Show when Dark) -->
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile: Theme + Hamburger -->
                <div class="flex lg:hidden items-center h-full">
                    <div class="flex h-full border-l border-black dark:border-white/20">
                        <button 
                            @click="toggleTheme"
                            class="w-[60px] h-full flex items-center justify-center hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            aria-label="Toggle Theme"
                        >
                            <!-- Moon Icon (Show when Light) -->
                            <svg v-if="!isDark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                            <!-- Sun Icon (Show when Dark) -->
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex h-full border-l border-black dark:border-white/20">
                        <button 
                            @click="isMobileMenuOpen = !isMobileMenuOpen"
                            class="w-[60px] h-full flex items-center justify-center hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                            aria-label="Menu"
                        >
                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <div class="layout-content flex-1 overflow-y-auto dark:text-[#EDEDEC] bg-white dark:bg-[#161615]">
                <div class="h-full flex flex-col">
                    <div :class="mainContainerClass" class="flex-1">
                        <slot />
                    </div>

                    <!-- Footer - Mobile Only (scrolls with content) -->
                    <div class="lg:hidden shrink-0 px-4 md:px-8 py-4 border-t border-black dark:border-white/10 flex flex-col md:flex-row justify-between items-center gap-4 text-[10px] font-bold uppercase tracking-widest text-gray-400 bg-white dark:bg-[#161615]">
                        <div class="flex items-center gap-4 flex-wrap justify-center md:justify-start">
                            <span>©2025 Andrew Gosselin</span>
                        </div>
                        <div class="flex gap-4">
                            <a href="#" class="hover:text-black dark:hover:text-white transition-colors" aria-label="GitHub">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                                </svg>
                            </a>
                            <a href="#" class="hover:text-black dark:hover:text-white transition-colors" aria-label="LinkedIn">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer - Desktop Only (fixed at bottom) -->
            <div class="hidden lg:flex shrink-0 px-4 md:px-8 py-4 border-t border-black dark:border-white/10 flex-row justify-between items-center gap-4 text-[10px] font-bold uppercase tracking-widest text-gray-600 bg-white dark:bg-[#161615]">
                <div class="flex items-center gap-4 flex-wrap justify-center md:justify-start">
                    <span>©2025 Andrew Gosselin</span>
                </div>
                <div class="flex gap-4">
                    <a target="_blank" href="https://github.com/andrewgosselin" class="hover:text-black dark:hover:text-white transition-colors" aria-label="GitHub">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </a>
                    <a target="_blank" href="https://www.linkedin.com/in/andrew-gosselin/" class="hover:text-black dark:hover:text-white transition-colors" aria-label="LinkedIn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Overlay -->
        <div 
            v-if="isMobileMenuOpen" 
            @click="isMobileMenuOpen = false"
            class="fixed inset-0 bg-black/50 z-50 lg:hidden"
        ></div>

        <!-- Mobile Menu Drawer -->
        <div 
            :class="[
                'fixed top-0 right-0 h-full w-[280px] bg-white dark:bg-[#161615] border-l border-black dark:border-white/10 z-50 transform transition-transform duration-300 lg:hidden',
                isMobileMenuOpen ? 'translate-x-0' : 'translate-x-full'
            ]"
        >
            <div class="flex flex-col h-full">
                <div class="h-[70px] border-b border-black dark:border-white/10 flex items-center justify-between px-6">
                    <span class="font-bold text-lg dark:text-[#EDEDEC]">Menu</span>
                    <button 
                        @click="isMobileMenuOpen = false"
                        class="p-2 hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors"
                        aria-label="Close menu"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 dark:text-[#EDEDEC]">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <nav class="flex-1 overflow-y-auto p-4">
                    <!-- Status Info for Mobile -->
                    <div class="mb-6 pb-6 border-b border-black/10 dark:border-white/10">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="text-xs font-bold tracking-[0.2em] uppercase dark:text-[#EDEDEC]">Open to work</span>
                        </div>
                        <div class="text-xs font-bold tracking-[0.2em] uppercase text-gray-400">The Hague, NL</div>
                    </div>

                    <Link 
                        v-for="(url, label) in $page.props.nav_items" 
                        :key="url"
                        :href="url" 
                        @click="isMobileMenuOpen = false"
                        prefetch
                        class="block px-4 py-3 mb-2 rounded-lg transition-colors uppercase text-sm font-medium tracking-wide"
                        :class="[
                            currentPageLabel === label 
                                ? 'bg-gray-100 dark:bg-white/10 text-black dark:text-white font-bold' 
                                : 'hover:bg-gray-50 dark:hover:bg-white/5 text-gray-500 dark:text-gray-400'
                        ]"
                    >
                        {{ label }}
                    </Link>
                </nav>
            </div>
        </div>
    </div>
</template>
