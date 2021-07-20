import { only } from "../helpers/helpers";
import store from "./store.js";
import i18n from "./i18n.js";

const defaultPluginOptions = {
    id: null,
    i18n: {}, // object of languages (key is e.g. 'en', 'de', 'fr', ...)
}
const defaultSlotOptions = {
    of: null, // id of registered plugin
    slot: 'tools', // one of 'tab', 'tools' or 'settings'
    icon: '',
    label: '',
    key: null,
    component: null,
    componentTag: null,
}

export const SpPS = {
    data: {
        plugins: [],
        app: null,
    },
    initialize: app => {
        global.SpPS = SpPS;
        SpPS.data.app = app;
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
    register: (options) => {
        if(!options.id) {
            throw new Error('Your plugin needs an id to be installed!');
        }
        if(SpPS.data.plugins.findIndex(p => p.id == options.id) > -1) {
            throw new Error('A plugin with that ID is already installed!');
        }
        const mergedOptions = {
            ...defaultPluginOptions,
            ...only(options, Object.keys(defaultPluginOptions)),
        };
        if(Object.keys(mergedOptions.i18n).length > 0) {
            SpPS.registerI18n(mergedOptions.id, mergedOptions.i18n);
        }
        SpPS.data.plugins.push(mergedOptions);
    },
    intoSlot: (options) => {
        if(!options.of || SpPS.data.plugins.findIndex(p => p.id == options.of) == -1) {
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
    }
}

export default SpPS;