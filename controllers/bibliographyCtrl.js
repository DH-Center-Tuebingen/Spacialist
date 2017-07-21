spacialistApp.controller('bibliographyCtrl', function($scope, literatureService) {
    $scope.sortType = 'author';
    $scope.sortReverse = false;
    $scope.searchTerm = '';
    var localBibliography = this.bibliography;

    $scope.openAddLiteratureDialog = function() {
        literatureService.openAddLiteratureDialog(localBibliography);
    };

    $scope.importBibTexFile = function(file, invalidFiles) {
        literatureService.importBibTexFile(file, invalidFiles, localBibliography);
    };

    $scope.deleteLiteratureEntry = function(entry) {
        literatureService.deleteLiteratureEntry(entry, localBibliography);
    }
});
