spacialistApp.component('preferences', {
    bindings: {
        tab: '@',
        availablePreferences: '<'
    },
    templateUrl: 'templates/preferences.html',
    controller: ['userService', 'snackbarService', '$translate', function(userService, snackbarService, $translate) {
        var vm = this;

        vm.preferences = angular.copy(vm.availablePreferences);
        vm.onUpdate = function(pref) {
            userService.storePreference(pref).then(function(response) {
                var content = $translate.instant('snackbar.data-stored.success');
                snackbarService.addAutocloseSnack(content, 'success');
            });
        };
    }]
});

spacialistApp.component('upreferences', {
    bindings: {
        tab: '@',
        user: '<',
        userConfig: '<'
    },
    templateUrl: 'templates/preferences.html',
    controller: ['userService', 'snackbarService', '$translate', function(userService, snackbarService, $translate) {
        var vm = this;

        vm.preferences = angular.copy(vm.userConfig);
        vm.onUpdate = function(pref) {
            userService.storeUserPreference(pref, vm.user.user.id).then(function(response) {
                var content = $translate.instant('snackbar.data-stored.success');
                snackbarService.addAutocloseSnack(content, 'success');
            });
        };
        vm.isUserPref = true;
    }]
});
