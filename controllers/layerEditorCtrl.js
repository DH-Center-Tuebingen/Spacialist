spacialistApp.controller('layerEditorCtrl', ['$scope', 'mainService', 'layerEditorService', function($scope, mainService, layerEditorService) {
    $scope.setSelectedLayer = layerEditorService.setSelectedLayer;
    $scope.selectedLayer = layerEditorService.selectedLayer;
    $scope.contextLayers = layerEditorService.contextLayers;

    $scope.onDelete = layerEditorService.onDelete;
    $scope.onOrder = layerEditorService.onOrder;
}]);
