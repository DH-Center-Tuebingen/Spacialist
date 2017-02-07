spacialistApp.controller('mapCtrl', ['$scope', 'mapService', 'mainService', '$compile', function($scope, mapService, mainService, $compile) {
    $scope.map = mapService.map;
    $scope.mapObject = mapService.mapObject;
    $scope.markerIcons = mapService.markerIcons;
    $scope.markers = mapService.markers;
    $scope.currentElement = mainService.currentElement;
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
     * listener for different leaflet actions
     */
    $scope.$on('leafletDirectiveMap.popupclose', function(event, args) {

    });
    $scope.$on('leafletDirectiveMap.popupopen', function(event, args) {
       var featureId = args.leafletEvent.popup._source.feature.id;
       var context = mapService.getMatchingContext(featureId);
       if(context !== null) {
           mainService.setCurrentElement(context, undefined, false);
       } else {
           mainService.unsetCurrentElement();
       }
    //    var newScope = $scope.$new();
    //    newScope.stream = args.leafletEvent.popup.options.feature;
       $compile(args.leafletEvent.popup._contentNode)($scope);
    });
    /**
     * If the marker has been created, add the marker to the marker-array and store it in the database
     */
    $scope.$on('leafletDirectiveDraw.draw:created', function(event, args) {
        var type = args.leafletEvent.layerType;
        var layer = args.leafletEvent.layer;
        var coords;
        if(type == 'marker') {
            coords = [ layer.getLatLng() ];
        } else {
            coords = layer.getLatLngs();
            coords.push(angular.copy(coords[0]));
        }
        mapService.addGeodata(type, coords);
    });
    $scope.$on('leafletDirectiveDraw.draw:drawstart', function(event, args) {
        $scope.markerPlaceMode = true;
    });
    $scope.$on('leafletDirectiveDraw.draw:drawstop', function(event, args) {
        $scope.markerPlaceMode = false;
    });

    $scope.isEmpty = function(obj) {
        if (typeof obj === 'undefined') return false;
        return Object.keys(obj).length === 0;
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
