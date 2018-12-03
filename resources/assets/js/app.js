import Multiselect from 'vue-multiselect';
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import { fab } from '@fortawesome/free-brands-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';
import { fas } from '@fortawesome/free-solid-svg-icons';
import VModal from 'vue-js-modal';
import Axios from 'axios';
import VueRouter from 'vue-router';

import VueI18n from 'vue-i18n';
import en from './i18n/en';
import de from './i18n/de';

import App from './App.vue';
import Login from './components/Login.vue';
const MainView = () => import(/* webpackChunkName: "group-main" */ './components/MainView.vue')
const EntityDetail = () => import(/* webpackChunkName: "group-main" */ './components/EntityDetail.vue')
const ReferenceModal = () => import(/* webpackChunkName: "group-main" */ './components/EntityReferenceModal.vue')
// Tools
const Bibliography = () => import(/* webpackChunkName: "group-bib" */ './components/BibliographyTable.vue')
const BibliographyItemModal = () => import(/* webpackChunkName: "group-bib" */ './components/BibliographyItemModal.vue')
// Settings
import Users from './components/Users.vue';
import Roles from './components/Roles.vue';
import Preferences from './components/Preferences.vue';
import UserPreferences from './components/UserPreferences.vue';
const DataModel = () => import(/* webpackChunkName: "group-bib" */ './components/DataModel.vue')
const DataModelDetailView = () => import(/* webpackChunkName: "group-bib" */ './components/DataModelDetailView.vue')

import VueUploadComponent from 'vue-upload-component';
import moment from 'moment';
import VCalendar from 'v-calendar';
import VeeValidate from 'vee-validate';
import Notifications from 'vue-notification';
import SpacialistPluginSystem from './plugin.js';
import VTooltip from 'v-tooltip';

library.add(fas, far, fab);
dom.watch(); // search for <i> tags to replace with <svg>

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

const PQueue = require('p-queue');
require('typeface-raleway');
require('typeface-source-code-pro');
require('popper.js');
require('bootstrap');
window.Vue = require('vue');
window._ = require('lodash');
$ = jQuery  = window.$ = window.jQuery = require('jquery');
require('./globals.js');

Axios.defaults.baseURL = 'api/v1';

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
Vue.queue = Vue.prototype.$httpQueue = window.$httpQueue = new PQueue({concurrency: 1});
Vue.prototype.$http = Axios;
Vue.axios = Axios;
Vue.use(VueRouter);

Vue.use(SpacialistPluginSystem);

Vue.use(VueI18n);
Vue.use(VModal, {dynamic: true});
Vue.use(VeeValidate);
Vue.use(Notifications);
Vue.use(VCalendar, {
    firstDayOfWeek: 2,
    popoverVisibility: 'focus'
});
Vue.use(VTooltip, {
    popover: {
        defaultBaseClass: 'popover',
        defaultInnerClass: 'popover-body',
        defaultArrowClass: 'arrow'
    }
});

const router = new VueRouter({
    scrollBehavior(to, from, savedPosition) {
        return {
            x: 0,
            y: 0
        };
    },
    routes: [
        // deprecated pre-0.6 routes
        {
            path: '/s',
            redirect: { name: 'home' },
            children: [
                {
                    path: 'context/:id',
                    redirect: to => {
                        return {
                            name: 'entitydetail',
                            params: {
                                id: to.params.id
                            }
                        }
                    },
                    children: [{
                        path: 'sources/:aid',
                        redirect: to => {
                            return {
                                name: 'entityrefs',
                                params: {
                                    id: to.params.id,
                                    aid: to.params.aid,
                                }
                            }
                        }
                    }]
                },
                {
                    path: 'f/:id',
                    redirect: to => {
                        return {
                            name: 'file',
                            params: {
                                id: to.params.id
                            }
                        }
                    }
                },
                {
                    path: 'user',
                    redirect: { name: 'users' },
                    // TODO user edit route (redirect to users or add it)
                },
                {
                    path: 'role',
                    redirect: { name: 'roles' },
                    // TODO role edit route (redirect to roles or add it)
                },
                {
                    path: 'editor/data-model',
                    redirect: { name: 'dme' },
                    children: [{
                        path: 'contexttype/:id',
                        redirect: to => {
                            return {
                                name: 'dmdetail',
                                params: {
                                    id: to.params.id
                                }
                            }
                        }
                    }]
                },
                {
                    path: 'preferences/:id',
                    redirect: to => {
                        return {
                            name: 'userpreferences',
                            params: {
                                id: to.params.id
                            }
                        }
                    }
                }
            ]
        },
        {
            path: '/',
            name: 'home',
            component: MainView,
            children: [
                {
                    path: 'e/:id',
                    name: 'entitydetail',
                    component: EntityDetail,
                    children: [
                        {
                            path: 'refs/:aid',
                            name: 'entityrefs',
                            component: ReferenceModal
                        }
                    ]
                }
            ],
            meta: {
                auth: true
            }
        },
        // Tools
        {
            path: '/bibliography',
            name: 'bibliography',
            component: Bibliography,
            children: [
                {
                    path: 'edit/:id',
                    name: 'bibedit',
                    component: BibliographyItemModal
                },
                {
                    path: 'new',
                    name: 'bibnew',
                    component: BibliographyItemModal
                }
            ],
            meta: {
                auth: true
            }
        },
        {
            path: '/login',
            name: 'login',
            component: Login,
            meta: {
                auth: false
            }
        },
        // Settings
        {
            path: '/mg/users',
            name: 'users',
            component: Users,
            meta: {
                auth: true
            }
        },
        {
            path: '/mg/roles',
            name: 'roles',
            component: Roles,
            meta: {
                auth: true
            }
        },
        {
            path: '/editor/dm',
            name: 'dme',
            component: DataModel,
            children: [
                {
                    path: 'et/:id',
                    name: 'dmdetail',
                    component: DataModelDetailView
                }
            ],
            meta: {
                auth: true
            }
        },
        {
            path: '/preferences',
            name: 'preferences',
            component: Preferences,
            meta: {
                auth: true
            }
        },
        {
            path: '/preferences/u/:id',
            name: 'userpreferences',
            component: UserPreferences,
            meta: {
                auth: true
            }
        },
    ]
});

