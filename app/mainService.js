spacialistApp.service('mainService', ['httpGetFactory', 'httpPostFactory', 'httpPostPromise', 'modalFactory', '$uibModal', 'moduleHelper', 'imageService', 'literatureService', 'mapService', function(httpGetFactory, httpPostFactory, httpPostPromise, modalFactory, $uibModal, moduleHelper, imageService, literatureService, mapService) {
    var main = {};
    var modalFields;

    main.currentElement = {
        element: {},
        data: {},
        fields: {}
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
    main.legendList = {};

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
                        ctid: value.ctid
                    });
                }
                if(value.ctid !== null || value.aid !== null || value.val !== null || value.datatype !== null) {
                    main.contextReferences[index].push({
                        aid: value.aid,
                        val: value.val,
                        ctid: value.ctid,
                        datatype: value.datatype
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
                        ctid: value.ctid
                    });
                }
                if (value.ctid !== null || value.aid !== null || value.val !== null || value.datatype !== null) {
                    main.artifactReferences[index].push({
                        aid: value.aid,
                        val: value.val,
                        ctid: value.ctid,
                        datatype: value.datatype
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
                ctid: copy.ctid,
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
                children: []
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
                main.contextList.push(current);
                if(!main.legendList[current.typelabel]) {
                    main.legendList[current.typelabel] = {
                        name: current.typelabel,
                        color: main.getColorForId(current.typename)
                    };
                }
                if(current.children) addMetadata(current.children);
            }
            mapService.addLegend(main.legendList);
            mapService.getGeodata(main.contextList);
            //main.getContextListStarted = false;
        });
    }

    function addMetadata(contexts) {
        for(var i=0; i<contexts.length; i++) {
            var current = contexts[i];
            if(!main.legendList[current.typelabel]) {
                main.legendList[current.typelabel] = {
                    name: current.typelabel,
                    color: main.getColorForId(current.typename)
                };
            }
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
        defaults = {
            name: 'Neuer Top-Kontext',
            reclevel: -1,
            children: []
        };
        var parent = defaults;
        angular.extend(parent, data);
        main.createModalHelper({
            parent: parent,
            expand: function() {}
        }, 'context', true);
    };

    function updateElementData(elem) {
        updateElementDataHelper(elem, main.contextList);
    }

    function updateElementDataHelper(elem, children) {
        if(typeof children == 'undefined') return;
        for(var i=0; i<children.length; i++) {
            var child = children[i];
            if(child.id == elem.id) {
                child.data = elem.data;
                if(child.name != elem.name) {
                    console.log(child.name);
                    console.log(elem.name);
                    $scope.renameMarker(child.name, elem.name);
                    child.name = elem.name;
                    setMarker(elem, true);
                }
                break;
            }
            updateElementDataHelper(elem, child.children);
        }
    }

    main.storeElement = function(elem, data) {
        var parsedData = [];
        for(var key in data) {
            if(data.hasOwnProperty(key)) {
                var value = data[key];
                if(key != 'name' && !key.endsWith('pos')) {
                    var attr = {};
                    attr.key = key;
                    attr.value = value;
                    parsedData.push(attr);
                }
            }
        }
        elem.data = parsedData;
        var promise = storeElement(elem);
        promise.then(function(newRealId){
            elem.data = newRealId.data;
            updateElementData(elem);
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
        formData.append('ctid', elem.ctid);
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
        modalFactory.deleteModal(elem.name, function() {
            deleteElement(elem, function() {
                //$itemScope.remove(); TODO remove from list
            });
        }, 'Wenn Sie dieses Element löschen, werden auch alle Kind-Elemente gelöscht!');
    };

    function deleteElement(elem, onSuccess) {
        console.log("Removing element " + elem.name + " with ID " + elem.id);
        httpGetFactory('api/context/delete/' + elem.id, function(callback) {
            onSuccess();
        });
    }

    main.openSourceModal = function(fieldname, fieldid, currentVal) {
        modalFields = {
            name: fieldname,
            id: fieldid,
            literature: literatureService.literature.slice(),
            addedSources: [],
            value: currentVal || 100,
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
        var aid = fieldid;
        var cid = main.currentElement.element.id;
        httpGetFactory('api/sources/get/' + aid + '/' + cid, function(sources) {
            angular.forEach(sources, function(src, key) {
                modalFields.addedSources.push({
                    id: src.id,
                    cid: src.cid,
                    aid: src.attribute_id,
                    src: src.literature,
                    desc: src.description
                });
            });
        });
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/source-modal.html',
            windowClass: 'wide-modal',
            controller: function($uibModalInstance) {
                this.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                };
                this.savePossibility = function() {
                    var formData = new FormData();
                    formData.append('cid', cid);
                    formData.append('aid', fieldid);
                    formData.append('possibility', modalFields.value);
                    httpPostFactory('api/context/set/possibility', formData, function(callback) {
                        main.currentElement.data[fieldid+'_pos'] = modalFields.value;
                    });
                };
                this.modalFields = modalFields;
                this.addSource = undefined; //TODO
                this.deleteSourceEntry = undefined; //TODO
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
        var cid = $scope.currentElement.id;
        var formData = new FormData();
        formData.append('cid', cid);
        formData.append('aid', aid);
        formData.append('lid', currentSource.id);
        formData.append('desc', currentDesc);
        httpPostFactory('api/sources/add', formData, function(row) {
            modalFields.addedSources.push({
                id: row.sid,
                cid: cid,
                aid: aid,
                src: currentSource,
                desc: currentDesc
            });
        });
        modalFields.currentSource = undefined;
        modalFields.currentDesc = undefined;
    }

    /**
     * Remove a source entry at the given index `index` from the given array `arr`.
     */
    function deleteSourceEntry(index, arr) {
        var src = arr[index];
        var title = '';
        if(typeof src.src !== 'undefined' && typeof src.src.title !== 'undefined') title = src.src.title;
        else if(typeof src.literature !== 'undefined' && typeof src.literature.title !== 'undefined') title = src.literature.title;
        modalFactory.deleteModal(title, function() {
            var cid = -1;
            var aid = -1;
            var lid = -1;
            if(typeof src.cid !== 'undefined') cid = src.cid;
            else if(typeof src.cid !== 'undefined') cid = src.cid;
            else return;
            if(typeof src.aid !== 'undefined') aid = src.aid;
            else if(typeof src.attribute_id !== 'undefined') aid = src.attribute_id;
            else return;
            if(typeof src.src !== 'undefined' && src.src.lid !== 'undefined') lid = src.src.id;
            else if(typeof src.literature_id !== 'undefined') lid = src.literature_id;
            else return;
            httpGetFactory('api/sources/delete/literature/'+aid+'/'+cid+'/'+lid, function(callback) {
                arr.splice(index, 1);
            });
        }, '');
    }

    function parseData(data) {
        var parsedData = {};
        for(var i=0; i<data.length; i++) {
            var value = data[i];
            var index = value.attribute_id + '_' + (value.o_id || '');
            var posIndex = index + 'pos';
            var val = value.str_val;
            var dType = value.datatype;
            parsedData[posIndex] = value.possibility || 100;
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
            } else {
                parsedData[index] = val;
            }
        }
        return parsedData;
    }

    main.expandTreeTo = function(path) {
        var t = angular.element(document.getElementById('context-tree')).scope();
        var nodesScope = t.$nodesScope;
        var children = nodesScope.childNodes();
        var finished = false;
        for(var i=0; i<path.length; i++) {
            var level = path[i];
            for(var j=0; j<children.length; j++) {
                var child = children[j];
                if(level.id == child.$modelValue.id) {
                    if(path.length - 1 == i) {
                        main.setCurrentElement(child.$modelValue, undefined, false);
                        finished = true;
                        break;
                    }
                    child.expand();
                    children = child.childNodes();
                    break;
                }
            }
            if(finished) break;
        }
    };

    main.unsetCurrentElement = function() {
        if(typeof main.currentElement == 'undefined') return;
        // setMarker(main.currentElement, false);
        main.currentElement.element = {};
        main.currentElement.data = {};
        main.currentElement.fields = {};
    };

    main.setCurrentElement = function(target, elem, openAgain) {
        if(typeof elem != 'undefined' && elem.id == target.id) {
            main.unsetCurrentElement();
            mapService.closePopup();
            return;
        }
        elem = target;
        console.log(elem);
        if(elem.typeid === 0) { //context
            elem.fields = main.contextReferences[elem.typename].slice();
        } else if(elem.typeid == 1) { //find
            elem.fields = main.artifactReferences[elem.typename].slice();
        }
        var data = {};
        data = parseData(elem.data);
        main.currentElement.data = data;
        main.currentElement.fields = elem.fields;
        main.currentElement.element = {
            id: elem.id,
            name: elem.name,
            root_cid: elem.root_cid || -1,
            typeLabel: elem.typelabel,
            typeId: elem.typeid,
            ctid: elem.context_type_id,
            geodata_id: elem.geodata_id
        };
        if(typeof openAgain == 'undefined') openAgain = true;
        if(elem.geodata_id !== null && openAgain) {
            mapService.openPopup(elem.geodata_id);
        } else if(elem.geodata_id === null) {
            mapService.closePopup();
        }
        // setMarker(main.currentElement, true);
        loadLinkedImages(main.currentElement.element.id);
    };

    main.createModalHelper = function($itemScope, elemType, copyPosition) {
        var parent = $itemScope.parent;
        var selection = [];
        var msg = '';
        if(elemType == 'context') {
            selection = main.contexts.slice();
            msg = 'Neuen Kontext anlegen';
        } else if(elemType == 'find') {
            selection = main.artifacts.slice();
            msg = 'Neuen Fund anlegen';
        }
        modalFactory.createModal(parent.name, msg, selection, function(name, type) {
            var elem = {
                name: name,
                ctid: type.ctid,
                root_cid: parent.id,
                reclevel: parent.reclevel + 1,
                typeid: type.type,
                typename: type.index,
                typelabel: type.title,
                data: [],
                children: []
            };
            var formData = new FormData();
            formData.append('name', name);
            formData.append('ctid', type.ctid);
            if(typeof parent.id != 'undefined') formData.append('root_cid', parent.id);
            httpPostFactory('api/context/set', formData, function(newElem) {
                elem.id = newElem.id;
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
