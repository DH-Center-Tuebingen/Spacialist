spacialistApp.service('layerEditorService', ['httpGetFactory', 'httpPostFactory', 'mapService', 'snackbarService', '$translate', function(httpGetFactory, httpPostFactory, mapService, snackbarService, $translate) {
    var editor = {};
    editor.selectedLayer = {
        layer: {},
        key: ''
    };
    editor.contextLayers = mapService.map.layers;

    editor.setSelectedLayer = function(l, key) {
        editor.selectedLayer.layer = angular.merge({}, l);
        editor.selectedLayer.key = key;
    };

    editor.updateLayer = function(layer, key) {
        var tmpLayer = angular.merge({}, layer);
        delete tmpLayer.visible;
        delete tmpLayer.layerParams;
        if(tmpLayer.layerOptions.context_type_id) {
            delete tmpLayer.type;
            delete tmpLayer.data;
            delete tmpLayer.name;
        }
        var tgt;
        if(isOverlay(tmpLayer)) {
            tgt = editor.contextLayers.overlays[key];
        } else {
            tgt = editor.contextLayers.baselayers[key];
        }
        var formData = new FormData();
        formData.append('id', tmpLayer.layerOptions.layer_id);
        delete tmpLayer.layerOptions.layer_id;
        var updated = false;
        var colorUpdated = tgt.layerOptions.color != layer.layerOptions.color;
        for(var k in tmpLayer) {
            if(tmpLayer.hasOwnProperty(k)) {
                if(tmpLayer[k] !== null && tmpLayer[k] instanceof Object && !angular.equals(tmpLayer[k], tgt[k])) {
                    for(var l in tmpLayer[k]) {
                        if(tmpLayer[k].hasOwnProperty(l)) {
                            if(needsUpdate(tgt[k][l], tmpLayer[k][l])) {
                                formData.append(l, tmpLayer[k][l]);
                                updated = true;
                            }
                        }
                    }
                } else {
                    if(needsUpdate(tgt[k], tmpLayer[k])) {
                        formData.append(k, tmpLayer[k]);
                        updated = true;
                    }
                }
            }
        }
        if(!updated) {
            var content = $translate.instant('snackbar.layer-update.success');
            snackbarService.addAutocloseSnack(content, 'success');
        }
        httpPostFactory('api/overlay/update', formData, function(response) {
            if(response.error) {
                // TODO errorModal
                return;
            }
            var dfltBaselayerChanged = false;
            if(!isOverlay(layer)) {
                dfltBaselayerChanged = layer.layerOptions.visible != tgt.layerOptions.visible;
            }
            for(var k in layer) {
                if(layer.hasOwnProperty(k)) {
                    if(layer[k] !== null && layer[k] instanceof Object) {
                        tgt[k] = angular.merge({}, layer[k]);
                    } else {
                        tgt[k] = layer[k];
                    }
                }
            }
            if(dfltBaselayerChanged) {
                angular.forEach(editor.contextLayers.baselayers, function(l) {
                    l.layerOptions.visible = l.layerOptions.layer_id == layer.layerOptions.layer_id;
                });
            }
            if(colorUpdated) {
                angular.forEach(mapService.mapLayers[tgt.layerOptions.layer_id].getLayers(), function(l) {
                    l.options.fillColor = tgt.layerOptions.color;
                });
            }
            var content = $translate.instant('snackbar.layer-update.success');
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };


    editor.addNewBaselayer = function() {
        addNewLayer(false);
    };

    editor.addNewOverlay = function() {
        addNewLayer(true);
    };

    editor.onDelete = function(cl) {
        httpGetFactory('api/overlay/delete/' + cl.layerOptions.layer_id, function(response) {
            if(response.error) {
                console.log(response.error);
                return;
            }
            var layers;
            if(isOverlay(cl)) {
                layers = editor.contextLayers.overlays;
            } else {
                layers = editor.contextLayers.baselayers;
            }
            delete layers[cl.layerOptions.layer_id];
        });
    };

    editor.onOrder = {
        up: function(cl) {
            httpGetFactory('api/overlay/move/' + cl.layerOptions.layer_id + '/up', function(response) {
                if(response.error) {
                    console.log(response.error);
                    return;
                }
                var layers;
                if(isOverlay(cl)) {
                    layers = editor.contextLayers.overlays;
                } else {
                    layers = editor.contextLayers.baselayers;
                }
                // TODO reorder in Object?
            });
        },
        down: function(cl) {
            httpGetFactory('api/overlay/move/' + cl.layerOptions.layer_id + '/down', function(response) {
                if(response.error) {
                    console.log(response.error);
                    return;
                }
                var layers;
                if(isOverlay(cl)) {
                    layers = editor.contextLayers.overlays;
                } else {
                    layers = editor.contextLayers.baselayers;
                }
                // TODO reorder in Object?
            });
        }
    };

    function addNewLayer(isOverlay) {
        var layers;
        if(isOverlay) {
            layers = editor.contextLayers.overlays;
        } else {
            layers = editor.contextLayers.baselayers;
        }
        var name = $translate.instant('layer-editor.default-name');
        var formData = new FormData();
        formData.append('name', name);
        formData.append('is_overlay', isOverlay);
        httpPostFactory('api/overlay/add', formData, function(response) {
            if(response.error) {
                return;
            }
            var l = response.layer;
            mapService.setLayers([l]);
        });
    }

    function needsUpdate(valueTgt, valueSrc) {
        if(typeof valueSrc == 'function') return false;
        return valueTgt != valueSrc;
    }

    function isOverlay(layer) {
        return layer.layerOptions.is_overlay;
    }

    return editor;
}]);
