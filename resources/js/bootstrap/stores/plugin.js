import { defineStore } from "pinia";
import { kebabCase } from 'lodash';

import {
    install,
    publishScript,
    remove,
    refresh,
    refreshInfo as refreshInfoApi,
    uninstall,
    upload,
} from '@/api/plugin.js';

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
            if(this.stores[id]) {
                console.error(`A Plugin with id="${id}" already registered a store!`);
                return;
            }

            this.stores[id] = defineStore(`plugin_${id}`, store);
        },
        // apply(id, data, method = null) {
        //     const idx = this.plugins.findIndex(p => p.id == id);
        //     if(idx == -1) return;

        //     let plugin = null;
        //     let remove = false;

        //     if(method === 'delete') {
        //         const plugin = this.plugins.splice(idx, 1)?.[0];
        //         remove = true;
        //     } else {
        //         const props = only(data, ['installed_at', 'updated_at', 'update_available', 'version']);
        //         const updatedPlugin = this.plugins[idx];
        //         for(let k in props) {
        //             updatedPlugin[k] = props[k];
        //         }

        //         if(method === "uninstall") {
        //             plugin = updatedPlugin;
        //             remove = true;
        //         }
        //     }

        //     if(remove) {
        //         this.removeSlotsOf(plugin);
        //     }
        // },
        async install(id) {
            const data = await install(id)
            this.overridePlugin(data.plugin)
            appendScriptsAndStyles(data);
        },
        getPluginById(id) {
            return this.plugins.find(plugin => plugin.id === id)
        },
        overridePlugin(plugin) {
            const pluginIndex = this.plugins.findIndex(cachedPlugin => cachedPlugin.id === plugin.id)
            if(pluginIndex != -1) {
                this.plugins.splice(pluginIndex, 1, plugin)
            } else {
                this.plugins.push(plugin)
            }
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
            this.stores = {};
        },
        async remove(id) {
            const data = await remove(id);
            removeScriptsAndStyles(data);
            this.removePlugin(id);
        },
        async uninstall(id) {
            const data = await uninstall(id)
            const plugin = data.plugin;
            const kebabedName = kebabCase(plugin.name);
            this.overridePlugin(plugin)


            // We use the window element here, as it resulted in an error, when
            // trying to import the SpPS variable diretly:
            // `Cannot access "router" before initialization`
            // [TODO] This should be fixed in the plugin system rework.
            if(window?.SpPS?.data?.plugins && window.SpPS.data.plugins[kebabedName]) {
                delete window?.SpPS.data.plugins[kebabedName];
            }

            this.unregisterSlots(kebabedName);
            this.unregisterPreferences(kebabedName);
            removeScriptsAndStyles(data);
        },
        async upload(file) {
            const response = await upload(file)
            const plugin = response.plugin
            this.apply(plugin.id, data)
            return response;
        },
        unregisterSlots(id) {
            filterAllChildArrays(this.registeredSlots, (plugin) => plugin.of != id);
        },
        // removeSlotsOf(plugin) {
        //     const slots = this.registeredSlots;
        //     const pluginId = slugify(plugin.name);
        //     for(let k in slots) {
        //         const slot = slots[k];
        //         slot.forEach(slotPlugin => {
        //             if(slotPlugin.of == pluginId) {
        //                 const spIdx = slot.findIndex(sp => sp.of == pluginId);
        //                 slot.splice(spIdx, 1);
        //             }
        //         });
        //     }
        // },
        unregisterPreferences(id) {
            filterAllChildArrays(this.registeredPluginPreferences, (plugin) => plugin.of != id);
        },
        removePlugin(id) {
            const pluginIndex = this.plugins.findIndex(cachedPlugin => cachedPlugin.id === id)
            if(pluginIndex != -1) {
                this.plugins.splice(pluginIndex, 1, this.plugins[pluginIndex])
            }
        }
    },
    getters: {
        pluginsSortedByTitle() {
            return this.plugins.sort((a, b) => {

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