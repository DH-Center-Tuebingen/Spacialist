import { computed } from 'vue';
import store from '../bootstrap/store';


export const usePluginSlot = (name) => {
    
    const setPluginData = (data) => {
        if(!data.plugin_data) {
            console.error(`Entity type does not have a 'plugin_data' property.`);
            return;
        }

        for(const pluginSlot of store.getters.slotPlugins('dataModelOptions')) {
            const value = data.plugin_data[pluginSlot.key];
            if(!pluginSlot.methods?.setData)
                console.error(`Plugin '${pluginSlot.key}' does not has a 'setData' method.`);
            else
                pluginSlot.methods.setData(value);
        }
    };
    
    const pluginSlots = computed(() => store.getters.slotPlugins(name));

    return {
        setPluginData,
        pluginSlots,
    };
};