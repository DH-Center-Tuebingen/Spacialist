spacialistApp.controller('mainCtrl', ['$scope', 'mainService', 'mapService', '$state', '$translate', '$timeout', '$compile', function($scope, mainService, mapService, $state, $translate, $timeout, $compile) {
    $scope.moduleExists = mainService.moduleExists;
    $scope.filterTree = mainService.filterTree;

    var localContexts = this.contexts;
    var localConcepts = this.concepts;
    var localLayers = this.layer;
    var localMap = this.map;
    var mapObject;
    var localGeodata = this.geodata;
    var localContextTypes = this.contextTypes;
    var localTab = this.tab;

    this.onStore = function(context, data) {
        mainService.storeElement(context, data);
        var c = this.contexts.data[context.id];
        for(var k in context) {
            if(context.hasOwnProperty(k)) {
                c[k] = context[k];
            }
        }
    };

    this.onSourceAdd = function(entry) {
        console.log(entry);
    };

    if(localTab == 'map') {
        mapService.setupLayers(localLayers, localMap, localContexts, localConcepts);
        mapService.initMapObject().then(function(obj) {
            mapObject = obj;
            // wait a random amount of time, so mapObject.eachLayer has all layers
            $timeout(function() {
                mapObject.eachLayer(function(l) {
                    if(l.options.layer_id) {
                        localMap.mapLayers[l.options.layer_id] = l;
                    }
                });
                mapService.initGeodata(localGeodata, localContexts, localMap);
            }, 100);
        });
    }

    $scope.layerTwo = {
        activeTab: 'map',
        imageTab: {}
    };

    $scope.setActiveTab = function(tabId) {
        $scope.layerTwo.activeTab = tabId;
    };

    $scope.treeCallbacks = {
        toggle: function(collapsed, sourceNodeScope) {
            mainService.treeCallbacks.toggle(collapsed, sourceNodeScope, localContexts);
        },
        dropped: function(event) {
            mainService.treeCallbacks.dropped(event, localContexts);
        }
    };

    $scope.treeOptions = {
        getColorForId: mainService.getColorForId,
        'new-context-link': 'root.spacialist.add({type: "context"})'
    };

    $scope.newElementContextMenu = [
        [
            function($itemScope, $event, modelValue, text, $li) {
                return $translate.instant('context-menu.options-of', { object: localContexts.data[$itemScope.$parent.id].name });
            },
            function($itemScope, $event, modelValue, text, $li) {
            },
            function() { return false; }
        ],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">add_circle_outline</i> ' + $translate.instant('context-menu.new-artifact');
            },
            function($itemScope, $event, modelValue, text, $li) {
                $state.go('root.spacialist.add', {
                    type: 'find',
                    id: $itemScope.$parent.id
                });
        }, function($itemScope) {
            return this.contexts.data[$itemScope.$parent.id].type === 0;
        }],
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">add_circle_outline</i> ' + $translate.instant('context-menu.new-context');
            },
            function($itemScope, $event, modelValue, text, $li) {
                $state.go('root.spacialist.add', {
                    type: 'context',
                    id: $itemScope.$parent.id
                });
        }, function($itemScope) {
            return this.contexts.data[$itemScope.$parent.id].type === 0;
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">content_copy</i> ' + $translate.instant('context-menu.duplicate-element');
            },
                function($itemScope, $event, modelValue, text, $li) {
            mainService.duplicateElement($itemScope.$parent.id);
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-red context-menu-icon">delete</i> ' + $translate.instant('context-menu.delete');
            },
            function($itemScope, $event, modelValue, text, $li) {
                $state.go('root.spacialist.delete', {id: $itemScope.$parent.id});
                // mainService.deleteElement(localContexts, $itemScope.$parent.id);
            }
        ]
    ];

    // MAP RELATED CODE
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
        mapService.addGeodata(type, coords, -1, localContexts, localMap);
    });
    $scope.$on('leafletDirectiveDraw.mainmap.draw:edited', function(event, args) {
        var layers = args.leafletEvent.layers.getLayers();
        angular.forEach(layers, function(layer, key) {
            var type = layer.feature.geometry.type;
            var coords = mapService.getCoords(layer, type);
            var id = layer.feature.id;
            mapService.addGeodata(type, coords, id, localContexts, localMap);
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
                delete localMap.geodata.linkedContexts[id];
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
            localMap.geodata.linkedContexts[gid] = cid;
            localMap.geodata.linkedLayers[gid].bindTooltip(localContexts.data[cid].name);
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
            delete localMap.geodata.linkedContexts[$scope.currentGeodata.id]; // TODO currentGeodata
            linkedLayer = localMap.geodata.linkedLayers[$scope.currentGeodata.id]; // TODO currentGeodata
            linkedLayer.bindTooltip(linkedLayer.feature.properties.name);
        });
    };
}]);
