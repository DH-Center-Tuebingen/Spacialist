import Multiselect from 'vue-multiselect';
import fontawesome from '@fortawesome/fontawesome';
import regular from '@fortawesome/fontawesome-free-regular';
import solid from '@fortawesome/fontawesome-free-solid';
import brands from '@fortawesome/fontawesome-free-brands';
import VModal from 'vue-js-modal';
import Axios from 'axios';
import VueUploadComponent from 'vue-upload-component';
import moment from 'moment';
import VeeValidate from 'vee-validate';
import Notifications from 'vue-notification';
import SpacialistPluginSystem from './plugin.js';

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
Vue.use(VeeValidate);
Vue.use(Notifications);
Vue.use(SpacialistPluginSystem);

// Imported Components
Vue.component('multiselect', Multiselect);
Vue.component('file-upload', VueUploadComponent);
// Extended Components
Vue.component('context-search', require('./components/ContextSearch.vue'));
Vue.component('label-search', require('./components/LabelSearch.vue'));
Vue.component('csv-table', require('./components/CsvTable.vue'));

// Reusable Components
Vue.component('attributes', require('./components/AttributeList.vue'));
Vue.component('bibliography', require('./components/BibliographyTable.vue'));
Vue.component('context-tree', require('./components/ContextTree.vue'));
Vue.component('context-types', require('./components/ContextTypeList.vue'));
Vue.component('layer', require('./components/LayerList.vue'));
Vue.component('ol-map', require('./components/OlMap.vue'));

// Page Components
Vue.component('main-view', require('./components/MainView.vue'));
Vue.component('preferences', require('./components/Preferences.vue'));
Vue.component('roles', require('./components/Roles.vue'));
Vue.component('users', require('./components/Users.vue'));
Vue.component('user-preferences', require('./components/UserPreferences.vue'));
Vue.component('data-model', require('./components/DataModel.vue'));
Vue.component('layer-editor', require('./components/LayerEditor.vue'));
Vue.component('about-dialog', require('./components/About.vue'));

Vue.component('gis', require('./components/plugins/Gis.vue')); // TODO

// Filter
Vue.filter('date', function(value, format) {
    if(!format) format = 'DD.MM.YYYY hh:mm';
    if(value) {
        return moment.unix(Number(value)).utc().format(format);
    }
});
Vue.filter('bytes', function(value, precision) {
    if(!value) return value;
    precision = precision || 2;

    let units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    let bytes = parseFloat(value);

    let unitIndex;
    if(!isFinite(value) || isNaN(bytes)) {
        unitIndex = 0;
    } else {
        unitIndex = Math.floor(Math.log(bytes) / Math.log(1024));
    }

    let unit = units[unitIndex];
    let sizeInUnit = bytes / Math.pow(1024, unitIndex);
    return sizeInUnit.toFixed(precision) +  ' ' + unit;
});
Vue.filter('bibtexify', function(value, type) {
    let rendered = "<pre><code>";
    if(type) {
        rendered += "@"+type+" {";
        for(let k in value) {
            if(value[k] == null || value[k] == '') continue;
            rendered += "    <br />";
            rendered += "    " + k + " = \"" + value[k] + "\"";
        }
        rendered += "<br />";
        rendered += "}";
    }
    rendered += "</code></pre>";
    return rendered;
});

Vue.prototype.$showToast = function(title, text, type, duration) {
    type = type || 'info'; // success, info, warn, error
    duration = duration || 2000;
    this.$notify({
        group: 'spacialist',
        title: title,
        text: text,
        type: type,
        duration: duration
    });
};

const app = new Vue({
    el: '#app',
    mounted: function() {
        this.preferences = JSON.parse(this.$el.attributes.preferences.value);
        let extensions = this.preferences['prefs.load-extensions'];
        for(let k in extensions) {
            if(!extensions[k] || (k != 'map' && k != 'files')) {
                console.log("Skipping plugin " + k);
                continue;
            }
            let name = k;
            let nameExt = name + '.js';
            System.import('./plugins/' + nameExt).then(function(data) {
                Vue.use(data.default);
            });
        }
        this.$getSpacialistPlugins('plugins');
    },
    data: {
        selectedContext: {},
        onSelectContext: function(selection) {
            app.$data.selectedContext = Object.assign({}, selection);
        },
        preferences: {},
        plugins: {}
    },
    methods: {
        showAboutModal() {
            this.$modal.show('about-modal');
        }
    }
});
