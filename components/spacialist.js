spacialistApp.component('spacialist', {
    bindings: {
        contexts: '<',
        geodata: '<',
        layer: '<',
        user: '<',
        concepts:'<'
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
        user: '<',
        concepts: '<'
    },
    templateUrl: 'templates/context-data.html',
    controller: 'contextCtrl'
});
