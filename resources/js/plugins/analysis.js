const SpacialistPluginDataAnalysis = {
    name: 'SpacialistPluginDataAnalysis',
    install(Vue, options) {
        if(!Vue.prototype.$spacialistPluginsEnabled) {
            console.error("Spacialist Plugin System not found!");
            return;
        }
        Vue.prototype.$registerSpacialistPlugin({
            label: 'prefs.extension.data-analysis',
            icon: 'fa-chart-bar',
            href: '/tool/analysis'
        }, 'tools');
    }
};

export default SpacialistPluginFiles;
