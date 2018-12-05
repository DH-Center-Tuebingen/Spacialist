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
       'node_modules/cornerstone-wado-image-loader/dist/cornerstoneWADOImageLoaderWebWorker.min.js',
       'public/js/cornerstoneWADOImageLoaderWebWorker.min.js'
   )
   .copy(
       'node_modules/cornerstone-wado-image-loader/dist/cornerstoneWADOImageLoaderCodecs.min.js',
       'public/js/cornerstoneWADOImageLoaderCodecs.min.js'
   )
   .copy(
       'node_modules/vue-multiselect/dist/vue-multiselect.min.css',
       'public/css'
   )
   .webpackConfig({
      output: {
         publicPath: '/'
      }
   })
   .autoload({
       jquery: ['$'],
       axios: ['$http']
   })
   .extract([
       '@fortawesome/fontawesome-svg-core',
       '@fortawesome/free-brands-svg-icons',
       '@fortawesome/free-regular-svg-icons',
       '@fortawesome/free-solid-svg-icons',
       '@mapbox/togeojson',
       '@websanova/vue-auth',
       'axios',
       'bibtex-parse',
       'bootstrap',
       'cornerstone-core',
       'cornerstone-math',
       'cornerstone-tools',
       'cornerstone-wado-image-loader',
       'csv2geojson',
       'd3-dsv',
       'debounce',
       'jquery',
       'jszip',
       'lodash',
       'moment',
       'ol',
       'plotly.js',
       'popper.js',
       'proj4',
       'screenfull',
       'shpjs',
       'three-full',
       'transliteration',
       'tree-vue-component',
       'v-calendar',
       'v-tooltip',
       'vee-validate',
       'vue',
       'vue-i18n',
       'vue-context',
       'vue-highlightjs',
       'vue-infinite-scroll',
       'vue-js-modal',
       'vue-markdown',
       'vue-multiselect',
       'vue-notification',
       'vue-pdf',
       'vue-router',
       'vue-typeahead',
       'vue-upload-component',
       'vuedraggable',
       'wkx'
   ]);
