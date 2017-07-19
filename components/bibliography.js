spacialistApp.component('bibliography', {
    bindings: {
        bibliography: '<',
        user: '<'
    },
    templateUrl: 'bibliography.html',
    controller: 'bibliographyCtrl'
});

spacialistApp.component('bibedit', {
    bindings: {
        entry: '<'
    },
    template: '<input ng-model="$ctrl.entry.author"/>'
});
