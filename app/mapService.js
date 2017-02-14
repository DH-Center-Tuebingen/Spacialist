spacialistApp.service('mapService', ['httpGetFactory', 'httpPostFactory', 'httpGetPromise', 'leafletData', 'userService', function(httpGetFactory, httpPostFactory, httpGetPromise, leafletData, userService) {
    var contextGeodata;
    var localContexts;
    var defaultColor = '#00FF00';
    var map = {};
    map.geodataList = [];
    map.currentGeodata = {};

    initMapVariables();
    initMap();

    function cleanName(name) {
        return getKeyForName(name.replace(/[^\x00-\x7F]/g, ''));
    }

    function getKeyForName(name) {
        return name.replace(/-/, '');
    }

    map.addLegend = function(legend) {
        map.map.legend = {};
        map.map.legend.position = 'bottomleft';
        map.map.legend.colors = [];
        map.map.legend.labels = [];
        for(var k in legend) {
            if(legend.hasOwnProperty(k)) {
                var value = legend[k];
                map.map.legend.colors.push(value.color.color);
                map.map.legend.labels.push(value.name);
            }
        }
    };

    map.addGeodata = function(type, coords) {
        var formData = new FormData();
        formData.append('type', type);
        formData.append('coords', angular.toJson(coords));
        httpPostFactory('api/context/add/geodata', formData, function(response) {
            console.log(response);
            map.addListToMarkers([
                response.geodata
            ]);
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
                map.addListToMarkers(map.geodataList);
            }
        });
    };

    map.addListToMarkers = function(geodataList) {
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
            map.geoJson.addData(feature);
            // workaround, because calling `bringToBack()` in the `onEachFeature` throws an error (this._map is undefined)
            var currentLayers = map.geoJson.getLayers();
            var currentLayer = currentLayers[currentLayers.length - 1];
            if(feature.geometry.type != 'Point') {
                currentLayer.bringToBack();
            }
        }
        map.mapObject.fitBounds(map.geoJson.getBounds());
    };

    map.closePopup = function() {
        map.mapObject.closePopup();
    };

    map.openPopup = function(geodataId) {
        var alreadyFound = false;
        var layers = map.geoJson.getLayers();
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
            }
        };

        var osmAttr = '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>';
        var mqAttr = 'Tiles &copy; <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png" />';
        map.map.layers = {
            baselayers: {
                osm: {
                    name: 'OpenStreetMap',
                    url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    type: 'xyz',
                    layerOptions: {
                        attribution: osmAttr
                    }
                },
                mapquest: {
                    name: 'Mapquest',
                    type: 'xyz',
                    layerOptions: {
                        subdomains: '1234',
                        attribution: 'MapData ' + osmAttr + ', ' + mqAttr
                    },
                    url: 'https://otile{s}-s.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.jpg',
                },
                mapquestsat: {
                    name: 'Mapquest Satellite',
                    type: 'xyz',
                    layerOptions: {
                        subdomains: '1234',
                        attribution: 'MapData ' + osmAttr + ', ' + mqAttr
                    },
                    url: 'http://otile{s}.mqcdn.com/tiles/1.0.0/sat/{z}/{x}/{y}.jpg',
                },
                empty: {
                    name: 'Empty',
                    type: 'xyz',
                    url: ''
                }
            },
            overlays: {
                hillshade: {
                    name: "Hillshade Europa",
                    type: "wms",
                    url: "http://129.206.228.72/cached/hillshade",
                    visible: true,
                    layerOptions: {
                        layers: "europe_wms:hs_srtm_europa",
                        format: "image/png",
                        transparent: true,
                        opacity: 0.25,
                        attribution: "Hillshade layer by GIScience http://www.osm-wms.de"
                    }
                }
            }
        };
    }

    return map;
}]);
