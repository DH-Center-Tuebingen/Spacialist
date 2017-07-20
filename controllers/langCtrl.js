spacialistApp.controller('langCtrl', ['$scope', 'langService', function($scope, langService) {
    $scope.isLangSet = langService.isLangSet;

    var localConcepts = this.concepts;
    $scope.currentLanguage = {
        label: '',
        flagCode: this.userConfig.language
    };
    
    $scope.switchLanguage = function(langKey) {
        langService.switchLanguage(langKey, localConcepts);
        $scope.currentLanguage.flagCode = langKey;
    };
}]);
