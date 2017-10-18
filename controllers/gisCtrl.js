spacialistApp.controller('gisCtrl', ['mapService', '$timeout', function(mapService, $timeout) {
    var vm = this;

    vm.layerVisibility = {};
    vm.sublayerVisibility = {};
    vm.sublayerColors = {};

    vm.toggleLayerGroupVisibility = function(layerGroup, isVisible) {
        var p = vm.map.layers.overlays[layerGroup.id];
        p.visible = isVisible;
        p.layerOptions.visible = isVisible;
        layerGroup.visible = isVisible;
    };

    vm.toggleLayerVisibility = function(layer, isVisible) {
        layer.options.visible = isVisible;
        if(isVisible) {
            layer.setStyle(vm.sublayerColors[layer.feature.id]);
        } else {
            vm.sublayerColors[layer.feature.id] = {
                color: layer.options.color,
                fillColor: layer.options.fillColor
            };
            layer.setStyle({
                color: 'rgba(0,0,0,0)',
                fillColor: 'rgba(0,0,0,0)'
            });
        }
    };

    vm.init = function() {
        mapService.setupLayers(vm.layer, vm.map, vm.contexts, vm.concepts);
        mapService.initMapObject('gismap').then(function(obj) {
            vm.mapObject = obj;
            var fwOptions = {
                position: 'topleft',
                onClick: function() {
                    mapService.fitBounds(vm.map);
                }
            };
            L.control.fitworld(fwOptions).addTo(vm.mapObject);
            // wait a random amount of time, so mapObject.eachLayer has all layers
            $timeout(function() {
                vm.mapObject.eachLayer(function(l) {
                    if(l.options.layer_id) {
                        vm.map.mapLayers[l.options.layer_id] = l;
                    }
                });
                mapService.initGeodata(vm.geodata, vm.contexts, vm.map);
            }, 100);
        });
    };

    vm.init();
}]);
