import de from '../i18n/plugins/files-de.js';
import en from '../i18n/plugins/files-en.js';

Vue.component('files-plugin', require('../components/plugins/Files.vue'));

const SpacialistPluginFiles = {
    name: 'SpacialistPluginFiles',
    install(Vue, options) {
        if(Vue.i18n) {
            Vue.prototype.$spacialistAddPluginLanguage('de', de);
            Vue.prototype.$spacialistAddPluginLanguage('en', en);
        }
        if(!Vue.prototype.$spacialistPluginsEnabled) {
            console.error("Spacialist Plugin System not found!");
            return;
        }
        Vue.prototype.$registerSpacialistPlugin({
            label: 'plugins.files.title',
            icon: 'fa-folder',
            key: 'files',
            tag: 'files-plugin',
            hook: function(entity) {
            }
        }, 'tab');
    }
};

export default SpacialistPluginFiles;
