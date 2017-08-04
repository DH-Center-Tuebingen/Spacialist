spacialistApp.controller('headerCtrl', ['$scope', 'langService', 'userService', 'mainService', function($scope, langService, userService, mainService) {
    var vm = this;
    vm.isLangSet = langService.isLangSet;

    vm.toggleEditMode = function() {
        vm.editMode.enabled = !vm.editMode.enabled;
    };

    vm.currentLanguage = {
        label: '',
        flagCode: vm.userConfig.language
    };

    vm.switchLanguage = function(langKey) {
        langService.switchLanguage(langKey, vm.concepts);
        vm.currentLanguage.flagCode = langKey;
    };

    vm.logoutUser = function() {
        userService.logoutUser();
    };
}]);
