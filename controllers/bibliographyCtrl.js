spacialistApp.controller('bibliographyCtrl', function($scope, literatureService) {
    $scope.sortType = 'author';
    $scope.sortReverse = false;
    $scope.searchTerm = '';

    $scope.deleteLiteratureEntry = function(entry) {
        literatureService.deleteLiteratureEntry(entry);
    };

    $scope.editLiteratureEntry = function(entry) {
        literatureService.editLiteratureEntry(entry);
    };

    $scope.openAddLiteratureDialog = function() {
        literatureService.openAddLiteratureDialog();
    };

    $scope.importBibTexFile = function(file, invalidFiles) {
        literatureService.importBibTexFile(file, invalidFiles);
    };
});
