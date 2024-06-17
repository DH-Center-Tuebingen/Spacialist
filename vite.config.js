import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
// import react from '@vitejs/plugin-react';
import vue from '@vitejs/plugin-vue';
import { fileURLToPath, URL } from 'url';

const isOpen = process.env.IS_OPEN === 'true';

export default ({mode}) => {
    const env = loadEnv(mode, process.cwd(), 'VITE_');
    const config = {
        plugins: [
            laravel([
                (!isOpen) ? 'resources/js/app.js' : 'resources/js/open.js',
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
    };

    if(env.VITE_APP_PATH) {
        config.base = `${env.VITE_APP_PATH.replace(/\/$/, '')}/build/`;
    }

    return defineConfig(config);
}