spacialistApp.controller('layerEditCtrl', ['$scope', 'layerEditorService', function($scope, layerEditorService) {
    var vm = this;
    vm.editableLayer = angular.merge({}, vm.layer);

    vm.updateLayer = function(layer) {
        layerEditorService.updateLayer(layer, vm.layer);
    };
}]);
