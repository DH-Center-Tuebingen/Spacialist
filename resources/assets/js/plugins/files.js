Vue.component('files-plugin', require('../components/plugins/Files.vue'));

const SpacialistPluginFiles = {
    name: 'SpacialistPluginFiles',
    install(Vue, options) {
        if(!Vue.prototype.$spacialistPluginsEnabled) {
            console.error("Spacialist Plugin System not found!");
            return;
        }
        Vue.prototype.$registerSpacialistPlugin({
            label: 'prefs.extension.files',
            icon: 'fa-folder',
            key: 'files',
            tag: 'files-plugin',
            hook: function(entity) {
            }
        }, 'tab');
    }
};

export default SpacialistPluginFiles;
