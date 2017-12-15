spacialistApp.controller('pdfCtrl', ['$scope', 'httpGetFactory', function($scope, httpGetFactory) {
    $scope.status = {
        progress: 0
    };

    $scope.onProgress = function(event) {
        $scope.status.progress = Math.round(event.loaded / event.total * 100);
        $scope.$apply();
    };
}]);
