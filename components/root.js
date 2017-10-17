spacialistApp.component('root', {
    bindings: {
        user: '<',
        userConfig: '<',
        concepts: '<'
    },
    template: '<div ui-view="content-container" on-pref-update="$ctrl.onPrefUpdate(pref, uid)"></div>',
    controller: function($scope, userService, snackbarService, $translate) {
        var vm = this;
        $scope.concepts = this.concepts;

        vm.onPrefUpdate = function(pref, uid) {
            var promise;
            if(uid) {
                promise = userService.storeUserPreference(pref, uid);
            } else {
                promise = userService.storePreference(pref);
            }
            promise.then(function(response) {
                vm.userConfig[pref.label] = angular.copy(pref);
                var content = $translate.instant('snackbar.data-stored.success');
                snackbarService.addAutocloseSnack(content, 'success');
            });
        };
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
