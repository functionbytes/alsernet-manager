import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/helpdesk/app.tsx', // React islands entry point
                'resources/js/helpdesk/widget/widget-entry.tsx', // LiveChat widget
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
    esbuild: {
        jsx: 'automatic',
    },
});
