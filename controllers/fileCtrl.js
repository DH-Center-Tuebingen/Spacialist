spacialistApp.controller('fileCtrl', ['$scope', 'fileService', 'mainService', 'snackbarService', '$translate', function($scope, fileService, mainService, snackbarService, $translate) {
    $scope.search = {
        terms: {
            tags: []
        }
    };

    $scope.addTag = function(file, item) {
        fileService.addTag(file, item);
    };

    $scope.removeTag = function(file, item) {
        fileService.removeTag(file, item);
    };
}]);
