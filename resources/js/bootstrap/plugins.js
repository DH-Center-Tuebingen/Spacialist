import { only } from '@/helpers/helpers';
import store from './store.js';
import i18n from './i18n.js';
import {
    router,
    onBeforeRouteLeave,
    onBeforeRouteUpdate,
} from './router.js';
import {
    global_api,
    api_base,
} from '@/bootstrap/http.js';

import { addToast } from '@/plugins/toast.js';

import * as Vue from 'vue';

import * as filters from '@/helpers/filters.js';
import * as helpers from '@/helpers/helpers.js';
import * as colors from '@/helpers/colors.js';
import {
    getLayers,
    switchLayerPositions,
    changeLayerClass,
    getGeoJsonFormat,
    getWktFormat,
} from '@/helpers/map.js';
import {
    Node
} from '@/helpers/tree.js';
import {
    searchEntity,
} from '@/api.js';
import {
    iconList,
} from '@/bootstrap/font.js';
import {
    useModal,
} from 'vue-final-modal';

import RouteRootDummy from '@/components/plugins/RouteRootDummy.vue';
import * as buffer from 'buffer';

const defaultPluginOptions = {
    id: null,
    i18n: {}, // object of languages (key is e.g. 'en', 'de', 'fr', ...)
    routes: {},
    store: null,
    api: null,
}
const defaultSlotOptions = {
    of: null, // id of registered plugin
    slot: 'tab', // one of 'tab', 'tools' or 'settings'
    icon: '',
    label: '',
    key: null,
    component: null,
    componentTag: null,
    href: '', // for 'tools' and 'settings'
}

const {
    currentRoute,
    push,
} = router;

window.Vue = Vue;
window.Buffer = buffer.Buffer;

export const SpPS = {
    api: {
        filters: filters,
        helpers: helpers,
        colors: colors,
        mapHelpers: {
            getLayers,
            switchLayerPositions,
            changeLayerClass,
            getGeoJsonFormat,
            getWktFormat,
        },
        font: {
            iconList
        },
        searches: {
            entity: searchEntity,
        },
        store: store,
        router: {
            currentRoute,
            onBeforeRouteLeave,
            onBeforeRouteUpdate,
            push,
        },
        toast: addToast,
        http: global_api,
        endpoint: api_base,
        tree: {
            Node: Node,
        },
        modal: {
            useModal: useModal,
        },
    },
    data: {
        plugins: {},
        app: null,
        t: null,
    },
    initialize: (app, t) => {
        window.SpPS = SpPS;
        window.t = t;
        SpPS.data.app = app;
        SpPS.data.t = t;
    },
    registerI18n: (id, messages) => {
        for(let k in messages) {
            if(Object.keys(messages[k]).length != 1) {
                throw new Error(`Exactly one key is allowed for plugin i18n messages. You provided "${Object.keys(messages[k]).join('", "')}".`);
            }
            if(!messages[k][id]) {
                const wrongId = Object.keys(messages[k])[0];
                throw new Error(`Messages key must match your plugin id "${id}", but received "${wrongId}".`);
            }
            const {
                plugin,
                ...systemMessages
            } = i18n.global.messages.value[k];
            const pluginMessages = messages[k];
            const mergedMessages = {
                plugin: {
                    ...plugin,
                    ...pluginMessages,
                },
                ...systemMessages,
            };
            i18n.global.setLocaleMessage(k, mergedMessages);

        }
    },
    registerRoutes: (id, routes) => {
        const pluginRoute = {
            path: `/${id}`,
            name: id,
            component: RouteRootDummy,
            children: [],
            meta: {
                auth: true
            }
        };
        routes.forEach(r => {
            pluginRoute.children.push({
                path: r.path,
                component: r.component,
                name: `${id}_${r.path.replaceAll('/', '_')}`,
                children: r.children,
            });
        });
        router.addRoute(pluginRoute);
    },
    register: (options) => {
        if(!options.id) {
            throw new Error('Your plugin needs an id to be installed!');
        }
        if(!!SpPS.data.plugins[options.id]) {
            throw new Error('A plugin with that ID is already installed!');
        }
        const mergedOptions = {
            ...defaultPluginOptions,
            ...only(options, Object.keys(defaultPluginOptions)),
        };
        if(Object.keys(mergedOptions.i18n).length > 0) {
            SpPS.registerI18n(mergedOptions.id, mergedOptions.i18n);
        }
        if(mergedOptions.routes.length > 0) {
            SpPS.registerRoutes(mergedOptions.id, mergedOptions.routes);
        }
        if(mergedOptions.store) {
            store.registerModule(`pluginstore/${mergedOptions.id}`, mergedOptions.store);
        }
        SpPS.data.plugins[options.id] = mergedOptions;
    },
    intoSlot: (options) => {
        if(!options.of || !SpPS.data.plugins[options.of]) {
            throw new Error('This plugin part has no associated plugin or that plugin is not installed!');
        }
        if(!options.slot) {
            throw new Error('No slot for plugin provided!');
        }
        const mergedOptions = {
            ...defaultSlotOptions,
            ...only(options, Object.keys(defaultSlotOptions)),
        };
        if(mergedOptions.slot == 'tab' && !options.key) {
            mergedOptions.key = mergedOptions.of;
        }
        if(!mergedOptions.componentTag) {
            mergedOptions.componentTag = mergedOptions.key;
        }
        mergedOptions.componentTag = `sp-plugin-${mergedOptions.componentTag}`;
        if(!!mergedOptions.component) {
            if(typeof mergedOptions.component == 'string') {
                SpPS.data.app.component(mergedOptions.componentTag, {
                    template: mergedOptions.component,
                });
            } else {
                SpPS.data.app.component(mergedOptions.componentTag, mergedOptions.component);
            }
        }
        store.dispatch('registerPluginInSlot', mergedOptions);
    },
}

export default SpPS;