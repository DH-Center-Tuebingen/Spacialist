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
