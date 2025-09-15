import { defineStore } from 'pinia';

import { kebabCase } from 'lodash';
import useAttributeStore from './attribute.js';
import useBibliographyStore from './bibliography.js';
import useEntityStore from './entity.js';
import useUserStore from './user.js';

import {
    checkAccess,
    fetchAttributes,
    fetchBibliography,
    fetchTags,
    fetchTopEntities,
    fetchPreData,
    fetchUser,
    searchConceptSelection,
    uploadPlugin,
    installPlugin,
    uninstallPlugin,
    updatePlugin,
    removePlugin,
    refreshSession,
} from '@/api.js';

import {
    router,
} from '@/bootstrap/router.js';

import {
    fetchGlobals,
} from '@/open_api.js';

import {
    only,
    slugify,
} from '@/helpers/helpers.js';

import {
    appendScript,
    removeScript,
} from '@/helpers/plugins.js';

const resetState = ctx => {
    ctx.appInitialized = false;
    ctx.colorSets = [];
    ctx.concepts = {};
    ctx.cachedConceptSelections = {};
    ctx.mainView = {
        tab: 'references',
    };
    ctx.plugins = [];
    ctx.pluginStores = {};
    ctx.systemPreferences = {};
    ctx.tags = [];
    ctx.version = {};
};

