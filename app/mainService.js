spacialistApp.service('mainService', ['httpGetFactory', 'httpGetPromise', 'httpPostFactory', 'httpPostPromise', 'httpPutFactory', 'httpPutPromise', 'httpPatchFactory', 'httpDeleteFactory', 'httpDeletePromise', 'modalFactory', '$uibModal', 'moduleHelper', 'environmentService', 'imageService', 'literatureService', 'mapService', 'snackbarService', 'searchService', '$timeout', '$state', '$translate', function(httpGetFactory, httpGetPromise, httpPostFactory, httpPostPromise, httpPutFactory, httpPutPromise, httpPatchFactory, httpDeleteFactory, httpDeletePromise, modalFactory, $uibModal, moduleHelper, environmentService, imageService, literatureService, mapService, snackbarService, searchService, $timeout, $state, $translate) {
    var main = {};
    var modalFields;

    main.editMode = {
        enabled: false
    };

    main.currentElement = {
        element: {},
        form: {},
        data: {},
        geometryType: '',
        fields: {},
        sources: {}
    };
    main.contextTypes = [];
    main.contexts = environmentService.contexts;
    main.contextReferences = {};
    main.artifacts = [];
    main.artifactReferences = {};
    main.treeCallbacks = {};
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

    main.treeCallbacks.dropped = function(event, contexts) {
        var hasParent = event.dest.nodesScope.$nodeScope && event.dest.nodesScope.$nodeScope.$modelValue;
        var oldParent = environmentService.getParentId(id);
        var hadParent = oldParent !== null;
        var id = event.source.nodeScope.$modelValue;
        var index = event.dest.index;
        var rank = index + 1;
        var parent;
        var formData = new FormData();
        formData.append('rank', rank);
        if(hasParent) {
            parent = event.dest.nodesScope.$nodeScope.$modelValue;
            formData.append('parent_id', parent);
        }
        httpPatchFactory('api/context/' + id + "/rank", formData, function(response) {
            if(response.error) {
                modalFactory.errorModal(response.error);
                return;
            }
            // element has not been moved
            if(((!hasParent && !hadParent) || parent == oldParent) && index == oldIndex) {
                return;
            }
            var oldIndex = contexts.data[id].rank - 1;
            var startIndex = oldIndex;
            if(((!hasParent && !hadParent) || parent == oldParent) && index < oldIndex) {
                startIndex++;
            }
            var children;
            var oldChildren;
            if(hasParent) {
                children = contexts.children[parent];
            } else {
                children = contexts.roots;
            }
            if(hadParent) {
                oldChildren = contexts.children[oldParent];
            } else {
                oldChildren = contexts.roots;
            }
            var i;
            for(i=startIndex; i<oldChildren.length; i++) {
                contexts.data[oldChildren[i]].rank--;
            }
            contexts.data[id].rank = rank;
            for(i=index+1; i<children.length; i++) {
                contexts.data[children[i]].rank++;
            }
        });
    };
    main.treeCallbacks.toggle = function(collapsed, sourceNodeScope, contexts) {
        contexts.data[sourceNodeScope.$modelValue].collapsed = collapsed;
    };

    main.toggleEditMode = function() {
        main.editMode.enabled = !main.editMode.enabled;
    };

    main.getContextData = function(id) {
        return httpGetPromise.getData('api/context/' + id + '/data').then(function(response) {
            return parseData(response.data);
        });
    };

    main.getContextFields = function(ctid) {
        return httpGetPromise.getData('api/context/context_type/' + ctid + '/attribute').then(function(response) {
            return response;
        });
    };

    main.getDropdownOptions = function() {
        return httpGetPromise.getData('api/context/dropdown_options').then(function(response) {
            console.log(response);
            return response;
        });
    };

    main.duplicateElement = function(id) {
        httpPostFactory('api/context/' + id + '/duplicate', new FormData(), function(newElem) {
            var parent = main.contexts.data[main.contexts.data[id].root_context_id];
            var copy = newElem.obj;
            var elem = {
                id: copy.id,
                name: copy.name,
                context_type_id: copy.context_type_id,
                root_context_id: copy.root_context_id,
                rank: copy.rank,
                typeid: copy.typeid,
                typename: copy.typename,
                typelabel: copy.typelabel,
                data: [],
                lasteditor: copy.lasteditor,
                updated_at: copy.updated_at,
                created_at: copy.created_at
            };
            main.addContextToTree(elem, parent);
            main.setCurrentElement(elem, main.currentElement);
        });
    };


    main.createNewContext = function(data, contextTypes) {
        $translate('create-dialog.new-top-context').then(function(translation) {
            var parent = {name : translation};
            angular.extend(parent, data);
            main.createModalHelper(parent, 'context', contextTypes);
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
        var promise = storeElement(elem, parsedData);
        promise.then(function(response){
            // TODO elem.form.$setPristine();
            var content = $translate.instant('snackbar.data-stored.success');
            snackbarService.addAutocloseSnack(content, 'success');
            if(response.error){
                modalFactory.errorModal(response.error);
                return;
            }
        });
    };

    main.filterTree = function(elements, term) {
        angular.forEach(elements.roots, function(r) {
            isVisible(elements, r, term.toUpperCase());
        });
    };

    main.contextSearch = function(searchString) {
        var formData = new FormData();
        formData.append('val', searchString);
        return httpPostPromise.getData('api/context/search', formData)
        .then(function(response) {
            return response;
        });
    };

    function isVisible(elems, id, term) {
        var noSearchTerm = !term || term.length === 0;
        var data = elems.data;
        var children = elems.children;
        if(noSearchTerm) {
            data[id].visible = true;
            data[id].collapsed = true;
        } else if(filters(data[id], term)) {
            data[id].visible = true;
            data[id].collapsed = true;
        } else {
            data[id].visible = false;
            data[id].collapsed = true;
        }
        if(children[id]) {
            for(var i=0; i<children[id].length; i++) {
                if(data[id].visible) {
                    var tmpId = children[id][i];
                    data[tmpId].visible = true;
                } else if(isVisible(elems, children[id][i], term)) {
                    data[id].visible = true;
                    data[id].collapsed = false;
                } else {
                    data[id].collapsed = false;
                }
            }
        }
        return data[id].visible;
    }

    function filters(elem, term) {
        return elem.name.toUpperCase().indexOf(term) > -1;
    }

    /**
     * Stores a single element of the context tree in the database
     * @return: returns a promise which returns the ID of the newly inserted context
     */
    function storeElement(elem, data) {
        console.log("store context " + elem.name);
        var formData = new FormData();
        formData.append('name', elem.name);

        for(var i=0; i<data.length; i++) {
            var d = data[i];
            var currValue = '';
            if (typeof d.value === 'object') {
                currValue = angular.toJson(d.value);
            } else {
                currValue = d.value;
            }
            formData.append(d.key, currValue);
        }
        var promise;
        if(typeof elem.id !== 'undefined' && elem.id != -1) {
            promise = httpPutPromise.getData('api/context/' + elem.id, formData);
        }
        else {
            promise = httpPostPromise.getData('api/context', formData);
        }
        return promise;
    }

    /**
     * Remove a source entry at the given index `index` from the given array `arr`.
     */
    main.deleteSourceEntry = function(index, key) {
        var src = main.currentElement.sources[key][index];
        var id = src.id;
        var title = src.literature.title + ' (' + src.description + ')';
        modalFactory.deleteModal(title, function() {
            httpDeleteFactory('api/source/'+id, function(callback) {
                main.currentElement.sources[key].splice(index, 1);
            });
        }, '');
    };

    main.addListEntry = function(index, inp, arr) {
        if(typeof arr[index] == 'undefined') arr[index] = [];
        arr[index].push({
            'name': inp[index]
        });
    };

    main.removeListItem = function(index, arr, $index) {
        arr[index].splice($index, 1);
    };

    function parseData(data) {
        var parsedData = {};
        for(var i=0; i<data.length; i++) {
            var value = data[i];
            var index = value.attribute_id;
            var posIndex = index + '_pos';
            var descIndex = index + '_desc';
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
                parsedData[index] = value.val;
            } else if(dType == 'context') {
                parsedData[index] = value.val;
            } else if(dType == 'integer' || dType == 'percentage') {
                parsedData[index] = parseInt(val);
            } else if(dType == 'double') {
                parsedData[index] = parseFloat(value.dbl_val);
            } else if(dType == 'date') {
                parsedData[index] = new Date(value.dt_val);
            } else {
                parsedData[index] = val;
            }
        }
        return parsedData;
    }

    main.updateContextById = function(id, newValues) {
        main.expandTree(main.contexts, id);

        angular.merge(main.currentElement.element, newValues);
        angular.merge(main.contexts.data[id], newValues);
    };

    main.deleteContext = function(context, contexts) {
        var id = context.id;
        return httpDeletePromise.getData('api/context/' + id).then(function(callback) {
            var rootId = contexts.data[id].root_context_id;
            var index;
            var children;
            if(rootId && rootId > 0) {
                index = contexts.children[rootId].indexOf(id);
                children = contexts.children[rootId];
            }
            else {
                index = contexts.roots.indexOf(id);
                children = contexts.roots;
            }
            if(index > -1) {
                children.splice(index, 1);
            }
            for(var i=index; i<children.length; i++) {
                contexts.data[children[i]].rank--;
            }
            delete contexts.data[id];
        });
    };

    /**
    *   This function expands the tree up the selected element
    */
    main.expandTree = function(tree, id, firstRun) {
        // check for undefined, not false => firstRun = firstRun || true; would always be true
        if(typeof firstRun == 'undefined') firstRun = true;
        // only expand if element is not the first aka selected one
        if(!firstRun) {
            tree.data[id].collapsed = false;
        }
        rootId = tree.data[id].root_context_id;
        if(rootId && rootId > 0) {
            main.expandTree(tree, rootId, false);
        }
    };

    /**
    *   This function adds a new context to the tree
    */
    main.addContextToTree = function(elem, parent, tree) {
        // insert the context into the context list
        elem.collapsed = true;
        elem.visible = true;
        tree.data[elem.id] = elem;

        var children;
        // insert the context into the tree
        if(parent > 0) { // elem is no root context
            if(!tree.children[parent]) { // parent was leaf before, create children list
                tree.children[parent] = [];
            }
            children = tree.children[parent];
        }
        else { // elem is root context
            children = tree.roots;
        }
        var index = elem.rank - 1; // rank is 1-n, index 0-(n-1)
        // insert elem at index and update other indices
        children.splice(index, 0, elem.id);
        for(var i=index+1; i<children.length; i++) {
            children[i].rank++;
        }
    };


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
            if(mapService.getPopupGeoId() == elem.geodata_id) mapService.closePopup();
            main.unsetCurrentElement();
            return;
        }
        var isCurrentlyLinked = mapService.geodata.linkedContexts[elem.geodata_id] && mapService.geodata.linkedContexts[elem.geodata_id] > 0;
        elem = target;
        var layerId = mapService.geodata.linkedGeolayer[elem.context_type_id];
        var layer = mapService.map.layers.overlays[layerId];
        main.currentElement.geometryType = layer.layerOptions.type;
        console.log(elem);
        if(elem.typeid === 0) { //context
            elem.fields = main.contextReferences[elem.typename].slice();
        } else if(elem.typeid == 1) { //find
            elem.fields = main.artifactReferences[elem.typename].slice();
        }
        var data = {};
        httpGetFactory('api/context/' + elem.id + '/data', function(response) {
            if(response.error) {
                modalFactory.errorModal(response.error);
                return;
            }
            data = parseData(response.data);
            main.currentElement.data = data;
        });
        main.currentElement.sources = {};
        httpGetFactory('api/source/by_context/' + elem.id, function(response) {
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
            httpGetFactory('api/geodata/wktToGeojson/'+inp.value, function(response) {
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

    main.createModalHelper = function(parent, elemType, contextTypes) {
        if(main.hasUnstagedChanges()) {
            var onDiscard = function() {
                main.currentElement.form.$setPristine();
                return main.createModalHelper(parent, elemType, contextTypes);
            };
            var onConfirm = function() {
                main.currentElement.form.$setPristine();
                main.storeElement(main.currentElement.element, main.currentElement.data);
                return main.createModalHelper(parent, elemType, contextTypes);
            };
            modalFactory.warningModal('context-form.confirm-discard', onConfirm, onDiscard);
            return;
        }
        var selection = [];
        var msg = '';
        if(elemType == 'context') {
            selection = contextTypes.filter(function(t) {
                return t.type === 0;
            });
            msg = 'create-dialog.new-context-description';
        } else if(elemType == 'find') {
            selection = contextTypes.filter(function(t) {
                return t.type === 1;
            });
            msg = 'create-dialog.new-artifact-description';
        }
        modalFactory.createModal(parent.name, msg, selection, function(name, type) {
            var formData = new FormData();
            formData.append('name', name);
            formData.append('context_type_id', type.context_type_id);
            if(typeof parent.id != 'undefined') formData.append('root_context_id', parent.id);
            httpPostFactory('api/context', formData, function(response) {
                var newContext = response.context;
                var elem = {
                    id: newContext.id,
                    name: name,
                    context_type_id: type.context_type_id,
                    root_context_id: parent.id,
                    rank: newContext.rank,
                    typeid: type.type,
                    typename: type.index,
                    typelabel: type.title,
                    data: [],
                    lasteditor: newContext.lasteditor,
                    updated_at: newContext.updated_at,
                    created_at: newContext.created_at
                };
                // main.addContextToTree(elem, parent);
                // main.setCurrentElement(elem, main.currentElement);
                $state.go('root.spacialist.data', {id: elem.id});
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
