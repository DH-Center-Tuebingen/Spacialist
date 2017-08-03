spacialistApp.controller('layerEditCtrl', ['$scope', 'layerEditorService', function($scope, layerEditorService) {
    var vm = this;
    $scope.editableLayer = angular.merge({}, vm.layer);

    $scope.updateLayer = function(layer) {
        layerEditorService.updateLayer(layer, vm.layer);
    };
}]);
