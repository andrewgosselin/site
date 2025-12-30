<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
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

withDefaults(defineProps<{
    mainContainerClass?: string;
}>(), {
    mainContainerClass: 'p-8'
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
        <div id="sidebar" class="hidden lg:block absolute left-0 top-0 z-10">
            <div class="brandingContainer">
                <div class="logoContainer">
                    <img src="/assets/branding/logo.png" alt="Logo">
                </div>
                <!-- Added Ref Binding here -->
                <div class="title" ref="titleRef">
                    <div class="subtitle1" ref="subtitleRef">$@!&(#(@&!#$$%*@!&*(!</div>
                    <div class="first" ref="firstRef">#*@)($*%%</div>
                    <div class="last" ref="lastRef">*%#*)!*@(%</div>
                </div>
            </div>
        </div>

        <!-- Right Panel -->
        <div class="h-full w-[90%] lg:w-[80%] flex flex-col bg-white dark:bg-[#161615] relative">
            
            <!-- Top Navigation -->
            <nav class="layout-navbar h-[70px] w-full border-b border-black dark:border-white/10 flex items-center px-8 shrink-0 dark:text-[#EDEDEC] bg-white dark:bg-[#161615]">
                <!-- Left: Current Page -->
                <div class="text-xl font-bold">
                    {{ currentPageLabel }}
                </div>

                <div class="flex-1"></div>

                <!-- Right: Navigation Links -->
                <div class="flex items-center h-full">
                    <Link 
                        v-for="(url, label) in $page.props.nav_items" 
                        :key="url"
                        :href="url" 
                        class="px-6 h-full flex items-center hover:bg-gray-50 dark:hover:bg-white/5 transition-colors uppercase text-sm font-medium tracking-wide"
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
                     <div class="flex h-full border-l border-black dark:border-white/20">
                        <button class="w-[100px] h-full flex items-center justify-center hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    </div>
                </div>
            </nav>

            <!-- Main Content Area -->
            <div class="layout-content flex-1 overflow-y-auto dark:text-[#EDEDEC] bg-white dark:bg-[#161615]" :class="mainContainerClass">
                <slot />
            </div>
        </div>
    </div>
</template>
