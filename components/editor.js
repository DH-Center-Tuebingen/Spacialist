spacialistApp.component('datamodel', {
    bindings: {
        attributes: '<',
        attributetypes: '<',
        concepts: '<',
        contextTypes: '<',
        geometryTypes: '<',
        userConfig: '<'
    },
    templateUrl: 'data-model.html',
    controller: 'dataEditorCtrl'
});

spacialistApp.component('contexttypeedit', {
    bindings: {
        contextType: '<',
        attributes: '<',
        concepts: '<',
        fields: '<'
    },
    templateUrl: 'templates/context-type.html',
    controller: 'dataEditorCtrl'
});

spacialistApp.component('layer', {
    bindings: {
        avLayers: '<',
        concepts: '<'
    },
    templateUrl: 'layer-editor.html',
    controller: 'layerEditorCtrl'
});

spacialistApp.component('layeredit', {
    bindings: {
        layer: '<',
        concepts: '<'
    },
    templateUrl: 'templates/layer-edit.html',
    controller: 'layerEditCtrl'
});

spacialistApp.component('gis', {
    bindings: {
        concepts: '<',
        contexts: '<',
        map: '<',
        layer: '<',
        geodata: '<'
    },
    templateUrl: 'templates/gis.html',
    controller: 'gisCtrl'
});

spacialistApp.component('gisanalysis', {
    bindings: {
        concepts: '<',
        contexts: '<',
        map: '<',
        layer: '<',
        geodata: '<'
    },
    templateUrl: 'templates/gis-analysis.html',
    controller: function(httpPostFactory) {
        var vm = this;

        vm.filters = '[{"comp":">","values":[6,[48.52,9.05]], "func": "distance"}]';
        vm.contextTypes = '[2, 20, 17]';
        vm.columns = '[]';

        vm.filter = function() {
            var formData = new FormData();
            formData.append('filters', vm.filters);
            formData.append('contextTypes', vm.contextTypes);
            formData.append('columns', vm.columns);
            httpPostFactory('api/analysis/filter', formData, function(response) {
                console.log(response);
            })
        }
    }
});
