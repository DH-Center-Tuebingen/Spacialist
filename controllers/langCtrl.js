spacialistApp.controller('LangCtrl', ['$scope', 'langService', function($scope, langService) {
    $scope.availableLanguages = langService.availableLanguages;
    $scope.currentLanguage = langService.currentLanguage;
    $scope.isLangSet = langService.isLangSet;
}]);
