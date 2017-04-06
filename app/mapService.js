spacialistApp.service('mapService', ['httpGetFactory', 'httpPostFactory', 'httpGetPromise', 'httpPostPromise', 'leafletData', 'userService', 'environmentService', 'leafletBoundsHelpers', function(httpGetFactory, httpPostFactory, httpGetPromise, httpPostPromise, leafletData, userService, environmentService, leafletBoundsHelpers) {
    var localContexts;
    var defaultColor = '#00FF00';
    var map = {};
    map.currentGeodata = {};
    map.contexts = environmentService.contexts;
    map.geodata = {};
    map.geodata.linkedContexts = [];
    map.geodata.linkedLayers = [];
    map.featureGroup = new L.FeatureGroup();

    var availableLayerKeys = [
        'subdomains', 'attribution', 'opacity', 'layers', 'styles', 'format', 'version', 'visible'
    ];

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
        if(!map.mapObject._popup) return -1;
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

    map.getGeodata = function() {
        httpGetFactory('api/context/get/geodata', function(response) {
            if(response.error) {
                //TODO show modal
                console.log("ERROR OCCURED");
            } else {
                var geodata = response.geodata;
                map.addListToMarkers(geodata, true);
                angular.forEach(map.contexts.data, function(elem) {
                    if(elem.geodata_id && elem.geodata_id > 0){
                        map.geodata.linkedContexts[elem.geodata_id] = elem.id;
                    }
                });
            }
        });
    };

    map.addListToMarkers = function(geodataList, isInit) {
        isInit = isInit || false;
        if(!userService.can('view_geodata')) {
            return;
        }
        for(var k in geodataList) {
            if(geodataList.hasOwnProperty(k)) {
                var geodata = geodataList[k];
                var feature = {
                    type: 'Feature',
                    id: geodata.id,
                    geometry: geodata.geodata,
                    properties: {
                        name: 'Geodata #' + geodata.id,
                        color: geodata.color,
                        popupContent: "<div ng-include src=\"'layouts/marker.html'\"></div>"
                    }
                };
                map.map.geojson.data.features.push(feature);
            }
        }
    };

    map.getCoords = function(layer, type) {
        var coords;
        if(type == 'marker' || type == 'Point') {
            coords = [ layer.getLatLng() ];
        } else {
            coords = layer.getLatLngs();
            if(type.toLowerCase() == 'polygon') coords[0].push(angular.copy(coords[0][0]));
        }
        return coords;
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
                    map.selectedLayer = layer;
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
        return httpGetPromise.getData('api/context/get/byGeodata/' + featureId);
    };

    map.updateMarker = function(geodata_id, color) {
        if(typeof geodata_id == 'undefined') return;
        if(geodata_id <= 0) return;
        var formData = new FormData();
        formData.append('id', geodata_id);
        if(typeof color != 'undefined') formData.append('color', color);
        httpPostPromise.getData('api/context/set/color', formData).then(
            function(response) {
                map.map.selectedLayer.feature.properties.color = response.color || '#000000';
                map.map.selectedLayer.setStyle({fillColor: response.color});
            }
        );
    };

    function initMap() {
        leafletData.getMap('mainmap').then(function(mapObject) {
            map.mapObject = mapObject;
        });
        leafletData.getGeoJSON('mainmap').then(function(geoJson) {
            map.geoJson = geoJson;
        });
    }

    map.reinitVariables = function() {
        initMapVariables();
    };

    map.createBoundsFromArray = function(arr) {
        return leafletBoundsHelpers.createBoundsFromArray(arr);
    };

    function initMapVariables() {
        map.map = {};
        map.map.center = {};
        map.map.defaults = {
            maxZoom: 27
        };
        map.map.bounds = map.createBoundsFromArray([
            [-90, 180],
            [90, -180]
        ]);
        map.style = {
            fillColor: "#000000",
            weight: 1,
            opacity: 1,
            color: '#808080',
            fillOpacity: 0.5
        };
        map.map.geojson = {
            data: {
                type: 'FeatureCollection',
                features: []
            },
            style: function(feature) {
                var currentStyle = angular.copy(map.style);
                currentStyle.fillColor = feature.properties.color;
                return currentStyle;
            },
            pointToLayer: function(feature, latlng) {
                var m = L.circleMarker(latlng, map.style);
                m.setRadius(m.getRadius() / 2);
                return m;
            },
            onEachFeature: function(feature, layer) {
                if(feature.properties && feature.properties.popupContent) {
                    layer.bindPopup(feature.properties.popupContent, {
                        minWidth: 300,
                        feature: feature
                    });
                    if(map.geodata.linkedContexts[feature.id]){
                        name = environmentService.contexts.data[map.geodata.linkedContexts[feature.id]].name;
                        layer.bindTooltip(name);
                    }
                    else{
                        layer.bindTooltip(feature.properties.name);
                    }
                    layer.on('click', function(){
                        map.map.selectedLayer = layer;
                        layer.openPopup();
                    });
                }
                feature.properties.wkt = map.toWkt(layer);
                map.featureGroup.addLayer(layer);
                map.geodata.linkedLayers[feature.id] = layer;
                var newBounds = map.featureGroup.getBounds();
                var newNE = newBounds.getNorthEast();
                var newSW = newBounds.getSouthWest();
                map.map.bounds.northEast.lat = newNE.lat;
                map.map.bounds.northEast.lng = newNE.lng;
                map.map.bounds.southWest.lat = newSW.lat;
                map.map.bounds.southWest.lng = newSW.lng;
            }
        };
        map.map.selectedLayer = {};
        map.map.controls = {
            scale: true
        };

        map.map.drawOptions = {
            position: "bottomright",
            draw: {
                polyline: {
                    shapeOptions: map.style
                },
                polygon: {
                    shapeOptions: map.style
                },
                marker: {
                    shapeOptions: map.style
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
        layerOptions.maxZoom = map.map.defaults.maxZoom;
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

    map.toWkt = function(layer) {
        var coords = [];
        if(layer instanceof L.Polygon || layer instanceof L.Polyline) {
            var latlngs = layer.getLatLngs();
            for(var i=0; i<latlngs.length; i++) {
                coords.push(latlngs[i].lng + ' ' + latlngs[i].lat);
            }
            if (layer instanceof L.Polygon) {
                var latlng = layer.getLatLngs()[0];
                return 'POLYGON((' + coords.join(',') + ',' + latlng.lng + ' ' + latlng.lat + '))';
            } else if (layer instanceof L.Polyline) {
                return 'LINESTRING(' + coords.join(',') + ')';
            }
        } else if (layer instanceof L.CircleMarker) {
            return 'POINT(' + layer.getLatLng().lng + ' ' + layer.getLatLng().lat + ')';
        }
    };

    return map;
}]);
