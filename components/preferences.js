spacialistApp.component('preferences', {
    bindings: {
        tab: '@',
        onPrefUpdate: '&',
        availablePreferences: '<'
    },
    templateUrl: 'templates/preferences.html',
    controller: [function() {
        var vm = this;

        vm.preferences = angular.copy(vm.availablePreferences);
        vm.onUpdate = function(pref) {
            vm.onPrefUpdate({pref: pref, uid: undefined});
        };
    }]
});

spacialistApp.component('upreferences', {
    bindings: {
        tab: '@',
        user: '<',
        onPrefUpdate: '&',
        overridablePrefs: '<'
    },
    templateUrl: 'templates/preferences.html',
    controller: [function() {
        var vm = this;

        vm.preferences = angular.copy(vm.overridablePrefs);
        vm.onUpdate = function(pref) {
            vm.onPrefUpdate({pref: pref, uid: vm.user.user.id});
        };
        vm.isUserPref = true;
    }]
});
