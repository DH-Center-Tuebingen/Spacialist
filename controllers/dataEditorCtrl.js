spacialistApp.controller('dataEditorCtrl', ['$scope', 'dataEditorService', function($scope, dataEditorService) {
    var vm = this;

    $scope.addNewAttribute = function() {
        dataEditorService.addNewAttributeWindow(vm.attributetypes, vm.attributes);
    };

    $scope.onDeleteAttribute = function(attr) {
        dataEditorService.deleteAttribute(attr, vm.attributes);
    };

    $scope.addNewContextType = function() {
        dataEditorService.addNewContextTypeWindow(vm.geometryTypes, vm.contextTypes);
    };

    $scope.editContextType = function(e) {
        dataEditorService.editContextType(e);
    };

    $scope.deleteContextType = function(e) {
        dataEditorService.deleteContextType(e, vm.contextTypes, vm.concepts[e.thesaurus_url].label);
    };

    $scope.onRemoveAttrFromCt = function(attr) {
        dataEditorService.removeAttributeFromContextType(vm.contextType, attr, vm.fields);
    };

    $scope.onOrderAttrOfCt = {
        up: function(attr) {
            dataEditorService.moveAttributeOfContextTypeUp(attr, vm.fields);
        },
        down: function(attr) {
            dataEditorService.moveAttributeOfContextTypeDown(attr, vm.fields);
        }
    };

    $scope.addAttributeToContextType = function() {
        dataEditorService.addAttributeToContextTypeWindow(vm.contextType, vm.fields, vm.attributes, vm.concepts);
    };
}]);
