spacialistApp.controller('dataEditorCtrl', ['dataEditorService', 'mainService', function(dataEditorService, mainService) {
    var vm = this;

    vm.addNewAttribute = function() {
        dataEditorService.addNewAttributeWindow(vm.attributetypes, vm.attributes);
    };

    vm.onDeleteAttribute = function(attr) {
        dataEditorService.deleteAttribute(attr, vm.attributes);
    };

    vm.addNewContextType = function() {
        dataEditorService.addNewContextTypeWindow(vm.geometryTypes, vm.contextTypes);
    };

    vm.editContextType = function(e) {
        dataEditorService.editContextType(e);
    };

    vm.deleteContextType = function(e) {
        dataEditorService.deleteContextType(e, vm.contextTypes, vm.concepts[e.thesaurus_url].label);
    };

    vm.onRemoveAttrFromCt = function(attr) {
        dataEditorService.removeAttributeFromContextType(vm.contextType, attr, vm.fields);
    };

    vm.onOrderAttrOfCt = {
        up: function(attr) {
            dataEditorService.moveAttributeOfContextTypeUp(attr, vm.fields);
        },
        down: function(attr) {
            dataEditorService.moveAttributeOfContextTypeDown(attr, vm.fields);
        }
    };

    vm.addAttributeToContextType = function() {
        dataEditorService.addAttributeToContextTypeWindow(vm.contextType, vm.fields, vm.attributes, vm.concepts);
    };

    vm.onAddSubType = function(item) {
        dataEditorService.addSubContextTypeTo(vm.contextType.id, item.id);
    };

    vm.onRemoveSubType = function(item) {
        dataEditorService.removeSubContextTypeFrom(vm.contextType.id, item.id);
    };
}]);
