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
                linkedFiles: vm.linkedFiles,
                linkedFilesChildren: vm.linkedFilesChildren
            }
        });
        if(vm.tab == 'map' && vm.editContext.geodata_id) {
            vm.onSetGeodata({gid: vm.editContext.geodata_id, geodata: vm.map.geodata});
            var geodate = vm.map.geodata.linkedLayers[vm.editContext.geodata_id];
            if(geodate) {
                if(!geodate.isPopupOpen()) geodate.openPopup();
            }
        } else if(vm.map.geodata.linkedContexts[vm.globalGeodata.geodata.id]){
            // unset geodata if current context is not linked
            vm.onSetGeodata({gid: undefined});
        }
    };

    vm.setContext();
}]);
