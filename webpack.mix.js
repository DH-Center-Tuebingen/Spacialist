let mix = require('laravel-mix');

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
//    .copy(
//        'node_modules/vue-multiselect/dist/vue-multiselect.min.css',
//        'public/css'
//    )
   .options({
       fileLoaderDirs: {
           fonts: appPath + 'fonts'
       }
   })
   .webpackConfig({
      output: {
         publicPath: '/' + appPath
      },
   })
   .webpackConfig(webpack => {
       return {
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
           plugins: [
               new webpack.ProvidePlugin({
                    process : 'process/browser',
                    Buffer  : ['buffer', 'Buffer']
                })
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
