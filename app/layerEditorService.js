spacialistApp.service('layerEditorService', ['httpGetFactory', 'httpPostFactory', 'httpPatchFactory', 'httpDeleteFactory', 'mapService', 'snackbarService', '$translate', function(httpGetFactory, httpPostFactory, httpPatchFactory, httpDeleteFactory, mapService, snackbarService, $translate) {
    var editor = {};

    editor.updateLayer = function(layer, originalLayer) {
        var tmpLayer = angular.merge({}, layer);
        var layerId = tmpLayer.id;
        delete tmpLayer.id;
        var formData = new FormData();
        var colorUpdated = originalLayer.color != layer.color;
        var updated = colorUpdated;
        for(var k in tmpLayer) {
            if(tmpLayer.hasOwnProperty(k)) {
                if(needsUpdate(originalLayer[k], tmpLayer[k])) {
                    formData.append(k, tmpLayer[k]);
                    updated = true;
                }
            }
        }
        if(!updated) {
            var content = $translate.instant('snackbar.layer-update.success');
            snackbarService.addAutocloseSnack(content, 'success');
        }
        httpPatchFactory('api/overlay/' + layerId, formData, function(response) {
            if(response.error) {
                // TODO errorModal
                return;
            }
            var dfltBaselayerChanged = false;
            if(!isOverlay(layer)) {
                dfltBaselayerChanged = layer.visible != originalLayer.visible;
            }
            for(var k in layer) {
                if(layer.hasOwnProperty(k)) {
                    originalLayer[k] = layer[k];
                }
            }
            var content = $translate.instant('snackbar.layer-update.success');
            snackbarService.addAutocloseSnack(content, 'success');
        });
    };


    editor.addNewBaselayer = function(layers) {
        addNewLayer(false, layers);
    };

    editor.addNewOverlay = function(layers) {
        addNewLayer(true, layers);
    };

    editor.onDelete = function(layer, layers) {
        if(layer.context_type_id) return;
        if(layer.type == 'unlinked') return;
        httpDeleteFactory('api/overlay/' + layer.id, function(response) {
            if(response.error) {
                console.log(response.error);
                return;
            }
            angular.forEach(layers, function(l, k) {
                if(layer.id == l.id) layers.splice(k, 1);
            });
        });
    };

    editor.onOrder = {
        up: function(cl) {
            httpPatchFactory('api/overlay/move/' + cl.layerOptions.layer_id + '/up', function(response) {
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
            httpPatchFactory('api/overlay/move/' + cl.layerOptions.layer_id + '/down', function(response) {
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

    function addNewLayer(isOverlay, layers) {
        var name = $translate.instant('layer-editor.default-name');
        var formData = new FormData();
        formData.append('name', name);
        formData.append('is_overlay', isOverlay);
        httpPostFactory('api/overlay/add', formData, function(response) {
            if(response.error) {
                return;
            }
            layers.push(response.layer);
        });
    }

    function needsUpdate(valueTgt, valueSrc) {
        if(typeof valueSrc == 'function') return false;
        return valueTgt != valueSrc;
    }

    function isOverlay(layer) {
        return layer.is_overlay;
    }

    return editor;
}]);
