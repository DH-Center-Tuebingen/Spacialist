spacialistApp.controller('pdfCtrl', ['$scope', 'httpGetFactory', function($scope, httpGetFactory) {
    $scope.fileLoaded = false;
    $scope.isPreview = false;
    $scope.status = {
        progress: 0
    };

    $scope.togglePreview = function() {
        $scope.isPreview = !$scope.isPreview;
    };

    $scope.onProgress = function(event) {
        $scope.status.progress = Math.round(event.loaded / event.total * 100);
        $scope.$apply();
    };
}]);
