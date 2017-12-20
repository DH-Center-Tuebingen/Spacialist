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
                return vm.map.mapLayers[$itemScope.l.id] && vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
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
                return vm.map.mapLayers[$itemScope.l.id] && vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
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
                    l.counter = vm.map.mapLayers[$itemScope.l.id] && vm.map.mapLayers[l.id].getLayers().length;
                }
            },
            enabled: function($itemScope) {
                return vm.map.mapLayers[$itemScope.l.id] && vm.map.mapLayers[$itemScope.l.id].getLayers().length > 0;
            }
        },
        {
            html: '<a style="padding-right: 8px;" tabindex="-1" href="#"><i class="material-icons md-18 fa-light context-menu-icon">settings</i> ' + $translate.instant('gis.context-menu.properties') + '</a>',
            click: function($itemScope, $event, modelValue, text, $li) {
                var l = $itemScope.l;
                var concepts = vm.concepts;
                var contexts = vm.contexts;
                var map = vm.map;
                $uibModal.open({
                    templateUrl: "modals/gis-properties.html",
                    windowClass: 'wide-modal',
                    controller: ['$scope', 'mainService', 'httpGetFactory', function($scope, mainService, httpGetFactory) {
                        var vm = this;

                        vm.map = map;
                        vm.layer = l;
                        vm.concepts = concepts;
                        vm.contexts = contexts;
                        vm.layerName = l.context_type_id ? concepts[l.thesaurus_url].label : l.name;

                        vm.labelAttributes = [];

                        httpGetFactory('api/analysis/context_type/' + vm.layer.context_type_id + '/string', function (response) {
                            vm.labelAttributes.length = 0;
                            vm.labelAttributes.push({
                                id: -1, // indicate that it is not a real attribute
                                label: 'gis.properties.labels.label-name'
                            });
                            for(var i=0; i<response.length; i++) {
                                vm.labelAttributes.push(response[i]);
                            }
                        });

                        vm.fontStyles = [
                            {
                                label: 'gis.properties.labels.font.bold',
                                index: 'bold'
                            },
                            {
                                label: 'gis.properties.labels.font.italic',
                                index: 'italic'
                            },
                            {
                                label: 'gis.properties.labels.font.oblique',
                                index: 'oblique'
                            },
                            {
                                label: 'gis.properties.labels.font.bolditalic',
                                index: 'bolditalic'
                            },
                            {
                                label: 'gis.properties.labels.font.boldoblique',
                                index: 'boldoblique'
                            },
                            {
                                label: 'gis.properties.labels.font.regular',
                                index: 'regular'
                            }
                        ];

                        vm.fontMods = [
                            {
                                label: 'gis.properties.labels.font.mod.lower',
                                index: 'lower'
                            },
                            {
                                label: 'gis.properties.labels.font.mod.upper',
                                index: 'upper'
                            },
                            {
                                label: 'gis.properties.labels.font.mod.capitalize',
                                index: 'capitalize'
                            }
                        ];

                        vm.positions = [
                            {
                                label: 'gis.properties.labels.position.top',
                                index: 'top'
                            },
                            {
                                label: 'gis.properties.labels.position.bottom',
                                index: 'bottom'
                            },
                            {
                                label: 'gis.properties.labels.position.left',
                                index: 'left'
                            },
                            {
                                label: 'gis.properties.labels.position.right',
                                index: 'right'
                            },
                            {
                                label: 'gis.properties.labels.position.center',
                                index: 'center'
                            },
                            {
                                label: 'gis.properties.labels.position.auto',
                                index: 'auto'
                            },
                        ];

                        vm.label = {};
                        vm.font = {
                            transparency: 0,
                            color: '#000000',
                            size: 12,
                            style: vm.fontStyles[5]
                        };
                        vm.buffer = {
                            transparency: 0,
                            color: '#FFFFFF',
                            size: 1
                        };
                        vm.background = {
                            transparency: 0,
                            color: {
                                fill: '#FFFFFF',
                                border: '#000000'
                            },
                            size: {
                                x: 0,
                                y: 0,
                                border: 1
                            }
                        };
                        vm.position = {
                            offset: {
                                x: 0,
                                y: 0
                            }
                        };
                        vm.shadow = {
                            transparency: 0,
                            color: '#000000',
                            offset: {
                                x: 0,
                                y: 0
                            },
                            blur: 0,
                            spread: 0
                        };

                        vm.formShown = {
                            label: true,
                            font: false,
                            buffer: false,
                            background: false,
                            position: false,
                            shadow: false
                        };

                        vm.applyStyleSettings = function() {
                            if(!vm.map.mapLayers[vm.layer.id]) return;
                            if(!vm.label.attribute) return;

                            var layers = vm.map.mapLayers[vm.layer.id].getLayers();
                            var values = [];
                            // if attribute id is set, async load data of set attribute
                            // then loop over layers and set value to async loaded values (use empty string for non-present data)
                            if(vm.label.attribute.id > 0) {
                                httpGetFactory('api/analysis/context_type/' + vm.layer.context_type_id + '/attribute/' + vm.label.attribute.id, function(response) {
                                    for(var i=0; i<layers.length; i++) {
                                        var l = layers[i];
                                        var linkedContextId = vm.map.geodata.linkedContexts[l.feature.id];
                                        var linkedContext = vm.contexts.data[linkedContextId];
                                        if(response[linkedContext.id]) {
                                            var data = parseData([response[linkedContext.id].pivot]);
                                            values.push(data[vm.label.attribute.id]);
                                        } else {
                                            values.push('');
                                        }
                                    }
                                    vm.applyStyleValues(layers, values);
                                });
                            } else {
                                for(var i=0; i<layers.length; i++) {
                                    var l = layers[i];
                                    var linkedContextId = vm.map.geodata.linkedContexts[l.feature.id];
                                    var linkedContext = vm.contexts.data[linkedContextId];
                                    values.push(linkedContext.name);
                                }
                                vm.applyStyleValues(layers, values);
                            }
                        };

                        vm.applyStyleValues = function(layers, values) {
                            var className = 'tooltip-' + (new Date()).getTime();
                            var tooltip = {
                                className: className
                            };

                            tooltip.permanent = true;
                            tooltip.interactive = false;

                            vm.applyPosition(tooltip);

                            for(var i=0; i<layers.length; i++) {
                                var l = layers[i];
                                l.unbindTooltip();
                                if(values[i].length > 0) {
                                    l.bindTooltip(values[i], tooltip);
                                }
                            }
                            var tooltipInstances = $('.'+className);
                            vm.removeTooltipClasses(tooltipInstances);
                            vm.applyBuffer(tooltipInstances);
                            vm.applyFont(tooltipInstances);
                            vm.applyBackground(tooltipInstances);
                            vm.applyShadow(tooltipInstances);
                        }

                        vm.removeTooltipClasses = function(tti) {
                            tti.removeClass('leaflet-tooltip-left');
                            tti.removeClass('leaflet-tooltip-right');
                            tti.removeClass('leaflet-tooltip-top');
                            tti.removeClass('leaflet-tooltip-bottom');
                        };

                        vm.applyFont = function(tti) {
                            if(!vm.font.active) return;
                            var opacity = vm.getOpacity(vm.font.transparency);
                            var c = hex2rgba(vm.font.color);
                            c.a = opacity;
                            c = rgba2str(c);
                            var s = vm.font.size;
                            tti.css('color', c);
                            if(s) tti.css('font-size', s + 'px');
                            // TODO font family
                            // tti.css('font-family', '');
                            var mod = vm.font.mod;
                            var style = vm.font.style;
                            if(style) {
                                switch(style.index) {
                                    case 'bold':
                                        tti.css('font-weight', 'bold');
                                        break;
                                    case 'italic':
                                        tti.css('font-style', 'italic');
                                        break;
                                    case 'oblique':
                                        tti.css('font-style', 'oblique');
                                        break;
                                    case 'bolditalic':
                                        tti.css('font-weight', 'bold');
                                        tti.css('font-style', 'italic');
                                        break;
                                    case 'boldoblique':
                                        tti.css('font-weight', 'bold');
                                        tti.css('font-style', 'oblique');
                                        break;
                                    case 'regular':
                                    default:
                                        tti.css('font-weight', 'normal');
                                        tti.css('font-style', 'normal');
                                        break;
                                }
                            }
                            if(mod) {
                                switch(mod.index) {
                                    case 'lower':
                                    tti.css('text-transform', 'lowercase');
                                    break;
                                    case 'upper':
                                    tti.css('text-transform', 'uppercase');
                                    break;
                                    case 'capitalize':
                                    tti.css('text-transform', 'capitalize');
                                    break;
                                }
                            }
                        };

                        vm.applyBuffer = function(tti) {
                            if(!vm.buffer.active) return;
                            var opacity = vm.getOpacity(vm.buffer.transparency);
                            var c = hex2rgba(vm.buffer.color);
                            c.a = opacity;
                            var cs = rgba2str(c);
                            var ss = vm.createRoundBuffer(vm.buffer.size, cs);
                            tti.css('text-shadow', ss);
                        };

                        vm.applyBackground = function(tti) {
                            if(!vm.background.active) return;
                            var opacity = vm.getOpacity(vm.background.transparency);
                            var fc = hex2rgba(vm.background.color.fill);
                            var bc = hex2rgba(vm.background.color.border);
                            fc.a = bc.a = opacity;
                            fc = rgba2str(fc);
                            bc = rgba2str(bc);
                            var x = vm.background.size.x || 0;
                            var y = vm.background.size.y || 0;
                            var border = vm.background.size.border
                            tti.css('padding', y +'px ' + x + 'px');
                            tti.css('border', border + 'px solid ' + bc)
                            tti.css('background-color', fc);
                        };

                        vm.applyShadow = function(tti) {
                            if(!vm.shadow.active) return;
                            var opacity = vm.getOpacity(vm.shadow.transparency);
                            var c = hex2rgba(vm.shadow.color);
                            c.a = opacity;
                            c = rgba2str(c);
                            var x = vm.shadow.offset.x || 0;
                            var y = vm.shadow.offset.y || 0;
                            var blur = vm.shadow.blur || 0;
                            var spread = vm.shadow.spread || 0;
                            tti.css('box-shadow', x + 'px ' + y + 'px ' + blur + 'px ' + spread + 'px ' + c);
                        };

                        vm.applyPosition = function(tt) {
                            if(!vm.position.active) return;
                            var x = vm.position.offset.x || 0;
                            var y = vm.position.offset.y || 0;
                            tt.offset = L.point(x, y);
                            if(vm.position.position) {
                                tt.direction = vm.position.position.index;
                            }
                        };

                        // if transparency is present, set opacity to 100%-transparency
                        vm.getOpacity = function(trans) {
                            if(trans) return 1 - (trans/100);
                            return 1;
                        };

                        vm.createRoundBuffer = function(s, color) {
                            var dirs = [
                                '-1px -1px ' + s + 'px ' + color,
                                '-1px 1px ' + s + 'px ' + color,
                                '1px -1px ' + s + 'px ' + color,
                                '1px 1px ' + s + 'px ' + color
                            ];
                            return dirs.join(', ');
                        };

                        vm.toggleForm = function(id) {
                            vm.formShown[id] = !vm.formShown[id];
                        };

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
                vm.csvPreviewCount = 10;
                vm.csvHasHeader = true;
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
                    if(file == null) return;
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
                                case 'geojson':
                                    vm.content.geojson = angular.fromJson(vm.content.geojson);
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

                vm.updateIsHeaderSet = function() {
                    vm.parseCsvHeader();
                }

                vm.parseCsvHeader = function() {
                    var row = vm.content.csv.split('\n')[0];
                    var delimiter = vm.csvDelim || ',';
                    if(delimiter == '\\t') {
                        delimiter = '\t';
                    }
                    var dsv = d3.dsv(delimiter);
                    var cols = dsv.parseRows(row)[0];
                    if(!vm.csvHasHeader) {
                        cols = createCountingCsvHeader(cols.length, $translate);
                    }
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
                        if(!vm.csvHasHeader) {
                            var delimiter = vm.csvDelim || ',';
                            if(delimiter == '\\t') {
                                delimiter = '\t';
                            }
                            var tmpHeader = vm.csvHeaderColumns.join(delimiter);
                            tmpHeader += "\n";
                            content = tmpHeader + content;
                        }
                        csv2geojson.csv2geojson(content, {
                            lonfield: x,
                            latfield: y,
                            delimiter: delim
                        }, function(err, data) {
                            console.log(err);
                            vm.preview.csv = angular.copy(data);
                            vm.convertProjection(vm.preview.csv, epsg);
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
                        vm.convertProjection(vm.preview.csv, epsg);
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
                        vm.convertProjection(vm.preview.shape, epsg);
                        vm.result.shape = response;
                    });
                };

                vm.parseGeoJSON = function(content, epsg) {
                    if(!vm.content.geojson || !vm.epsg) return;
                    vm.preview.geojson = content;
                    vm.convertProjection(vm.preview.geojson, epsg);
                    vm.result.geojson = content;
                }

                vm.searchEPSG = function(text) {
                    return vm.epsgs.filter(function(epsg) {
                        // check if text matches either srid or srtext
                        // do not add epsg to result if not
                        text = text.toUpperCase();
                        if(epsg.auth_srid.toString().indexOf(text) == -1 && epsg.srtext.toUpperCase().indexOf(text) == -1) return false;
                        return true;
                    })
                }

                vm.convertProjection = function(geojson, epsg) {
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
        if(!p) return;
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
