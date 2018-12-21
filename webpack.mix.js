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

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css')
   .copy(
       'node_modules/cornerstone-wado-image-loader/dist/cornerstoneWADOImageLoaderWebWorker.min.js',
       'public/js/cornerstoneWADOImageLoaderWebWorker.min.js'
   )
   .copy(
       'node_modules/cornerstone-wado-image-loader/dist/cornerstoneWADOImageLoaderCodecs.min.js',
       'public/js/cornerstoneWADOImageLoaderCodecs.min.js'
   )
   .copy(
       'node_modules/zlibjs/bin/inflate.min.js',
       'public/js/inflate.min.js'
   )
   .copy(
       'node_modules/vue-multiselect/dist/vue-multiselect.min.css',
       'public/css'
   )
   .options({
       fileLoaderDirs: {
           fonts: appPath + 'fonts'
       }
   })
   .webpackConfig({
      output: {
         publicPath: '/' + appPath
      }
   })
   .autoload({
       jquery: ['$'],
       axios: ['$http']
   });
   // TODO wait for webpack 5, since css extraction does not work with v4 and extract()
   // .extract();
