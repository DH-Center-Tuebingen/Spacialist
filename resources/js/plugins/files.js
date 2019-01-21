import de from '../i18n/plugins/files-de.js';
import en from '../i18n/plugins/files-en.js';
import Files from '../components/plugins/Files.vue';

const SpacialistPluginFiles = {
    name: 'SpacialistPluginFiles',
    install(Vue, options) {
        Vue.component('files-plugin', Files);

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
        Vue.prototype.$uploadFile = function(file, metadata) {
            let formData = new FormData();
            formData.append('file', file.file);
            if(metadata) {
                if(metadata.copyright.length) {
                    formData.append('copyright', metadata.copyright);
                }
                if(metadata.description.length) {
                    formData.append('description', metadata.description);
                }
                if(metadata.tags.length) {
                    formData.append('tags', JSON.stringify(metadata.tags.map(t => t.id)));
                }
            }
            return $http.post('file/new', formData);
        };
    }
};

export default SpacialistPluginFiles;
