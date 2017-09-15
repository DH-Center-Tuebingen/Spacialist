spacialistApp.controller('layerEditorCtrl', ['layerEditorService', function(layerEditorService) {
    var vm = this;

    vm.addNewBaselayer = function() {
        layerEditorService.addNewBaselayer(vm.avLayers);
    };

    vm.addNewOverlay = function() {
        layerEditorService.addNewOverlay(vm.avLayers);
    };

    vm.onDelete = function(l) {
        layerEditorService.onDelete(l, vm.avLayers);
    };
}]);
