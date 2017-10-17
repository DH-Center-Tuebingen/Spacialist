spacialistApp.controller('pdfCtrl', ['$scope', 'httpGetFactory', function($scope, httpGetFactory) {
    $scope.fileLoaded = false;
    $scope.isPreview = false;
    $scope.isMd = false;
    $scope.isCsv = false;
    $scope.status = {
        progress: 0
    };

    $scope.togglePreview = function() {
        $scope.isPreview = !$scope.isPreview;
        $scope.isMd = !$scope.isMd;
    };

    $scope.toggleCsv = function(url) {
        $scope.isPreview = !$scope.isPreview;
        $scope.isCsv = !$scope.isCsv;
        if($scope.isCsv) {
            httpGetFactory(url, function(response) {
                $scope.csvData = response;
            });
        } else {
            delete $scope.csvData;
        }
    }

    $scope.onProgress = function(event) {
        $scope.status.progress = Math.round(event.loaded / event.total * 100);
        $scope.$apply();
    };
}]);
