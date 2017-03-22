spacialistApp.controller('editorCtrl', ['$scope', 'mainService', 'editorService', function($scope, mainService, editorService) {
    $scope.addNewContextType = editorService.addNewContextTypeWindow;

    $scope.attributeTypes = editorService.attributeTypes;
    $scope.existingAttributes = editorService.existingAttributes;
    $scope.existingContextTypes = editorService.existingContextTypes;
    $scope.existingArtifactTypes =  editorService.existingArtifactTypes;
    $scope.contextAttributes = mainService.contextReferences;
    $scope.dropdownOptions = mainService.dropdownOptions;

    $scope.setSelectedContext = function(c) {
        $scope.selectedCt = c;
        if(c.type === 0) $scope.ctAttributes = mainService.contextReferences[c.index];
        else if(c.type == 1) $scope.ctAttributes = mainService.artifactReferences[c.index];
    };
}]);
