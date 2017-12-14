spacialistApp.service('mainService', ['httpGetFactory', 'httpGetPromise', 'httpPostFactory', 'httpPostPromise', 'httpPutFactory', 'httpPutPromise', 'httpPatchFactory', 'httpDeleteFactory', 'httpDeletePromise', 'modalFactory', '$uibModal', 'moduleHelper', 'environmentService', 'fileService', 'literatureService', 'mapService', 'snackbarService', 'searchService', 'langService', '$timeout', '$state', '$translate', function(httpGetFactory, httpGetPromise, httpPostFactory, httpPostPromise, httpPutFactory, httpPutPromise, httpPatchFactory, httpDeleteFactory, httpDeletePromise, modalFactory, $uibModal, moduleHelper, environmentService, fileService, literatureService, mapService, snackbarService, searchService, langService, $timeout, $state, $translate) {
    var main = {};
    var modalFields;

    main.editMode = {
        enabled: false
    };

    main.contextTypes = [];
    main.contexts = environmentService.contexts;
    main.contextReferences = {};
    main.artifacts = [];
    main.artifactReferences = {};
    main.treeCallbacks = {};
    // main.legendList = {};

    main.datepickerOptions = {
        showWeeks: false,
        maxDate: new Date()
    };
    // $scope.date = {
    //     opened: false
    // };

    main.treeCallbacks.dropped = function(event, contexts) {
        var oldParentId = null;
        if (event.source.nodesScope.$nodeScope && event.source.nodesScope.$nodeScope.$modelValue) {
            oldParentId = event.source.nodesScope.$nodeScope.$modelValue.id;
        }
        var r = event.source.nodeScope.$modelValue;
        var id = r.id;
        var parentId = null;
        if (event.dest.nodesScope.$nodeScope && event.dest.nodesScope.$nodeScope.$modelValue) {
            parentId = event.dest.nodesScope.$nodeScope.$modelValue.id;
        }
        var index = event.dest.index;
        var oldIndex = contexts.data[id].rank - 1;
        var formData = new FormData();
        if(parentId) {
            formData.append('parent_id', parentId);
        }
        var rank = index + 1;
        if(((!parentId && !oldParentId) || parentId == oldParentId)) {
            if(index == oldIndex) {
                // element has not been moved
                return;
            }
        }
        formData.append('rank', rank);
        httpPatchFactory('api/context/' + id + "/rank", formData, function(response) {
        });
    };
    main.treeCallbacks.toggle = function(collapsed, sourceNodeScope, contexts) {
        contexts.data[sourceNodeScope.$modelValue.id].collapsed = collapsed;
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
            return response;
        });
    };

    main.duplicateElement = function(id, contexts) {
        httpPostFactory('api/context/' + id + '/duplicate', new FormData(), function(newElem) {
            var parent = contexts.data[contexts.data[id].root_context_id];
            var copy = newElem.obj;
            main.addContextToTree(copy, copy.root_context_id, contexts);
            $state.go('root.spacialist.context.data', {id: copy.id});
        });
    };

    main.addContext = function(context) {
        var formData = new FormData();
        formData.append('name', context.name);
        formData.append('context_type_id', context.type.id);
        if(context.parent) {
            formData.append('root_context_id', context.parent);
        }
        return httpPostPromise.getData('api/context', formData).then(function(response) {
            return response.context;
        });
    };

    main.storeElement = function(elem, data) {
        var parsedData = [];
        for(var key in data) {
            if(data.hasOwnProperty(key)) {
                var value = data[key];
                if(key != 'name') {
                    var attr = {};
                    attr.key = key;
                    attr.value = value;
                    parsedData.push(attr);
                }
            }
        }
        var promise = storeElement(elem, parsedData);
        return promise.then(function(response){
            return response;
        });
    };

    main.expandAll = function(tree) {
        angular.forEach(tree.data, function(d) {
            d.collapsed = false;
        });
    };

    main.collapseAll = function(tree) {
        angular.forEach(tree.data, function(d) {
            d.collapsed = true;
        });
    };

    main.setAllVisible = function(tree) {
        angular.forEach(tree.data, function(d) {
            d.visible = true;
        });
    };

    main.filterTree = function(elements, term) {
        main.setAllVisible(elements);
        main.collapseAll(elements);
        if(!term || term.length === 0) return;
        angular.forEach(elements.roots, function(r) {
            setVisible(elements, r, term.toUpperCase());
        });
    };

    main.globalSearch = function(term) {
        var langKey = langService.getCurrentLanguage();
        // encode search term to allow special chars such as '/', ' '
        term = window.encodeURIComponent(term);
        return httpGetPromise.getData('api/context/search/all/term=' + term + '/' + langKey)
        .then(function(response) {
            return response;
        });
    };

    main.contextSearch = function(searchString) {
        searchString = window.encodeURIComponent(searchString);
        return httpGetPromise.getData('api/context/search/term=' + searchString)
        .then(function(response) {
            return response;
        });
    };

    function setVisible(elems, e, term) {
        var data = elems.data;
        var children = elems.children;
        var match = filters(data[e.id], term);
        var cMatch = false;
        if(children[e.id]) {
            angular.forEach(children[e.id], function(child) {
                cMatch = cMatch || setVisible(elems, child, term);
            });
        }

        if(!match && !cMatch) { // term not found in subtree, do not show this element in result tree
            data[e.id].visible = false;
        }
        else if(cMatch) { // term found in subtree, expand this node
            data[e.id].collapsed = false;
        }

        return match || cMatch;
    }

    function filters(elem, term) {
        return elem && elem.name.toUpperCase().indexOf(term) > -1;
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

    function parseData(data) {
        var parsedData = {};
        for(var i=0; i<data.length; i++) {
            var value = data[i];
            var index = value.attribute_id;
            var posIndex = index + '_cert';
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
                parsedData[index] = parseInt(value.int_val);
            } else if(dType == 'boolean') {
                parsedData[index] = (parseInt(value.int_val) != 0);
            } else if(dType == 'double') {
                parsedData[index] = parseFloat(value.dbl_val);
            } else if(dType == 'date') {
                parsedData[index] = new Date(value.dt_val);
            } else if (dType == 'table') {
                if(typeof parsedData[index] == 'undefined') parsedData[index] = [];
                parsedData[index].push(JSON.parse(value.json_val));
            } else {
                parsedData[index] = val;
            }
        }
        return parsedData;
    }

    main.updateContextById = function(tree, id, newValues) {
        main.expandTree(tree, id);

        angular.merge(tree.data[id], newValues);
    };

    main.updateContextList = function(contexts, context, response) {
        context.lasteditor = response.context.lasteditor;
        context.updated_at = response.context.updated_at;
        context.updated_at = response.context.updated_at;
        context.lastmodified = updateLastModified(response.context);
        var c = contexts.data[context.id];
        for(var k in context) {
            if(context.hasOwnProperty(k)) {
                c[k] = context[k];
            }
        }
        var content = $translate.instant('snackbar.data-stored.success');
        snackbarService.addAutocloseSnack(content, 'success');
        if(response.error){
            modalFactory.errorModal(response.error);
            return;
        }
    };

    main.deleteContext = function(context, contexts) {
        var id = context.id;
        return httpDeletePromise.getData('api/context/' + id).then(function(callback) {
            var rootId = contexts.data[id].root_context_id;
            var index;
            var children;
            if(rootId && rootId > 0) {
                index = contexts.children[rootId].findIndex(function(child) {
                    return child.id == id;
                });
                children = contexts.children[rootId];
            }
            else {
                index = contexts.roots.findIndex(function(child) {
                    return child.id == id;
                });
                children = contexts.roots;
            }
            if(index > -1) {
                children.splice(index, 1);
            }
            for(var i=index; i<children.length; i++) {
                contexts.data[children[i].id].rank--;
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
        children.splice(index, 0, {id: elem.id});
        for(var i=index+1; i<children.length; i++) {
            tree.data[children[i].id].rank++;
        }
    };

    main.openGeographyModal = function($scope, aid) {
        var featureGroup = new L.FeatureGroup();
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
        var createdListener = $scope.$on('leafletDirectiveMap.placermap.draw:created', function(event, args) {
            var type = args.leafletEvent.layerType;
            type = mapService.convertToStandardGeomtype(type);
            var layer = args.leafletEvent.layer;
            var coords = [];
            if(type == 'Point') {
                var latlng = layer.getLatLng();
                coords.push(latlng.lng);
                coords.push(latlng.lat);
            } else {
                var latlngs = layer.getLatLngs();
                // "convert" MultiPolygon to Polygon
                if(type == 'Polygon') latlngs = latlngs[0];
                for(var i=0; i<latlngs.length; i++) {
                    var curr = latlngs[i];
                    var arr = [];
                    arr.push(curr.lng);
                    arr.push(curr.lat);
                    coords.push(arr);
                }
                if(type == 'Polygon') {
                    coords.push(angular.copy(coords[0]));
                    coords = [coords];
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
        var bounds = angular.copy(mapService.map.bounds);
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

    function loadLinkedFiles(id) {
        if(!moduleHelper.controllerExists('fileCtrl')) return;
        fileService.getFilesForContext(id);
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
