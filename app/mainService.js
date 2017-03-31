spacialistApp.service('mainService', ['httpGetFactory', 'httpGetPromise', 'httpPostFactory', 'httpPostPromise', 'modalFactory', '$uibModal', 'moduleHelper', 'imageService', 'literatureService', 'mapService', 'snackbarService', '$timeout', '$translate', function(httpGetFactory, httpGetPromise, httpPostFactory, httpPostPromise, modalFactory, $uibModal, moduleHelper, imageService, literatureService, mapService, snackbarService, $timeout, $translate) {
    var main = {};
    var modalFields;

    main.currentElement = {
        element: {},
        form: {},
        data: {},
        fields: {},
        sources: {}
    };
    main.contextList = [];
    main.contexts = [];
    main.contextReferences = {};
    main.artifacts = [];
    main.artifactReferences = {};
    main.dropdownOptions = {};
    main.dimensionUnits = [
        'nm', 'µm', 'mm', 'cm', 'dm', 'm', 'km'
    ];
    // main.legendList = {};

    main.datepickerOptions = {
        showWeeks: false,
        maxDate: new Date()
    };
    // $scope.date = {
    //     opened: false
    // };

    init();

    function init() {
        getContexts();
        getArtifacts();
        getDropdownOptions();
        getContextList();
        mapService.reinitVariables();
    }

    function getContexts() {
        httpGetFactory('api/context/get', function(callback) {
            angular.forEach(callback, function(value, key) {
                var index = value.index;
                var title = value.title;
                if(typeof main.contextReferences[index] === 'undefined') {
                    main.contextReferences[index] = [];
                    main.contexts.push({
                        title: title,
                        index: index,
                        type: value.type,
                        context_type_id: value.context_type_id
                    });
                }
                if(value.context_type_id && value.aid && value.val && value.datatype) {
                    main.contextReferences[index].push({
                        aid: value.aid,
                        val: value.val,
                        context_type_id: value.context_type_id,
                        datatype: value.datatype,
                        position: value.position
                    });
                }
            });
        });
    }

    function getArtifacts() {
        httpGetFactory('api/context/artifacts/get', function(callback) {
            angular.forEach(callback, function(value, key) {
                var index = value.index;
                var title = value.title;
                if (typeof main.artifactReferences[index] === 'undefined') {
                    main.artifactReferences[index] = [];
                    main.artifacts.push({
                        title: title,
                        index: index,
                        type: value.type,
                        context_type_id: value.context_type_id
                    });
                }
                if (value.context_type_id && value.aid && value.val && value.datatype) {
                    main.artifactReferences[index].push({
                        aid: value.aid,
                        val: value.val,
                        context_type_id: value.context_type_id,
                        datatype: value.datatype,
                        position: value.position
                    });
                }
            });
        });
    }

    function getDropdownOptions() {
        httpGetFactory('api/context/getChoices', function(callback) {
            for(var i=0; i<callback.length; i++) {
                var value = callback[i];
                var index = value.aid + "_";
                if(typeof value.oid != 'undefined') index += value.oid;
                if (value.datatype == 'string-sc' || value.datatype == 'string-mc') {
                    if(value.choices !== null) {
                        main.dropdownOptions[index] = value.choices;
                    }
                } else if (value.datatype == 'epoch') {
                    main.dropdownOptions[index] = value.choices;
                }
            }
        });
    }

    main.duplicateElement = function($itemScope) {
        var parent = $itemScope.parent;
        var id = parent.id;
        httpGetFactory('api/context/duplicate/' + id, function(newElem) {
            var copy = newElem.obj;
            var elem = {
                id: copy.id,
                name: copy.name,
                context_type_id: copy.context_type_id,
                root_cid: parent.root_cid,
                reclevel: parent.reclevel,
                typeid: parent.typeid,
                typename: parent.typename,
                typelabel: parent.typelabel,
                icon: copy.icon,
                color: copy.color,
                lat: copy.lat,
                lng: copy.lng,
                data: copy.data,
                children: [],
                position: copy.position
            };
            $itemScope.$parent.$parent.$modelValue.push(elem);
            addMarker(elem);
            main.setCurrentElement(elem, main.currentElement);
        });
    };

    function getContextList() {
        //main.getContextListStarted = true;
        httpGetFactory('api/context/getRecursive', function(contextList) {
            for(var i=0; i<contextList.length; i++) {
                var current = contextList[i];
                current.collapsed = true;
                main.contextList.push(current);
                // if(!main.legendList[current.typelabel]) {
                //     main.legendList[current.typelabel] = {
                //         name: current.typelabel,
                //         color: main.getColorForId(current.typename)
                //     };
                // }
                if(current.children) addMetadata(current.children);
            }
            // mapService.addLegend(main.legendList);
            mapService.getGeodata(main.contextList);
            //main.getContextListStarted = false;
        });
    }

    function addMetadata(contexts) {
        for(var i=0; i<contexts.length; i++) {
            var current = contexts[i];
            current.collapsed = true;
            // if(!main.legendList[current.typelabel]) {
            //     main.legendList[current.typelabel] = {
            //         name: current.typelabel,
            //         color: main.getColorForId(current.typename)
            //     };
            // }
            if(current.children) addMetadata(current.children);
        }
    }

    // function displayMarkers(contextList) {
    //     if(!moduleHelper.controllerExists('mapCtrl')) return;
    //     mapService.displayMarkers(contextList);
    // }

    // var addMarker = function(elem) {
    //     if(!moduleHelper.controllerExists('mapCtrl')) return;
    //     scopeService.addMarker(elem);
    // };

    // var setMarker = function(currentElement, focus) {
    //     var name = currentElement.name.replace(/-/, '');
    //     if(typeof scopeService.markers[name] != 'undefined') scopeService.markers[name].focus = focus;
    // };

    main.createNewContext = function(data) {
        if(main.hasUnstagedChanges()) {
            var onDiscard = function() {
                main.currentElement.form.$setPristine();
                return main.createNewContext(data);
            };
            var onConfirm = function() {
                main.currentElement.form.$setPristine();
                main.storeElement(main.currentElement.element, main.currentElement.data);
                return main.createNewContext(data);
            };
            modalFactory.warningModal('context-form.confirm-discard', onConfirm, onDiscard);
            return;
        }
        defaults = {
            reclevel: -1,
            children: main.contextList
        };
        $translate('create-dialog.new-top-context').then(function(translation) {
            defaults.name = translation;
            var parent = defaults;
            angular.extend(parent, data);
            main.createModalHelper({
                parent: parent,
                expand: function() {}
            }, 'context', true);
        });
    };

    main.storeElement = function(elem, data) {
        var parsedData = [];
        for(var key in data) {
            if(data.hasOwnProperty(key)) {
                var value = data[key];
                if(key != 'name' && !key.endsWith('pos') && !key.endsWith('desc')) {
                    var attr = {};
                    attr.key = key;
                    attr.value = value;
                    parsedData.push(attr);
                }
            }
        }
        elem.data = parsedData;
        var promise = storeElement(elem);
        promise.then(function(response){
            if(response.error){
                modalFactory.errorModal(response.error);
                return;
            }
            elem.data = response.data;
        });
    };

    /**
     * Stores a single element of the context tree in the database
     * @return: returns a promise which returns the ID of the newly inserted context
     */
    function storeElement(elem) {
        console.log("store context " + elem.name);
        var root_cid = elem.root_cid;
        var formData = new FormData();
        formData.append('name', elem.name);
        formData.append('context_type_id', elem.context_type_id);
        if(root_cid != -1) {
            formData.append('root_cid', root_cid);
        }
        if(typeof elem.id !== 'undefined' && elem.id != -1) {
            formData.append('id', elem.id);
        }
        for(var i=0; i<elem.data.length; i++) {
            var d = elem.data[i];
            var currValue = '';
            if (typeof d.value === 'object') {
                currValue = angular.toJson(d.value);
            } else {
                currValue = d.value;
            }
            formData.append(d.key, currValue);
        }
        var promise = httpPostPromise.getData('api/context/set', formData);
        return promise;
    }

    main.deleteElement = function(elem) {
        var toDelete = true;
        httpGetPromise.getData('api/context/get/parents/' + elem.id).then(
            function(response) {
                if(response.error) {
                    modalFactory.errorModal(response.error);
                    return;
                }
                var path = response.path;
                modalFactory.deleteModal(elem.name, function() {
                    deleteElement(elem, function() {
                        updateContext(path, {}, toDelete);
                        if(main.currentElement.element.id == elem.id) {
                            main.unsetCurrentElement();
                        }
                    });
                }, 'delete-confirm.warning');
            }
        );
    };

    function deleteElement(elem, onSuccess) {
        console.log("Removing element " + elem.name + " with ID " + elem.id);
        httpGetFactory('api/context/delete/' + elem.id, function(callback) {
            onSuccess();
        });
    }

    main.openSourceModal = function(fieldname, fieldid, currentVal, currentDesc) {
        var aid = fieldid;
        var cid = main.currentElement.element.id;
        if(!main.currentElement.sources['#'+aid]) {
            main.currentElement.sources['#'+aid] = [];
        }
        modalFields = {
            name: fieldname,
            id: aid,
            literature: literatureService.literature.slice(),
            addedSources: main.currentElement.sources['#'+aid],
            value: currentVal || 100,
            description: currentDesc,
            setPossibility: function(event) {
                var max = event.currentTarget.scrollWidth;
                var click = event.originalEvent.layerX;
                var curr = modalFields.value;
                var newVal = parseInt(click/max*100);
                if(Math.abs(newVal-curr) < 10) {
                    if(newVal > curr) newVal = parseInt((newVal+10)/10)*10;
                    else newVal = parseInt(newVal/10)*10;
                } else {
                    newVal = parseInt((newVal+5)/10)*10;
                }
                event.currentTarget.children[0].style.width = newVal+"%";
                modalFields.value = newVal;
            }
        };
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/source-modal.html',
            windowClass: 'wide-modal shrinked-modal',
            controller: function($uibModalInstance) {
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.savePossibility = function() {
                    var formData = new FormData();
                    formData.append('cid', cid);
                    formData.append('aid', aid);
                    formData.append('possibility', modalFields.value);
                    if(modalFields.description) formData.append('possibility_description', modalFields.description);
                    httpPostFactory('api/context/set/possibility', formData, function(callback) {
                        main.currentElement.data[aid+'_pos'] = modalFields.value;
                        main.currentElement.data[aid+'_desc'] = modalFields.description;
                    });
                };
                this.savePossibilityAndClose = function() {
                    this.savePossibility();
                    this.cancel();
                };
                this.modalFields = modalFields;
                this.addSource = addSource;
                this.deleteSourceEntry = main.deleteSourceEntry;
            },
            //scope: $scope
            controllerAs: 'mc'
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    };

    /**
     * Adds the current selected source entry `currentSource` with the given description `currentDesc` for the given attribute `aid` to the database and the source modal window array
     */
    function addSource(currentSource, currentDesc, aid) {
        if(typeof main.currentElement.element.id == 'undefined') return;
        if(!currentDesc) currentDesc = '';
        var cid = main.currentElement.element.id;
        var formData = new FormData();
        formData.append('cid', cid);
        formData.append('aid', aid);
        formData.append('lid', currentSource.id);
        formData.append('desc', currentDesc);
        httpPostFactory('api/sources/add', formData, function(response) {
            var source = response.source;
            addContextSource(source);
        });
        modalFields.currentSource = undefined;
        modalFields.currentDesc = undefined;
    }

    /**
     * Remove a source entry at the given index `index` from the given array `arr`.
     */
    main.deleteSourceEntry = function(index, key) {
        var src = main.currentElement.sources[key][index];
        var id = src.id;
        var title = src.literature.title + ' (' + src.description + ')';
        modalFactory.deleteModal(title, function() {
            httpGetFactory('api/sources/delete/'+id, function(callback) {
                main.currentElement.sources[key].splice(index, 1);
            });
        }, '');
    };

    function parseData(data) {
        var parsedData = {};
        for(var i=0; i<data.length; i++) {
            var value = data[i];
            var index = value.attribute_id + '_' + (value.o_id || '');
            var posIndex = index + 'pos';
            var descIndex = index + 'desc';
            var val = value.str_val;
            var dType = value.datatype;
            parsedData[posIndex] = value.possibility || 100;
            parsedData[descIndex] = value.possibility_description;
            if(dType == 'list') {
                if(typeof parsedData[index] == 'undefined') parsedData[index] = [];
                parsedData[index].push({
                    name: val
                });
            } else if(dType == 'string-sc') {
                parsedData[index] = value.val;
            } else if(dType == 'string-mc') {
                if(typeof parsedData[index] == 'undefined') parsedData[index] = [];
                parsedData[index].push(value.val);
            } else if(dType == 'dimension') {
                if(typeof value.val != 'undefined') parsedData[index] = JSON.parse(value.val);
            } else if(dType == 'epoch') {
                if(typeof value.val != 'undefined') parsedData[index] = JSON.parse(value.val);
            } else if(dType == 'geography') {
                console.log(value.val);
                parsedData[index] = value.val;
            } else {
                parsedData[index] = val;
            }
        }
        return parsedData;
    }

    main.updateContextById = function(id, newValues, toDelete) {
        toDelete = toDelete || false;
        httpGetFactory('api/context/get/parents/' + id, function(response) {
            if(response.error) {
                modalFactory.errorModal(response.error);
                return;
            }
            updateContext(response.path, newValues, toDelete);
        });
    };

    function updateContext(path, values, toDelete) {
        var t = angular.element(document.getElementById('context-tree')).scope();
        var nodesScope = t.$nodesScope;
        var children = nodesScope.childNodes();
        updateContextHelper(path, children, values, 0, toDelete);
    }

    function updateContextHelper(pathArray, children, values, depth, toDelete) {
        var level = pathArray[depth];
        for(var j=0; j<children.length; j++) {
            var child = children[j];
            if(level.id == child.$modelValue.id) {
                if(pathArray.length - 1 == depth) {
                    if(toDelete) {
                        child.remove();
                    } else {
                        angular.merge(main.currentElement.element, values);
                        angular.merge(child.$modelValue, values);
                    }
                    break;
                }
                // child.expand();
                // calling expand() on child should be enough, but child.childNodes() then returns an array with undefined values.
                //Thus we use this "simple" DOM-based method to simulate a click on the element and toggle it.
                //This only works because we broadcast the collapse-all event beforehand.
                $timeout(function() {
                    //we have to expand the element if it is collapsed to get access to the childnodes
                    var wasCollapsed = child.collapsed;
                    if(wasCollapsed) {
                        child.$element[0].firstChild.childNodes[2].click();
                    }
                    children = child.childNodes();
                    depth++;
                    updateContextHelper(pathArray, children, values, depth, toDelete);
                }, 0, false);
                break;
            }
        }
    }

    main.expandTreeTo = function(id) {
        main.contextList.forEach(function(node) {
            shouldExpand(node, id);
        });
    };

    function shouldExpand(node, id) {
        if(node.id == id) {
            main.setCurrentElement(node, main.currentElement, false);
            return true;
        }
        for(var i in node.children) {
            if(shouldExpand(node.children[i], id)) {
                node.collapsed = false;
                return true;
            }
        }
        return false;
    }

    main.unsetCurrentElement = function(dontUnsetUnlinked) {
        dontUnsetUnlinked = dontUnsetUnlinked || false;
        if(typeof main.currentElement == 'undefined') return;
        if(dontUnsetUnlinked) {
            console.log(main.currentElement.element.geodata_id);
            if(typeof main.currentElement.element.geodata_id == 'undefined' || main.currentElement.element.geodata_id === null) {
                return;
            }
        }
        main.currentElement.element = {};
        main.currentElement.data = {};
        main.currentElement.fields = {};
        main.currentElement.sources = {};
    };

    function addContextSource(source) {
        var index = '#' + source.attribute_id;
        if(typeof main.currentElement.sources[index] == 'undefined') {
            main.currentElement.sources[index] = [];
        }
        main.currentElement.sources[index].push(source);
    }

    main.hasUnstagedChanges = function() {
        return main.currentElement.form && main.currentElement.form.$dirty;
    };

    main.setCurrentElement = function(target, elem, openAgain) {
        if(main.hasUnstagedChanges()) {
            var onDiscard = function() {
                main.currentElement.form.$setPristine();
                return main.setCurrentElement(target, elem, openAgain);
            };
            var onConfirm = function() {
                main.currentElement.form.$setPristine();
                main.storeElement(main.currentElement.element, main.currentElement.data);
                return main.setCurrentElement(target, elem, openAgain);
            };
            modalFactory.warningModal('context-form.confirm-discard', onConfirm, onDiscard);
            return;
        }
        if(typeof elem != 'undefined' && elem.id == target.id) {
            main.unsetCurrentElement();
            if(mapService.getPopupGeoId() == elem.geodata_id) mapService.closePopup();
            return;
        }
        var isCurrentlyLinked = mapService.getPopupGeoId() == elem.geodata_id;
        elem = target;
        console.log(elem);
        if(elem.typeid === 0) { //context
            elem.fields = main.contextReferences[elem.typename].slice();
        } else if(elem.typeid == 1) { //find
            elem.fields = main.artifactReferences[elem.typename].slice();
        }
        var content = 'Loading data of ' + elem.name;
        snackbarService.addPersistentSnack('getElemData', content);
        var data = {};
        httpGetFactory('api/context/get/data/' + elem.id, function(response) {
            if(response.error) {
                modalFactory.errorModal(response.error);
                return;
            }
            snackbarService.closeSnack('getElemData');
            var content = 'Successfully loaded data of ' + elem.name;
            snackbarService.addAutocloseSnack(content, 'warning');
            data = parseData(response.data);
            main.currentElement.data = data;
        });
        main.currentElement.sources = {};
        httpGetFactory('api/sources/get/' + elem.id, function(response) {
            if(response.error) {
                modalFactory.errorModal(response.error);
                return;
            }
            var sources = response.sources;
            for(var k in main.currentElement.sources) {
                if(main.currentElement.sources.hasOwnProperty(k)) {
                    main.currentElement.sources[k].length = 0;
                    delete main.currentElement.sources[k];
                }
            }
            angular.forEach(sources, function(source, i) {
                addContextSource(source);
            });
        });
        var lastmodified = elem.updated_at || elem.created_at;
        var d = new Date(lastmodified);
        main.currentElement.fields = elem.fields;
        elem.lastmodified = d.toLocaleDateString() + ' ' + d.toLocaleTimeString();
        elem.root_cid = elem.root_cid || -1;
        main.currentElement.element = elem;
        if(typeof openAgain == 'undefined') openAgain = true;
        if(elem.geodata_id !== null && openAgain) {
            mapService.openPopup(elem.geodata_id);
        } else if(elem.geodata_id === null && isCurrentlyLinked) {
            mapService.closePopup();
        }
        loadLinkedImages(main.currentElement.element.id);
    };

    main.openGeographyModal = function($scope, aid) {
        var inp = document.getElementById('a' + aid);
        if(inp.value) {
            var formData = new FormData();
            formData.append('wkt', inp.value);
            httpPostFactory('api/context/wktToGeojson', formData, function(response) {
                if(response.error) return;
                var feature = {
                    type: 'Feature',
                    id: 1,
                    geometry: {
                        type: response.geometry.type,
                        coordinates: response.geometry.coordinates
                    }
                };
                geojson.data.features.push(feature);
            });
        }
        var featureGroup = new L.FeatureGroup();
        var createdListener = $scope.$on('leafletDirectiveMap.placermap.draw:created', function(event, args) {
            var type = args.leafletEvent.layerType;
            switch(type) {
                case 'marker':
                    type = 'Point';
                    break;
                case 'polyline':
                    type = 'LineString';
                    break;
                case 'polygon':
                    type = 'Polygon';
                    break;
            }
            var layer = args.leafletEvent.layer;
            var coords = [];
            if(type == 'Point') {
                var latlng = layer.getLatLng();
                coords.push(latlng.lng);
                coords.push(latlng.lat);
            } else {
                var latlngs = layer.getLatLngs();
                for(var i=0; i<latlngs.length; i++) {
                    var curr = latlngs[i];
                    var arr = [];
                    arr.push(curr.lng);
                    arr.push(curr.lat);
                    coords.push(arr);
                }
                if(type == 'Polygon') {
                    coords.push(coords[0]);
                    var newCoords = [];
                    newCoords.push(coords);
                    coords = newCoords;
                }
            }
            var feature = {
                type: 'Feature',
                id: 1,
                geometry: {
                    type: type,
                    coordinates: coords
                }
            };
            geojson.data.features.push(feature);
        });
        var drawStartListener = $scope.$on('leafletDirectiveMap.placermap.draw:drawstart', function(event, args) {
            featureGroup.clearLayers();
            geojson.data.features.length = 0;
        });
        var drawOptions = angular.copy(mapService.map.drawOptions);
        var bounds = mapService.map.bounds;
        var geojson = {
            data: {
                type: 'FeatureCollection',
                features: []
            },
            pointToLayer: function(feature, latlng) {
                return L.circleMarker(latlng);
            },
            onEachFeature: function(feature, layer) {
                featureGroup.addLayer(layer);
                var newBounds = featureGroup.getBounds();
                var newNE = newBounds.getNorthEast();
                var newSW = newBounds.getSouthWest();
                bounds.northEast.lat = newNE.lat;
                bounds.northEast.lng = newNE.lng;
                bounds.southWest.lat = newSW.lat;
                bounds.southWest.lng = newSW.lng;
            }
        };
        drawOptions.edit.featureGroup = featureGroup;
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/map-placer.html',
            windowClass: 'wide-modal',
            scope: $scope,
            controller: function($uibModalInstance) {
                this.drawOptions = drawOptions;
                this.controls = mapService.map.controls;
                this.bounds = bounds;
                this.geojson = geojson;
                this.layers = mapService.map.layers;
                this.cancel = function(result) {
                    createdListener();
                    drawStartListener();
                    $uibModalInstance.dismiss('cancel');
                };
                this.finish = function(event, args) {
                    createdListener();
                    drawStartListener();
                    var layers = featureGroup.getLayers();
                    if(layers.length == 1) {
                        var layer = layers[0];
                        var wkt = mapService.toWkt(layer);
                        inp.value = wkt;
                        angular.element(inp).change(); // hack to dirty the input field
                    } else {
                        inp.value = '';
                        angular.element(inp).change();
                    }
                    $uibModalInstance.dismiss('ok');
                };
            },
            controllerAs: 'mc'
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    };

    main.createModalHelper = function($itemScope, elemType, copyPosition) {
        if(main.hasUnstagedChanges()) {
            var onDiscard = function() {
                main.currentElement.form.$setPristine();
                return main.createModalHelper($itemScope, elemType, copyPosition);
            };
            var onConfirm = function() {
                main.currentElement.form.$setPristine();
                main.storeElement(main.currentElement.element, main.currentElement.data);
                return main.createModalHelper($itemScope, elemType, copyPosition);
            };
            modalFactory.warningModal('context-form.confirm-discard', onConfirm, onDiscard);
            return;
        }
        var parent = $itemScope.parent;
        var selection = [];
        var msg = '';
        if(elemType == 'context') {
            selection = main.contexts.slice();
            msg = 'create-dialog.new-context-description';
        } else if(elemType == 'find') {
            selection = main.artifacts.slice();
            msg = 'create-dialog.new-artifact-description';
        }
        modalFactory.createModal(parent.name, msg, selection, function(name, type) {
            var formData = new FormData();
            formData.append('name', name);
            formData.append('context_type_id', type.context_type_id);
            if(typeof parent.id != 'undefined') formData.append('root_cid', parent.id);
            httpPostFactory('api/context/set', formData, function(response) {
                var newContext = response.context;
                var elem = {
                    id: newContext.id,
                    name: name,
                    context_type_id: type.context_type_id,
                    root_context_id: parent.id,
                    reclevel: parent.reclevel + 1,
                    typeid: type.type,
                    typename: type.index,
                    typelabel: type.title,
                    data: [],
                    children: [],
                    lasteditor: newContext.lasteditor,
                    updated_at: newContext.updated_at,
                    created_at: newContext.created_at
                };
                parent.children.push(elem);
                main.setCurrentElement(elem, main.currentElement);
                $itemScope.expand();
            });
        });
    };

    function loadLinkedImages(id) {
        if(!moduleHelper.controllerExists('imageCtrl')) return;
        imageService.getImagesForContext(id);
    }

    main.isEmpty = function(obj) {
        if (typeof obj === 'undefined') return false;
        return Object.keys(obj).length === 0;
    };

    /**
     * `name` is the name of the controller which is bound to this module
     * @returns whether the given controller exists or not
     */
    main.moduleExists = function(name) {
        return moduleHelper.controllerExists(name);
    };

    /**
     * @returns hash code for a given string `str`
     */
    function getHashCode(str) {
        var hash = 0;
        if(str.length === 0) return hash;
        for(var i=0; i<str.length; i++) {
            var chr = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + chr;
            hash |= 0;
        }
        return hash;
    }

    /**
     * @returns json object with color settings for different context types, based on their id `id`.
     * Colors are in hsl format with fixed saturation and lightness. Hue is computed based on the `id`'s hash.
     */
    main.getColorForId = function(id) {
        var sat = '90%';
        var lgt = '65%';
        var hue = 0;
        if(typeof id !== 'undefined') {
            var newId = '';
            for(var i=0; i<id.length; i++) {
                newId += id.charCodeAt(i);
            }
            hue = getHashCode(newId) % 360;
        }
        var hsl = [
            hue,
            sat,
            lgt
        ];
        return {
            'color': 'hsl(' + hsl.join(',') + ')'
        };
    };

    return main;
}]);
