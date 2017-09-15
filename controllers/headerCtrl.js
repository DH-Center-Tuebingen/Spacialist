spacialistApp.controller('headerCtrl', ['$scope', 'langService', 'userService', 'mainService', function($scope, langService, userService, mainService) {
    var vm = this;
    vm.currentLanguage = {};
    vm.isLangSet = langService.isLangSet;

    vm.toggleEditMode = function() {
        vm.editMode.enabled = !vm.editMode.enabled;
    };

    vm.initLanguage = function() {
        var initKey = vm.userConfig ? vm.userConfig.language : undefined;
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
