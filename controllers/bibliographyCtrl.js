spacialistApp.controller('bibliographyCtrl', function($scope, literatureService) {
    $scope.sortType = 'author';
    $scope.sortReverse = false;
    $scope.searchTerm = '';

    $scope.deleteLiteratureEntry = literatureService.deleteLiteratureEntry;

    $scope.openAddLiteratureDialog = function() {
        literatureService.openAddLiteratureDialog();
    };

    $scope.importBibTexFile = function(file, invalidFiles) {
        literatureService.importBibTexFile(file, invalidFiles);
    };
});
