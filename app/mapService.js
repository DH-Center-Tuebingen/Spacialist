spacialistApp.service('mapService', ['httpGetFactory', 'leafletData', 'userService', function(httpGetFactory, leafletData, userService) {
    var contextGeodata;
    var localContexts;
    var defaultColor = '#00FF00';
    var map = {};
    map.geodataList = [];

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

    map.getGeodata = function(contexts, contextMap) {
        localContexts = contexts;
        contextGeodata = contextMap;
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
            var cIndex = contextGeodata['#' + geodata.id];
            var color, icon;
            if(localContexts[cIndex]) {
                color = localContexts[cIndex].color;
                icon = localContexts[cIndex].icon;
            } else {
                color = defaultColor;
                icon = defaultIcon;
            }
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
            map.mapObject.fitBounds(map.geoJson.getBounds());
        }
    };

    map.closePopup = function() {
        map.mapObject.closePopup();
    };

    map.openPopup = function(geodataId) {
        angular.forEach(map.geoJson.getLayers(), function(layer, key) {
            if(layer.feature.id == geodataId) {
                layer.openPopup();
            }
        });
    };

    map.getMatchingContext = function(featureId) {
        var cIndex = contextGeodata['#' + featureId];
        return localContexts[cIndex] || null;
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
        map.map.bounds = {
            southWest: {
                lat: 90,
                lng: 180
            },
            northEast: {
                lat: -90,
                lng: -180
            }
        };
        map.map.layercontrol = {
            icons: {
                uncheck: "fa fa-fw fa-square-o",
                check: "fa fa-fw fa-check-square-o",
                radio: "fa fa-fw fa-check-circle-o",
                unradio: "fa fa-fw fa-circle-thin",
                up: "fa fa-fw fa-level-up",
                down: "fa fa-fw fa-level-down",
                open: "fa fa-fw fa-caret-down",
                close: "fa fa-fw fa-caret-up"
            }
        };
        map.map.events = {
            markers: {
                enable: ['click', 'drag', 'dragstart', 'dragend', 'popupopen', 'popupclose']
            }
        };
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
                /*hillshade: {
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
                }*/
            }
        };
    }

    return map;
}]);
