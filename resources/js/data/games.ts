export interface Game {
    slug: string;
    title: string;
    description: string;
    image: string;
    bg: string;
    link: string;
    component?: string;
}

export const games: Game[] = [
    {
        slug: "dino-run",
        title: "Dino Run",
        description: "The classic offline endless runner. Jump over cacti and dodge birds!",
        image: "/assets/dino/dino3.png",
        bg: "bg-gray-100 dark:bg-gray-800",
        link: "/arcade/dino-run",
        component: "DinoGame"
    },
    {
        slug: "snake",
        title: "Snake",
        description: "Navigate the grid, eat food, and grow longer without hitting the walls or yourself.",
        image: "https://placehold.co/400x300/1a1a1a/FFF?text=Snake",
        bg: "bg-emerald-50 dark:bg-emerald-900/10",
        link: "/arcade/snake"
    },
    {
        slug: "tetris",
        title: "Tetris",
        description: "Stack falling blocks to clear lines in this timeless puzzle game.",
        image: "https://placehold.co/400x500/1a1a1a/FFF?text=Tetris",
        bg: "bg-blue-50 dark:bg-blue-900/10",
        link: "/arcade/tetris"
    },
    {
        slug: "pong",
        title: "Pong",
        description: "The original arcade tennis game. Defeat the AI paddle!",
        image: "https://placehold.co/400x250/1a1a1a/FFF?text=Pong",
        bg: "bg-slate-50 dark:bg-slate-800",
        link: "/arcade/pong"
    },
    {
        slug: "2048",
        title: "2048",
        description: "Slide tiles to combine numbers and reach the 2048 tile.",
        image: "https://placehold.co/400x400/1a1a1a/FFF?text=2048",
        bg: "bg-orange-50 dark:bg-orange-900/10",
        link: "/arcade/2048"
    },
    {
        slug: "breakout",
        title: "Breakout",
        description: "Smash bricks with a ball and paddle. Don't let the ball drop!",
        image: "https://placehold.co/400x350/1a1a1a/FFF?text=Breakout",
        bg: "bg-red-50 dark:bg-red-900/10",
        link: "/arcade/breakout"
    }
];
