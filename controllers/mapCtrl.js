spacialistApp.controller('mapCtrl', ['$scope', '$compile', function($scope, $compile) {

    /**
     * listener for different leaflet actions
     */
    $scope.$on('leafletDirectiveMap.mainmap.popupclose', function(event, args) {
        // mapService.unsetCurrentGeodata();
    });
    $scope.$on('leafletDirectiveMap.mainmap.popupopen', function(event, args) {
        var popup = args.leafletEvent.popup;
        var newScope = $scope.$new();
        newScope.stream = popup.options.feature;
        $compile(popup._contentNode)(newScope);
        var geodataId = args.leafletEvent.popup._source.feature.id;
        // mapService.setCurrentGeodata(geodataId);
        // var promise = mapService.getMatchingContext(geodataId);
        // promise.then(function(response) {
        //     if(response.error) {
        //         modalFactory.errorModal(response.error);
        //     } else {
        //         var matchingId = response.context_id;
        //         if(matchingId !== null) {
        //             mainService.expandTree(matchingId);
        //             mainService.setCurrentElement(mainService.contexts.data[matchingId], mainService.currentElement, false);
        //         } else {
        //             var dontUnsetUnlinked = true;
        //             mainService.unsetCurrentElement(dontUnsetUnlinked);
        //         }
        //     }
        // });
    });
    /**
     * If the marker has been created, add the marker to the marker-array and store it in the database
     */
    $scope.$on('leafletDirectiveDraw.mainmap.draw:created', function(event, args) {
        var type = args.leafletEvent.layerType;
        var layer = args.leafletEvent.layer;
        var coords = mapService.getCoords(layer, type);
        mapService.addGeodata(type, coords);
    });
    $scope.$on('leafletDirectiveDraw.mainmap.draw:edited', function(event, args) {
        var layers = args.leafletEvent.layers.getLayers();
        angular.forEach(layers, function(layer, key) {
            var type = layer.feature.geometry.type;
            var coords = mapService.getCoords(layer, type);
            var id = layer.feature.id;
            mapService.addGeodata(type, coords, id);
        });
    });
    $scope.$on('leafletDirectiveDraw.mainmap.draw:deleted', function(event, args) {
        var layers = args.leafletEvent.layers.getLayers();
        angular.forEach(layers, function(layer, key) {
            var id = layer.feature.id;
            httpDeleteFactory('api/geodata/' + id, function(response) {
                if(response.error) {
                    modalFactory.errorModal(response.error);
                    return;
                }
                delete $scope.geodata.linkedContexts[id];
            });
        });
    });

    $scope.linkGeodata = function(cid, gid) {
        var promise = mapService.linkGeodata(cid, gid);
        promise.then(function(response) {
            if(response.error) {
                modalFactory.errorModal(response.error);
                return;
            }
            var updatedContext = response.context;
            var updatedValues = {
                geodata_id: updatedContext.geodata_id
            };
            mainService.updateContextById(cid, updatedValues);
            $scope.geodata.linkedContexts[gid] = cid;
            $scope.geodata.linkedLayers[gid].bindTooltip($scope.contexts.data[cid].name);
        });
    };

    $scope.unlinkGeodata = function(cid) {
        var promise = mapService.unlinkGeodata(cid);
        promise.then(function(response) {
            if(response.error) {
                modalFactory.errorModal(response.error);
                return;
            }
            var updatedValues = {
                geodata_id: undefined
            };
            mainService.updateContextById(cid, updatedValues);
            delete $scope.geodata.linkedContexts[$scope.currentGeodata.id];
            linkedLayer = $scope.geodata.linkedLayers[$scope.currentGeodata.id];
            linkedLayer.bindTooltip(linkedLayer.feature.properties.name);
        });
    };

    $scope.isEmpty = function(obj) {
        if (typeof obj === 'undefined') return false;
        return Object.keys(obj).length === 0;
    };
}]);
