spacialistApp.controller('mapCtrl', ['$scope', 'mapService', 'mainService', 'modalFactory', '$compile', function($scope, mapService, mainService, modalFactory, $compile) {
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
        $compile(args.leafletEvent.popup._contentNode)($scope);
        var featureId = args.leafletEvent.popup._source.feature.id;
        var promise = mapService.getMatchingContext(featureId);
        promise.then(function(response) {
            if(response.error) {
                modalFactory.errorModal(response.error);
            } else {
                var path = response.path;
                if(path !== null) {
                    mainService.expandTreeTo(path);
                } else {
                    mainService.unsetCurrentElement();
                }
            }
        });
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
}]);
