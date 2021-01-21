import Multiselect from 'vue-multiselect';
import { library, dom } from '@fortawesome/fontawesome-svg-core';
import {
    faFacebookSquare,
    faGithub,
    faHtml5,
    faLaravel,
    faOrcid,
    faVuejs
} from '@fortawesome/free-brands-svg-icons';
import {
    faClipboard,
    faQuestionCircle
} from '@fortawesome/free-regular-svg-icons';
import {
    faAdjust,
    faAngleDoubleLeft,
    faAngleDoubleRight,
    faAngleDown,
    faAngleLeft,
    faAngleRight,
    faAngleUp,
    faBan,
    faBell,
    faBinoculars,
    faBolt,
    faBook,
    faBookmark,
    faCalculator,
    faCalendarAlt,
    faCamera,
    faCaretDown,
    faCaretUp,
    faChartBar,
    faChartPie,
    faCheck,
    faCheckCircle,
    faCircle,
    faClock,
    faClone,
    faCog,
    faCogs,
    faComment,
    faComments,
    faCopy,
    faCopyright,
    faCubes,
    faDotCircle,
    faDownload,
    faDrawPolygon,
    faEdit,
    faEllipsisH,
    faEnvelope,
    faExchangeAlt,
    faExclamation,
    faExclamationCircle,
    faExpand,
    faExternalLinkAlt,
    faEyeSlash,
    faFile,
    faFileAlt,
    faFileArchive,
    faFileAudio,
    faFileCode,
    faFileDownload,
    faFileExcel,
    faFileExport,
    faFileImport,
    faFileMedicalAlt,
    faFilePdf,
    faFilePowerpoint,
    faFileUpload,
    faFileVideo,
    faFileWord,
    faFolder,
    faGlobeAfrica,
    faIdBadge,
    faInfoCircle,
    faLayerGroup,
    faLightbulb,
    faLink,
    faList,
    faLongArrowAltDown,
    faLongArrowAltLeft,
    faLongArrowAltRight,
    faLongArrowAltUp,
    faMagic,
    faMapMarkedAlt,
    faMapMarkerAlt,
    faMicrochip,
    faMobileAlt,
    faMonument,
    faPalette,
    faPaperPlane,
    faPause,
    faPaw,
    faPlay,
    faPlus,
    faPrint,
    faQuestion,
    faRedoAlt,
    faReply,
    faRoad,
    faRuler,
    faRulerCombined,
    faSave,
    faSearch,
    faSearchPlus,
    faShieldAlt,
    faSignOutAlt,
    faSitemap,
    faSlidersH,
    faSort,
    faSortAlphaDown,
    faSortAlphaUp,
    faSortAmountDown,
    faSortAmountUp,
    faSortDown,
    faSortNumericDown,
    faSortNumericUp,
    faSortUp,
    faSpinner,
    faStop,
    faStopwatch,
    faSun,
    faSync,
    faTable,
    faTags,
    faTasks,
    faTh,
    faTimes,
    faTrash,
    faUnderline,
    faUndo,
    faUndoAlt,
    faUnlink,
    faUnlockAlt,
    faUser,
    faUserClock,
    faUserCheck,
    faUserCog,
    faUserEdit,
    faUsers,
    faUserTimes,
    faVolumeMute,
    faVolumeUp,
    faDatabase,
    faIndent
} from '@fortawesome/free-solid-svg-icons';
import VModal from 'vue-js-modal';
import Axios from 'axios';
import VueRouter from 'vue-router';
import auth from '@websanova/vue-auth';
import authBearer from '@websanova/vue-auth/drivers/auth/bearer.js';
import authHttp from './queued-axios-1.x-driver.js';
import authRouter  from '@websanova/vue-auth/drivers/router/vue-router.2.x.js';

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
import UserNotifications from './components/UserNotifications.vue';
import UserActivity from './components/UserActivity.vue';
import GlobalActivity from './components/GlobalActivity.vue';
import UserProfile from './components/UserProfile.vue';
const DataModel = () => import(/* webpackChunkName: "group-bib" */ './components/DataModel.vue')
const DataModelDetailView = () => import(/* webpackChunkName: "group-bib" */ './components/DataModelDetailView.vue')

import VueUploadComponent from 'vue-upload-component';
import * as dayjs from 'dayjs';
import DatePicker from 'vue2-datepicker';
import VeeValidate from 'vee-validate';
import Notifications from 'vue-notification';
import SpacialistPluginSystem from './plugin.js';
import VueScrollTo from 'vue-scrollto';
import InfiniteLoading from 'vue-infinite-loading';
import VueHighlightJS from 'vue-highlightjs';
import 'highlight.js/styles/atom-one-dark.css';