Vue.router = router;
App.router = Vue.router;

Axios.interceptors.response.use(response => {
    return response;
}, error => {
    if(error.response.status == 401) {
        let redirectQuery = {};
        // Only append redirect query if from another route than login
        // to prevent recursivly appending current route's full path
        // on reloading login page
        if(Vue.router.currentRoute.name != 'login') {
            redirectQuery.redirect = Vue.router.currentRoute.fullPath;
        }
        Vue.auth.logout({
            redirect: {
                name: 'login',
                query: redirectQuery
            }
        });
    } else {
        Vue.prototype.$throwError(error);
    }
    return Promise.reject(error);
});

const messages = {
    en: en,
    de: de
};

const i18n = new VueI18n({
    locale: navigator.language,
    fallbackLocale: 'en',
    messages
});
Vue.i18n = i18n;

Vue.use(require('@websanova/vue-auth'), {
   auth: require('@websanova/vue-auth/drivers/auth/bearer.js'),
   http: require('./queued-axios-1.x-driver.js'),
   router: require('@websanova/vue-auth/drivers/router/vue-router.2.x.js'),
   forbiddenRedirect: {
       name: 'home'
   },
   notFoundRedirect: {
       name: 'home'
   },
});

// Imported Components
Vue.component('multiselect', Multiselect);
Vue.component('file-upload', VueUploadComponent);

// Extended Components
Vue.component('global-search', require('./components/GlobalSearch.vue'));
Vue.component('entity-search', require('./components/EntitySearch.vue'));
Vue.component('label-search', require('./components/LabelSearch.vue'));
Vue.component('csv-table', require('./components/CsvTable.vue'));

// Reusable Components
Vue.component('attributes', require('./components/AttributeList.vue'));
Vue.component('entity-tree', require('./components/EntityTree.vue'));
Vue.component('entity-types', require('./components/EntityTypeList.vue'));
Vue.component('ol-map', require('./components/OlMap.vue'));
Vue.component('color-gradient', require('./components/Gradient.vue'));

// Page Components
Vue.component('entity-reference-modal', require('./components/EntityReferenceModal.vue'));
Vue.component('discard-changes-modal', require('./components/DiscardChangesModal.vue'));
Vue.component('about-dialog', require('./components/About.vue'));
Vue.component('error-modal', require('./components/Error.vue'));

Vue.component('data-analysis', require('./components/plugins/DataAnalysis.vue'));

// Filter
Vue.filter('date', function(value, format = 'DD.MM.YYYY HH:mm', useLocale = false, isDateString) {
    if(value) {
        let mom;
        if(isDateString) {
            mom = moment(value);
        } else {
            mom = moment.unix(Number(value));
        }
        if(!useLocale) {
            mom = mom.utc();
        }
        return mom.format(format);
    }
});
Vue.filter('datestring', function(value, useLocale = true) {
    if(value) {
        let mom = moment.unix(Number(value));
        if(useLocale) {
            return mom.toLocaleString();
        }
        return mom.utc().toString();
    }
});
Vue.filter('time', function(value, withHours) {
    if(value) {
        let hours = 0;
        let rHours = 0;
        if(withHours) {
            hours = parseInt(Math.floor(value / 3600));
            rHours = hours * 3600;
        }
        const minutes = parseInt(Math.floor((value-rHours) / 60));
        const rMin = minutes * 60;
        const seconds = parseInt(Math.floor(value - rHours - rMin));

        const paddedH = hours > 9 ? hours : `0${hours}`;
        const paddedM = minutes > 9 ? minutes : `0${minutes}`;
        const paddedS = seconds > 9 ? seconds : `0${seconds}`;

        if(withHours) {
            return `${paddedH}:${paddedM}:${paddedS}`;
        } else {
            return `${paddedM}:${paddedS}`;
        }
    } else {
        if(withHours) {
            return '00:00:00';
        } else {
            return '00:00';
        }
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

const app = new Vue({
    el: '#app',
    i18n: i18n,
    router: router,
    render: h => {
        return h(App, {
            props: {
                onInit: _ => {
                    app.init();
                }
            }
        })
    },
    beforeMount: function() {
        this.init();
    },
    methods: {
        init() {
            Vue.prototype.$httpQueue.add(() =>
            Axios.get('pre').then(response =>  {
                this.preferences = response.data.preferences;
                this.concepts = response.data.concepts;
                this.entityTypes = response.data.entityTypes;
                app.$auth.ready(_ => {
                    // Check if user is logged in and set preferred language
                    // instead of browser default
                    if(app.$auth.check()) {
                        Vue.i18n.locale = this.preferences['prefs.gui-language'];
                    }
                });
                const extensions = this.preferences['prefs.load-extensions'];
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
            }));
        }
    },
    data() {
        return {
            selectedEntity: {},
            onSelectEntity: function(selection) {
                app.$data.selectedEntity = Object.assign({}, selection);
            },
            preferences: {},
            concepts: {},
            entityTypes: {},
            plugins: {},
            onInit: null
        }
    }
});
