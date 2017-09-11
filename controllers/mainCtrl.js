spacialistApp.controller('mainCtrl', ['$scope', 'mainService', 'mapService', 'fileService', '$uibModal', '$state', '$translate', '$timeout', '$compile', function($scope, mainService, mapService, fileService, $uibModal, $state, $translate, $timeout, $compile) {
    var vm = this;
    vm.dimensionUnits = mainService.dimensionUnits;
    vm.currentElement = mainService.currentElement;
    vm.currentGeodata = mapService.currentGeodata;
    vm.isLinkPossible = mapService.isLinkPossible;

    $scope.moduleExists = mainService.moduleExists;
    $scope.filterTree = mainService.filterTree;

    vm.onStore = function(context, data) {
        mainService.storeElement(context, data);
        var c = vm.contexts.data[context.id];
        for(var k in context) {
            if(context.hasOwnProperty(k)) {
                c[k] = context[k];
            }
        }
        vm.currentElement.form.$setPristine();
    };

    vm.onSourceAdd = function(entry) {
        console.log(entry);
    };

    if(vm.tab == 'map') {
        mapService.setupLayers(vm.layer, vm.map, vm.contexts, vm.concepts);
        mapService.initMapObject().then(function(obj) {
            vm.mapObject = obj;
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
    }

    vm.openNewContextModal = function(type, parent) {
        $uibModal.open({
            templateUrl: "modals/add-context.html",
            controller: ['$scope', function($scope) {
                $scope.contexts = vm.contexts;
                $scope.concepts = vm.concepts;
                $scope.type = type;
                $scope.parent = parent;

                if($scope.type == 'context') {
                    $scope.contextTypes = vm.contextTypes.filter(function(t) {
                        return t.type === 0;
                    });
                } else if($scope.type == 'find') {
                    $scope.contextTypes = vm.contextTypes.filter(function(t) {
                        return t.type == 1;
                    });
                }
                $scope.newContext = {
                    name: '',
                    type: ''
                };
                $scope.newContext.parent = $scope.parent > 0 ? $scope.parent : undefined;

                $scope.cancel = function() {
                    $scope.$dismiss();
                };

                $scope.onAdd = function(c) {
                    mainService.addContext(c).then(function(response) {
                        var newContext = response;
                        mainService.addContextToTree(newContext, c.parent, vm.contexts);
                        $scope.$close(true);
                        $state.go('root.spacialist.data', {id: newContext.id});
                    });
                };
            }],
            controllerAs: '$ctrl'
        });
    };

    vm.updateMarkerOptions = function(geodata) {
        mapService.updateMarker(geodata);
    };

    $scope.treeCallbacks = {
        toggle: function(collapsed, sourceNodeScope) {
            mainService.treeCallbacks.toggle(collapsed, sourceNodeScope, vm.contexts);
        },
        dropped: function(event) {
            mainService.treeCallbacks.dropped(event, vm.contexts);
        }
    };

    $scope.treeOptions = {
        getColorForId: mainService.getColorForId,
        'new-context-link': 'root.spacialist.add({type: "context"})'
    };

    $scope.newElementContextMenu = [
        [
            function($itemScope, $event, modelValue, text, $li) {
                return $translate.instant('context-menu.options-of', { object: vm.contexts.data[$itemScope.$parent.id].name });
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
                vm.openNewContextModal('find', $itemScope.$parent.id);
        }, function($itemScope) {
            return vm.contexts.data[$itemScope.$parent.id].type === 0;
        }],
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">add_circle_outline</i> ' + $translate.instant('context-menu.new-context');
            },
            function($itemScope, $event, modelValue, text, $li) {
                vm.openNewContextModal('context', $itemScope.$parent.id);
        }, function($itemScope) {
            return vm.contexts.data[$itemScope.$parent.id].type === 0;
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">content_copy</i> ' + $translate.instant('context-menu.duplicate-element');
            },
                function($itemScope, $event, modelValue, text, $li) {
            mainService.duplicateElement($itemScope.$parent.id, vm.contexts);
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-red context-menu-icon">delete</i> ' + $translate.instant('context-menu.delete');
            },
            function($itemScope, $event, modelValue, text, $li) {
                $state.go('root.spacialist.data.delete', {id: $itemScope.$parent.id});
            }
        ]
    ];

    $scope.hasSources = function(element) {
        return Object.keys(element.sources).length > 0;
    };

    // MAP RELATED CODE
    /**
     * listener for different leaflet actions
     */
    $scope.$on('leafletDirectiveMap.mainmap.popupclose', function(event, args) {
        // mapService.unsetCurrentGeodata();
        // $state.go('^');
    });
    $scope.$on('leafletDirectiveMap.mainmap.popupopen', function(event, args) {
        var popup = args.leafletEvent.popup;
        var newScope = $scope.$new();
        newScope.stream = popup.options.feature;
        $compile(popup._contentNode)(newScope);
        // var geodataId = args.leafletEvent.popup._source.feature.id;
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
        mapService.addGeodata(type, coords, -1, vm.contexts, vm.map);
    });
    $scope.$on('leafletDirectiveDraw.mainmap.draw:edited', function(event, args) {
        var layers = args.leafletEvent.layers.getLayers();
        angular.forEach(layers, function(layer, key) {
            var type = layer.feature.geometry.type;
            var coords = mapService.getCoords(layer, type);
            var id = layer.feature.id;
            mapService.addGeodata(type, coords, id, vm.contexts, vm.map);
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
                delete vm.map.geodata.linkedContexts[id];
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
            mainService.updateContextById(vm.contexts, cid, updatedValues);
            vm.map.geodata.linkedContexts[gid] = cid;
            vm.map.geodata.linkedLayers[gid].bindTooltip(vm.contexts.data[cid].name);
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
            mainService.updateContextById(vm.contexts, cid, updatedValues);
            delete vm.map.geodata.linkedContexts[vm.currentGeodata.id];
            linkedLayer = vm.map.geodata.linkedLayers[vm.currentGeodata.id];
            linkedLayer.bindTooltip(linkedLayer.feature.properties.name);
        });
    };

    // FILE RELATED CODE

    var linkFileContextMenu = [function($itemScope) {
        var f = $itemScope.f;
        var content;
        for(var i=0; i<f.linked_images.length; i++) {
            if(f.linked_images[i].context_id == mainService.currentElement.element.id) {
                content = $translate.instant('photo.unlink-from', { name: mainService.currentElement.element.name });
                break;
            }
        }
        if(!content) {
            content = $translate.instant('photo.link-to', { name: mainService.currentElement.element.name });
        }
        return '<i class="material-icons md-18">add_circle_outline</i> ' + content;
    }, function ($itemScope) {
        var f = $itemScope.f;
        var fileId = f.id;
        var contextId = mainService.currentElement.element.id;
        for(var i=0; i<f.linked_images.length; i++) {
           if(f.linked_images[i].context_id == mainService.currentElement.element.id) {
               fileService.unlinkImage(fileId, contextId);
               return;
           }
        }
        fileService.linkImage(fileId, contextId);
    }, function() {
        return mainService.currentElement.element.id > 0;
    }];
    var deleteFile = [function($itemScope) {
        var content = $translate.instant('photo.delete', { name: $itemScope.f.filename });
       return '<i class="material-icons md-18">delete</i> ' + content;
    }, function ($itemScope, $event, modelValue, text, $li) {
        fileService.deleteFile($itemScope.f, vm.files);
    }];

    vm.fileContextMenu = [
        linkFileContextMenu,
        null,
        deleteFile
    ];

    /**
     * enables drag & drop support for image upload, calls `$scope.uploadImages` if files are dropped on the `dropFiles` model
     */
    $scope.$watch('dropFiles', function() {
        vm.uploadImages($scope.dropFiles);
    });

    vm.uploadImages = function($files, $invalidFiles) {
        vm.uploadStatus = fileService.uploadImages($files, $invalidFiles, vm.files);
    };
}]);
