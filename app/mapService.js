spacialistApp.service('mapService', ['httpGetFactory', 'httpPostFactory', 'httpGetPromise', 'leafletData', 'userService', 'leafletBoundsHelpers', function(httpGetFactory, httpPostFactory, httpGetPromise, leafletData, userService, leafletBoundsHelpers) {
    var contextGeodata;
    var localContexts;
    var defaultColor = '#00FF00';
    var map = {};
    map.geodataList = [];
    map.currentGeodata = {};
    map.featureGroup = new L.FeatureGroup();

    var availableLayerKeys = [
        'subdomains', 'attribution', 'opacity', 'layers', 'styles', 'format', 'version', 'visible'
    ];

    initMapVariables();
    initMap();

    function cleanName(name) {
        return getKeyForName(name.replace(/[^\x00-\x7F]/g, ''));
    }

    function getKeyForName(name) {
        return name.replace(/-/, '');
    }

    // map.addLegend = function(legend) {
    //     map.map.legend = {};
    //     map.map.legend.position = 'bottomleft';
    //     map.map.legend.colors = [];
    //     map.map.legend.labels = [];
    //     for(var k in legend) {
    //         if(legend.hasOwnProperty(k)) {
    //             var value = legend[k];
    //             map.map.legend.colors.push(value.color.color);
    //             map.map.legend.labels.push(value.name);
    //         }
    //     }
    // };

    map.getPopupGeoId = function() {
        return map.mapObject._popup.options.feature.id;
    };

    map.addGeodata = function(type, coords, id) {
        var formData = new FormData();
        if(id) {
            formData.append('id', id);
        }
        formData.append('type', type);
        formData.append('coords', angular.toJson(coords));
        httpPostFactory('api/context/add/geodata', formData, function(response) {
            console.log(response);
            if(!id) {
                map.addListToMarkers([
                    response.geodata
                ]);
            }
        });
    };

    map.getGeodata = function(contexts) {
        localContexts = contexts;
        httpGetFactory('api/context/get/geodata', function(response) {
            if(response.error) {
                //TODO show modal
                console.log("ERROR OCCURED");
            } else {
                var geodatas = response.geodata;
                for(var i=0; i<geodatas.length; i++) {
                    var current = geodatas[i];
                    map.geodataList.push(current);
                }
                map.addListToMarkers(map.geodataList, true);
            }
        });
    };

    map.addListToMarkers = function(geodataList, isInit) {
        isInit = isInit || false;
        if(!userService.can('view_geodata')) {
            return;
        }
        var defaultIcon = map.markerIcons[0].icon;
        for(var i=0; i<geodataList.length; i++) {
            var geodata = geodataList[i];
            var color, icon;
            // if(localContexts[cIndex]) {
            //     color = localContexts[cIndex].color;
            //     icon = localContexts[cIndex].icon;
            // } else {
                color = defaultColor;
                icon = defaultIcon;
            // }
            var feature = {
                type: 'Feature',
                id: geodata.id,
                geometry: geodata.geodata,
                properties: {
                    name: 'Geodata #' + geodata.id,
                    color: color,
                    icon: icon,
                    popupContent: "<div ng-include src=\"'layouts/marker.html'\"></div>"
                }
            };
            map.map.geojson.data.features.push(feature);
            // workaround, because calling `bringToBack()` in the `onEachFeature` throws an error (this._map is undefined)
            // var currentLayers = map.geoJson.getLayers();
            // var currentLayer = currentLayers[currentLayers.length - 1];
            // if(feature.geometry.type != 'Point') {
            //     currentLayer.bringToBack();
            // }
        }
    };

    map.closePopup = function() {
        map.mapObject.closePopup();
    };

    map.openPopup = function(geodataId) {
        var alreadyFound = false;
        var layers = map.featureGroup.getLayers();
        angular.forEach(layers, function(layer, key) {
            if(!alreadyFound) {
                if(layer.feature.id == geodataId) {
                    alreadyFound = true;
                    layer.openPopup();
                }
            }
        });
    };

    map.setCurrentGeodata = function(gid) {
        map.currentGeodata.id = gid;
    };

    map.unsetCurrentGeodata = function() {
        delete map.currentGeodata.id;
    };

    map.linkGeodata = function(cid, gid) {
        return httpGetPromise.getData('api/context/link/geodata/' + cid + '/' + gid);
    };

    map.unlinkGeodata = function(cid) {
        return httpGetPromise.getData('api/context/unlink/geodata/' + cid);
    };

    map.getMatchingContext = function(featureId) {
        // console.log(contextGeodata);
        // console.log(localContexts);
        // var cIndex = contextGeodata['#' + featureId];
        // return localContexts[cIndex] || null;
        return httpGetPromise.getData('api/context/get/byGeodata/' + featureId);
    };

    map.renameMarker = function(oldName, newName) {
        var messageScope = function() { return $scope; };
        var oldKey = getKeyForName(oldName);
        var oldMarker = map.map.markers[oldKey];
        if(typeof oldMarker == 'undefined') return;
        var oldInfo = oldMarker.contextInfo;
        var iconOpts = {
            className: 'fa fa-fw fa-lg fa-' + oldInfo.icon,
            color: oldInfo.color,
            iconSize: [20, 20]
        };
        //add the current marker and load it's stored attribute values
        var id = oldInfo.id;
        var latlng = {
            lat: oldMarker.lat,
            lng: oldMarker.lng
        };
        var title = addMarker(latlng, iconOpts, newName, id);
        map.map.markers[title].message = "<div ng-include src=\"'layouts/marker.html'\"></div>";
        map.map.markers[title].getMessageScope = messageScope;
        // add values to own object. Easier to read relevant values
        map.map.markers[title].contextInfo = {
            data: oldInfo.data,
            id: id,
            title: title,
            name: newName,
            root_cid: oldInfo.root_cid,
            typeid: oldInfo.typeid,
            typename: oldInfo.typename,
            ctid: oldInfo.ctid,
            color: oldInfo.color,
            icon: oldInfo.icon
        };
        delete map.map.markers[oldKey];
    };

    function initMap() {
        leafletData.getMap().then(function(mapObject) {
            map.mapObject = mapObject;
        });
        leafletData.getGeoJSON().then(function(geoJson) {
            map.geoJson = geoJson;
        });
    }

    function initMapVariables() {
        map.map = {};
        map.map.bounds = leafletBoundsHelpers.createBoundsFromArray([
            [-90, 180],
            [90, -180]
        ]);
        map.markerIcons = [
            {
                icon: 'plus',
                name: 'plus'
            },
            {
                icon: 'close',
                name: 'close'
            },
            {
                icon: 'circle',
                name: 'circle'
            },
            {
                icon: 'circle-o',
                name: 'circle-o'
            },
            {
                icon: 'dot-circle-o',
                name: 'dot-circle-o'
            },
            {
                icon: 'square',
                name: 'square'
            },
            {
                icon: 'square-o',
                name: 'square-o'
            },
            {
                icon: 'star',
                name: 'star'
            },
            {
                icon: 'asterisk',
                name: 'asterisk'
            },
            {
                icon: 'flag',
                name: 'flag'
            },
            {
                icon: 'flag-o',
                name: 'flag-o'
            },
            {
                icon: 'map-marker',
                name: 'map-marker'
            },
            {
                icon: 'map-pin',
                name: 'map-pin'
            },
            {
                icon: 'university',
                name: 'university'
            }
        ];
        var style = {
            fillColor: "green",
            weight: 2,
            opacity: 1,
            color: 'black',
            dashArray: '3',
            fillOpacity: 0.5
        };
        map.map.geojson = {
            data: {
                type: 'FeatureCollection',
                features: []
            },
            style: function(feature) {
                var currentStyle = angular.copy(style);
                currentStyle.fillColor = feature.properties.color;
                return currentStyle;
            },
            pointToLayer: function(feature, latlng) {
                return L.circleMarker(latlng, style);
            },
            onEachFeature: function(feature, layer) {
                if(feature.properties && feature.properties.popupContent) {
                    layer.bindPopup(feature.properties.popupContent, {
                        minWidth: 300,
                        feature: feature
                    });
                }
                map.featureGroup.addLayer(layer);
                var newBounds = map.featureGroup.getBounds();
                var newNE = newBounds.getNorthEast();
                var newSW = newBounds.getSouthWest();
                map.map.bounds.northEast.lat = newNE.lat;
                map.map.bounds.northEast.lng = newNE.lng;
                map.map.bounds.southWest.lat = newSW.lat;
                map.map.bounds.southWest.lng = newSW.lng;
            }
        };
        map.map.markers = {};
        map.map.controls = {
            scale: true
        };

        map.map.drawOptions = {
            position: "bottomright",
            draw: {
                polyline: {
                    metric: false
                },
                polygon: {
                    metric: false,
                    showArea: true,
                    drawError: {
                        color: '#b00b00',
                        timeout: 1000
                    },
                    shapeOptions: {
                        color: 'blue'
                    }
                },
                marker: {
                    icon: L.divIcon({
                        className: 'fa fa-fw fa-plus',
                        iconSize: [20, 20]
                    })
                },
                circle: false,
                rectangle: false
            },
            edit: {
                featureGroup: map.featureGroup,
                remove: true
            }
        };

        map.map.layers = {
            baselayers: {},
            overlays: {}
        };

        httpGetFactory('api/overlay/get/all', function(response) {
            console.log(response.layers);
            angular.forEach(response.layers, function(layer, key) {
                var id = layer.id;
                var currentLayer = {};
                currentLayer.name = layer.name;
                currentLayer.url = layer.url;
                currentLayer.type = layer.type;
                currentLayer.visible = layer.visible;
                var layerOptions = {};
                currentLayer.layerOptions = setLayerOptions(layer);
                if(layer.is_overlay) {
                    map.map.layers.overlays[id] = currentLayer;
                } else {
                    map.map.layers.baselayers[id] = currentLayer;
                }
                console.log(currentLayer);
            });
        });
    }

    function setLayerOptions(l) {
        var layerOptions = {};
        layerOptions.noWrap = true;
        layerOptions.detectRetina = true;
        if(l.is_overlay) {
            layerOptions.transparent = true;
        }
        for(var k in l) {
            if(l.hasOwnProperty(k)) {
                if(!isIllegalKey(k) && l[k] !== null) {
                    layerOptions[k] = l[k];
                }
            }
        }
        return layerOptions;
    }

    function isIllegalKey(k) {
        return availableLayerKeys.indexOf(k) < 0;
    }

    return map;
}]);
