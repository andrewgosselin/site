<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = withDefaults(defineProps<{
    showMoreLink?: boolean;
}>(), {
    showMoreLink: true
});

const canvasRef = ref<HTMLCanvasElement | null>(null);
const containerRef = ref<HTMLElement | null>(null);

// Game Constants
const GRID_SIZE = 20;
const SPEED = 100; // ms per frame

// Colors - Retro Arcade Theme
const COLORS = {
    bg: '#000000',
    snakeHead: '#00FF00',
    snakeBody: '#00AA00',
    food: '#FF0000',
    grid: '#111111',
    text: '#00FF00'
};

// State
type GameState = 'START' | 'PLAYING' | 'GAME_OVER';
const currentState = ref<GameState>('START');
const score = ref(0);
const highScore = ref(0);

// Game Logic Vars
let gameLoopId: number | null = null;
let lastTime = 0;
let tilesX = 0;
let tilesY = 0;

type Point = { x: number, y: number };
let snake: Point[] = [];
let velocity: Point = { x: 1, y: 0 };
let nextVelocity: Point = { x: 1, y: 0 }; // Buffer input
let food: Point = { x: 5, y: 5 };

onMounted(() => {
    // Load High Score
    const saved = localStorage.getItem('snake_highscore');
    if (saved) highScore.value = parseInt(saved);

    window.addEventListener('resize', handleResize);
    window.addEventListener('keydown', handleKeydown);
    
    // Initial Setup
    handleResize();
    resetGame();
    draw(); // Draw initial screen
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    window.removeEventListener('keydown', handleKeydown);
    if (gameLoopId) cancelAnimationFrame(gameLoopId);
});

const handleResize = () => {
    if (!canvasRef.value || !containerRef.value) return;
    const parent = containerRef.value;
    
    // Snap to grid
    canvasRef.value.width = parent.clientWidth - (parent.clientWidth % GRID_SIZE);
    canvasRef.value.height = parent.clientHeight - (parent.clientHeight % GRID_SIZE);
    
    tilesX = canvasRef.value.width / GRID_SIZE;
    tilesY = canvasRef.value.height / GRID_SIZE;

    draw();
};

const spawnFood = () => {
    if(!canvasRef.value) return;
    
    // Simple random spawn (can optimize to avoid snake body)
    let valid = false;
    while (!valid) {
        food = {
            x: Math.floor(Math.random() * tilesX),
            y: Math.floor(Math.random() * tilesY)
        };
        
        // Check collision with snake
        valid = !snake.some(segment => segment.x === food.x && segment.y === food.y);
    }
};

const resetGame = () => {
    if (!canvasRef.value) return;
    
    snake = [
        { x: 10, y: 10 },
        { x: 9, y: 10 },
        { x: 8, y: 10 }
    ];
    velocity = { x: 1, y: 0 };
    nextVelocity = { x: 1, y: 0 };
    score.value = 0;
    spawnFood();
};

const startGame = () => {
    if (currentState.value === 'PLAYING') return;
    
    currentState.value = 'PLAYING';
    resetGame();
    lastTime = performance.now();
    loop(lastTime);
};

const gameOver = () => {
    currentState.value = 'GAME_OVER';
    if (score.value > highScore.value) {
        highScore.value = score.value;
        localStorage.setItem('snake_highscore', highScore.value.toString());
    }
};

const handleKeydown = (e: KeyboardEvent) => {
    // Prevent scrolling
    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Space'].includes(e.code)) {
        if (e.target === document.body) e.preventDefault();
    }

    if (currentState.value !== 'PLAYING') {
        if (e.code === 'Space' || e.code === 'Enter') {
            startGame();
        }
        return;
    }

    switch (e.code) {
        case 'ArrowUp':
            if (velocity.y === 0) nextVelocity = { x: 0, y: -1 };
            break;
        case 'ArrowDown':
            if (velocity.y === 0) nextVelocity = { x: 0, y: 1 };
            break;
        case 'ArrowLeft':
            if (velocity.x === 0) nextVelocity = { x: -1, y: 0 };
            break;
        case 'ArrowRight':
            if (velocity.x === 0) nextVelocity = { x: 1, y: 0 };
            break;
    }
};

const update = () => {
    // Apply buffered input
    velocity = nextVelocity;

    const head = { ...snake[0] };
    head.x += velocity.x;
    head.y += velocity.y;

    // Wall Collision
    if (head.x < 0 || head.x >= tilesX || head.y < 0 || head.y >= tilesY) {
        gameOver();
        return;
    }

    // Self Collision
    // Don't check tail because it will move
    for (let i = 0; i < snake.length - 1; i++) {
        if (head.x === snake[i].x && head.y === snake[i].y) {
            gameOver();
            return;
        }
    }

    // Move Snake
    snake.unshift(head);

    // Food Collision
    if (head.x === food.x && head.y === food.y) {
        score.value += 10;
        spawnFood();
        // Don't pop tail, so it grows
    } else {
        snake.pop();
    }
};

