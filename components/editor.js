spacialistApp.component('datamodel', {
    bindings: {
        attributes: '<',
        attributetypes: '<',
        concepts: '<',
        contextTypes: '<'
    },
    templateUrl: 'data-model.html',
    controller: 'dataEditorCtrl'
});

spacialistApp.component('contexttypeedit', {
    bindings: {
        contextType: '<',
        concepts: '<',
        fields: '<'
    },
    templateUrl: 'templates/context-type.html'
});

spacialistApp.component('layer', {
    bindings: {
        layers: '<'
    },
    templateUrl: 'layer-editor.html'
});
