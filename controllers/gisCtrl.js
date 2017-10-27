spacialistApp.controller('gisCtrl', ['mapService', '$uibModal', '$translate', '$timeout', function(mapService, $uibModal, $translate, $timeout) {
    var vm = this;

    vm.layerVisibility = {};
    vm.sublayerVisibility = {};
    vm.sublayerColors = {};

    vm.layerContextMenu = [
        [
            '<i class="material-icons md-18 fa-light context-menu-icon">zoom_in</i> ' + $translate.instant('gis.context-menu.zoom-to-layer'),
            function($itemScope, $event, modelValue, text, $li) {
                var parentLayer = vm.map.mapLayers[$itemScope.l.id];
                mapService.fitBoundsToLayer(parentLayer, vm.map);
            },
            function($itemScope) {
                return vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            }
        ],
        [
            '<i class="material-icons md-18 fa-light context-menu-icon">file_upload</i> ' + $translate.instant('gis.context-menu.export-layer'),
            function($itemScope, $event, modelValue, text, $li) {
                return;
            },
            function($itemScope) {
                return vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            }
        ],
        [
            '<i class="material-icons md-18 fa-light context-menu-icon">timer</i> ' + $translate.instant('gis.context-menu.toggle-feature-count'),
            function($itemScope, $event, modelValue, text, $li) {
                var l = $itemScope.l;
                if(l.counter > 0) {
                    delete l.counter;
                } else {
                    l.counter = vm.map.mapLayers[l.id].getLayers().length;
                }
            },
            function($itemScope) {
                return vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            }
        ],
        [
            '<i class="material-icons md-18 fa-light context-menu-icon">settings</i> ' + $translate.instant('gis.context-menu.properties'),
            function($itemScope, $event, modelValue, text, $li) {
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
        ]
    ];

    vm.openImportWindow = function() {
        $uibModal.open({
            templateUrl: "modals/gis-import.html",
            windowClass: 'wide-modal',
            controller: ['$scope', 'fileService', 'httpGetPromise', 'httpPostPromise', function($scope, fileService, httpGetPromise, httpPostPromise) {
                var vm = this;
                vm.activeTab = 'csv';
                vm.content = {};
                vm.file = {};
                vm.result = {};
                vm.preview = {};
                vm.csvHeaderColumns = [];
                vm.parsedKml;
                vm.shapeType;

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
                }

                vm.setEpsgToSrid = function(srid) {
                    for(var j=0, e; e=vm.epsgs[j]; j++) {
                        if(e.auth_srid == srid) {
                            vm.epsg = e;
                            break;
                        }
                    }
                }

                vm.setEpsgToText = function(srtext) {
                    for(var j=0, e; e=vm.epsgs[j]; j++) {
                        if(e.srtext == srtext) {
                            vm.epsg = e;
                            break;
                        }
                    }
                }

                vm.uploadFile = function(file) {
                    fileService.uploadFiles([file], null, vm.uploadedData);
                };

                vm.parseCsvHeader = function() {
                    var row = vm.content.csv.split('\n')[0];
                    var delimiter = vm.csvDelim || ',';

                    // RegExp and logic from https://stackoverflow.com/questions/8493195/how-can-i-parse-a-csv-string-with-javascript-which-contains-comma-in-data (posted by https://stackoverflow.com/users/433790/ridgerunner)
                    var re_valid = new RegExp("^\\s*(?:'[^'\\\\]*(?:\\\\[\\S\\s][^'\\\\]*)*'|\"[^\"\\\\]*(?:\\\\[\\S\\s][^\"\\\\]*)*\"|[^"+delimiter+"'\"\\s\\\\]*(?:\\s+[^"+delimiter+"'\"\\s\\\\]+)*)\\s*(?:"+delimiter+"\\s*(?:'[^'\\\\]*(?:\\\\[\\S\\s][^'\\\\]*)*'|\"[^\"\\\\]*(?:\\\\[\\S\\s][^\"\\\\]*)*\"|[^"+delimiter+"'\"\\s\\\\]*(?:\\s+[^"+delimiter+"'\"\\s\\\\]+)*)\\s*)*$");
                    var re_value = new RegExp("(?!\\s*$)\\s*(?:'([^'\\\\]*(?:\\\\[\\S\\s][^'\\\\]*)*)'|\"([^\"\\\\]*(?:\\\\[\\S\\s][^\"\\\\]*)*)\"|([^"+delimiter+"'\"\\s\\\\]*(?:\\s+[^"+delimiter+"'\"\\s\\\\]+)*))\\s*(?:"+delimiter+"|$)", "g");
                    if (!re_valid.test(row)) return;
                    vm.csvHeaderColumns.length = 0;
                    row.replace(re_value, function(m0, m1, m2, m3) {
                        // unescape ' in single quoted values.
                        if(m1 !== undefined) vm.csvHeaderColumns.push(m1.replace(/\\'/g, "'"));
                        // unescape " in double quoted values.
                        else if(m2 !== undefined) vm.csvHeaderColumns.push(m2.replace(/\\"/g, '"'));
                        else if(m3 !== undefined) vm.csvHeaderColumns.push(m3);
                        return '';
                    });
                    if (/,\s*$/.test(row)) vm.csvHeaderColumns.push('');
                };

                vm.parseCsv = function(content, x, y, delim, epsg) {
                    delim = delim || ',';
                    csv2geojson.csv2geojson(content, {
                        latfield: x,
                        lonfield: y,
                        delimiter: delim
                    }, function(err, data) {
                        console.log(err);
                        vm.preview.csv = angular.copy(data);
                        vm.ConvertProjection(vm.preview.csv, epsg);
                        vm.result.csv = data;
                    });
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
                        f.geometry.coordinates = proj.inverse(f.geometry.coordinates);
                    }
                }

                vm.upload = function() {
                    if(!vm.result[vm.activeTab]) return;
                    if((vm.activeTab == 'csv' || vm.activeTab == 'shape') && !vm.epsg) return;
                    var formData = new FormData();
                    formData.append('collection', angular.toJson(vm.result[vm.activeTab]));
                    formData.append('srid', vm.epsg.auth_srid);
                    httpPostPromise.getData('api/geodata/geojson', formData).then(function(response) {
                        // TODO add new geo objects
                    });
                }

                vm.close = function() {
                    $scope.$dismiss('close');
                };
            }],
            controllerAs: '$ctrl'
        }).result.then(function(reason) {
        }, function(reason) {
        });
    }

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
