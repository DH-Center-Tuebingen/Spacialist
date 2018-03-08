const SpacialistPluginSystem = {
    name: 'SpacialistPluginSystem',
    install(Vue, options) {
        Vue.prototype.$spacialistPluginsEnabled = true;
        Vue.prototype.$spacialistHooks = [];
        Vue.prototype.$spacialistPlugins = {
            tab: [],
            tools: [],
            settings: []
        };
        Vue.prototype.$registerHook = function(hook) {
            Vue.prototype.$spacialistHooks.push(hook);
        };
        Vue.prototype.$requestHooks = function(entity) {
            Vue.prototype.$spacialistHooks.forEach(function(hook) {
                hook(entity);
            });
        };
        Vue.prototype.$getSpacialistPlugins = function(to) {
            this[to] = Vue.prototype.$spacialistPlugins;
        };
        Vue.prototype.$registerSpacialistTab = function(plugin) {
            Vue.prototype.$spacialistPlugins.tab.push(plugin);
            Vue.prototype.$registerHook(plugin.hook);
        };
        Vue.prototype.$registerSpacialistTool = function(plugin) {
            Vue.prototype.$spacialistPlugins.tools.push(plugin);
        };
        Vue.prototype.$registerSpacialistSetting = function(plugin) {
            Vue.prototype.$spacialistPlugins.settings.push(plugin);
        };
        Vue.prototype.$registerSpacialistPlugin = function(plugin, slot) {
            switch(slot) {
                case 'tab':
                    Vue.prototype.$registerSpacialistTab(plugin);
                    break;
                case 'tools':
                    Vue.prototype.$registerSpacialistTool(plugin);
                    break;
                case 'settings':
                    Vue.prototype.$registerSpacialistSetting(plugin);
                    break;
                default:
                    console.error("Slot " + slot + " is not supported for Spacialist plugins");
                    return;
            }
        };
    }
};

export default SpacialistPluginSystem;
