spacialistApp.controller('gisCtrl', ['mapService', '$uibModal', '$timeout', function(mapService, $uibModal, $timeout) {
    var vm = this;

    vm.layerVisibility = {};
    vm.sublayerVisibility = {};
    vm.sublayerColors = {};

    vm.layerContextMenu = [
        [
            '<i class="material-icons md-18 fa-light context-menu-icon">zoom_in</i> Zoom To Layer',
            function($itemScope, $event, modelValue, text, $li) {
                var parentLayer = vm.map.mapLayers[$itemScope.l.id];
                mapService.fitBoundsToLayer(parentLayer, vm.map);
            },
            function($itemScope) {
                return vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            }
        ],
        [
            '<i class="material-icons md-18 fa-light context-menu-icon">file_upload</i> Export Layer',
            function($itemScope, $event, modelValue, text, $li) {
                return;
            },
            function($itemScope) {
                return vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            }
        ],
        [
            '<i class="material-icons md-18 fa-light context-menu-icon">timer</i> Toggle Feature Count',
            function($itemScope, $event, modelValue, text, $li) {
                var l = $itemScope.l;
                if(l.counter > 0) {
                    delete l.counter;
                } else {
                    l.counter = vm.map.mapLayers[l.id].getLayers().length;
                }
            },
            function($itemScope) {
                return vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            }
        ],
        [
            '<i class="material-icons md-18 fa-light context-menu-icon">settings</i> Properties',
            function($itemScope, $event, modelValue, text, $li) {
                var l = $itemScope.l;
                var concepts = vm.concepts;
                $uibModal.open({
                    templateUrl: "modals/gis-properties.html",
                    windowClass: 'wide-modal',
                    controller: ['$scope', function($scope) {
                        var vm = this;

                        vm.layer = l;
                        vm.layerName = l.context_type_id ? concepts[l.thesaurus_url].label : l.name;

                        vm.close = function() {
                            $scope.$dismiss('close');
                        }
                    }],
                    controllerAs: '$ctrl'
                }).result.then(function(reason) {
                }, function(reason) {
                });
            }
        ]
    ];

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
            L.control.togglemeasurements({
                position: 'topleft'
            }).addTo(vm.mapObject);
            L.control.zoomBox({
                modal: false,
                position: "topleft"
            }).addTo(vm.mapObject);
            L.control.graphicScale({
                position: 'bottomleft',
                minUnitWidth: 50,
                maxUnitsWidth: 300,
                fill: true,
                doubleLine: true
            }).addTo(vm.mapObject);
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