const draw = () => {
    if (!canvasRef.value) return;
    const ctx = canvasRef.value.getContext('2d');
    if (!ctx) return;

    // Background
    ctx.fillStyle = COLORS.bg;
    ctx.fillRect(0, 0, canvasRef.value.width, canvasRef.value.height);

    // Draw Grid (optional, low opacity)
    ctx.strokeStyle = COLORS.grid;
    ctx.lineWidth = 1;
    for (let x = 0; x <= canvasRef.value.width; x += GRID_SIZE) {
        ctx.beginPath();
        ctx.moveTo(x, 0);
        ctx.lineTo(x, canvasRef.value.height);
        ctx.stroke();
    }
    for (let y = 0; y <= canvasRef.value.height; y += GRID_SIZE) {
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(canvasRef.value.width, y);
        ctx.stroke();
    }

    // Draw Food
    ctx.fillStyle = COLORS.food;
    // Make food slightly smaller than grid
    const pad = 2;
    ctx.fillRect(
        food.x * GRID_SIZE + pad, 
        food.y * GRID_SIZE + pad, 
        GRID_SIZE - (pad * 2), 
        GRID_SIZE - (pad * 2)
    );

    // Draw Snake
    snake.forEach((segment, index) => {
        ctx.fillStyle = index === 0 ? COLORS.snakeHead : COLORS.snakeBody;
        ctx.fillRect(
            segment.x * GRID_SIZE + pad,
            segment.y * GRID_SIZE + pad,
            GRID_SIZE - (pad * 2),
            GRID_SIZE - (pad * 2)
        );
    });

    // Score Text for 'PLAYING' mode
    if (currentState.value === 'PLAYING') {
         ctx.fillStyle = 'rgba(255, 255, 255, 0.5)';
         ctx.font = '16px "Courier New", monospace';
         ctx.fillText(`SCORE: ${score.value}`, 10, 20);
    }
};

const loop = (timestamp: number) => {
    if (currentState.value !== 'PLAYING') {
        draw(); // Keep drawing mainly for resize stability or game over screen background
        return;
    }

    const elapsed = timestamp - lastTime;
    
    if (elapsed > SPEED) {
        update();
        draw();
        lastTime = timestamp;
    }

    gameLoopId = requestAnimationFrame(loop);
};

</script>

<template>
    <div 
        ref="containerRef" 
        class="w-full h-full relative group cursor-pointer select-none overflow-hidden bg-black font-mono"
        @touchstart.prevent
        @mousedown.stop
    >
        <canvas ref="canvasRef" class="block mx-auto"></canvas>

        <!-- Overlay for Start / Game Over -->
        <div 
            v-if="currentState !== 'PLAYING'"
            class="absolute inset-0 flex flex-col items-center justify-center bg-black/5 dark:bg-black/20 backdrop-blur-[1px] transition-opacity z-10 p-4"
        >
            <!-- Hide Title on Game Over to save space -->
            <div v-if="currentState === 'START'" class="mb-6 text-center">
                <h1 class="text-4xl md:text-5xl font-black italic text-black dark:text-white tracking-tighter mb-1 uppercase transform -rotate-2">
                    Snake
                </h1>
                <div class="w-full h-2 bg-black dark:bg-white/20"></div>
            </div>

            <div v-if="currentState === 'GAME_OVER'" class="mb-4 flex flex-col items-center animate-in fade-in zoom-in duration-300">
                <div class="bg-red-500 text-white px-3 py-1 font-black uppercase tracking-widest transform rotate-2 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] text-sm mb-2">
                    Game Over
                </div>
                <div class="flex flex-col gap-0 items-center">
                    <span class="text-[8px] font-bold uppercase tracking-widest text-gray-500">Score</span>
                    <span class="text-4xl font-black text-black dark:text-white leading-none">{{ score }}</span>
                </div>
            </div>

            <div v-if="currentState === 'START'" class="mb-8 p-4 border-2 border-dashed border-black/10 dark:border-white/10 rounded-lg">
               <div class="flex gap-4 text-xs font-bold uppercase tracking-widest text-gray-400">
                    <div class="flex flex-col items-center gap-1">
                        <kbd class="px-2 py-1 bg-white border border-gray-200 rounded text-black shadow-sm">↑↓</kbd>
                        <span>Move</span>
                    </div>
                    <div class="w-px bg-black/10"></div>
                    <div class="flex flex-col items-center gap-1">
                        <span class="text-black dark:text-white">{{ highScore }}</span>
                        <span>Best</span>
                    </div>
               </div>
            </div>
            <!-- Compact Best Score for Game Over -->
            <div v-else class="mb-4 text-xs font-bold uppercase tracking-widest text-gray-400">
                Best: <span class="text-black dark:text-white">{{ highScore }}</span>
            </div>

            <button 
                class="mb-3 px-6 py-2 bg-white text-black font-bold uppercase tracking-widest border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-y-[2px] hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] active:translate-y-[4px] active:shadow-none transition-all"
                @click="startGame"
            >
                {{ currentState === 'START' ? 'Start Game' : 'Try Again' }}
            </button>

            <Link 
                v-if="showMoreLink"
                href="/arcade"
                class="px-4 py-1 text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-black dark:hover:text-white hover:underline decoration-2 underline-offset-4 transition-all"
            >
                Exit Game
            </Link>
        </div>
    </div>
</template>

<style scoped>
/* No scoped styles needed anymore, using utility classes */
</style>
