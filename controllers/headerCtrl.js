spacialistApp.controller('headerCtrl', ['$scope', 'langService', 'userService', function($scope, langService, userService) {
    var vm = this;
    vm.isLangSet = langService.isLangSet;

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
