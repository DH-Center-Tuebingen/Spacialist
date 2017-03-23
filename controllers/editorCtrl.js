spacialistApp.controller('editorCtrl', ['$scope', 'mainService', 'editorService', function($scope, mainService, editorService) {
    $scope.addNewContextType = editorService.addNewContextTypeWindow;
    $scope.setSelectedContext = editorService.setSelectedContext;

    $scope.attributeTypes = editorService.attributeTypes;
    $scope.existingAttributes = editorService.existingAttributes;
    $scope.existingContextTypes = editorService.existingContextTypes;
    $scope.existingArtifactTypes =  editorService.existingArtifactTypes;
    $scope.contextAttributes = editorService.contextReferences;
    $scope.dropdownOptions = editorService.dropdownOptions;
    $scope.ct = editorService.ct;
    // $scope.selectedCt = editorService.ct.selected;
    // $scope.ctAttributes = editorService.ct.attributes;

    $scope.onRemoveAttrFromCt = function(attr) {
        console.log("deleting...");
        console.log(attr);
        editorService.removeAttributeFromContextType(attr, $scope.ct.selected);
    };

    $scope.onOrderAttrOfCt = {
        up: function(attr) {
            editorService.moveAttributeOfContextTypeUp(attr);
        },
        down: function(attr) {
            editorService.moveAttributeOfContextTypeDown(attr);
        }
    };
}]);