export const useSystemStore = defineStore('system', {
    state: _ => ({
        appInitialized: false,
        hasAnalysis: false,
        colorSets: [],
        concepts: {},
        cachedConceptSelections: {},
        mainView: {
            tab: 'references',
        },
        plugins: [],
        pluginStores: {},
        systemPreferences: {},
        registeredPluginSlots: {
            tab: [],
            tools: [],
            settings: [],
        },
        registeredPluginPreferences: {
            user: {},
            system: {},
        },
        tags: [],
        version: {},
        // TODO
        file: {},
        geometryTypes: [],
        datatypeData: {},
        accessPoints: {},
    }),
    getters: {
        translateConcept: state => url => {
            if(!url || !state.concepts) return url;
            if(!state.concepts[url]) return url;
            return state.concepts[url].label;
        },
        hasPreference: state => (key, property) => {
            const preference = useUserStore().getPreferenceByKey(key);
            if(preference) {
                return preference[property] || preference;
            }
            return false;
        },
        getPreference: state => key => {
            return useUserStore().getPreferenceByKey(key);
        },
        getProjectName: state => slug => {
            const projectName = useUserStore().getPreferenceByKey('prefs.project-name');
            return slug ? slugify(projectName) : projectName;
        },
        hasPlugin: state => nameId => {
            return state.plugins.some(plugin => plugin.name == nameId);
        },
        getPluginStore: state => id => {
            return state.pluginStores[id] ? state.pluginStores[id]() : {};
        },
        getSlotPlugins: state => slot => {
            const plugins = state.registeredPluginSlots;
            return slot ? plugins[slot] : plugins;
        },
        getDatatypeDataOf: state => key => state.datatypeData[key],
    },
    actions: {
        getConceptById(id) {
            return this.concepts[id];
        },
        setAppState(state) {
            this.appInitialized = state;
        },
        setMainViewTab(tab) {
            this.mainView.tab = tab;
        },
        setConcepts(concepts) {
            this.concepts = concepts;
        },
        addCachedConceptSelection(data) {
            this.cachedConceptSelections[data.id] = data.selection;
        },
        addPluginStore(id, store) {
            if(this.pluginStores[id]) {
                console.error(`A Plugin with id="${id}" already registered a store!`);
                return;
            }

            this.pluginStores[id] = defineStore(`plugin_${id}`, store);
        },
        async checkAuthState() {
            return await fetchUser();
        },
        async checkAccess(route) {
            const accessResponse = await checkAccess(route);
            if(accessResponse.status == 200 && accessResponse?.data?.redirect) {
                router.push(accessResponse.data.redirect);
                return false;
            }

            return true;
        },
        async setUser() {
            const userStore = useUserStore();

            const userData = await fetchUser();

            const loginSuccessful = userData.status == 'success';
            userStore.setLoginState(loginSuccessful);
            userStore.setActiveUser(loginSuccessful ? userData.data : {});
        },
        async initialize(locale) {
            resetState(this);

            const attributeStore = useAttributeStore();
            const bibliographyStore = useBibliographyStore();
            const entityStore = useEntityStore();
            const userStore = useUserStore();



            const preData = await fetchPreData();
            this.concepts = preData.concepts;
            this.systemPreferences = preData.system_preferences;
            this.colorSets = preData.colorSets;
            this.hasAnalysis = preData.analysis;
            this.datatypeData = preData.datatype_data;
            this.accessPoints = preData.accesspoints;
            entityStore.initializeEntityTypes(preData.entityTypes);
            userStore.setPreferences(preData.preferences);

            if(locale?.value) {
                locale.value = this.getPreference('prefs.gui-language');
            }

            attributeStore.setAttributes(preData.attributes);
            attributeStore.setAttributeSelections(preData.attributeSelections);
            userStore.setUsers(preData.users, preData.deleted_users);
            userStore.setRoles(preData.roles, preData.permissions, preData.presets);
            entityStore.initialize(preData.topEntities);
            bibliographyStore.initialize(preData.bibliography);
            this.setTags(preData.tags);
            this.version = preData.version;
            this.plugins = preData.plugins;
            this.geometryTypes = preData.geometryTypes;
            attributeStore.setAttributeTypes(preData.attributeTypes);
            this.appInitialized = true;
        },
        async initializeOpenAccess() {
            return fetchGlobals().then(data => {
                this.concepts = data.concepts;
                this.preferences = data.preferences;
                return data;
            });
        },
        addPlugin(data) {
            const idx = this.plugins.findIndex(p => p.id == data.id);
            if(idx > -1) {
                this.plugins[idx] = data;
            } else {
                this.plugins.push(data);
            }
        },
        updatePlugin(data) {
            const idx = this.plugins.findIndex(p => p.id == data.plugin_id);
            if(idx == -1) return;

            let plugin = null;
            let remove = false;

            if(data.deleted) {
                const delPlugins = this.plugins.splice(idx, 1);
                plugin = delPlugins[0];
                remove = true;
            } else {
                const props = only(data.properties, ['installed_at', 'updated_at', 'update_available', 'version']);
                const updPlugin = this.plugins[idx];
                for(let k in props) {
                    updPlugin[k] = props[k];
                }

                if(data.uninstalled) {
                    plugin = updPlugin;
                    remove = true;
                }
            }

            if(plugin && remove) {
                const slots = this.registeredPluginSlots;
                const pluginId = slugify(plugin.name);
                for(let k in slots) {
                    const slot = slots[k];
                    slot.forEach(slotPlugin => {
                        if(slotPlugin.of == pluginId) {
                            const spIdx = slot.findIndex(sp => sp.of == pluginId);
                            slot.splice(spIdx, 1);
                        }
                    });
                }
            }
        },
        registerPluginInSlot(data) {
            this.registeredPluginSlots[data.slot].push(data);
        },
        registerPluginPreference(data) {
            const category = this.registeredPluginPreferences[data.category];
            if(!category[data.subcategory]) {
                category[data.subcategory] = {
                    preferences: [],
                };
            }
            const pref = {
                of: data.of,
                title: data.label,
                label: data.key,
                component: data.component,
                default_value: data.default_value,
            };
            if(data.data) {
                pref.data = data.data;
            }
            if(data.custom_subcategory) {
                category[data.subcategory].custom = true;
                category[data.subcategory].title = data.custom_label;
            }
            category[data.subcategory].preferences.push(pref);
        },
        setTags(tags) {
            this.tags = tags;
        },
        setColorSets(data) {
            this.colorSets = data;
        },
        setAnalysis(state) {
            this.hasAnalysis = state;
        },
        setDatatypeData(data) {
            for(let k in data) {
                this.datatypeData[k] = data[k];
            }
        },
        async fetchConceptSelection(id) {
            const cachedSelection = this.cachedConceptSelections[id];
            if(!cachedSelection) {
                const selection = await searchConceptSelection(id);
                this.cachedConceptSelections[id] = selection || [];
            }
            return this.cachedConceptSelections[id];
        },
        async uploadPlugin(file) {
            return uploadPlugin(file).then(data => {
                this.addPlugin(data);
                return data;
            });
        },
        async installPlugin(id) {
            return installPlugin(id).then(data => {
                this.updatePlugin({
                    plugin_id: id,
                    properties: {
                        installed_at: data.plugin.installed_at,
                        updated_at: data.plugin.updated_at,
                    },
                });
                appendScript(data.install_location);
            });
        },
        async uninstallPlugin(id) {
            return uninstallPlugin(id).then(data => {
                const plugin = data.plugin;
                const kebabedName = kebabCase(plugin.name);
                
                
                // We use the window element here, as it resulted in an error, when
                // trying to import the SpPS variable diretly:
                // `Cannot access "router" before initialization`
                // [TODO] This should be fixed in the plugin system rework.
                if(window?.SpPS?.data?.plugins && window.SpPS.data.plugins[kebabedName]) {
                    delete window?.SpPS.data.plugins[kebabedName];
                }
                
                this.unregisterPluginSlots(kebabedName);
                this.unregisterPluginPreferences(kebabedName);
                this.updatePlugin({
                    plugin_id: id,
                    uninstalled: true,
                    properties: {
                        installed_at: null,
                        updated_at: plugin.updated_at,
                    },
                });
                removeScript(data.uninstall_location);
            });
        },
        async unregisterPluginSectionFrom(name, pluginId) {
            const slots = this[name];
            for(let k in slots) {
                const slot = slots[k];
                for(let i = slot.length - 1; i >= 0; i--) {
                    const plugin = slot[i];
                    if(plugin.of == pluginId) {
                        slot.splice(i, 1);
                    }
                }
            }
        },
        async unregisterPluginSlots(pluginId) {
            this.unregisterPluginSectionFrom('registeredPluginSlots', pluginId);
        },
        async unregisterPluginPreferences(pluginId) {
            this.unregisterPluginSectionFrom('registeredPluginPreferences', pluginId);
        },
        async patchPlugin(id) {
            return updatePlugin(id).then(data => {
                this.updatePlugin({
                    plugin_id: id,
                    properties: {
                        installed_at: data.installed_at,
                        updated_at: data.updated_at,
                        version: data.version,
                        changelog: data.changelog,
                        update_available: false,
                    },
                });
                return data;
            });
        },
        async removePlugin(id) {
            return removePlugin(id).then(data => {
                this.updatePlugin({
                    plugin_id: id,
                    deleted: true,
                });
                removeScript(data.uninstall_location);
            });
        }
    },
});

export default useSystemStore;