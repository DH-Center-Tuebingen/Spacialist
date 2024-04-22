import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path, { dirname } from 'path';
import { fileURLToPath } from 'url';

const isOpen = process.env.IS_OPEN === 'true';
const buildDir = isOpen ? 'build_open' : 'build';

const _dirname = dirname(fileURLToPath(import.meta.url));

export default ({ mode }) => {
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
        resolve: {
            alias: {
                '@': path.resolve(_dirname, './resources/js/'),
                '%store': path.resolve(_dirname, './resources/js/bootstrap/store.js'),
                '%router': path.resolve(_dirname, './resources/js/bootstrap/router.js'),
            },
        },
    };

    if(env.VITE_APP_PATH) {
        config.base = `${env.VITE_APP_PATH.replace(/\/$/, '')}/${buildDir}/`;
    }

    return defineConfig(config);
};