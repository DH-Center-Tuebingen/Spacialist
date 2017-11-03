spacialistApp.controller('gisCtrl', ['mapService', 'httpGetPromise', '$uibModal', '$translate', '$timeout', function(mapService, httpGetPromise, $uibModal, $translate, $timeout) {
    var vm = this;

    vm.layerVisibility = {};
    vm.sublayerVisibility = {};
    vm.sublayerColors = {};

    vm.exportLayer = function(l, type) {
        var id = l.id;
        var filename;
        if(!Number.isInteger(id) && id.toUpperCase() == 'UNLINKED') {
            id = vm.map.mapLayers[id].options.original_id;
            filename = 'Unlinked';
        } else {
            filename = vm.concepts[l.thesaurus_url].label;
        }
        httpGetPromise.getData('api/overlay/' + id + '/export/' + type).then(function(response) {
            var suffix;
            switch(type) {
                case 'csv':
                case 'wkt':
                    suffix = '.csv';
                    break;
                case 'kml':
                    suffix = '.kml';
                    break;
                case 'kmz':
                    suffix = '.kmz';
                    break;
                case 'gml':
                    suffix = '.gml';
                    break;
                case 'geojson':
                default:
                    suffix = '.json';
                    break;
            }
            filename += suffix;
            createDownloadLink(response, filename);
        });
    }

    vm.layerContextMenu = [
        {
            html: '<a style="padding-right: 8px;" tabindex="-1" href="#"><i class="material-icons md-18 fa-light context-menu-icon">zoom_in</i> ' + $translate.instant('gis.context-menu.zoom-to-layer') + '</a>',
            click:  function($itemScope, $event, modelValue, text, $li) {
                var parentLayer = vm.map.mapLayers[$itemScope.l.id];
                mapService.fitBoundsToLayer(parentLayer, vm.map);
            },
            enabled: function($itemScope) {
                console.log($itemScope.l);
                return vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            },
            displayed: function() {
                return true;
            }
        },
        {
            html: '<a style="padding-right: 8px;" tabindex="-1" href="#"><i class="material-icons md-18 fa-light context-menu-icon">file_upload</i> ' + $translate.instant('gis.context-menu.export-layer') + '</a>',
            click: function($itemScope, $event, modelValue, text, $li) {
                vm.exportLayer($itemScope.l, 'geojson');
            },
            enabled: function($itemScope) {
                return vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            },
            children: [
                {
                    text: 'GeoJSON',
                    click: function($itemScope, $event, modelValue, text, $li) {
                        vm.exportLayer($itemScope.l, 'geojson');
                    },
                },
                {
                    text: 'CSV',
                    click: function($itemScope, $event, modelValue, text, $li) {
                        vm.exportLayer($itemScope.l, 'csv');
                    },
                },
                {
                    text: 'WKT',
                    click: function($itemScope, $event, modelValue, text, $li) {
                        vm.exportLayer($itemScope.l, 'wkt');
                    },
                },
                {
                    text: 'KML',
                    click: function($itemScope, $event, modelValue, text, $li) {
                        vm.exportLayer($itemScope.l, 'kml');
                    },
                },
                {
                    text: 'KMZ',
                    click: function($itemScope, $event, modelValue, text, $li) {
                        vm.exportLayer($itemScope.l, 'kmz');
                    },
                },
                {
                    text: 'GML',
                    click: function($itemScope, $event, modelValue, text, $li) {
                        vm.exportLayer($itemScope.l, 'gml');
                    },
                }
            ]
        },
        {
            html: '<a style="padding-right: 8px;" tabindex="-1" href="#"><i class="material-icons md-18 fa-light context-menu-icon">timer</i> ' + $translate.instant('gis.context-menu.toggle-feature-count') + '</a>',
            click: function($itemScope, $event, modelValue, text, $li) {
                var l = $itemScope.l;
                if(l.counter > 0) {
                    delete l.counter;
                } else {
                    l.counter = vm.map.mapLayers[l.id].getLayers().length;
                }
            },
            enabled: function($itemScope) {
                return vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            }
        },
        {
            html: '<a style="padding-right: 8px;" tabindex="-1" href="#"><i class="material-icons md-18 fa-light context-menu-icon">settings</i> ' + $translate.instant('gis.context-menu.properties') + '</a>',
            click: function($itemScope, $event, modelValue, text, $li) {
                var l = $itemScope.l;
                var concepts = vm.concepts;
                $uibModal.open({
                    templateUrl: "modals/gis-properties.html",
                    windowClass: 'wide-modal',
                    controller: ['$scope', function($scope) {
                        var vm = this;

                        vm.layer = l;
                        vm.layerName = l.context_type_id ? concepts[l.thesaurus_url].label : l.name;

                        vm.close = function() {
                            $scope.$dismiss('close');
                        }
                    }],
                    controllerAs: '$ctrl'
                }).result.then(function(reason) {
                }, function(reason) {
                });
            }
        }
    ];

    vm.openImportWindow = function() {
        $uibModal.open({
            templateUrl: "modals/gis-import.html",
            windowClass: 'wide-modal',
            controller: ['$scope', 'fileService', 'httpGetPromise', 'httpPostPromise', '$translate', function($scope, fileService, httpGetPromise, httpPostPromise, $translate) {
                var vm = this;
                vm.activeTab = 'csv';
                vm.content = {};
                vm.file = {};
                vm.result = {};
                vm.preview = {};
                vm.coordType = 'latlon';
                vm.csvDelimiters = [
                    {
                        label: $translate.instant('gis.importer.csv.delimiter.type-comma'),
                        key: ','
                    },
                    {
                        label: $translate.instant('gis.importer.csv.delimiter.type-tab'),
                        key: '\t'
                    },
                    {
                        label: $translate.instant('gis.importer.csv.delimiter.type-space'),
                        key: ' '
                    },
                    {
                        label: $translate.instant('gis.importer.csv.delimiter.type-colon'),
                        key: ':'
                    },
                    {
                        label: $translate.instant('gis.importer.csv.delimiter.type-semicolon'),
                        key: ';'
                    },
                    {
                        label: $translate.instant('gis.importer.csv.delimiter.type-pipe'),
                        key: '|'
                    },
                    {
                        label: $translate.instant('gis.importer.csv.delimiter.type-custom'),
                        key: 'custom'
                    }
                ];
                vm.csvHeaderColumns = [];
                vm.shapeType = '';

                httpGetPromise.getData('api/geodata/epsg_codes').then(function(response) {
                    vm.epsgs = response;
                });

                vm.loadFileContent = function(file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $scope.$apply(function() {
                            if(vm.activeTab == 'shape') {
                                if(!vm.content[vm.activeTab]) {
                                    vm.content[vm.activeTab] = {};
                                }
                                vm.content.shape[vm.shapeType] = e.target.result;
                            } else {
                                vm.content[vm.activeTab] = e.target.result;
                            }
                            switch(vm.activeTab) {
                                case 'kml':
                                    vm.parseKmlKmz(vm.content.kml);
                                    break;
                                case 'csv':
                                    vm.parseCsvHeader();
                                    break;
                            }
                        });
                    };
                    delete vm.content[vm.activeTab];
                    if(vm.activeTab == 'kml' && file.name.endsWith('.kmz')) {
                        reader.readAsDataURL(file);
                    } else {
                        // shape allows multiple files
                        if(vm.activeTab == 'shape') {
                            vm.readShapeFiles(reader, file, 0);
                        } else {
                            reader.readAsText(file);
                        }
                    }
                };

                vm.readShapeFiles = function(reader, files, i) {
                    if(i == files.length) return;
                    var f = files[i];
                    var s = f.name;
                    var suffix = s.substr(s.length-4, s.length);
                    vm.shapeType = suffix.substr(1);
                    switch(suffix) {
                        case '.shp':
                        case '.dbf':
                            reader.readAsArrayBuffer(f);
                            reader.onloadend = function() {
                                vm.readShapeFiles(reader, files, i+1);
                            };
                            break;
                        case '.prj':
                        case '.qpj':
                            reader.readAsText(f);
                            reader.onloadend = function() {
                                if(vm.shapeType == 'qpj') {
                                    // qpj files has an additional new line
                                    var epsgText = vm.content.shape.qpj.split('\n', 1)[0];
                                    // pre-select epsg-code if qpj content matches srtext of one of the existing epsg codes
                                    vm.setEpsgToText(epsgText);
                                }
                                vm.readShapeFiles(reader, files, i+1);
                            };
                            break;
                        default:
                            vm.readShapeFiles(reader, files, i+1);
                            break;
                    }
                };

                vm.setEpsgToSrid = function(srid) {
                    for(var j=0, e; e=vm.epsgs[j]; j++) {
                        if(e.auth_srid == srid) {
                            vm.epsg = e;
                            break;
                        }
                    }
                };

                vm.setEpsgToText = function(srtext) {
                    for(var j=0, e; e=vm.epsgs[j]; j++) {
                        if(e.srtext == srtext) {
                            vm.epsg = e;
                            break;
                        }
                    }
                };

                vm.uploadFile = function(file) {
                    fileService.uploadFiles([file], null, vm.uploadedData);
                };

                vm.updateDelimiterType = function(delim) {
                    if(delim.key != 'custom') {
                        vm.csvDelim = delim.key;
                        vm.parseCsvHeader();
                    }
                };

                vm.parseCsvHeader = function() {
                    var row = vm.content.csv.split('\n')[0];
                    var delimiter = vm.csvDelim || ',';
                    if(delimiter == '\\t') {
                        delimiter = '\t';
                    }
                    var dsv = d3.dsv(delimiter);
                    var cols = dsv.parseRows(row)[0];
                    vm.csvHeaderColumns.length = 0;
                    for(var i=0,c; c=cols[i]; i++) {
                        vm.csvHeaderColumns.push(c);
                    }
                };

                vm.parsingDisabled = function() {
                    return !vm.content.csv || (vm.coordType == 'latlon' && (!vm.x || !vm.y)) || (vm.coordType == 'wkt' && !vm.wkt) || !vm.epsg;
                };

                vm.parseCsv = function(content, x, y, wkt, delim, epsg) {
                    if(vm.parsingDisabled()) return;

                    delim = delim || ',';
                    if(delim == '\\t') {
                        delim = '\t';
                    }
                    if(x && y) {
                        csv2geojson.csv2geojson(content, {
                            lonfield: x,
                            latfield: y,
                            delimiter: delim
                        }, function(err, data) {
                            console.log(err);
                            vm.preview.csv = angular.copy(data);
                            vm.ConvertProjection(vm.preview.csv, epsg);
                            vm.result.csv = data;
                        });
                    } else if(wkt) {
                        var featureCollection = {
                            type: 'featureCollection',
                            features: []
                        };
                        var wkx = require('wkx');
                        var dsv = d3.dsv(delim);
                        var rows = dsv.parse(content);
                        for(var i=0, r; r=rows[i]; i++) {
                            var wktString = r[wkt];
                            var geom = wkx.Geometry.parse(wktString);
                            geom = geom.toGeoJSON();
                            featureCollection.features.push({
                                type: 'Feature',
                                geometry: geom
                            });
                        }
                        vm.preview.csv = angular.copy(featureCollection);
                        vm.ConvertProjection(vm.preview.csv, epsg);
                        vm.result.csv = featureCollection;
                    }
                };

                vm.parseKmlKmz = function(content) {
                    if(vm.file.kml.name.endsWith('.kmz')) {
                        zip.workerScriptsPath = 'node_modules/zipjs-browserify/vendor/';
                        zip.createReader(new zip.Data64URIReader(content), function(reader) {
                            reader.getEntries(function(entries) {
                                if(entries.length) {
                                    for(var i=0, e; e = entries[i]; i++) {
                                        if(e.directory) continue;
                                        if(e.filename.endsWith('.kml')) {
                                            e.getData(new zip.TextWriter(), function(text) {
                                                vm.parseKml(text);
                                            });
                                        }
                                    }
                                }
                            });
                        }, function(error) {
                        });
                    } else {
                        vm.parseKml(content);
                    }
                };

                vm.parseKml = function(content) {
                    var parser = new DOMParser();
                    var kmlDoc = parser.parseFromString(content, "text/xml");
                    vm.result.kml = toGeoJSON.kml(kmlDoc);
                    vm.preview.kml = angular.copy(vm.result.kml);
                };

                vm.parseShape = function(content, epsg) {
                    shapefile.read(content.shp, content.dbf).then(function(response) {
                        vm.preview.shape = angular.copy(response);
                        vm.ConvertProjection(vm.preview.shape, epsg);
                        vm.result.shape = response;
                    });
                };

                vm.ConvertProjection = function(geojson, epsg) {
                    var proj = proj4(epsg.srtext);
                    for(var i=0, f; f=geojson.features[i]; i++) {
                        // TODO proj4js can only convert simple points ([x, y] or {x: x, y: y})
                        // continue if geometry type is unsupported
                        if(f.geometry.type != 'Point') continue;
                        f.geometry.coordinates = proj.inverse(f.geometry.coordinates);
                    }
                };

                vm.upload = function() {
                    if(!vm.result[vm.activeTab]) return;
                    if((vm.activeTab == 'csv' || vm.activeTab == 'shape') && !vm.epsg) return;
                    var formData = new FormData();
                    formData.append('collection', angular.toJson(vm.result[vm.activeTab]));
                    formData.append('srid', vm.epsg.auth_srid);
                    httpPostPromise.getData('api/geodata/geojson', formData).then(function(response) {
                        // TODO add new geo objects
                    });
                };

                vm.close = function() {
                    $scope.$dismiss('close');
                };
            }],
            controllerAs: '$ctrl'
        }).result.then(function(reason) {
        }, function(reason) {
        });
    };

    vm.toggleLayerGroupVisibility = function(layerGroup, isVisible) {
        var p = vm.map.layers.overlays[layerGroup.id];
        p.visible = isVisible;
        p.layerOptions.visible = isVisible;
        layerGroup.visible = isVisible;
    };

    vm.toggleLayerVisibility = function(layer, isVisible) {
        layer.options.visible = isVisible;
        if(isVisible) {
            layer.setStyle(vm.sublayerColors[layer.feature.id]);
        } else {
            vm.sublayerColors[layer.feature.id] = {
                color: layer.options.color,
                fillColor: layer.options.fillColor
            };
            layer.setStyle({
                color: 'rgba(0,0,0,0)',
                fillColor: 'rgba(0,0,0,0)'
            });
        }
    };

    vm.init = function() {
        mapService.setupLayers(vm.layer, vm.map, vm.contexts, vm.concepts);
        mapService.initMapObject('gismap').then(function(obj) {
            vm.mapObject = obj;
            var fwOptions = {
                position: 'topleft',
                onClick: function() {
                    mapService.fitBounds(vm.map);
                }
            };
            L.control.fitworld(fwOptions).addTo(vm.mapObject);
            L.control.togglemeasurements({
                position: 'topleft'
            }).addTo(vm.mapObject);
            L.control.zoomBox({
                modal: false,
                position: "topleft"
            }).addTo(vm.mapObject);
            L.control.graphicScale({
                position: 'bottomleft',
                minUnitWidth: 50,
                maxUnitsWidth: 300,
                fill: true,
                doubleLine: true
            }).addTo(vm.mapObject);
            // wait a random amount of time, so mapObject.eachLayer has all layers
            $timeout(function() {
                vm.mapObject.eachLayer(function(l) {
                    if(l.options.layer_id) {
                        vm.map.mapLayers[l.options.layer_id] = l;
                    }
                });
                mapService.initGeodata(vm.geodata, vm.contexts, vm.map, false);
            }, 100);
        });
    };

    vm.init();
}]);
