spacialistApp.controller('layerEditorCtrl', ['$scope', 'layerEditorService', function($scope, layerEditorService) {
    var localLayers = this.avLayers;

    $scope.addNewBaselayer = function() {
        layerEditorService.addNewBaselayer(localLayers);
    };

    $scope.addNewOverlay = function() {
        layerEditorService.addNewOverlay(localLayers);
    };

    $scope.onDelete = function(l) {
        layerEditorService.onDelete(l, localLayers);
    };
}]);
