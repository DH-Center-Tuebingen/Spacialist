spacialistApp.controller('dataEditorCtrl', ['$scope', 'mainService', 'dataEditorService', function($scope, mainService, dataEditorService) {
    $scope.addNewContextType = dataEditorService.addNewContextTypeWindow;
    $scope.addNewAttribute = dataEditorService.addNewAttributeWindow;
    $scope.addAttributeToContextType = dataEditorService.addAttributeToContextTypeWindow;
    $scope.setSelectedContext = dataEditorService.setSelectedContext;

    $scope.existingAttributes = dataEditorService.existingAttributes;
    $scope.existingContextTypes = dataEditorService.existingContextTypes;
    $scope.existingArtifactTypes =  dataEditorService.existingArtifactTypes;
    $scope.contextAttributes = dataEditorService.contextReferences;
    $scope.dropdownOptions = dataEditorService.dropdownOptions;
    $scope.ct = dataEditorService.ct;

    $scope.onDeleteAttribute = function(attr) {
        dataEditorService.deleteAttribute(attr);
    };

    $scope.onRemoveAttrFromCt = function(attr) {
        dataEditorService.removeAttributeFromContextType(attr, $scope.ct.selected);
    };

    $scope.onOrderAttrOfCt = {
        up: function(attr) {
            dataEditorService.moveAttributeOfContextTypeUp(attr);
        },
        down: function(attr) {
            dataEditorService.moveAttributeOfContextTypeDown(attr);
        }
    };

    $scope.editElementType = function(e) {
        dataEditorService.editContextType(e);
    };

    $scope.deleteElementType = function(e) {
        dataEditorService.deleteElementType(e);
    };
}]);

spacialistApp.controller('layerEditorCtrl', ['$scope', 'mainService', 'dataEditorService', function($scope, mainService, dataEditorService) {
    $scope.setSelectedContext = dataEditorService.setSelectedContext;

    $scope.existingContextTypes = dataEditorService.existingContextTypes;
    $scope.existingArtifactTypes =  dataEditorService.existingArtifactTypes;
    $scope.ct = dataEditorService.ct;
}]);
