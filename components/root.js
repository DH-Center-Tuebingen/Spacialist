spacialistApp.component('root', {
    bindings: {
        user: '<',
        userConfig: '<',
        concepts: '<'
    },
    template: '<ui-view/>',
    controller: function($scope) {
        $scope.concepts = this.concepts;
    }
});

spacialistApp.component('header', {
    bindings: {
        user: '<',
        concepts: '<',
        userConfig: '<',
        availableLanguages: '<',
        editMode: '<'
    },
    templateUrl: 'templates/header.html',
    controller: 'headerCtrl'
});
