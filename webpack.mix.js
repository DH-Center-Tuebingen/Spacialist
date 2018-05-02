let mix = require('laravel-mix');

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

mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css')
   .copy(
       'node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css',
       'public/css'
   )
   .copy(
       'node_modules/cornerstone-wado-image-loader/dist/cornerstoneWADOImageLoaderWebWorker.min.js',
       'public/js/cornerstoneWADOImageLoaderWebWorker.min.js'
   )
   .copy(
       'node_modules/cornerstone-wado-image-loader/dist/cornerstoneWADOImageLoaderCodecs.min.js',
       'public/js/cornerstoneWADOImageLoaderCodecs.min.js'
   )
   .copy(
       'node_modules/highlight.js/styles/github.css',
       'public/css/highlightjs.css'
   )
   .copy(
       'node_modules/vue-multiselect/dist/vue-multiselect.min.css',
       'public/css'
   )
   .autoload({
       jquery: ['$'],
       axios: ['$http']
   })
   .extract([
       '@bosket/core',
       '@bosket/tools',
       '@bosket/vue',
       '@fortawesome/fontawesome',
       '@fortawesome/fontawesome-free-brands',
       '@fortawesome/fontawesome-free-regular', '@fortawesome/fontawesome-free-solid',
       '@hscmap/vue-menu',
       'axios',
       'bootstrap',
       'bootstrap-datepicker',
       'cornerstone-core',
       'cornerstone-math',
       'cornerstone-tools',
       'cornerstone-wado-image-loader',
       'd3-dsv',
       'debounce',
       'jquery',
       'lodash',
       'moment',
       'ol',
       'popper.js',
       'screenfull',
       'three-full',
       'transliteration',
       'vee-validate',
       'vue',
       'vue-highlightjs',
       'vue-infinite-scroll',
       'vue-js-modal',
       'vue-markdown',
       'vue-multiselect',
       'vue-pdf',
       'vue-typeahead',
       'vue-upload-component',
       'vuedraggable'
   ]);
