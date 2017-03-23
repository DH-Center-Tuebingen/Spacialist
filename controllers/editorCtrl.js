spacialistApp.controller('editorCtrl', ['$scope', 'mainService', 'editorService', function($scope, mainService, editorService) {
    $scope.addNewContextType = editorService.addNewContextTypeWindow;
    $scope.addNewAttribute = editorService.addNewAttributeWindow;
    $scope.addAttributeToContextType = editorService.addAttributeToContextTypeWindow;
    $scope.setSelectedContext = editorService.setSelectedContext;

    $scope.attributeTypes = editorService.attributeTypes;
    $scope.existingAttributes = editorService.existingAttributes;
    $scope.existingContextTypes = editorService.existingContextTypes;
    $scope.existingArtifactTypes =  editorService.existingArtifactTypes;
    $scope.contextAttributes = editorService.contextReferences;
    $scope.dropdownOptions = editorService.dropdownOptions;
    $scope.ct = editorService.ct;

    $scope.onRemoveAttrFromCt = function(attr) {
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
