spacialistApp.controller('layerEditCtrl', ['$scope', 'layerEditorService', function($scope, layerEditorService) {
    $scope.editableLayer = angular.merge({}, this.layer);
    var originalLayer = this.layer;

    $scope.updateLayer = function(layer) {
        layerEditorService.updateLayer(layer, originalLayer);
    };
}]);
