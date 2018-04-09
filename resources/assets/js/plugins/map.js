Vue.component('map-plugin', require('../components/plugins/Map.vue'));

const SpacialistPluginMap = {
    name: 'SpacialistPluginMap',
    install(Vue, options) {
        if(!Vue.prototype.$spacialistPluginsEnabled) {
            console.error("Spacialist Plugin System not found!");
            return;
        }
        // Map
        Vue.prototype.$registerSpacialistPlugin({
            label: 'prefs.extension.map',
            icon: 'fa-map-marker-alt',
            key: 'map',
            tag: 'map-plugin',
            hook: function(entity) {
            }
        }, 'tab');
        // Layer Editor
        Vue.prototype.$registerSpacialistPlugin({
            label: 'prefs.extension.layer-editor',
            icon: 'fa-sticky-note',
            href: '/editor/layer'
        }, 'settings');
        // GIS View
        Vue.prototype.$registerSpacialistPlugin({
            label: 'prefs.extension.gis-view',
            icon: 'fa-globe',
            href: '/tool/gis'
        }, 'tools');
    }
};

export default SpacialistPluginMap;
