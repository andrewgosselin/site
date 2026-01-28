<?php

return [
    'defaults' => [
        'title' => 'Andrew Gosselin | Full Stack Developer',
        'description' => 'Andrew Gosselin - Full Stack Developer based in The Hague, NL. View my portfolio, projects, and skills.',
        'keywords' => 'Andrew Gosselin, Full Stack Developer, Laravel, Vue, PHP, Web Development, The Hague',
        'image' => 'https://gosselin.dev/assets/branding/banner.webp',
        'type' => 'website',
        'twitter_card' => 'summary_large_image',
    ],

    'routes' => [
        'home' => [
            'title' => 'Home',
            'description' => 'Andrew Gosselin - Full Stack Developer based in The Hague, NL. View my portfolio, projects, and skills.',
        ],
        'projects' => [
            'title' => 'Projects',
            'description' => 'Explore my portfolio of web development projects, including Laravel, Vue.js, and other web applications.',
        ],
        'tools.index' => [
            'title' => 'Tools',
            'description' => 'A collection of developer utilities including JSON tools, Encoders, Generators, and SEO Checkers.',
        ],
        'tools.seo-checker' => [
            'title' => 'SEO Checker',
            'description' => 'Analyze and preview how your website appears in Google Search, Twitter, standard social media (Open Graph), and Discord.',
            'keywords' => 'SEO Tool, Meta Tag Checker, Open Graph Preview, Twitter Card Preview, Discord Embed Preview',
        ],
        'tools.json' => [
            'title' => 'JSON Formatter & Validator',
            'description' => 'Format, validate, and explore JSON data with a tree view. A free developer tool for parsing and debugging JSON.',
            'keywords' => 'JSON Formatter, JSON Validator, JSON Beautifier, JSON Tree View, Developer Tools',
        ],
        'tools.encoder' => [
            'title' => 'Encoder / Decoder',
            'description' => 'Online tool to encode and decode URL, Base64, JWT, and HTML entities. Secure, client-side processing.',
            'keywords' => 'URL Encoder, Base64 Decoder, JWT Decoder, HTML Entity Encoder, Developer Tools',
        ],
        'tools.generator' => [
            'title' => 'Generator Tool',
            'description' => 'Generate random UUIDs (v4), NanoIDs, secure passwords, and cryptographic hashes (MD5, SHA) client-side.',
            'keywords' => 'UUID Generator, Password Generator, Hash Generator, NanoID Generator, Developer Tools',
        ],
        'tools.image' => [
            'title' => 'Image Tool',
            'description' => 'Convert, resize, crop, and transform images in your browser. Supports PNG, JPEG, and WebP formats.',
            'keywords' => 'Image Converter, Image Resizer, Image Cropper, WebP Converter, Developer Tools',
        ],
        'tools.ind' => [
            'title' => 'IND Public Register Search',
            'description' => 'Search recognised sponsors in the Netherlands IND Public Register for regular labour and highly skilled migrants.',
            'keywords' => 'IND Register, Recognised Sponsors, Netherlands Skilled Migrants, Dutch Visa Sponsors, IND Search Tool',
        ],
    ],
];
