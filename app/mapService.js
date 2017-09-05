spacialistApp.service('mapService', ['httpGetFactory', 'httpPostFactory', 'httpPutFactory', 'httpGetPromise', 'httpPostPromise', 'httpPutPromise', 'httpPatchPromise', 'leafletData', 'userService', 'environmentService', 'langService', 'leafletBoundsHelpers', '$state', '$timeout', function(httpGetFactory, httpPostFactory, httpPutFactory, httpGetPromise, httpPostPromise, httpPutPromise, httpPatchPromise, leafletData, userService, environmentService, langService, leafletBoundsHelpers, $state, $timeout) {
    var localContexts;
    var defaultColor = '#00FF00';
    var invisibleLayers;
    var map = {};
    map.currentGeodata = {};
    map.contexts = environmentService.contexts;
    map.featureGroup = new L.FeatureGroup();

    map.availableGeometryTypes = {
        point: 'POINT',
        linestring: 'LINESTRING',
        polygon: 'POLYGON'
    };

    var availableLayerKeys = [
        'subdomains', 'attribution', 'opacity', 'layers', 'styles', 'format', 'version', 'visible'
    ];

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

    map.addGeodata = function(type, coords, id, contexts, mapArr) {
        var formData = new FormData();
        type = convertToStandardGeomtype(type);
        if(!type) {
            snackbarService.addAutocloseSnack('TODO invalid geomtype', 'error');
        }
        formData.append('type', type);
        formData.append('coords', angular.toJson(coords));
        if(id > 0) {
            httpPutFactory('api/geodata/'+id, formData, function(response) {
                if (response.error) {
                    snackbarService.addAutocloseSnack(response.error, 'error');
                }
            });
        } else {
            httpPostFactory('api/geodata', formData, function(response) {
                map.addListToMarkers([
                    response.geodata
                ], contexts, mapArr);
            });
        }
    };

    function convertToStandardGeomtype(type) {
        switch(type) {
            case 'marker':
            case 'Marker':
            case 'point':
                return 'Point';
            case 'linestring':
            case 'polyline':
            case 'linestring':
            case 'Linestring':
                return 'LineString';
            case 'polygon':
                return 'Polygon';
        }
        return undefined;
    }

    function initHiddenLayers(mapArr) {
        for(var i=0; i<invisibleLayers.length; i++) {
            mapArr.mapLayers[invisibleLayers[i]].remove();
        }
    }

    map.addListToMarkers = function(geodataList, contexts, mapArr, isInit) {
        isInit = isInit || false;
        if(!userService.can('view_geodata')) {
            return;
        }
        for(var k in geodataList) {
            if(geodataList.hasOwnProperty(k)) {
                var geodata = geodataList[k];
                var lid;
                var color;
                if(mapArr.geodata.linkedContexts[geodata.id]) {
                    var cid = mapArr.geodata.linkedContexts[geodata.id];
                    var c = contexts.data[cid];
                    var ctid = c.context_type_id;
                    lid = mapArr.geodata.linkedGeolayer[ctid];
                    if(mapArr.mapLayers[lid]) {
                        color = mapArr.mapLayers[lid].options.color;
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
                if(lid && mapArr.mapLayers[lid]) {
                    mapArr.mapLayers[lid].addData(feature);
                } else {
                    mapArr.mapLayers.unlinked.addData(feature);
                }
            }
        }
        if(isInit) {
            var newBounds = map.featureGroup.getBounds();
            var newNE = newBounds.getNorthEast();
            var newSW = newBounds.getSouthWest();
            mapArr.bounds.northEast.lat = newNE.lat;
            mapArr.bounds.northEast.lng = newNE.lng;
            mapArr.bounds.southWest.lat = newSW.lat;
            mapArr.bounds.southWest.lng = newSW.lng;
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

    map.setCurrentGeodata = function(gid, geodata) {
        var layer = geodata.linkedLayers[gid];
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
        return httpPatchPromise.getData('api/context/geodata/' + cid + '/' + gid, new FormData());
    };

    map.unlinkGeodata = function(cid) {
        return httpPatchPromise.getData('api/context/geodata/' + cid, new FormData());
    };

    map.getMatchingContext = function(featureId) {
        return httpGetPromise.getData('api/context/byGeodata/' + featureId);
    };

    map.updateMarker = function(geodata) {
        if(typeof geodata.id == 'undefined') return;
        if(geodata.id <= 0) return;
        var color = geodata.color;
        var lat = geodata.lat;
        var lng = geodata.lng;
        var formData = new FormData();
        if(typeof color != 'undefined') formData.append('color', color);
        if(typeof lat != 'undefined' && typeof lng != 'undefined') {
            var coords = [{
                lat: geodata.lat,
                lng: geodata.lng
            }];
            formData.append('coords', angular.toJson(coords));
            formData.append('type', 'Point');
        }
        httpPutPromise.getData('api/geodata/' + geodata.id, formData).then(
            function(response) {
                map.map.selectedLayer.feature.properties.color = response.color || '#000000';
                if(map.map.selectedLayer.feature.geometry.type == 'Point') {
                    if(typeof response.geodata != 'undefined') {
                        var lng = response.geodata.geodata.coordinates[0];
                        var lat = response.geodata.geodata.coordinates[1];
                        var latlng = L.latLng(lat, lng);
                        map.map.selectedLayer.setLatLng(latlng);
                    }
                }
            }
        );
    };

    map.createBoundsFromArray = function(arr) {
        return leafletBoundsHelpers.createBoundsFromArray(arr);
    };

    map.initMapObject = function() {
        return leafletData.getMap('mainmap');
    };

    map.initMapVariables = function() {
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
        map.map.style = {
            fillColor: "#000000",
            weight: 1,
            opacity: 1,
            color: '#808080',
            fillOpacity: 0.5
        };
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
                    shapeOptions: map.map.style,
                    allowIntersection: false,
                    guideLayers: guideLayers
                },
                polygon: {
                    shapeOptions: map.map.style,
                    guideLayers: guideLayers,
                    snapDistance: 5
                },
                marker: {
                    shapeOptions: map.map.style
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

        // was map, not map.map

        map.map.mapLayers = {};
        map.map.geodata = {};
        map.map.geodata.linkedContexts = [];
        map.map.geodata.linkedLayers = [];
        map.map.geodata.linkedGeolayer = {};

        return map.map;
    };

    map.getLayers = function() {
        return httpGetPromise.getData('api/overlay').then(function(response) {
            angular.forEach(response.layers, function(l) {
                l.opacity = parseFloat(l.opacity);
            });
            return response.layers;
        });
    };

    map.getGeodata = function() {
        return httpGetPromise.getData('api/geodata').then(function(response) {
            return response.geodata;
        });
    };

    map.initGeodata = function(geodata, contexts, mapArr) {
        for(var k in contexts.data) {
            if(contexts.data.hasOwnProperty(k)) {
                var elem = contexts.data[k];
                if(elem.geodata_id && elem.geodata_id > 0){
                    mapArr.geodata.linkedContexts[elem.geodata_id] = elem.id;
                }
            }
        }
        map.addListToMarkers(geodata, contexts, mapArr, true);
        initHiddenLayers(mapArr);
    };

    map.setupLayers = function(layers, mapArr, contexts, concepts) {
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
                currentLayer.name = concepts[layer.thesaurus_url].label;
                angular.merge(currentLayer.layerOptions, setContextLayerOptions(layer));
                currentLayer.type = 'geoJSONShape';
                currentLayer.data = {
                    type: 'FeatureCollection',
                    features: []
                };
                mapArr.geodata.linkedGeolayer[layer.context_type_id] = id;
                setGeojsonLayerOptions(currentLayer.layerOptions, mapArr, contexts);
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
                        setGeojsonLayerOptions(currentLayer.layerOptions, mapArr, contexts);
                        break;
                    default:
                        currentLayer.url = layer.url;
                        break;
                }
            }
            if(layer.is_overlay) {
                mapArr.layers.overlays[id] = currentLayer;
            } else {
                currentLayer.top = layer.visible;
                mapArr.layers.baselayers[id] = currentLayer;
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

    function setGeojsonLayerOptions(lo, mapArr, contexts) {
        lo.style = function(feature) {
            var currentStyle = angular.copy(mapArr.style);
            currentStyle.fillColor = feature.properties.color;
            return currentStyle;
        };
        lo.pointToLayer = function(feature, latlng) {
            var m = L.circleMarker(latlng, mapArr.style);
            m.setRadius(m.getRadius()/2);
            return m;
        };
        lo.onEachFeature = function(feature, layer) {
            if(feature.properties && feature.properties.popupContent) {
                layer.bindPopup(feature.properties.popupContent, {
                    minWidth: 300,
                    feature: feature
                });
                var name;
                if(mapArr.geodata.linkedContexts[feature.id]){
                    name = contexts.data[mapArr.geodata.linkedContexts[feature.id]].name;
                }
                else{
                    name = feature.properties.name;
                }
                layer.bindTooltip(name);
                layer.on('click', function(){
                    $state.go('root.spacialist.geodata', {id: layer.feature.id});
                });
            }
            feature.properties.wkt = map.toWkt(layer);
            map.featureGroup.addLayer(layer);
            mapArr.geodata.linkedLayers[feature.id] = layer;
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
        } else if (layer instanceof L.Marker) {
            return 'POINT(' + layer.getLatLng().lng + ' ' + layer.getLatLng().lat + ')';
        }
    };

    return map;
}]);
