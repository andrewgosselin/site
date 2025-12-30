<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = withDefaults(defineProps<{
    showMoreLink?: boolean;
}>(), {
    showMoreLink: true
});

const canvasRef = ref<HTMLCanvasElement | null>(null);
const containerRef = ref<HTMLElement | null>(null);

// Assets
const assets: Record<string, HTMLImageElement> = {
    dino1: new Image(), // Shocked
    dino2: new Image(), // Blinking
    dino3: new Image(), // Normal
    dino4: new Image(), // Run 1
    dino5: new Image(), // Run 2
    cactus1: new Image(),
    cactus2: new Image(),
    cactus3: new Image(),
    cactus4: new Image(),
    cactus5: new Image(),
    cactus6: new Image(),
    bird: new Image()
};

// Game Constants
const GRAVITY = 0.6;
const JUMP_FORCE = -10;
const SPEED = 6;
const SPAWN_RATE = 90; // Frames

// State
type GameState = 'LOADING' | 'IDLE' | 'INTRO_JUMP' | 'INTRO_SHOCK' | 'PLAYING' | 'GAME_OVER';
const currentState = ref<GameState>('LOADING');
const score = ref(0);

// Logic Vars
let gameLoopId: number;
let frameCount = 0;
let introTimer = 0;

let dino = {
    x: 50,
    y: 0,
    w: 44, // approx width of sprite
    h: 47, // approx height of sprite
    dy: 0,
    grounded: false,
    sprite: 'dino3' // Key of assets
}; 

// Obstacles with dynamic dimensions
let obstacles: Array<{ x: number; y: number; w: number; h: number; type: string; origW?: number; origH?: number }> = [];

onMounted(() => {
    loadAssets().then(() => {
        currentState.value = 'IDLE';
        window.addEventListener('resize', handleResize);
        handleResize();
        // Start render loop even in IDLE to show static dino
        loop();
    });
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    cancelAnimationFrame(gameLoopId);
});

const loadAssets = async () => {
    const load = (img: HTMLImageElement, src: string) => {
        return new Promise<void>((resolve) => {
            img.onload = () => resolve();
            img.src = src;
        });
    };
    
    await Promise.all([
        load(assets.dino1, '/assets/dino/dino1.png'),
        load(assets.dino2, '/assets/dino/dino2.png'),
        load(assets.dino3, '/assets/dino/dino3.png'),
        load(assets.dino4, '/assets/dino/dino4.png'),
        load(assets.dino5, '/assets/dino/dino5.png'),
        load(assets.cactus1, '/assets/dino/cactus1.png'),
        load(assets.cactus2, '/assets/dino/cactus2.png'),
        load(assets.cactus3, '/assets/dino/cactus3.png'),
        load(assets.cactus4, '/assets/dino/cactus4.png'),
        load(assets.cactus5, '/assets/dino/cactus5.png'),
        load(assets.cactus6, '/assets/dino/cactus6.png'),
        load(assets.bird, '/assets/dino/bird.png'),
    ]);
};

const handleResize = () => {
    if (!canvasRef.value || !containerRef.value) return;
    const parent = containerRef.value;
    canvasRef.value.width = parent.clientWidth;
    canvasRef.value.height = parent.clientHeight;
    
    // Reset Ground Level
    if (dino.grounded || currentState.value === 'IDLE') {
        dino.y = canvasRef.value.height - dino.h - 10;
    }
    
    draw();
};

const startIntro = () => {
    if (currentState.value !== 'IDLE' && currentState.value !== 'GAME_OVER') return;
    
    currentState.value = 'INTRO_JUMP';
    score.value = 0;
    obstacles = [];
    
    // Immediate Jump
    dino.dy = JUMP_FORCE;
    dino.grounded = false;
    dino.sprite = 'dino3'; // Normal jump
};

