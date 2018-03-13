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
   .copy('node_modules/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css', 'public/css')
   .copy('node_modules/vue-multiselect/dist/vue-multiselect.min.css', 'public/css')
   .copy('node_modules/highlight.js/styles/github.css', 'public/css/highlightjs.css')
   .autoload({
       jquery: ['$']
   })
   .extract(['jquery', 'bootstrap', '@fortawesome/fontawesome', '@fortawesome/fontawesome-free-brands', '@fortawesome/fontawesome-free-regular', '@fortawesome/fontawesome-free-solid', '@bosket/core', '@bosket/tools', '@bosket/vue', 'axios', 'lodash', 'vue', '@hscmap/vue-menu', 'bootstrap-datepicker', 'debounce', 'moment', 'ol', 'popper.js', 'screenfull', 'three', 'vee-validate', 'vue-highlightjs', 'vue-js-modal', 'vue-markdown', 'vue-multiselect', 'vue-pdf', 'vue-typeahead', 'vue-upload-component', 'vuedraggable']);
