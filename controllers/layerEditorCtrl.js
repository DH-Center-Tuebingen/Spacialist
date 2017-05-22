spacialistApp.controller('layerEditorCtrl', ['$scope', 'mainService', 'layerEditorService', function($scope, mainService, layerEditorService) {
    $scope.setSelectedLayer = layerEditorService.setSelectedLayer;
    $scope.selectedLayer = layerEditorService.selectedLayer;
    $scope.contextLayers = layerEditorService.contextLayers;

    $scope.onDelete = layerEditorService.onDelete;
    $scope.onOrder = layerEditorService.onOrder;
    $scope.updateLayer = layerEditorService.updateLayer;
    $scope.addNewBaselayer = layerEditorService.addNewBaselayer;
    $scope.addNewOverlay = layerEditorService.addNewOverlay;
}]);