const update = () => {
    if (!canvasRef.value) return;
    const h = canvasRef.value.height;

    // --- Physics (Always apply gravity if not grounded) ---
    dino.dy += GRAVITY;
    dino.y += dino.dy;

    // Ground Collision
    if (dino.y + dino.h > h - 10) {
        dino.y = h - 10 - dino.h;
        dino.dy = 0;
        
        if (!dino.grounded) {
             // Just landed
             dino.grounded = true;
             
             // State Transitions based on landing
             if (currentState.value === 'INTRO_JUMP') {
                 currentState.value = 'INTRO_SHOCK';
                 introTimer = 0; // Reset timer for shock phase
                 dino.sprite = 'dino1'; // Shocked
             }
        }
    }

    // --- State Logic ---
    frameCount++;

    if (currentState.value === 'IDLE') {
        dino.sprite = 'dino3'; // Normal
        // Keep dino grounded in idle
        if (dino.grounded) dino.dy = 0; 
    }
    else if (currentState.value === 'INTRO_JUMP') {
        dino.sprite = 'dino3'; // Jumping normal
    }
    else if (currentState.value === 'INTRO_SHOCK') {
        introTimer++;
        dino.sprite = 'dino1'; // Shocked
        
        // After ~40 frames (approx 0.6s), switch to Blink/Talking
        if (introTimer > 40) {
            dino.sprite = 'dino2'; // Blink
            // Draw bubble in draw() function
        }
        
        // After ~120 frames (approx 2s total), START RUNNING
        if (introTimer > 120) {
            currentState.value = 'PLAYING';
            frameCount = 0;
        }
    }
    else if (currentState.value === 'PLAYING') {
        // Run Animation
        // Toggle every 8 frames
        const runFrame = Math.floor(frameCount / 8) % 2; 
        dino.sprite = runFrame === 0 ? 'dino4' : 'dino5';
        
        // Jump Logic (if grounded) handled by input, sprite override
        if (!dino.grounded) {
            dino.sprite = 'dino3'; // Stationary frame for jump
        }

        // --- Obstacles ---
        if (frameCount % SPAWN_RATE === 0) {
            // 20% Chance for Bird
            if (Math.random() < 0.2) {
                const img = assets.bird;
                let scale = 1; 
                // Cap bird size
                if (img.height > 40) scale = 40 / img.height;
                
                // Random Height: Low (jumpable) or High (run under)
                // 60% chance Low
                const isHigh = Math.random() > 0.6;
                const yOffset = isHigh ? 70 : 15; 
                
                obstacles.push({
                    x: canvasRef.value.width,
                    y: h - 10 - (img.height * scale) - yOffset,
                    w: img.width * scale,
                    h: img.height * scale,
                    type: 'bird'
                });
            } else {
                 // Randomly select 1-6 CACTI
                const cactusType = `cactus${Math.floor(Math.random() * 6) + 1}`;
                const img = assets[cactusType];
                
                // Safe fallback if not loaded (though should be)
                const naturalW = img?.width || 20;
                const naturalH = img?.height || 40;

                // Cap height at 50 to avoid massive obstacles
                let scale = 1;
                if (naturalH > 50) {
                    scale = 50 / naturalH;
                }
                
                obstacles.push({
                    x: canvasRef.value.width,
                    y: h - 10 - (naturalH * scale),
                    w: naturalW * scale,
                    h: naturalH * scale,
                    origW: naturalW,
                    origH: naturalH,
                    type: cactusType
                });
            }
        }
        
        for (let i = obstacles.length - 1; i >= 0; i--) {
            let obs = obstacles[i];
            obs.x -= SPEED;
            if (obs.type === 'bird') obs.x -= 2; // Birds fly faster
            
            if (obs.x + obs.w < 0) {
                obstacles.splice(i, 1);
                score.value++;
            }
            
            // Collision (Simple AABB)
            // Shrink hitbox slightly for fairness
            if (
                dino.x + 10 < obs.x + obs.w - 5 && // Tighter hitboxes
                dino.x + dino.w - 10 > obs.x + 5 &&
                dino.y + 10 < obs.y + obs.h &&
                dino.y + dino.h > obs.y + 10
            ) {
                currentState.value = 'GAME_OVER';
                dino.sprite = 'dino1'; // Shocked calculation
            }
        }
    }
};

