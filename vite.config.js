import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'],
            refresh: true,
            fonts: [
                bunny('Plus Jakarta Sans', { weights: [400, 500, 600, 700], optimizedFallbacks: false }),
                bunny('Cormorant Garamond', { weights: [500, 600, 700], optimizedFallbacks: false }),
                bunny('Amiri', { weights: [400, 700], optimizedFallbacks: false }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
