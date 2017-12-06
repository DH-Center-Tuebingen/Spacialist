spacialistApp.controller('mainCtrl', ['$scope', 'httpDeleteFactory', 'mainService', 'mapService', 'fileService', 'snackbarService', 'modalFactory', '$uibModal', '$state', '$translate', '$timeout', '$compile', function($scope, httpDeleteFactory, mainService, mapService, fileService, snackbarService, modalFactory, $uibModal, $state, $translate, $timeout, $compile) {
    var vm = this;
    vm.isLinkPossible = mapService.isLinkPossible;

    $scope.moduleExists = mainService.moduleExists;
    $scope.filterTree = mainService.filterTree;

    vm.onStore = function(context, data) {
        if(context.form.$invalid) {
            var content = $translate.instant('snackbar.data-stored.error');
            snackbarService.addAutocloseSnack(content, 'error');
            return;
        }
        mainService.storeElement(context, data).then(function(response) {
            if(response.error){
                modalFactory.errorModal(response.error);
                return;
            }
            context.form.$setPristine();
            mainService.updateContextList(vm.contexts, context, response);
        });
    };

    vm.onSetContext = function(id, data) {
        vm.globalContext.context = vm.contexts.data[id];
        for(var k in data) {
            if(data.hasOwnProperty(k)) {
                vm.globalContext[k] = data[k];
            }
        }
    };

    vm.onSetGeodata = function(gid, geodata) {
        if(!gid) {
            for(var k in vm.globalGeodata) {
                if(vm.globalGeodata.hasOwnProperty(k)) {
                    vm.globalGeodata[k] = {};
                }
            }
            return;
        }
        var layer = geodata.linkedLayers[gid];
        if(!layer) return vm.onSetGeodata(undefined);
        vm.globalGeodata.geodata.id = gid;
        vm.globalGeodata.geodata.type = layer.feature.geometry.type;
        vm.globalGeodata.geodata.color = layer.feature.properties.color;

        if(vm.globalGeodata.geodata.type == 'Point') {
            var latlng = layer.getLatLng();
            vm.globalGeodata.geodata.lat = latlng.lat;
            vm.globalGeodata.geodata.lng = latlng.lng;
        } else {
            vm.globalGeodata.geodata.lat = undefined;
            vm.globalGeodata.geodata.lng = undefined;
        }
    };

    if(vm.tab == 'map') {
        mapService.setupLayers(vm.layer, vm.map, vm.contexts, vm.concepts);
        mapService.initMapObject('mainmap').then(function(obj) {
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
                mapService.initGeodata(vm.geodata, vm.contexts, vm.map, true);
            }, 100);
        });
    }

    vm.openNewContextModal = function(type, parent) {
        $uibModal.open({
            templateUrl: "modals/add-context.html",
            controller: ['$scope', function($scope) {
                $scope.contexts = vm.contexts;
                $scope.concepts = vm.concepts;
                $scope.userConfig = vm.userConfig;
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
                        $state.go('root.spacialist.context.data', {id: newContext.id});
                    });
                };
            }],
            controllerAs: '$ctrl'
        });
    };

    vm.updateMarkerOptions = function(geodata) {
        mapService.updateMarker(geodata, vm.map);
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
                return '' + $translate.instant('context-menu.options-of', { object: vm.contexts.data[$itemScope.$modelValue.id].name });
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
                vm.openNewContextModal('find', $itemScope.$modelValue.id);
        }, function($itemScope) {
            return vm.contexts.data[$itemScope.$modelValue.id].type === 0;
        }],
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">add_circle_outline</i> ' + $translate.instant('context-menu.new-context');
            },
            function($itemScope, $event, modelValue, text, $li) {
                vm.openNewContextModal('context', $itemScope.$modelValue.id);
        }, function($itemScope) {
            return vm.contexts.data[$itemScope.$modelValue.id].type === 0;
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-green context-menu-icon">content_copy</i> ' + $translate.instant('context-menu.duplicate-element');
            },
                function($itemScope, $event, modelValue, text, $li) {
            mainService.duplicateElement($itemScope.$modelValue.id, vm.contexts);
        }],
        null,
        [
            function() {
                return '<i class="material-icons md-18 fa-light fa-red context-menu-icon">delete</i> ' + $translate.instant('context-menu.delete');
            },
            function($itemScope, $event, modelValue, text, $li) {
                $state.go('root.spacialist.context.data.delete', {id: $itemScope.$modelValue.id});
            }
        ]
    ];

    vm.hasSources = function(element) {
        if(!element.sources) return false;
        return Object.keys(element.sources).length > 0;
    };

    // MAP RELATED CODE
    /**
     * listener for different leaflet actions
     */
    $scope.$on('leafletDirectiveMap.mainmap.popupclose', function(event, args) {
    });
    $scope.$on('leafletDirectiveMap.mainmap.popupopen', function(event, args) {
        var popup = args.leafletEvent.popup;
        var newScope = $scope.$new();
        newScope.stream = popup.options.feature;
        $compile(popup._contentNode)(newScope);
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
    $scope.$on('leafletDirectiveDraw.mainmap.draw:deletestart', function(event, args) {
        mapService.modes.deleting = true;
    });
    $scope.$on('leafletDirectiveDraw.mainmap.draw:deletestop', function(event, args) {
        mapService.modes.deleting = false;
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
            delete vm.map.geodata.linkedContexts[vm.globalGeodata.geodata.id];
            linkedLayer = vm.map.geodata.linkedLayers[vm.globalGeodata.geodata.id];
            linkedLayer.bindTooltip(linkedLayer.feature.properties.name);
        });
    };

    // FILE RELATED CODE

    var linkFileContextMenu = [function($itemScope) {
        var f = $itemScope.f;
        var content;
        for(var i=0; i<f.linked_files.length; i++) {
            if(f.linked_files[i].context_id == vm.globalContext.context.id) {
                content = $translate.instant('file.unlink-from', { name: vm.globalContext.context.name });
                break;
            }
        }
        if(!content) {
            content = $translate.instant('file.link-to', { name: vm.globalContext.context.name });
        }
        return '<i class="material-icons md-18">add_circle_outline</i> ' + content;
    }, function ($itemScope) {
        var f = $itemScope.f;
        var fileId = f.id;
        var contextId = vm.globalContext.context.id;
        for(var i=0; i<f.linked_files.length; i++) {
           if(f.linked_files[i].context_id == contextId) {
               fileService.unlinkFile(fileId, contextId);
               return;
           }
        }
        fileService.linkFile(fileId, contextId);
    }, function() {
        return vm.globalContext.context.id > 0;
    }];
    var deleteFile = [function($itemScope) {
        var content = $translate.instant('file.delete', { name: $itemScope.f.filename });
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
     * enables drag & drop support for file upload, calls `$scope.uploadFiles` if files are dropped on the `dropFiles` model
     */
    $scope.$watch('dropFiles', function() {
        vm.uploadFiles($scope.dropFiles);
    });

    vm.uploadFiles = function($files, $invalidFiles) {
        vm.uploadStatus = fileService.uploadFiles($files, $invalidFiles, vm.files);
    };
}]);
