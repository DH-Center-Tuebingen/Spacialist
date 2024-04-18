import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

const isOpen = process.env.IS_OPEN === 'true';
const buildDir = isOpen ? 'build_open' : 'build';

export default ({mode}) => {
    const env = loadEnv(mode, process.cwd(), 'VITE_');
    const config = {
        plugins: [
            laravel([
                isOpen ? 'resources/js/open.js' : 'resources/js/app.js',
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
        build: {
            manifest: isOpen ? 'manifest.open.json' : 'manifest.json',
            outDir: `public/${buildDir}`,
        },
    };

    if(env.VITE_APP_PATH) {
        config.base = `${env.VITE_APP_PATH.replace(/\/$/, '')}/${buildDir}/`;
    }

    return defineConfig(config);
};