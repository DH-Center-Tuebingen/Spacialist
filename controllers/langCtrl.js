spacialistApp.controller('langCtrl', ['$scope', 'langService', function($scope, langService) {
    $scope.isLangSet = langService.isLangSet;

    var lConcepts = this.concepts;

    $scope.switchLanguage = function(langKey) {
        langService.switchLanguage(langKey, lConcepts);
    };
}]);
