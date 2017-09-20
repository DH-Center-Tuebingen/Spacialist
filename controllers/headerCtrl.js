spacialistApp.controller('headerCtrl', ['$scope', 'langService', 'userService', 'mainService', '$state', function($scope, langService, userService, mainService, $state) {
    var vm = this;
    $scope.concepts = vm.concepts;
    vm.currentLanguage = {};
    vm.isLangSet = langService.isLangSet;

    vm.onSearchSelect = function($item, $model, $label) {
        switch($model.type) {
            case 'context':
                $state.go('root.spacialist.data', {id: $model.id});
                break;
            case 'layer':
                $state.go('root.editor.layer.edit', {id: $model.id});
                break;
            case 'file':
                $state.go('root.spacialist.file', {id: $model.id});
                break;
            case 'user':
                $state.go('root.user.edit', {id: $model.id});
                break;
        }
    };

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

    vm.globalSearch = function(term) {
        return mainService.globalSearch(term);
    };

    vm.initLanguage();
}]);
