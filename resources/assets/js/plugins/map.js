import de from '../i18n/plugins/map-de.js';
import en from '../i18n/plugins/map-en.js';
import LayerEditor from '../components/plugins/MapLayerEditor.vue';
import LayerEditorDetail from '../components/plugins/MapLayerEditorDetail.vue';
import Gis from '../components/plugins/MapGis.vue';

Vue.component('map-plugin', require('../components/plugins/Map.vue'));
Vue.component('layer-list', require('../components/plugins/MapLayerList.vue'));

const SpacialistPluginMap = {
    name: 'SpacialistPluginMap',
    install(Vue, options) {
        if(Vue.i18n) {
            Vue.prototype.$spacialistAddPluginLanguage('de', de);
            Vue.prototype.$spacialistAddPluginLanguage('en', en);
        }
        if(Vue.router) {
            Vue.router.addRoutes([
                // deprecated pre-0.6 routes
                {
                    path: 'geodata/:id',
                    redirect: to => {
                        return {
                            name: 'geodata',
                            params: {
                                id: to.params.id
                            }
                        }
                    }
                },
                {
                    path: 'editor/layer',
                    redirect: { name: 'layeredit' },
                    children: [{
                        path: 'layer/:id',
                        redirect: to => {
                            return {
                                name: 'ldetail',
                                params: {
                                    id: to.params.id
                                }
                            }
                        }
                    }]
                },
                {
                    path: 'editor/gis',
                    redirect: { name: 'home' } // TODO not home
                },
                // New routes
                // {
                //     path: 'geodata/:id',
                //     name: 'geodata',
                //     component: , // TODO
                //     meta: {
                //         auth: true
                //     }
                // },
                {
                    path: '/editor/layer',
                    name: 'layeredit',
                    component: LayerEditor,
                    children: [
                        {
                            path: 'l/:id',
                            name: 'ldetail',
                            component: LayerEditorDetail,
                        }
                    ],
                    meta: {
                        auth: true
                    }
                },
                {
                    path: '/tool/gis',
                    name: 'gis',
                    component: Gis,
                    meta: {
                        auth: true
                    }
                },
            ]);
        }
        if(!Vue.prototype.$spacialistPluginsEnabled) {
            console.error("Spacialist Plugin System not found!");
            return;
        }
        // Map
        Vue.prototype.$registerSpacialistPlugin({
            label: 'plugins.map.tab.title',
            icon: 'fa-map-marker-alt',
            key: 'map',
            tag: 'map-plugin',
            hook: function(entity) {
            }
        }, 'tab');
        // Layer Editor
        Vue.prototype.$registerSpacialistPlugin({
            label: 'plugins.map.layer-editor.title',
            icon: 'fa-layer-group',
            href: {
                name: 'layeredit'
            }
        }, 'settings');
        // GIS View
        Vue.prototype.$registerSpacialistPlugin({
            label: 'plugins.map.gis.title',
            icon: 'fa-globe-africa',
            href: {
                name: 'gis'
            }
        }, 'tools');
    }
};

export default SpacialistPluginMap;
