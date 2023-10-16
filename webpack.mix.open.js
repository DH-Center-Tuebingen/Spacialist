let mix = require('laravel-mix');
const path = require('path');
// const CircularDependencyPlugin = require('circular-dependency-plugin')

/*
 |--------------------------------------------------------------------------
 | App Path
 |--------------------------------------------------------------------------
 |
 | The relative path of your app in your web browser's root folder
 | **without** leading and **with** trailing slash
 |
 |--------------------------------------------------------------------------
 | Example
 |--------------------------------------------------------------------------
 |
 | Document Root: /var/www/html
 | App Root: /var/www/html/spacialist/instance1
 | => appPath = 'spacialist/instance1/'
 |
 */

const appPath = process.env.MIX_APP_PATH;
const appName = process.env.APP_NAME;

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.js('resources/js/open.js', 'public/js/open').vue()
    .options({
        runtimeChunkPath: 'js/open',
        fileLoaderDirs: {
            fonts: appPath + 'fonts'
        },
    })
    .sourceMaps()
    .webpackConfig(webpack => {
        return {
            output: {
                publicPath: '/' + appPath
            },
            plugins: [
                new webpack.DefinePlugin({
                    __APPNAME__: `'${appName}'`,
                }),
            ],
        }
    })
    .extract();

if(`public/${appPath}fonts` !== 'public/fonts') {
    mix.copyDirectory(`public/${appPath}fonts`, 'public/fonts');
}
mix.alias({
    '@': path.join(__dirname, 'resources/js'),
    vue$: path.join(__dirname, 'node_modules/vue/dist/vue.esm-bundler.js')
})