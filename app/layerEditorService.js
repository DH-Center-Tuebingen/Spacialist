spacialistApp.service('layerEditorService', ['httpGetFactory', 'mapService', function(httpGetFactory, mapService) {
    var editor = {};
    editor.selectedLayer = {
        layer: {}
    };
    editor.contextLayers = mapService.map.layers;

    editor.setSelectedLayer = function(l) {
        editor.selectedLayer.layer = l;
    };

    editor.onDelete = function(cl) {
        httpGetFactory('api/overlay/delete/' + cl.layerOptions.layer_id, function(response) {
            if(response.error) {
                console.log(response.error);
                return;
            }
            var layers;
            if(cl.layerOptions.is_overlay) {
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
                if(cl.layerOptions.is_overlay) {
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
                if(cl.layerOptions.is_overlay) {
                    layers = editor.contextLayers.overlays;
                } else {
                    layers = editor.contextLayers.baselayers;
                }
                // TODO reorder in Object?
            });
        }
    };

    return editor;
}]);
