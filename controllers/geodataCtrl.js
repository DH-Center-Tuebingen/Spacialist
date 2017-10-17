spacialistApp.controller('geodataCtrl', function($state) {
    var vm = this;

    vm.setGlobalGeodata = function() {
        vm.onSetGeodata({gid: vm.geodataId, geodata: vm.map.geodata});
    };

    vm.initGeodata = function() {
        vm.setGlobalGeodata();
        var cid = vm.map.geodata.linkedContexts[vm.geodataId];
        if(cid) {
            $state.go('root.spacialist.context.data', {id: cid}, {inherit: true, reload: 'root.spacialist.context.data'});
        }
    };

    vm.initGeodata();
});
