spacialistApp.controller('mapCtrl', ['$scope', 'mapService', function($scope, mapService) {
    $scope.map = mapService.map;
    $scope.mapObject = mapService.mapObject;
    $scope.markerIcons = mapService.markerIcons;
    $scope.markers = mapService.markers;
    ////
    $scope.markerOptions = {};
    $scope.closedAlerts = {};
    $scope.output = {};
    $scope.relations = [];
    $scope.allImages = [];
    $scope.unlinkedFilter = {
        contexts: {}
    };
    $scope.markerValues = {};
    $scope.hideLists = {};
    $scope.lists = {};
    $scope.input = {};
    $scope.editEntry = {};
    ////

    $scope.renameMarker = function(oldName, newName) {
        mapService.renameMarker(oldName, newName);
    };

    $scope.addMarker = function(elem) {
        mapService.addContextToMarkers(elem);
    };

    $scope.updateMarkerOptions = function(markerId, markerKey, color, icon) {
        if(typeof markerId == 'undefined') return;
        if(markerId <= 0) return;
        var formData = new FormData();
        formData.append('id', markerId);
        if(typeof color != 'undefined') formData.append('color', color);
        if(typeof icon != 'undefined') formData.append('icon', icon.icon);
        httpPostPromise.getData('api/context/set/icon', formData).then(
            function(icon) {
                console.log(icon);
                angular.extend($scope.markers[markerKey].icon, {
                    className: 'fa fa-fw fa-lg fa-' + icon.icon,
                    color: icon.color
                });
            }
        );
    };

    /**
     * Opens the popup of the current activated/clicked marker
     * Also stores the object's attribute values in an array `markerValues`
     * Initial values are stored in `markerDefaultValues`
     */
    var openPopup = function(options) {
        $rootScope.$emit('setCurrentElement', {
            target: $scope.currentElement,
            source: scopeService.markers[options.title].contextInfo
        });
        $scope.activeMarker = options.id;
        $scope.markerKey = options.title;
        $scope.markerOptions = {
            icon: {
                icon: scopeService.markers[options.title].contextInfo.icon,
                name: scopeService.markers[options.title].contextInfo.icon
            },
            color: scopeService.markers[options.title].contextInfo.color
        };
        var currentOpts = {};
        if (typeof scopeService.markers[options.title].contextInfo !== 'undefined') {
            currentOpts = angular.extend({}, scopeService.markers[options.title].contextInfo);
            currentOpts.title = options.title;
        } else {
            currentOpts = angular.extend({}, options);
        }
        $scope.markerValues.locked = !options.draggable;
        updateMarkerOpts(currentOpts);
        //$scope.markerChoices = scopeService.markerChoices;
    };

    /**
     * listener for different leaflet actions
     */
    $scope.$on('leafletDirectiveMarker.popupopen', function(event, args) {
        openPopup(scopeService.markers[args.leafletEvent.target.options.title].contextInfo);
    });
    /**
     * If the marker's popup was closed, reset all values which hold marker specific values
     */
    $scope.$on('leafletDirectiveMarker.popupclose', function(event, args) {
        $scope.activeMarker = -1;
        $scope.markerKey = undefined;
        $scope.sideNav.contextHistory = undefined;
    });
    /**
     * While dragging the marker, update its geo position
     */
    $scope.$on('leafletDirectiveMarker.drag', function(event, args) {
        var latlng = args.leafletEvent.target.getLatLng();
        updateMarkerPos(latlng.lat, latlng.lng);
    });
    /**
     * On drag start, set the dragged marker as current marker
     */
    $scope.$on('leafletDirectiveMarker.dragstart', function(event, args) {
        var currentOpts = angular.extend({}, args.leafletEvent.target.options);
        $scope.markerActive = true;
        $scope.activeMarker = currentOpts.id;
        updateMarkerOpts(currentOpts);
    });
    /**
     * On dragend (release mouse button), store the new geo position and open the marker's popup
     */
    $scope.$on('leafletDirectiveMarker.dragend', function(event, args) {
        var opts = args.leafletEvent.target.options;
        opts.lat = $scope.markerValues.lat;
        opts.lng = $scope.markerValues.lng;
        scopeService.markers[opts.title].myOptions.lat = opts.lat;
        scopeService.markers[opts.title].myOptions.lng = opts.lng;
        scopeService.markers[opts.title].focus = true;
    });
    /**
     * If the marker has been created, add the marker to the marker-array and store it in the database
     */
    $scope.$on('leafletDirectiveDraw.draw:created', function(event, args) {
        console.log("created!");
        // var layer = args.leafletEvent.layer;
        // var iconOpts = layer.options.icon.options;
        // var latlng = layer._latlng;
        // var opts = {};
        // opts.lat = latlng.lat;
        // opts.lng = latlng.lng;
        // scopeService.createNewContext(opts);
    });
    $scope.$on('leafletDirectiveDraw.draw:drawstart', function(event, args) {
        $scope.markerPlaceMode = true;
    });
    $scope.$on('leafletDirectiveDraw.draw:drawstop', function(event, args) {
        $scope.markerPlaceMode = false;
    });

    $scope.selectItem = function(scope) {
        console.log(scope.$modelValue);
    };

    /*
     * Creates a marker at a given location `latlng`, with given marker icon options `iconOpts`, an optional title ("" or `null` if you don't want to set it) and an id.
     */
    var addMarker = function(latlng, iconOpts, msg, id) {
        //if no msg (title) is present, set it to Marker_X
        if(typeof msg === 'undefined' || msg === null || msg.length === 0) {
            var max = 0;
            //get the max index of the markers (Marker_X)
            for(var pos in scopeService.markers) {
                console.log(pos);
                var markerTitle = scopeService.markers[pos].title;
                var matches = markerTitle.match(/Marker_([0-9]+)/);
                if(matches === null) continue;
                var mId = Number(matches[1]);
                if (mId > max) max = mId;
            }
            msg = "Marker_" + (max+1);
        }
        var title = msg.replace(/-/, ''); //'-' is not allowed in marker title
        scopeService.markers[title] = {
            title: title,
            lat: latlng.lat,
            lng: latlng.lng,
            draggable: false,
            icon: {
                type: "div",
                className: iconOpts.className,
                color: iconOpts.color,
                iconSize: iconOpts.iconSize
            }
        };
        if (typeof id !== 'undefined') {
            scopeService.markers[title].id = id;
        }
        scopeService.markers = scopeService.markers;
        return title;
    };

    /**
     * Set the variable `value` as value for the given key `id` in the `markerValues` object.
     */
    var setMarkerOption = function(id, value) {
        $scope.markerValues[id] = value;
    };

    $scope.isEmpty = function(obj) {
        if (typeof obj === 'undefined') return false;
        return Object.keys(obj).length === 0;
    };

    $scope.updateUnlinkedFilter = function(id, isContext) {
        if (typeof isContext === 'undefined' || !isContext) {
            //console.log($scope.unlinkedFilter[id]);
        } else {
            //console.log($scope.unlinkedFilter.contexts[id]);
        }
    };

    /**
     * Toggle if the marker should be draggable or not
     */
    $scope.togglePositionLock = function() {
        scopeService.markers[$scope.markerValues.title].draggable = $scope.markerValues.locked;
    };

    /**
     * Store current marker values `markerValues` in the database
     * current marker id is stored in `activeMarker`
     */
    $scope.updateEntry = function() {
        $scope.markerValues.id = $scope.activeMarker;
        if (typeof $scope.markerValues.id === 'undefined') {
            $scope.markerValues.id = -1;
        }
        var title = $scope.markerValues.title;
        var formData = new FormData();
        var opts = {};
        var ignores = ['locked', 'images', 'sources'];
        for (var key in $scope.markerValues) {
            if ($scope.markerValues.hasOwnProperty(key)) {
                if(ignores.indexOf(key) >= 0) continue;
                var value = $scope.markerValues[key];
                if (typeof value === 'object') {
                    formData.append(key, angular.toJson(value));
                } else {
                    formData.append(key, value);
                }
                opts[key] = value;
            }
        }
        //update marker values
        httpPostFactory('api/context/add', formData, function(callback) {
            var newId = parseInt(callback.cid, 10);
            setMarkerOption('id', newId);
            opts.id = newId;
            $scope.activeMarker = newId;
            scopeService.markers[title].myOptions = opts;
            scopeService.markers[title].message = '<h5>' + scopeService.markers[title].myOptions.name + '</h5>';
            //after storing new values, update default values (`markerDefaultValues`)
            markerDefaultValues = angular.extend({}, $scope.markerValues);
        });
    };

    /**
     * Reset current marker values to the initial values `markerDefaultValues`
     */
    $scope.resetEntry = function() {
        scopeService.markers[$scope.markerValues.title].lat = markerDefaultValues.lat;
        scopeService.markers[$scope.markerValues.title].lng = markerDefaultValues.lng;
        $scope.markerValues = angular.extend({}, markerDefaultValues);
    };

    /**
     * Remove current marker from db. Opens a confirm dialog beforehand.
     */
    $scope.removeEntry = function() {
        $scope.deleteModalFields = {
            name: $scope.markerValues.name
        };
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/delete-confirm.html',
            //windowClass: 'wide-modal',
            controller: function($uibModalInstance) {
                $scope.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                },
                $scope.deleteConfirmed = function() {
                    $uibModalInstance.dismiss('ok');
                    delete scopeService.markers[$scope.markerValues.title];
                    httpGetFactory('api/context/delete/' + $scope.activeMarker, function(callback) {});
                    resetMarkerOpts();
                }
            },
            scope: $scope
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    };

    $scope.addListEntry = function(aid, oid, text, arr) {
        var index = aid + '_' + (oid || '');
        var tmpArr = $scope.$eval(arr);
        var inp = $scope.$eval(text);
        if(typeof tmpArr[index] == 'undefined') tmpArr[index] = [];
        tmpArr[index].push({
            'name': inp[index]
        });
        inp[index] = '';
        /*if(typeof arr === 'undefined') arr = [];
        arr.push({
            'name': text
        });*/
        /*var arr = [];
        console.log($scope.fields[index]);
        arr.push({
            'name': $scope.input[index]
        });
        $scope.input[index] = "";
        if (typeof $scope.editEntry[index] === 'undefined') $scope.editEntry[index] = [];
        $scope.editEntry[index].push(false);
        if (typeof $scope.markerValues[index] !== 'undefined') arr = $scope.markerValues[index].concat(arr);
        setMarkerOption(index, arr);*/
    };

    $scope.editListEntry = function(ctid, aid, $index, val, tableIndex) {
        $scope.cancelEditListEntry();
        var name = ctid + "_" + aid;
        $scope.currentEditName = name;
        $scope.currentEditIndex = $index;
        if (typeof tableIndex !== 'undefined') {
            $scope.currentEditCol = tableIndex;
            $scope.editEntry[name][$index][tableIndex] = true;
        } else {
            $scope.editEntry[name][$index] = true;
        }
        $scope.initialListVal = val;
    };

    $scope.cancelEditListEntry = function() {
        if (typeof $scope.currentEditName !== 'undefined' && typeof $scope.currentEditIndex !== 'undefined') {
            if (typeof $scope.currentEditCol !== 'undefined') {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex][$scope.currentEditCol] = false;
                $scope.markerValues[$scope.currentEditName].selectedEpochs[$scope.currentEditIndex][$scope.currentEditCol] = $scope.initialListVal;
            } else {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex] = false;
                $scope.markerValues[$scope.currentEditName][$scope.currentEditIndex] = $scope.initialListVal;
            }
        }
        $scope.currentEditName = undefined;
        $scope.currentEditIndex = undefined;
        $scope.currentEditCol = undefined;
        $scope.initialListVal = undefined;
    };

    $scope.storeEditListEntry = function() {
        if (typeof $scope.currentEditName !== 'undefined' && typeof $scope.currentEditIndex !== 'undefined') {
            if (typeof $scope.currentEditCol !== 'undefined') {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex][$scope.currentEditCol] = false;
            } else {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex] = false;
            }
        }
        $scope.currentEditName = undefined;
        $scope.currentEditIndex = undefined;
        $scope.currentEditCol = undefined;
        $scope.initialListVal = undefined;
    };

    $scope.removeListItem = function(aid, oid, arr, $index) {
        var index = aid + '_' + (oid || '');
        var tmpArr = $scope.$eval(arr);
        tmpArr[index].splice($index, 1);
        //var name = aid + "_" + oid;
        //$scope.markerValues[name].splice($index, 1);
    };

    $scope.toggleList = function(ctid, aid) {
        var index = ctid + "_" + aid;
        $scope.hideLists[index] = !$scope.hideLists[index];
    };

    $scope.updateFileDesc = function(fd) {
        scopeService.filedesc = fd;
    };
    $scope.updateDates = function(dts) {
        scopeService.datetable = dts;
    };
    $scope.updateEpochs = function(epochs) {
        scopeService.epochs = epochs;
    };

    $scope.updateInput = function($event) {
        setMarkerOption($event.target.id, $event.target.value);
    };

    $scope.updateSelectInput = function($model) {
        setMarkerOption($model.$name, $model.$modelValue);
    };

    $scope.updateMSelectInput = function($select) {
        $model = $select.ngModel;
        $scope.markerValues[$model.$name] = $select.selected;
    };

    /**
     * Stores all children of the given root element `current` in a tree structure in a json object.
     */
    var createContextTree = function(current) {
        if (typeof $scope.roots[current.realId] === 'undefined') return current;
        var children = $scope.roots[current.realId].slice();
        delete $scope.roots[current.realId];
        for (var i = 0; i < children.length; i++) {
            var ctx = children[i];
            var elem = {
                title: ctx.name,
                clickable: true,
                typeId: ctx.type,
                typeName: ctx.typename,
                typeLabel: ctx.typelabel,
                ctid: ctx.ctid,
                realId: ctx.id,
                root_cid: ctx.root_cid,
                data: {}
            };
            angular.forEach(ctx.data, function(value, key) {
                var aid = value.a_id;
                var attr = {};
                attr.ctid = ctx.ctid;
                attr.aid = aid;
                if(value.datatype == 'list') {
                    if(typeof elem.data[aid] == 'undefined') attr.value = [];
                    else attr.value = elem.data[aid].value;
                    attr.value.push({
                        'name': value.str_val
                    });
                } else {
                    attr.value = value.str_val;
                }
                elem.data[aid] = attr;
            });
            if (ctx.type === 0) {
                elem.fields = $scope.ctxtRefs[elem.typeName].slice();
                elem.children = [];
                elem.id = $scope.sideNav.contextIds++;
                current.children.push(elem);
                var idx = current.children.length - 1;
                current.children[idx] = createContextTree(current.children[idx]);
            } else if (ctx.type == 1) {
                elem.fields = $scope.artiRefs[elem.typeName].slice();
                elem.id = $scope.sideNav.contextIds++;
                current.children.push(elem);
            }
        }
        return current;
    };

    /**
     * Retrieves the children for the current active marker `$scope.activeMarker` and stores them in a tree structure using `createContextTree`.
     */
    var getContextHistory = function() {
        $scope.sideNav.contextHistory = [];
        var pId = $scope.sideNav.contextIds++;
        var realId = $scope.activeMarker;
        var title = scopeService.markers[$scope.markerKey].myOptions.name;
        $scope.sideNav.contextHistory.push({
            id: pId,
            realId: realId,
            title: title,
            typeId: 0,
            clickable: true,
            children: []
        });
        httpGetFactory('api/context/get/children/' + $scope.activeMarker, function(roots) {
            $scope.roots = roots;
            if (typeof roots[realId] !== 'undefined' && roots[realId].length > 0) {
                $scope.stuffNav.setHarrisTab();
            } else {
                $scope.stuffNav.setNewTab();
            }
            $scope.sideNav.contextHistory[0] = createContextTree($scope.sideNav.contextHistory[0]);
        });
        $scope.currentContext = $scope.sideNav.contextHistory[0];
    };

    /**
     * Updates the markerValues array with values from the given `opts` array
     */
    var updateMarkerOpts = function(opts) {
        angular.extend($scope.markerValues, opts);
        if (typeof opts.lat != 'undefined' && typeof opts.lng != 'undefined') updateMarkerPos(opts.lat, opts.lng);
    };

    var updateMarkerPos = function(lat, lng) {
        $scope.markerValues.lat = lat;
        $scope.markerValues.lng = lng;
    };

    var resetMarkerOpts = function() {
        $scope.markerValues = {};
        $scope.activeMarker = -1;
        $scope.markerActive = false;
    };
}]);

spacialistApp.directive('upload', function(httpPostFactory) {
    return {
        restrict: 'A',
        scope: false,
        link: function(scope, element, attr) {
            element.bind('change', function() {
                var formData = new FormData();
                formData.append('file', element[0].files[0]);
                scope.subNav.values = {
                    fileName: angular.element(this).val()
                };
                scope.subNav.values.fileData = formData;
                scope.subNav.fileImp = true;
                scope.subNav.valueImp = false;
            });
        }
    };
});
