spacialistApp.controller('contextCtrl', ['$scope', 'mainService', function($scope, mainService) {
    var vm = this;
    vm.currentElement = mainService.currentElement;
    vm.editContext = angular.copy(vm.context);
    mainService.setCurrentElement({
        element: vm.editContext,
        data: vm.data
    });

    vm.store = function(data) {
        vm.onStore({context: vm.editContext, data: data});
    };

    vm.addSource = function(entry) {
        vm.onSourceAdd({entry: entry});
    };

    vm.openGeographyPlacer = function(aid) {
        mainService.openGeographyModal($scope, aid);
    };

    vm.contextSearch = function(searchTerm) {
        return mainService.contextSearch(searchTerm);
    };
}]);
