spacialistApp.service('mapService', ['httpGetFactory', 'httpPostFactory', 'httpGetPromise', 'httpPostPromise', 'leafletData', 'userService', 'environmentService', 'langService', 'leafletBoundsHelpers', '$timeout', function(httpGetFactory, httpPostFactory, httpGetPromise, httpPostPromise, leafletData, userService, environmentService, langService, leafletBoundsHelpers, $timeout) {
    var localContexts;
    var defaultColor = '#00FF00';
    var invisibleLayers;
    var map = {};
    map.mapLayers = {};
    map.currentGeodata = {};
    map.contexts = environmentService.contexts;
    map.geodata = {};
    map.geodata.linkedContexts = [];
    map.geodata.linkedLayers = [];
    map.geodata.linkedGeolayer = {};
    map.featureGroup = new L.FeatureGroup();

    map.availableGeometryTypes = {
        point: 'POINT',
        linestring: 'LINESTRING',
        polygon: 'POLYGON'
    };

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
                for(var k in map.contexts.data) {
                // angular.forEach(map.contexts.data, function(elem) {
                    if(map.contexts.data.hasOwnProperty(k)) {
                        var elem = map.contexts.data[k];
                        if(elem.geodata_id && elem.geodata_id > 0){
                            map.geodata.linkedContexts[elem.geodata_id] = elem.id;
                        }
                    }
                }
                // });
                map.addListToMarkers(geodata, true);
                initHiddenLayers();
            }
        });
    };

    function initHiddenLayers() {
        for(var i=0; i<invisibleLayers.length; i++) {
            map.mapLayers[invisibleLayers[i]].remove();
        }
    }

    map.addListToMarkers = function(geodataList, isInit) {
        isInit = isInit || false;
        if(!userService.can('view_geodata')) {
            return;
        }
        for(var k in geodataList) {
            if(geodataList.hasOwnProperty(k)) {
                var geodata = geodataList[k];
                var lid;
                var color;
                if(map.geodata.linkedContexts[geodata.id]) {
                    var cid = map.geodata.linkedContexts[geodata.id];
                    var c = map.contexts.data[cid];
                    var ctid = c.context_type_id;
                    lid = map.geodata.linkedGeolayer[ctid];
                    if(map.mapLayers[lid]) {
                        color = map.mapLayers[lid].options.color;
                    }
                }
                if(!color) {
                    color = geodata.color;
                }
                var feature = {
                    type: 'Feature',
                    id: geodata.id,
                    geometry: geodata.geodata,
                    properties: {
                        name: 'Geodata #' + geodata.id,
                        color: color,
                        popupContent: "<div ng-include src=\"'layouts/marker.html'\"></div>"
                    }
                };
                // Add geodata to contexttype layer if it is linked to it
                if(lid && map.mapLayers[lid]) {
                    map.mapLayers[lid].addData(feature);
                } else {
                    map.mapLayers.unlinked.addData(feature);
                }
            }
        }
    };

    map.isLinkPossible = function(geodataType, layerType) {
        var gt = geodataType.toUpperCase();
        var lt = layerType.toUpperCase();

        return (
            gt.endsWith(map.availableGeometryTypes.point) &&
            lt.endsWith(map.availableGeometryTypes.point)
        ) || (
            gt.endsWith(map.availableGeometryTypes.linestring) &&
            lt.endsWith(map.availableGeometryTypes.linestring)
        ) || (
            gt.endsWith(map.availableGeometryTypes.polygon) &&
            lt.endsWith(map.availableGeometryTypes.polygon)
        );
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
        var layer = map.geodata.linkedLayers[gid];
        map.currentGeodata.id = gid;
        map.currentGeodata.type = layer.feature.geometry.type;
        map.currentGeodata.color = layer.feature.properties.color;
        if(map.currentGeodata.type == 'Point') {
            var latlng = layer.getLatLng();
            map.currentGeodata.lat = latlng.lat;
            map.currentGeodata.lng = latlng.lng;
        } else {
            map.currentGeodata.lat = undefined;
            map.currentGeodata.lng = undefined;
        }
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

    map.updateMarker = function(geodata) {
        if(typeof geodata.id == 'undefined') return;
        if(geodata.id <= 0) return;
        var color = geodata.color;
        var lat = geodata.lat;
        var lng = geodata.lng;
        var formData = new FormData();
        formData.append('id', geodata.id);
        if(typeof color != 'undefined') formData.append('color', color);
        if(typeof lat != 'undefined' && typeof lng != 'undefined') {
            formData.append('lat', lat);
            formData.append('lng', lng);
        }
        httpPostPromise.getData('api/context/set/props', formData).then(
            function(response) {
                map.map.selectedLayer.feature.properties.color = response.color || '#000000';
                map.map.selectedLayer.setStyle({fillColor: response.color});
                if(map.map.selectedLayer.feature.geometry.type == 'Point') {
                    if(typeof response.lat != 'undefined' && typeof response.lng != 'undefined') {
                        var latlng = L.latLng(response.lat, response.lng);
                        map.map.selectedLayer.setLatLng(latlng);
                    }
                }
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
        map.map.layers = {
            baselayers: {},
            overlays: {}
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
                    var name;
                    if(map.geodata.linkedContexts[feature.id]){
                        name = environmentService.contexts.data[map.geodata.linkedContexts[feature.id]].name;
                    }
                    else{
                        name = feature.properties.name;
                    }
                    layer.bindTooltip(name);
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

        var guideLayers = [
            map.featureGroup
        ];
        map.map.drawOptions = {
            position: "bottomright",
            draw: {
                polyline: {
                    shapeOptions: map.style,
                    allowIntersection: false,
                    guideLayers: guideLayers
                },
                polygon: {
                    shapeOptions: map.style,
                    guideLayers: guideLayers,
                    snapDistance: 5
                },
                marker: {
                    shapeOptions: map.style
                },
                circle: false,
                rectangle: false
            },
            edit: {
                featureGroup: map.featureGroup,
                remove: true,
                snapOptions: {
                    guideLayers: guideLayers,
                    snapDistance: 5
                }
            }
        };
    }

    map.initMapService = function() {
        initMapVariables();

        var promise = httpGetPromise.getData('api/overlay/get/all');
        promise.then(function(response) {
            map.setLayers(response.layers);
            // wait a random amount of time, so mapObject.eachLayer has all layers
            $timeout(function() {
                map.mapObject.eachLayer(function(l) {
                    if(l.options.layer_id) {
                        console.log(l.options.layer_id);
                        map.mapLayers[l.options.layer_id] = l;
                    }
                });
                map.getGeodata();
            }, 100);
        });
    };

    map.setLayers = function(layers) {
        var gKeyLoaded = false;
        invisibleLayers = [];
        for(var i=0; i<layers.length; i++) {
            var layer = layers[i];
            var id = layer.id;
            var currentLayer = {};
            currentLayer.visible = true; //all layers need to be visible first due to non-creation of invisible layers
            if(!layer.visible && layer.is_overlay) {
                invisibleLayers.push(id);
            }
            currentLayer.layerOptions = setLayerOptions(layer);
            if(layer.context_type_id) {
                currentLayer.name = layer.label;
                angular.merge(currentLayer.layerOptions, setContextLayerOptions(layer));
                currentLayer.type = 'geoJSONShape';
                currentLayer.data = {
                    type: 'FeatureCollection',
                    features: []
                };
                map.geodata.linkedGeolayer[layer.context_type_id] = id;
                setGeojsonLayerOptions(currentLayer.layerOptions);
            } else {
                currentLayer.name = layer.name;
                currentLayer.type = layer.type;
                switch(layer.type.toUpperCase()) {
                    case 'GOOGLE':
                        if(!gKeyLoaded) {
                            var s = document.createElement('script');
                            s.src = 'https://maps.googleapis.com/maps/api/js?key=' + layer.api_key + '&language=' + langService.getCurrentLanguage();
                            document.body.appendChild(s);
                            gKeyLoaded = true;
                        }
                        currentLayer.layerType = layer.layer_type;
                        break;
                    case 'BING':
                        currentLayer.key = layer.api_key;
                        currentLayer.layerOptions.type = layer.layer_type;
                        break;
                    case 'UNLINKED':
                        id = layer.id = 'unlinked';
                        currentLayer.data = {
                            type: 'FeatureCollection',
                            features: []
                        };
                        currentLayer.type = 'geoJSONShape';
                        currentLayer.layerOptions = setLayerOptions(layer);
                        setGeojsonLayerOptions(currentLayer.layerOptions);
                        break;
                    default:
                        currentLayer.url = layer.url;
                        break;
                }
            }
            if(layer.is_overlay) {
                map.map.layers.overlays[id] = currentLayer;
            } else {
                currentLayer.top = layer.visible;
                map.map.layers.baselayers[id] = currentLayer;
            }
        }
    };

    function setLayerOptions(l) {
        var layerOptions = {};
        layerOptions.maxZoom = map.map.defaults.maxZoom;
        layerOptions.noWrap = true;
        layerOptions.detectRetina = true;
        layerOptions.layer_id = l.id;
        layerOptions.position = l.position;
        layerOptions.is_overlay = l.is_overlay;
        if(l.is_overlay) {
            layerOptions.transparent = true;
        }
        for(var k in l) {
            if(l.hasOwnProperty(k)) {
                if(!isIllegalKey(k) && l[k] !== null) {
                    var lk = l[k];
                    if(k == 'opacity') lk = parseFloat(lk);
                    layerOptions[k] = lk;
                }
            }
        }
        return layerOptions;
    }

    function setContextLayerOptions(l) {
        var layerOptions = {};
        layerOptions.context_type_id = l.context_type_id;
        layerOptions.color = l.color;
        layerOptions.type = l.type;
        return layerOptions;
    }

    function setGeojsonLayerOptions(lo) {
        lo.style = function(feature) {
            var currentStyle = angular.copy(map.style);
            currentStyle.fillColor = feature.properties.color;
            return currentStyle;
        };
        lo.pointToLayer = function(feature, latlng) {
            var m = L.circleMarker(latlng, map.style);
            m.setRadius(m.getRadius() / 2);
            return m;
        };
        lo.onEachFeature = function(feature, layer) {
            if(feature.properties && feature.properties.popupContent) {
                layer.bindPopup(feature.properties.popupContent, {
                    minWidth: 300,
                    feature: feature
                });
                var name;
                if(map.geodata.linkedContexts[feature.id]){
                    name = environmentService.contexts.data[map.geodata.linkedContexts[feature.id]].name;
                }
                else{
                    name = feature.properties.name;
                }
                layer.bindTooltip(name);
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
        };
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
