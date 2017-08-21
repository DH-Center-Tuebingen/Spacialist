spacialistApp.component('preferences', {
    bindings: {
        tab: '@',
        availablePreferences: '<'
    },
    templateUrl: 'templates/preferences.html',
    controller: ['mainService', 'snackbarService', '$translate', function(mainService, snackbarService, $translate) {
        var vm = this;

        vm.preferences = angular.copy(vm.availablePreferences);
        vm.onUpdate = function(pref) {
            mainService.storePreference(pref).then(function(response) {
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
        userPreferences: '<'
    },
    templateUrl: 'templates/preferences.html',
    controller: ['mainService', 'snackbarService', '$translate', function(mainService, snackbarService, $translate) {
        var vm = this;

        vm.preferences = angular.copy(vm.userPreferences);
        vm.onUpdate = function(pref) {
            mainService.storeUserPreference(pref, vm.user.user.id).then(function(response) {
                var content = $translate.instant('snackbar.data-stored.success');
                snackbarService.addAutocloseSnack(content, 'success');
            });
        };
        vm.isUserPref = true;
    }]
});
