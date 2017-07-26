spacialistApp.controller('contextCtrl', ['$scope', 'mainService', '$translate', function($scope, mainService, $translate) {
    var localContext = this.context;
    var localData = this.data;

    $scope.storeElement = function() {
        mainService.storeElement(localContext, localData);
    };
}]);
