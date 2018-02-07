spacialistApp.component('datamodel', {
    bindings: {
        attributes: '<',
        attributetypes: '<',
        concepts: '<',
        contextTypes: '<',
        geometryTypes: '<',
        subContextTypes: '<',
        allowedSubContextTypes: '<',
        userConfig: '<'
    },
    templateUrl: 'data-model.html',
    controller: 'dataEditorCtrl'
});

spacialistApp.component('contexttypeedit', {
    bindings: {
        contextType: '<',
        contextTypes: '<',
        selectedSubContextTypes: '<',
        attributes: '<',
        concepts: '<',
        fields: '<'
    },
    templateUrl: 'templates/context-type.html',
    controller: 'selectedContextTypeCtrl'
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
