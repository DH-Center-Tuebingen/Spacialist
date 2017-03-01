spacialistApp.controller('editorCtrl', ['$scope', 'mainService', 'editorService', function($scope, mainService, editorService) {
    $scope.attributeTypes = editorService.attributeTypes;
    $scope.existingAttributes = editorService.existingAttributes;
    // $scope.existingContextTypes = editorService.existingContextTypes;
    // $scope.contextAttributes = editorService.contextAttributes;
    $scope.existingContextTypes = mainService.contexts;
    $scope.existingArtifactTypes =  mainService.artifacts;
    $scope.contextAttributes = mainService.contextReferences;
    $scope.dropdownOptions = mainService.dropdownOptions;

    $scope.setSelectedContext = function(c) {
        console.log(c);
        $scope.selectedCt = c;
        if(c.type == 0) $scope.ctAttributes = mainService.contextReferences[c.index];
        else if(c.type == 1) $scope.ctAttributes = mainService.artifactReferences[c.index];
    };
}]);
