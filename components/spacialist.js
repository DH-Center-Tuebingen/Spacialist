spacialistApp.component('spacialist', {
    bindings: {
        contexts: '<',
        user: '<',
        concepts:'<',
        map: '<',
        layer: '<',
        geodata: '<',
        contextTypes: '<'
    },
    templateUrl: 'view.html',
    controller: 'mainCtrl'
});

spacialistApp.component('spacialistdata', {
    bindings: {
        context: '<',
        data: '<',
        fields: '<',
        sources: '<',
        geodate: '<',
        user: '<',
        concepts: '<'
    },
    templateUrl: 'templates/context-data.html',
    controller: 'contextCtrl'
});
