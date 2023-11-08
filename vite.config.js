import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
// import react from '@vitejs/plugin-react';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'url';

const isOpen = process.env.IS_OPEN === 'true';

export default defineConfig({
    plugins: [
        laravel([
            (!isOpen) ? 'resources/js/app.js': 'resources/js/open.js',
            'resources/sass/app.scss',
            'resources/sass/app-dark_unrounded.scss',
        ]),
        vue({

            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});