import { EventBus } from './event-bus.js';

library.add(
    faFacebookSquare,
    faGithub,
    faHtml5,
    faLaravel,
    faVuejs,
    faClipboard,
    faQuestionCircle,
    faAdjust,
    faAngleDoubleLeft,
    faAngleDoubleRight,
    faAngleDown,
    faAngleLeft,
    faAngleRight,
    faAngleUp,
    faBan,
    faBell,
    faBinoculars,
    faBolt,
    faBook,
    faBookmark,
    faCalculator,
    faCalendarAlt,
    faCamera,
    faCaretDown,
    faCaretUp,
    faChartBar,
    faChartPie,
    faCheck,
    faCheckCircle,
    faCircle,
    faClock,
    faClone,
    faCog,
    faCogs,
    faComment,
    faComments,
    faCopy,
    faCopyright,
    faCubes,
    faDatabase,
    faDotCircle,
    faDownload,
    faDrawPolygon,
    faEdit,
    faEllipsisH,
    faEnvelope,
    faExchangeAlt,
    faExclamation,
    faExclamationCircle,
    faExpand,
    faExternalLinkAlt,
    faEyeSlash,
    faFile,
    faFileAlt,
    faFileArchive,
    faFileAudio,
    faFileCode,
    faFileDownload,
    faFileExcel,
    faFileExport,
    faFileImport,
    faFileMedicalAlt,
    faFilePdf,
    faFilePowerpoint,
    faFileUpload,
    faFileVideo,
    faFileWord,
    faFolder,
    faGlobeAfrica,
    faIdBadge,
    faIndent,
    faInfoCircle,
    faLayerGroup,
    faLightbulb,
    faLink,
    faList,
    faLongArrowAltDown,
    faLongArrowAltLeft,
    faLongArrowAltRight,
    faLongArrowAltUp,
    faMagic,
    faMapMarkedAlt,
    faMapMarkerAlt,
    faMicrochip,
    faMobileAlt,
    faMonument,
    faOrcid,
    faPalette,
    faPaperPlane,
    faPause,
    faPaw,
    faPlay,
    faPlus,
    faPrint,
    faQuestion,
    faQuestionCircle,
    faRedoAlt,
    faReply,
    faRoad,
    faRuler,
    faRulerCombined,
    faSave,
    faSearch,
    faSearchPlus,
    faShieldAlt,
    faSignOutAlt,
    faSitemap,
    faSlidersH,
    faSort,
    faSortAlphaDown,
    faSortAlphaUp,
    faSortAmountDown,
    faSortAmountUp,
    faSortDown,
    faSortNumericDown,
    faSortNumericUp,
    faSortUp,
    faSpinner,
    faStop,
    faStopwatch,
    faSun,
    faSync,
    faTable,
    faTags,
    faTasks,
    faTh,
    faTimes,
    faTrash,
    faUnderline,
    faUndo,
    faUndoAlt,
    faUnlink,
    faUnlockAlt,
    faUser,
    faUserClock,
    faUserCheck,
    faUserCog,
    faUserEdit,
    faUserTimes,
    faUsers,
    faVolumeMute,
    faVolumeUp
);
dom.watch(); // search for <i> tags to replace with <svg>

// Override vue-routers push method to catch (and "suppress") it's errors
const originalPush = VueRouter.prototype.push
VueRouter.prototype.push = function push(location, onResolve, onReject) {
    if(onResolve || onReject) {
        return originalPush.call(this, location, onResolve, onReject);
    }
    return originalPush.call(this, location)
        .catch(err =>
            console.log("Error while pushing new route")
        );
}

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

const {default: PQueue} = require('p-queue');
require('typeface-raleway');
require('typeface-source-code-pro');
require('popper.js');
require('bootstrap');
window.Vue = require('vue');
window._clone = require('lodash/clone');
window._cloneDeep = require('lodash/cloneDeep');
window._orderBy = require('lodash/orderBy');
window._debounce = require('lodash/debounce');
$ = jQuery  = window.$ = window.jQuery = require('jquery');
require('./globals.js');

let relativeTime = require('dayjs/plugin/relativeTime');
let utc = require('dayjs/plugin/utc')
dayjs.extend(relativeTime);
dayjs.extend(utc);

