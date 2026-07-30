import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { resolve, dirname, relative, sep } from 'path';
import { fileURLToPath } from 'url';
import { watch } from 'fs';

const isOpen = process.env.IS_OPEN === 'true';
const buildDir = isOpen ? 'build_open' : 'build';

const _dirname = dirname(fileURLToPath(import.meta.url));


/**
 * Chokidar somehow does not detect the top level
 * directories like: './app/**' or '/app/**'
 * Therefore we need to check the filepath instead.
 */
const ignoredRootDirectories = new Set([
    'app',
    'bootstrap',
    'config',
    'database',
    'lang',
    'node_modules',
    'public',
    'routes',
    'storage',
    'tests',
]);


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
        server: {
            watch: {
                // We need to ignore the backend files otherwise the vite watcher
                // would lock the files on windows leading to problems, e.g. when
                // uploading a plugin.
                ignored: (file) => {
                    const rel = relative(process.cwd(), file);

                    // Outside the project
                    if(rel.startsWith('..')) {
                        return false;
                    }

                    const first = rel.split(sep)[0];

                    return ignoredRootDirectories.has(first);
                },
            }
        },
        build: {
            manifest: isOpen ? 'manifest.open.json' : 'manifest.json',
            outDir: `public/${buildDir}`,
        },
        resolve: {
            alias: {
                '@': resolve(_dirname, './resources/js/'),
                '%store': resolve(_dirname, './resources/js/bootstrap/store.js'),
                '%router': resolve(_dirname, './resources/js/bootstrap/router.js'),
            },
        },
    };

    if(env.VITE_APP_PATH) {
        config.base = `${env.VITE_APP_PATH.replace(/\/$/, '')}/${buildDir}/`;
    }

    return defineConfig(config);
};