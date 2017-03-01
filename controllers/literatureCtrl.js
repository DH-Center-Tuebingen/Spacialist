spacialistApp.controller('literatureCtrl', function($scope, literatureService, userService, modalFactory, httpGetFactory, httpPostFactory) {
    $scope.can = userService.can;
    $scope.literature = literatureService.literature;
    $scope.currentUser = userService.currentUser;

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
});