// Create Axios instance for external (API) calls
Vue.prototype.$externalHttp = Axios.create({
  headers: {
    common: {},
  },
});

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
Vue.use(DatePicker);
Vue.use(VueScrollTo);
Vue.use(InfiniteLoading);
Vue.use(VueHighlightJS);

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
            path: '/activity/g',
            name: 'globalactivity',
            component: GlobalActivity,
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
        {
            path: '/notifications/:id',
            name: 'notifications',
            component: UserNotifications,
            meta: {
                auth: true
            }
        },
        {
            path: '/activity/u',
            name: 'useractivity',
            component: UserActivity,
            meta: {
                auth: true
            }
        },
        {
            path: '/profile',
            name: 'userprofile',
            component: UserProfile,
            meta: {
                auth: true
            }
        },
    ]
});
// Workaround to load plugin pages, whose routes
// are loaded after the initial page load
router.onReady(() => {
    const matchedComponents = router.getMatchedComponents(router.history.current.fullPath);
    if(matchedComponents.length == 1) {
        router.push(router.history.current.fullPath);
    }
});

Vue.router = router;
App.router = Vue.router;

Axios.interceptors.response.use(response => {
    return response;
}, error => {
    const code = error.response.status;
    switch(code) {
        case 401:
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
            break;
        case 400:
        case 422:
            // don't do anything. Handle these types at caller
            break;
        default:
            Vue.prototype.$throwError(error);
            break;
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

Vue.use(auth, {
   auth: authBearer,
   http: authHttp,
   router: authRouter,
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
import GlobalSearch from './components/GlobalSearch.vue';
import EntitySearch from './components/EntitySearch.vue';
import EntityTypeSearch from './components/EntityTypeSearch.vue';
import LabelSearch from './components/LabelSearch.vue';
import AttributeSearch from './components/AttributeSearch.vue';
import CsvTable from './components/CsvTable.vue';

// Reusable Components
import ActivityLog from './components/ActivityLog.vue';
import ActivityLogFilter from './components/ActivityLogFilter.vue';
import UserAvatar from './components/UserAvatar.vue';
import Attributes from './components/AttributeList.vue';
import EntityTree from './components/EntityTree.vue';
import EntityTypes from './components/EntityTypeList.vue';
import OlMap from './components/OlMap.vue';
import ColorGradient from './components/Gradient.vue';
import EntityBreadcrumbs from './components/EntityBreadcrumbs.vue';
import CommentList from './components/CommentList.vue';
import NotificationBody from './components/NotificationBody.vue';
import EmojiPicker from './components/EmojiPicker.vue';

// Page Components
import EntityReferenceModal from './components/EntityReferenceModal.vue';
import DiscardChangesModal from './components/DiscardChangesModal.vue';
import AboutDialog from './components/About.vue';
import ErrorModal from './components/Error.vue';
import UserInfoModal from './components/modals/UserInfo.vue';

Vue.component('global-search', GlobalSearch);
Vue.component('entity-search', EntitySearch);
Vue.component('entity-type-search', EntityTypeSearch);
Vue.component('label-search', LabelSearch);
Vue.component('attribute-search', AttributeSearch);
Vue.component('csv-table', CsvTable);
Vue.component('activity-log', ActivityLog);
Vue.component('activity-log-filter', ActivityLogFilter);
Vue.component('user-avatar', UserAvatar);
Vue.component('attributes', Attributes);
Vue.component('entity-tree', EntityTree);
Vue.component('entity-types', EntityTypes);
Vue.component('ol-map', OlMap);
Vue.component('color-gradient', ColorGradient);
Vue.component('entity-breadcrumbs', EntityBreadcrumbs);
Vue.component('comment-list', CommentList);
Vue.component('notification-body', NotificationBody);
Vue.component('emoji-picker', EmojiPicker);
Vue.component('entity-reference-modal', EntityReferenceModal);
Vue.component('discard-changes-modal', DiscardChangesModal);
Vue.component('about-dialog', AboutDialog);
Vue.component('error-modal', ErrorModal);
Vue.component('user-info-modal', UserInfoModal);

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
                this.users = response.data.users;
                // Check if user is logged in and set preferred language
                // instead of browser default
                if(!app.$auth.ready()) {
                    app.$auth.load().then(_ => {
                        app.$updateLanguage();
                    });
                } else {
                    app.$updateLanguage();
                }
                const extensions = this.preferences['prefs.load-extensions'];
                for(let k in extensions) {
                    if(!extensions[k] || (k != 'map' && k != 'files')) {
                        console.log("Skipping plugin " + k);
                        continue;
                    }
                    let name = k;
                    let nameExt = name + '.js';
                    import('./plugins/' + nameExt).then(data => {
                        Vue.use(data.default);
                        this.$addEnabledPlugin(name);
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
            users: [],
            plugins: {},
            onInit: null
        }
    }
});