const draw = () => {
    if (!canvasRef.value) return;
    const ctx = canvasRef.value.getContext('2d');
    if (!ctx) return;
    
    const w = canvasRef.value.width;
    const h = canvasRef.value.height;
    
    ctx.clearRect(0, 0, w, h);
    
    // Draw Ground Line
    ctx.beginPath();
    ctx.moveTo(0, h - 10);
    ctx.lineTo(w, h - 10);
    ctx.lineWidth = 1;
    ctx.strokeStyle = '#535353';
    ctx.stroke();
    
    // Draw Dino
    // @ts-ignore
    const spriteImg = assets[dino.sprite] || assets.dino3;
    if (spriteImg) {
        ctx.drawImage(spriteImg, dino.x, dino.y, dino.w, dino.h);
    }
    
    // Draw Speech Bubble (Only in INTRO_SHOCK phase 2)
    if (currentState.value === 'INTRO_SHOCK' && introTimer > 40) {
        // Bubble
        ctx.fillStyle = 'white';
        ctx.strokeStyle = 'black';
        ctx.lineWidth = 1;
        const bx = dino.x + dino.w + 5;
        const by = dino.y - 20;
        
        ctx.beginPath();
        ctx.rect(bx, by, 30, 25);
        ctx.fill();
        ctx.stroke();
        
        // Text
        ctx.fillStyle = 'black';
        ctx.font = 'bold 20px Arial';
        ctx.fillText('!', bx + 11, by + 20);
        
        // Tail
        ctx.beginPath();
        ctx.moveTo(bx, by+10);
        ctx.lineTo(bx-5, by+15);
        ctx.lineTo(bx, by+20);
        ctx.fill();
        ctx.stroke();
    }
    
    // Draw Obstacles
    for (const obs of obstacles) {
        const img = assets[obs.type];
        if (img) {
             ctx.drawImage(img, obs.x, obs.y, obs.w, obs.h);
        }
    }
    
    // Draw Score
    if (currentState.value === 'PLAYING' || currentState.value === 'GAME_OVER') {
        ctx.fillStyle = '#535353';
        ctx.font = '20px "Courier New", Courier, monospace';
        ctx.fillText(`HI ${score.value.toString().padStart(5, '0')}`, w - 120, 30);
    }
};

const loop = () => {
    update();
    draw();
    gameLoopId = requestAnimationFrame(loop);
};

const handleInput = () => {
    if (currentState.value === 'IDLE' || currentState.value === 'GAME_OVER') {
        startIntro();
    } else if (currentState.value === 'PLAYING') {
        // Jump
        if (dino.grounded) {
            dino.dy = JUMP_FORCE;
            dino.grounded = false;
        }
    }
};

window.addEventListener('keydown', (e) => {
    if (e.code === 'Space' || e.code === 'ArrowUp') {
        if (e.target === document.body) e.preventDefault();
        handleInput();
    }
});
</script>

<template>
    <div 
        ref="containerRef" 
        class="w-full h-full relative group cursor-pointer select-none overflow-hidden" 
        @mousedown="handleInput"
        @touchstart.prevent="handleInput"
    >
        <canvas ref="canvasRef" class="block w-full h-full"></canvas>
        
        <!-- Play Button Overlay -->
        <div 
            v-if="currentState === 'IDLE' || currentState === 'GAME_OVER'"
            class="absolute inset-0 flex flex-col gap-3 items-center justify-center bg-black/5 dark:bg-black/20 backdrop-blur-[1px] transition-opacity"
        >
            <button 
                class="px-6 py-2 bg-white text-black font-bold uppercase tracking-widest border border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] active:translate-y-[4px] active:shadow-none transition-all z-20"
                @click.stop="startIntro"
                @mousedown.stop
                @touchstart.stop
            >
                {{ currentState === 'GAME_OVER' ? 'Try Again' : 'Play Dino' }}
            </button>
            
            <Link 
                v-if="showMoreLink"
                href="/arcade"
                class="px-4 py-1 text-xs bg-white text-gray-500 font-bold uppercase tracking-widest border border-gray-300 shadow-[2px_2px_0px_0px_rgba(0,0,0,0.1)] hover:translate-y-[1px] hover:shadow-[1px_1px_0px_0px_rgba(0,0,0,0.1)] hover:text-black hover:border-black active:translate-y-[2px] active:shadow-none transition-all z-20"
                @click.stop
                @mousedown.stop
                @touchstart.stop
            >
                More Games
            </Link>
        </div>
    </div>
</template>
