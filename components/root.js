spacialistApp.component('root', {
    bindings: {
        user: '<',
        config: '<',
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
        currentLanguage: '<',
        availableLanguages: '<'
    },
    templateUrl: 'templates/header.html',
    controller: 'langCtrl'
});
