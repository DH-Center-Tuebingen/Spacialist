import Multiselect from 'vue-multiselect';
import fontawesome from '@fortawesome/fontawesome';
import regular from '@fortawesome/fontawesome-free-regular';
import solid from '@fortawesome/fontawesome-free-solid';
import brands from '@fortawesome/fontawesome-free-brands';
import VModal from 'vue-js-modal';
import Axios from 'axios';
import VueUploadComponent from 'vue-upload-component';

fontawesome.library.add(solid, regular, brands);

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// require('./bootstrap');
require('popper.js')
require('bootstrap')

window.Vue = require('vue');
window._ = require('lodash');
$ = jQuery  = window.$ = window.jQuery = require('jquery');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.prototype.$http = Axios;
Vue.use(VModal);

// Imported Components
Vue.component('multiselect', Multiselect);
Vue.component('file-upload', VueUploadComponent);
// Extended Components
Vue.component('typeahead', require('./components/Typeahead.vue'));

// Reusable Components
Vue.component('attributes', require('./components/AttributeList.vue'));
Vue.component('bibliography', require('./components/BibliographyTable.vue'));
Vue.component('context-tree', require('./components/ContextTree.vue'));
Vue.component('context-types', require('./components/ContextTypeList.vue'));
Vue.component('layer', require('./components/LayerList.vue'));
Vue.component('ol-map', require('./components/OlMap.vue'));

// Page Components
Vue.component('preferences', require('./components/Preferences.vue'))
Vue.component('roles', require('./components/Roles.vue'))
Vue.component('users', require('./components/Users.vue'))
Vue.component('user-preferences', require('./components/UserPreferences.vue'))

const app = new Vue({
    el: '#app',
    data: {
        tab: 'map',
        selectedContext: {},
        onSelectContext: function(selection) {
            app.$data.selectedContext = JSON.parse(JSON.stringify(selection));
        }
    }
});
