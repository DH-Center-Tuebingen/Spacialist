spacialistApp.controller('headerCtrl', ['$scope', 'langService', 'userService', 'mainService', '$state', function($scope, langService, userService, mainService, $state) {
    var vm = this;
    vm.state = $state;
    vm.currentLanguage = {};
    vm.isLangSet = langService.isLangSet;

    vm.toggleEditMode = function() {
        vm.editMode.enabled = !vm.editMode.enabled;
    };

    vm.initLanguage = function() {
        var initKey;
        if(vm.userConfig) {
            var langPref = vm.userConfig['prefs.gui-language'];
            if(langPref) initKey = langPref.value;
        }
        langService.setInitLanguage(vm.currentLanguage, initKey);
    };

    vm.switchLanguage = function(langKey) {
        langService.switchLanguage(vm.currentLanguage, langKey, vm.concepts);
        vm.currentLanguage.flagCode = langKey;
    };

    vm.logoutUser = function() {
        userService.logoutUser();
    };

    vm.initLanguage();
}]);
