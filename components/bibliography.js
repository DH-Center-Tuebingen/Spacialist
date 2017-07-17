spacialistApp.component('bibliography', {
    bindings: {
        bibliography: '<'
    },
    templateUrl: 'bibliography.html'
});

spacialistApp.component('bibedit', {
    bindings: {
        entry: '<'
    },
    template: '<input ng-model="$ctrl.entry.author"/>'
});
