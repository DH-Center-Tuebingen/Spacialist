import { defineStore } from "pinia";
import { kebabCase } from 'lodash';

import {
    install,
    publishScript,
    remove,
    refresh,
    refreshInfo as refreshInfoApi,
    uninstall,
    update,
    upload,
} from '@/api/plugin.js';

import {
    appendScript,
    removeScript,
} from '@/helpers/plugins.js';

import {
    only,
    slugify,
} from '@/helpers/helpers.js';

import { isInstalled } from '@/helpers/plugins.js';
import { filterAllChildArrays } from "@/helpers/object";
import {
    appendScripts,
    appendScriptsAndStyles,
    getPluginTitle,
    removeScripts,
    removeScriptsAndStyles
} from "../../helpers/plugins";

export const usePluginStore = defineStore('plugin', {
    state: _ => ({
        plugins: [],
        stores: {},
        registeredAttributes: {},
        registeredPreferences: {
            user: {},
            system: {},
        },
        registeredSlots: {
            tab: [],
            tools: [],
            settings: [],
        },
    }),
    actions: {
        add(plugin) {
            const idx = this.plugins.findIndex(p => p.id == plugin.id);
            if(idx > -1) {
                this.plugins[idx] = plugin;
            } else {
                this.plugins.push(plugin);
            }
        },
        addStore(id, store) {
            if(this.pluginStores[id]) {
                console.error(`A Plugin with id="${id}" already registered a store!`);
                return;
            }

            this.pluginStores[id] = defineStore(`plugin_${id}`, store);
        },
        apply(data) {
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
                const slots = this.registeredSlots;
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
        async install(id) {
            return install(id).then(data => {
                this.apply({
                    plugin_id: id,
                    properties: {
                        installed_at: data.plugin.installed_at,
                        updated_at: data.plugin.updated_at,
                    },
                });

                appendScriptsAndStyles(data);
            });
        },
        // getPluginAttributeLabel(datatype) {
        //     const attributeType = this.registerAttribute.find(attributeType => {
        //         return attributeType.datatype == datatype && !!attributeType.plugin;
        //     });

        //     return attributeType?.label;
        // },
        /**
         * Helper function to compose the plugin slot name. This is used to avoid conflicts between plugins, that want to register in the same slot, 
         * e.g. "tab". The plugin slots are composed of the plugin name and the slot name, e.g. 'file-tab'.
         * 
         * @param {string} slotName - The name of the slot, e.g. "tab", "tools", "settings" 
         * @param {string} pluginName - The plugin slots are composed of the plugin name and the slot name to avoid conflicts, e.g. 'file-tab'. 
         * @returns {string} The composed slot name, e.g. 'file-tab'
         */
        getSlotName(slotName, pluginName) {
            return `${pluginName}-${slotName}`;
        },
        getSlotItems(slotName, pluginName = null) {
            if(pluginName) {
                slotName = this.getSlotName(slotName, pluginName);
            }

            if(!this.registeredSlots[slotName]) {
                console.error('Plugin slot does not exist', slotName);
                return [];
            }

            return this.registeredSlots[slotName] ?? [];
        },
        async publishScript(plugin) {
            console.log('Publishing script for plugin', plugin);
            if(plugin.scripts) {
                removeScripts(plugin.scripts);
            }
            const downloadUrl = await publishScript(plugin.id)
            appendScripts([downloadUrl]);
        },
        set(plugins) {
            this.plugins = plugins;
        },
        async refresh() {
            const plugins = await refresh()
            this.set(plugins);
        },
        async refreshInfo(plugin) {
            const data = await refreshInfoApi(plugin.id);
            const idx = this.plugins.find(p => p.id == data.id)
            if(idx > -1) {
                this.plugins[idx] = data;
            }
        },
        registerSlot(slot) {
            if(this.registeredSlots[slot]) {
                console.error('Plugin slot already exists', slot);
                return;
            }

            if(!this.registeredSlots[slot]) {
                this.registeredSlots[slot] = [];
            }
        },
        registerAttribute(data) {
            const { datatype } = data;
            console.trace('Registering plugin attribute with datatype:', datatype, data);


            if(!datatype) {
                console.error('Plugin attribute is missing datatype', data);
                return;
            }

            if(this.registeredAttributes[datatype]) {
                console.error('Plugin attribute already exists', datatype);
                return;
            }

            this.registeredAttributes[datatype] = data;
        },
        registerPreference(data) {
            const category = data.category;
            if(!category) {
                console.error('Plugin preference category does not exist', data.category);
                return;
            }


            const preferenceCategory = this.registeredPreferences[category];

            const subcategory = data.subcategory;
            if(!subcategory) {
                console.error('Plugin preference is missing subcategory', data);
                return;
            }

            if(!preferenceCategory[data.subcategory]) {
                preferenceCategory[data.subcategory] = {
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
                preferenceCategory[data.subcategory].custom = true;
                preferenceCategory[data.subcategory].title = data.custom_label;
            }
            preferenceCategory[data.subcategory].preferences.push(pref);
        },
        registerInSlot(data) {
            const slot = data.slot;
            if(!slot) {
                console.error('Plugin slot is missing', data);
                return;
            }

            if(!this.registeredSlots[slot]) {
                console.error('Plugin slot is not supported', slot);
                return;
            }

            this.registeredSlots[slot].push(data);
        },
        reset() {
            this.plugins = [];
            this.pluginStores = {};
        },
        async remove() {
            return remove(id).then(data => {
                this.apply({
                    plugin_id: id,
                    deleted: true,
                });
                removeScriptsAndStyles(data);
            });
        },
        async uninstall(id) {
            return uninstall(id).then(data => {
                const plugin = data.plugin;
                const kebabedName = kebabCase(plugin.name);

                // We use the window element here, as it resulted in an error, when
                // trying to import the SpPS variable diretly:
                // `Cannot access "router" before initialization`
                // [TODO] This should be fixed in the plugin system rework.
                if(window?.SpPS?.data?.plugins && window.SpPS.data.plugins[kebabedName]) {
                    delete window?.SpPS.data.plugins[kebabedName];
                }

                this.unregisterSlots(kebabedName);
                this.unregisterPreferences(kebabedName);
                this.apply({
                    plugin_id: id,
                    uninstalled: true,
                    properties: {
                        installed_at: null,
                        updated_at: plugin.updated_at,
                    },
                });

                removeScriptsAndStyles(data);
            });
        },
        async update(id) {
            return update(id).then(data => {
                this.apply({
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
        async upload(file) {
            return upload(file).then(data => {
                this.add(data);
                return data;
            });
        },
        unregisterSlots(id) {
            filterAllChildArrays(this.registeredSlots, (plugin) => plugin.of != id);
        },
        unregisterPreferences(id) {
            filterAllChildArrays(this.registeredPluginPreferences, (plugin) => plugin.of != id);
        },
    },
    getters: {
        pluginsSortedByTitle() {
            return Object.values(this.plugins).sort((a, b) => {

                if(isInstalled(a) && !isInstalled(b)) return -1;
                if(!isInstalled(a) && isInstalled(b)) return 1;

                const titleA = getPluginTitle(a);
                const titleB = getPluginTitle(b);

                return titleA.localeCompare(titleB);
            });
        }
    },
});

export default usePluginStore;