spacialistApp.component('spacialist', {
    bindings: {
        contexts: '<',
        user: '<',
        concepts:'<',
        map: '<',
        layer: '<',
        geodata: '<'
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
