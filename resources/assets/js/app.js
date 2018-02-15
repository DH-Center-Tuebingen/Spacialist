  import Multiselect from 'vue-multiselect';

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// require('./bootstrap');
require('popper.js')
require('bootstrap')

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('context-tree', require('./components/ContextTree.vue'));
Vue.component('ol-map', require('./components/OlMap.vue'));
Vue.component('preferences', require('./components/Preferences.vue'))
Vue.component('users', require('./components/Users.vue'))
Vue.component('roles', require('./components/Roles.vue'))
Vue.component('multiselect', Multiselect);

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
