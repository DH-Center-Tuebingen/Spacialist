let mix = require('laravel-mix');
const path = require('path');

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

mix.js('resources/js/app.js', 'public/js').vue()
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/app-dark_unrounded.scss', 'public/css')
    .copy(
        'node_modules/vue-final-modal/dist/style.css',
        'public/css/modal.css'
    )
    //    .copy(
    //        'node_modules/vue-multiselect/dist/vue-multiselect.min.css',
    //        'public/css'
    //    )
    .options({
        fileLoaderDirs: {
            fonts: appPath + 'fonts'
        }
    })
    .webpackConfig(webpack => {
        return {
            module: {
                rules: [{
                    test: /\.m?js$/,
                    resolve: {
                        fullySpecified: false
                    },
                    include: /node_modules/,
                    type: 'javascript/auto',
                }]
            },
            resolve: {
                //    alias: {
                //        videojs: 'video.js',
                //        WaveSurfer: 'wavesurfer.js',
                //        RecordRTC: 'recordrtc'
                //    },
                fallback: {
                    fs: false
                }
            },
            output: {
                publicPath: '/' + appPath
            },
           plugins: [
               new webpack.ProvidePlugin({
                    process : 'process/browser',
                    Buffer: ['buffer', 'Buffer'],
                }),
                new webpack.DefinePlugin({
                    __APPNAME__: `'${appName}'`,
                }),
        //        new webpack.ProvidePlugin({
        //            videojs: 'video.js/dist/video.cjs.js',
        //            RecordRTC: 'recordrtc',
        //            MediaStreamRecorder: ['recordrtc', 'MediaStreamRecorder']
        //        })
           ]
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

mix.browserSync({
    proxy: 'http://localhost:8000',
    files: 'resources/**/*',
});
