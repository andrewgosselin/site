export interface Project {
    id: string;
    title: string;
    description: string;
    image?: string;
    tags: string[];
    link?: string;
    github?: string;
    status: 'completed' | 'idea' | 'wip';
}

export const projects: Project[] = [
    {
        id: '1',
        title: 'Test Project',
        description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod.',
        image: 'https://placehold.co/600x400/1a1a1a/FFF?text=Test+Project',
        tags: ['Laravel', 'Vue', 'Tailwind'],
        link: 'https://example.com',
        github: 'https://github.com',
        status: 'completed'
    },
    {
        id: '2',
        title: 'Test Project',
        description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod.',
        image: 'https://placehold.co/600x400/1a1a1a/FFF?text=Test+Project',
        tags: ['TypeScript', 'Canvas', 'Vue'],
        link: '/arcade/dino-run',
        status: 'completed'
    },
    {
        id: '3',
        title: 'Test Project',
        description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod.',
        image: 'https://placehold.co/600x400/1a1a1a/FFF?text=Test+Project',
        tags: ['Inertia.js', 'PHP', 'GSAP'],
        status: 'completed'
    }
];

export const ideas: Project[] = [
    {
        id: 'idea-1',
        title: 'Test Project',
        description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod.',
        tags: ['Python', 'VS Code'],
        status: 'idea'
    },
    {
        id: 'idea-2',
        title: 'Test Project',
        description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod.',
        tags: ['Solidity', 'React'],
        status: 'idea'
    },
    {
        id: 'idea-3',
        title: 'Test Project',
        description: 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Quisquam, quod.',
        tags: ['IOT', 'Arduino', 'Flutter'],
        status: 'idea'
    }
];
