spacialistApp.controller('dataEditorCtrl', ['dataEditorService', 'mainService', function(dataEditorService, mainService) {
    var vm = this;

    vm.addNewAttribute = function() {
        dataEditorService.addNewAttributeWindow(vm.attributetypes, vm.attributes);
    };

    vm.onDeleteAttribute = function(attr) {
        dataEditorService.deleteAttribute(attr, vm.attributes);
    };

    vm.addNewContextType = function() {
        dataEditorService.addNewContextTypeWindow(vm.geometryTypes, vm.onContextTypeAdded);
    };

    vm.editContextType = function(e) {
        dataEditorService.editContextType(e);
    };

    vm.deleteContextType = function(e) {
        dataEditorService.deleteContextType(e, vm.contextTypes, vm.concepts[e.thesaurus_url].label);
    };

    vm.onContextTypeAdded = function(ct) {
        var r = ct.is_root;
        ct.is_root = r == 'true' || r == '1' || r == 't';
        vm.contextTypes.push(ct);
    };
}]);

spacialistApp.controller('selectedContextTypeCtrl', ['dataEditorService', 'mainService', function(dataEditorService, mainService) {
    var vm = this;

    vm.addAttributeToContextType = function() {
        dataEditorService.addAttributeToContextTypeWindow(vm.contextType, vm.fields, vm.attributes, vm.concepts);
    };

    vm.changeContextTypeRoot = function(root) {
        dataEditorService.changeContextTypeRoot(vm.contextType.id, root);
    };

    vm.onAddSubType = function(item) {
        dataEditorService.addSubContextTypeTo(vm.contextType.id, item.id);
    };

    vm.onRemoveSubType = function(item) {
        dataEditorService.removeSubContextTypeFrom(vm.contextType.id, item.id);
    };

    vm.selectAllSubType = function() {
        dataEditorService.selectAllSubType(vm.contextType.id).then(function(response) {
            vm.selectedSubContextTypes.length = 0;
            angular.merge(vm.selectedSubContextTypes, vm.contextTypes);
        });
    };

    vm.selectNoSubType = function() {
        dataEditorService.selectNoSubType(vm.contextType.id).then(function(response) {
            vm.selectedSubContextTypes.length = 0;
        });
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
}]);
