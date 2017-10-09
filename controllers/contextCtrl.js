spacialistApp.controller('contextCtrl', ['$scope', 'mainService', function($scope, mainService) {
    var vm = this;

    vm.store = function(data) {
        vm.onStore({context: vm.editContext, data: data});
    };

    vm.addSource = function(entry) {
        vm.onSourceAdd({entry: entry});
    };

    vm.openGeographyPlacer = function(aid) {
        mainService.openGeographyModal($scope, aid);
    };

    vm.contextSearch = function(searchTerm) {
        return mainService.contextSearch(searchTerm);
    };

    vm.setContext = function() {
        var geometryType = '';
        if(vm.layer) {
            var ctxLayer = vm.layer.find(function(l) {
                return l.context_type_id == vm.editContext.context_type_id;
            });
            if(ctxLayer) geometryType = ctxLayer.type;
        }
        vm.onSetContext({
            id: vm.editContext.id,
            data: {
                sources: vm.sources,
                geometryType: geometryType,
                linkedFiles: vm.linkedFiles
            }
        });
    };

    vm.setContext();
}]);
