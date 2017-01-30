spacialistApp.controller('mapCtrl', ['$rootScope', '$scope', '$timeout', '$sce', 'leafletData', 'scopeService', 'httpPostFactory', 'httpPostPromise', 'httpGetFactory', 'modalService', '$uibModal', '$auth', '$state', '$http', function($rootScope, $scope, $timeout, $sce, leafletData, scopeService, httpPostFactory, httpPostPromise, httpGetFactory, modalService, $uibModal, $auth, $state, $http) {
    scopeService.map = {};
    scopeService.map.events = {
        markers: {
            enable: ['click', 'drag', 'dragstart', 'dragend', 'popupopen', 'popupclose']
        }
    }
    $scope.markerOptions = {};
    scopeService.map.controls = {
        scale: true
    }
    scopeService.map.drawOptions = {
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
            circle: {
                showArea: true,
                metric: false,
                shapeOptions: {
                    color: '#ffff00'
                }
            },
            marker: {
                icon: L.divIcon({
                    className: 'fa fa-fw fa-plus',
                    iconSize: [20, 20]
                })
            }
        }
    }
    if (typeof $scope.map === 'undefined') $scope.map = {};
    if (typeof scopeService.mapBounds === 'undefined') scopeService.mapBounds = {};
    if (typeof scopeService.filedesc === 'undefined') $scope.filedesc = undefined;
    else $scope.filedesc = scopeService.filedesc;
    if (typeof $scope.mapBounds === 'undefined') $scope.mapBounds = scopeService.mapBounds;
    if (typeof $scope.map.events === 'undefined') $scope.map.events = scopeService.map.events;
    if (typeof $scope.map.controls === 'undefined') $scope.map.controls = scopeService.map.controls;
    if(typeof scopeService.attribData != 'undefined')
    leafletData.getMap().then(function(map) {
        scopeService.markers = scopeService.markers;
        scopeService.mapObject = $scope.mapObject = map;
    });
    angular.extend($scope, scopeService);

    $scope.map.layercontrol = {
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
    }

    var osmAttr = '&copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>';
    var mqAttr = 'Tiles &copy; <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png" />';
    $scope.map.layers = {
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
    }
    $scope.closedAlerts = {};
    $scope.output = {};
    $scope.relations = [];
    $scope.allImages = [];
    $scope.unlinkedFilter = {
        contexts: {}
    };
    $scope.markerValues = {};
    $scope.hideLists = {};
    $scope.lists = {};
    $scope.input = {};
    $scope.editEntry = {};
    $scope.availableTags = [{
        name: 'sommer'
    }, {
        name: 'sonne'
    }, {
        name: 'römisch'
    }, {
        name: 'meer'
    }, {
        name: 'griechisch'
    }];

    var icons = $scope.icons = [
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

    //klay/d3
    $scope.d3GraphJson = {
        "id": "",
        "x": 0,
        "y": 0,
        "children": [{
            "id": "n1",
            "labels": [{
                "text": "n1"
            }],
            "width": 30,
            "height": 10,
            "x": 50,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "0, 0"
            }
        }, {
            "id": "n2",
            "labels": [{
                "text": "n2"
            }],
            "width": 30,
            "height": 10,
            "x": 50,
            "y": 80,
            "properties": {
                "de.cau.cs.kieler.position": "0, 20"
            }
        }, {
            "id": "n3",
            "labels": [{
                "text": "n3"
            }],
            "width": 30,
            "height": 10,
            "x": 80,
            "y": 80,
            "properties": {
                "de.cau.cs.kieler.position": "0, 40"
            }
        }, {
            "id": "n4",
            "labels": [{
                "text": "n4"
            }],
            "width": 30,
            "height": 10,
            "x": 20,
            "y": 110,
            "properties": {
                "de.cau.cs.kieler.position": "150, 60"
            }
        }, {
            "id": "n5",
            "labels": [{
                "text": "n5"
            }],
            "width": 30,
            "height": 10,
            "x": 80,
            "y": 110,
            "properties": {
                "de.cau.cs.kieler.position": "0, 80"
            }
        }, {
            "id": "n6",
            "labels": [{
                "text": "n6"
            }],
            "width": 30,
            "height": 10,
            "x": 110,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "0, 100"
            }
        }, {
            "id": "n7",
            "labels": [{
                "text": "n7"
            }],
            "width": 30,
            "height": 10,
            "x": 110,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "90, 60"
            }
        }, {
            "id": "n8",
            "labels": [{
                "text": "n8"
            }],
            "width": 30,
            "height": 10,
            "x": 110,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "0, 120"
            }
        }, {
            "id": "n9",
            "labels": [{
                "text": "n9"
            }],
            "width": 30,
            "height": 10,
            "x": 110,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "50, 100"
            }
        }, {
            "id": "n10",
            "labels": [{
                "text": "n10"
            }],
            "width": 30,
            "height": 10,
            "x": 110,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "90, 80"
            }
        }, {
            "id": "n11",
            "labels": [{
                "text": "n11"
            }],
            "width": 30,
            "height": 10,
            "x": 110,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "90, 100"
            }
        }, {
            "id": "n12",
            "labels": [{
                "text": "n12"
            }],
            "width": 30,
            "height": 10,
            "x": 110,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "90, 120"
            }
        }, {
            "id": "n13",
            "labels": [{
                "text": "n13"
            }],
            "width": 30,
            "height": 10,
            "x": 110,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "150, 120"
            }
        }, {
            "id": "n14",
            "labels": [{
                "text": "n14"
            }],
            "width": 30,
            "height": 10,
            "x": 110,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "60, 160"
            }
        }],
        "edges": [{
            "id": "e0",
            "source": "n1",
            "target": "n2",
            "labels": [{
                "text": "e0"
            }]
        }, {
            "id": "e1",
            "source": "n2",
            "target": "n3",
            "labels": [{
                "text": "e1"
            }]
        }, {
            "id": "e2",
            "source": "n3",
            "target": "n5",
            "labels": [{
                "text": "e2"
            }]
        }, {
            "id": "e3",
            "source": "n3",
            "target": "n7",
            "labels": [{
                "text": "e3"
            }]
        }, {
            "id": "e4",
            "source": "n7",
            "target": "n4",
            "labels": [{
                "text": "e4"
            }]
        }, {
            "id": "e5",
            "source": "n7",
            "target": "n10",
            "labels": [{
                "text": "e5"
            }]
        }, {
            "id": "e6",
            "source": "n5",
            "target": "n6",
            "labels": [{
                "text": "e6"
            }]
        }, {
            "id": "e7",
            "source": "n10",
            "target": "n9",
            "labels": [{
                "text": "e7"
            }]
        }, {
            "id": "e8",
            "source": "n10",
            "target": "n11",
            "labels": [{
                "text": "e8"
            }]
        }, {
            "id": "e9",
            "source": "n6",
            "target": "n8",
            "labels": [{
                "text": "e9"
            }]
        }, {
            "id": "e10",
            "source": "n9",
            "target": "n8",
            "labels": [{
                "text": "e10"
            }]
        }, {
            "id": "e11",
            "source": "n11",
            "target": "n12",
            "labels": [{
                "text": "e11"
            }]
        }, {
            "id": "e12",
            "source": "n4",
            "target": "n13",
            "labels": [{
                "text": "e12"
            }]
        }, {
            "id": "e13",
            "source": "n8",
            "target": "n14",
            "labels": [{
                "text": "e13"
            }]
        }, {
            "id": "e14",
            "source": "n9",
            "target": "n14",
            "labels": [{
                "text": "e14"
            }]
        }, {
            "id": "e15",
            "source": "n12",
            "target": "n14",
            "labels": [{
                "text": "e15"
            }]
        }, {
            "id": "e16",
            "source": "n13",
            "target": "n14",
            "labels": [{
                "text": "e16"
            }]
        }]
    }

    $scope.addStuff = function() {
        $scope.d3GraphJson.children.push({
            "id": "n15",
            "labels": [{
                "text": "n14"
            }],
            "width": 30,
            "height": 10,
            "x": 150,
            "y": 40,
            "properties": {
                "de.cau.cs.kieler.position": "110, 160"
            }
        });
        $scope.d3GraphJson.edges.push({
            "id": "e17",
            "source": "n14",
            "target": "n15",
            "labels": [{
                "text": "e17"
            }]
        });
        $scope.createGraphFromD3($scope.d3GraphJson);
    }

    $scope.initD3Graph = function() {
        var svg = d3.select("#d3test")
            .append("svg")
            .attr("width", 700)
            .attr("height", 700)
            .append("g");
        var d3options = {
            fix: {
                algorithm: "de.cau.cs.kieler.fixed",
                layoutHierarchy: false
            }
        };
        $scope.d3root = svg.append("g");
        $scope.d3layouter = klay.d3kgraph()
            .size([700, 700])
            .transformGroup($scope.d3root)
            .options(d3options.fix);
        $scope.d3layoutGraph;
        $scope.createGraphFromD3($scope.d3GraphJson);
    }

    $scope.createGraphFromD3 = function(graph) {
            if (typeof graph === 'undefined') return;
            //d3.json("./neuertest.json", function(error, graph) {
            $scope.d3layoutGraph = graph;

            $scope.d3layouter.on("finish", function(d) {

                var nodes = $scope.d3layouter.nodes();
                var links = $scope.d3layouter.links(nodes);

                // #1 add the nodes' groups
                var nodeData = $scope.d3root.selectAll(".node")
                    .data(nodes, function(d) {
                        return d.id;
                    });

                var node = nodeData.enter()
                    .append("g")
                    .attr("class", function(d) {
                        if (d.children)
                            return "node compound";
                        else
                            return "node leaf";
                    });

                // add representing boxes for nodes
                var box = node.append("rect")
                    .attr("class", "atom")
                    .attr("width", 0)
                    .attr("height", 0);

                // add node labels
                node.append("text")
                    .attr("x", function(d) {
                        return d.width / 2
                    })
                    .attr("y", 6.5)
                    .attr("alignment-baseline", "middle")
                    .attr("text-anchor", "middle")
                    .text(function(d) {
                        return d.id;
                    })
                    .attr("font-size", "4px");


                // #2 add paths with arrows for the edges
                var linkData = $scope.d3root.selectAll(".link")
                    .data(links, function(d) {
                        return d.id;
                    });
                var link = linkData.enter()
                    .append("path")
                    .attr("class", "link")
                    .attr("d", "M0 0");

                // #3 update positions of all elements

                // node positions
                nodeData.transition()
                    .attr("transform", function(d) {
                        return "translate(" + (d.x || 0) + " " + (d.y || 0) + ")";
                    });
                // node sizes
                nodeData.select(".atom")
                    .transition()
                    .attr("width", function(d) {
                        return d.width;
                    })
                    .attr("height", function(d) {
                        return d.height;
                    });

                // edge routes, modified to start at the centered bottom and end at the centered top. Routes with different x or y value have two bendpoints
                linkData.transition().attr("d", function(d) {
                    var path = "";
                    if (d.sourcePoint && d.targetPoint) {
                        if (d.sourcePoint.y == d.targetPoint.y) {
                            if (d.sourcePoint.x <= d.targetPoint.x) {
                                path += "M" + (d.sourcePoint.x + 30) + " " + (d.sourcePoint.y + 5) + " ";
                                path += "L" + d.targetPoint.x + " " + (d.targetPoint.y + 5) + " ";
                            } else {
                                path += "M" + d.sourcePoint.x + " " + (d.sourcePoint.y + 5) + " ";
                                path += "L" + (d.targetPoint.x + 30) + " " + (d.targetPoint.y + 5) + " ";
                            }
                        } else {
                            if (d.sourcePoint.x != d.targetPoint.x) {
                                var bendY = (d.targetPoint.y + d.sourcePoint.y + 10) / 2;
                                d.bendPoints = [];
                                d.bendPoints.push({
                                    x: d.sourcePoint.x + 15,
                                    y: bendY
                                });
                                d.bendPoints.push({
                                    x: d.targetPoint.x + 15,
                                    y: bendY
                                });
                            }
                            path += "M" + (d.sourcePoint.x + 15) + " " + (d.sourcePoint.y + 10) + " ";
                            (d.bendPoints || []).forEach(function(bp, i) {
                                path += "L" + bp.x + " " + bp.y + " ";
                            });
                            path += "L" + (d.targetPoint.x + 15) + " " + d.targetPoint.y + " ";
                        }
                    }
                    return path;
                });

            });

            $scope.graphLoaded = true;
            // start an initial layout
            $scope.d3layouter.kgraph(graph);
        }

    var getKeyForName = function(name) {
        return name.replace(/-/, '');
    };

    $scope.renameMarker = function(oldName, newName) {
        var messageScope = function() { return $scope; };
        var oldKey = getKeyForName(oldName);
        var oldMarker = scopeService.markers[oldKey];
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
        scopeService.markers[title].message = "<div ng-include src=\"'layouts/marker.html'\"></div>";
        scopeService.markers[title].getMessageScope = messageScope;
        // add values to own object. Easier to read relevant values
        scopeService.markers[title].contextInfo = {
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
        delete scopeService.markers[oldKey];
    };

    var displayMarkersHelper = function(current, bounds) {
        var messageScope = function() { return $scope; };
        //for(var i = 0; i < current.length; i++) {
        for(var key in current) {
            if(!current.hasOwnProperty(key)) continue;
            var curr = current[key];
            if (typeof curr.lat == 'undefined' || typeof curr.lng == 'undefined' || curr.lat === null || curr.lng === null) continue;
            var latlng = {
                lat: Number(curr.lat),
                lng: Number(curr.lng)
            };
            //adjust map boundaries
            if (latlng.lat > bounds.northEast.lat) {
                bounds.northEast.lat = latlng.lat;
            }
            if (latlng.lat < bounds.southWest.lat) {
                bounds.southWest.lat = latlng.lat;
            }
            if (latlng.lng > bounds.northEast.lng) {
                bounds.northEast.lng = latlng.lng;
            }
            if (latlng.lng < bounds.southWest.lng) {
                bounds.southWest.lng = latlng.lng;
            }
            // set marker icon options
            var icon = curr.icon || icons[0].icon;
            var color = curr.color || '#00FF00';
            var iconOpts = {
                className: 'fa fa-fw fa-lg fa-' + icon,
                color: color,
                iconSize: [20, 20]
            };
            //add the current marker and load it's stored attribute values
            var id = curr.id;
            var title = addMarker(latlng, iconOpts, curr.name, id);
            scopeService.markers[title].message = "<div ng-include src=\"'layouts/marker.html'\"></div>";
            scopeService.markers[title].getMessageScope = messageScope;
            // add values to own object. Easier to read relevant values
            scopeService.markers[title].contextInfo = {
                data: curr.data,
                id: id,
                title: title,
                name: curr.name,
                root_cid: curr.root_cid,
                typeid: curr.typeid,
                typename: curr.typename,
                ctid: curr.ctid,
                color: color,
                icon: icon
            };
            if(typeof current.children != 'undefined') {
                displayMarkersHelper(current.children, bounds);
            }
        }
    };

    scopeService.displayMarkers = function(contextList) {
        if(!scopeService.can('view_geodata')) {
            return;
        }
        scopeService.markers = {};
        $scope.mapBounds = {
            southWest: {
                lat: 90,
                lng: 180
            },
            northEast: {
                lat: -90,
                lng: -180
            }
        };
        displayMarkersHelper(contextList, $scope.mapBounds);
        scopeService.mapBounds = $scope.mapBounds;
        $scope.markers = scopeService.markers;
    };

    scopeService.addMarker = function(elem) {
        if(typeof elem.lat == 'undefined' || typeof elem.lng == 'undefined') return;
        displayMarkersHelper({
            1: elem
        }, scopeService.mapBounds);
        $scope.mapBounds = scopeService.mapBounds;
        $scope.markers = scopeService.markers;
    };

    $scope.updateMarkerOptions = function(markerId, markerKey, color, icon) {
        if(typeof markerId == 'undefined') return;
        if(markerId <= 0) return;
        var formData = new FormData();
        formData.append('id', markerId);
        if(typeof color != 'undefined') formData.append('color', color);
        if(typeof icon != 'undefined') formData.append('icon', icon.icon);
        httpPostPromise.getData('api/context/set/icon', formData).then(
            function(icon) {
                console.log(icon);
                angular.extend(scopeService.markers[markerKey].icon, {
                    className: 'fa fa-fw fa-lg fa-' + icon.icon,
                    color: icon.color
                });
            }
        );
    };

    /**
     * Opens the popup of the current activated/clicked marker
     * Also stores the object's attribute values in an array `markerValues`
     * Initial values are stored in `markerDefaultValues`
     */
    var openPopup = function(options) {
        $rootScope.$emit('setCurrentElement', {
            target: $scope.currentElement,
            source: scopeService.markers[options.title].contextInfo
        });
        $scope.activeMarker = options.id;
        $scope.markerKey = options.title;
        $scope.markerOptions = {
            icon: {
                icon: scopeService.markers[options.title].contextInfo.icon,
                name: scopeService.markers[options.title].contextInfo.icon
            },
            color: scopeService.markers[options.title].contextInfo.color
        };
        var currentOpts = {};
        if (typeof scopeService.markers[options.title].contextInfo !== 'undefined') {
            currentOpts = angular.extend({}, scopeService.markers[options.title].contextInfo);
            currentOpts.title = options.title;
        } else {
            currentOpts = angular.extend({}, options);
        }
        $scope.markerValues.locked = !options.draggable;
        updateMarkerOpts(currentOpts);
        //$scope.markerChoices = scopeService.markerChoices;
    };

    /**
     * listener for different leaflet actions
     */
    $scope.$on('leafletDirectiveMarker.popupopen', function(event, args) {
        openPopup(scopeService.markers[args.leafletEvent.target.options.title].contextInfo);
    });
    /**
     * If the marker's popup was closed, reset all values which hold marker specific values
     */
    $scope.$on('leafletDirectiveMarker.popupclose', function(event, args) {
        $scope.activeMarker = -1;
        $scope.markerKey = undefined;
        $scope.sideNav.contextHistory = undefined;
    });
    /**
     * While dragging the marker, update its geo position
     */
    $scope.$on('leafletDirectiveMarker.drag', function(event, args) {
        var latlng = args.leafletEvent.target.getLatLng();
        updateMarkerPos(latlng.lat, latlng.lng);
    });
    /**
     * On drag start, set the dragged marker as current marker
     */
    $scope.$on('leafletDirectiveMarker.dragstart', function(event, args) {
        var currentOpts = angular.extend({}, args.leafletEvent.target.options);
        $scope.markerActive = true;
        $scope.activeMarker = currentOpts.id;
        updateMarkerOpts(currentOpts);
    });
    /**
     * On dragend (release mouse button), store the new geo position and open the marker's popup
     */
    $scope.$on('leafletDirectiveMarker.dragend', function(event, args) {
        var opts = args.leafletEvent.target.options;
        opts.lat = $scope.markerValues.lat;
        opts.lng = $scope.markerValues.lng;
        scopeService.markers[opts.title]['myOptions'].lat = opts.lat;
        scopeService.markers[opts.title]['myOptions'].lng = opts.lng;
        scopeService.markers[opts.title].focus = true;
    });
    /**
     * If the marker has been created, add the marker to the marker-array and store it in the database
     */
    $scope.$on('leafletDirectiveDraw.draw:created', function(event, args) {
        var layer = args.leafletEvent.layer;
        var iconOpts = layer.options.icon.options;
        var latlng = layer._latlng;
        var opts = {};
        opts.lat = latlng.lat;
        opts.lng = latlng.lng;
        scopeService.createNewContext(opts);
    });
    /*$scope.$on('leafletDirectiveDraw.draw:edited', function(event, args) {
        console.log("edited");
    });
    $scope.$on('leafletDirectiveDraw.draw:deleted', function(event, args) {
        console.log("deleted");
    });*/
    $scope.$on('leafletDirectiveDraw.draw:drawstart', function(event, args) {
        $scope.markerPlaceMode = true;
    });
    $scope.$on('leafletDirectiveDraw.draw:drawstop', function(event, args) {
        $scope.markerPlaceMode = false;
    });
    /*$scope.$on('leafletDirectiveDraw.draw:editstart', function(event, args) {
        console.log("editstart");
    });
    $scope.$on('leafletDirectiveDraw.draw:editstop', function(event, args) {
        console.log("editstop");
    });
    $scope.$on('leafletDirectiveDraw.draw:deletestart', function(event, args) {
        console.log("deletestart");
    });
    $scope.$on('leafletDirectiveDraw.draw:deletestop', function(event, args) {
        console.log("deletestop");
    });*/

    /**
     * Set initial values for the add/remove context tab
     */
    $scope.stuffNav = {
        newTab: true,
        editTab: false,
        harrisTab: false,
        resetTabs: function() {
            $scope.stuffNav.newTab = false;
            $scope.stuffNav.editTab = false;
            $scope.stuffNav.harrisTab = false;
        },
        setNewTab: function() {
            $scope.stuffNav.resetTabs();
            $scope.stuffNav.newTab = true;
        },
        setEditTab: function() {
            $scope.stuffNav.resetTabs();
            $scope.stuffNav.editTab = true;
        },
        setHarrisTab: function() {
            $scope.stuffNav.resetTabs();
            $scope.stuffNav.harrisTab = true;
        }
    }

    /**
     * Set initial values for the side navigation (map view, image view, ...)
     */
    $scope.sideNav = {
        mapTab: true,
        imageTab: false,
        listTab: false,
        stuffTab: false,
        sourcesTab: false,
        contextHistory: undefined,
        contextIds: 0,
        resetTabs: function() {
            $scope.sideNav.mapTab = false;
            $scope.sideNav.imageTab = false;
            $scope.sideNav.listTab = false;
            $scope.sideNav.stuffTab = false;
            $scope.sideNav.sourcesTab = false;
            if(typeof $scope.markerValues !== 'undefined') $scope.markerValues.sources = undefined;
            $scope.sideNav.activeOptionTab = undefined;
            $scope.sideNav.optionAttributes = undefined;
        },
        setMapTab: function() {
            $scope.sideNav.resetTabs();
            $scope.sideNav.mapTab = true;
        },
        setImageTab: function() {
            $scope.sideNav.resetTabs();
            $scope.sideNav.imageTab = true;
            $scope.loadedImages = $scope.allImages.slice(0, 10);
        },
        setListTab: function() {
            $scope.sideNav.resetTabs();
            $scope.sideNav.listTab = true;
        },
        setStuffTab: function() {
            $scope.sideNav.resetTabs();
            $scope.sideNav.stuffTab = true;
            if (typeof $scope.sideNav.contextHistory === 'undefined') {
                getContextHistory();
            }
        },
        setSourcesTab: function() {
            $scope.sideNav.resetTabs();
            $scope.sideNav.sourcesTab = true;
            var cid = $scope.activeMarker;
            httpGetFactory('api/sources/get/' + cid, function(sources) {
                $scope.markerValues.sources = sources;
            });
        },
        setOptionTab: function(index) {
            $scope.sideNav.resetTabs();
            for (var i = 0; i < $scope.optionFields.length; i++) {
                var field = $scope.optionFields[i];
                if (index == field.aid) {
                    $scope.sideNav.activeOptionTab = {
                        index: index,
                        name: field.val
                    }
                    $scope.sideNav.optionAttributes = [];
                    for (var j = 0; j < field.attributes.length; j++) {
                        var value = field.attributes[j];
                        $scope.sideNav.optionAttributes.push({
                            oid: value.o_id,
                            aid: value.a_id,
                            val: value.val,
                            datatype: value.datatype,
                            options: value.options
                        });
                    }
                    break;
                }
            }
        }
    }

    $scope.selectItem = function(scope) {
        console.log(scope.$modelValue);
    }

    /**
     * Set initial values for the left-hand tab
     * TODO deprecated, remove
     */
    $scope.subNav = {
        bibTab: true,
        impTab: false,
        fileImp: false,
        valueImp: false,
        setImportTab: function() {
            $scope.subNav.bibTab = false;
            $scope.subNav.impTab = true;
            $scope.subNav.expTab = false;
        },
        setBibTab: function() {
            $scope.subNav.bibTab = true;
            $scope.subNav.impTab = false;
            $scope.subNav.expTab = false;
        },
        setExportTab: function() {
            $scope.subNav.bibTab = false;
            $scope.subNav.impTab = false;
            $scope.subNav.expTab = true;
        },
        setFileImport: function() {
            $scope.subNav.fileImp = true;
            $scope.subNav.valueImp = false;
        },
        setValueImport: function() {
            $scope.subNav.fileImp = false;
            $scope.subNav.valueImp = true;
        },
        resetImport: function() {
            $scope.subNav.fileImp = false;
            $scope.subNav.valueImp = false;
        }
    }

    /*
     * Creates a marker at a given location `latlng`, with given marker icon options `iconOpts`, an optional title ("" or `null` if you don't want to set it) and an id.
     */
    var addMarker = function(latlng, iconOpts, msg, id) {
        //if no msg (title) is present, set it to Marker_X
        if(typeof msg === 'undefined' || msg === null || msg.length === 0) {
            var max = 0;
            //get the max index of the markers (Marker_X)
            for(var pos in scopeService.markers) {
                console.log(pos);
                var markerTitle = scopeService.markers[pos].title;
                var matches = markerTitle.match(/Marker_([0-9]+)/);
                if(matches === null) continue;
                var mId = Number(matches[1]);
                if (mId > max) max = mId;
            }
            msg = "Marker_" + (max+1);
        }
        var title = msg.replace(/-/, ''); //'-' is not allowed in marker title
        scopeService.markers[title] = {
            title: title,
            lat: latlng.lat,
            lng: latlng.lng,
            draggable: false,
            icon: {
                type: "div",
                className: iconOpts.className,
                color: iconOpts.color,
                iconSize: iconOpts.iconSize
            }
        };
        if (typeof id !== 'undefined') {
            scopeService.markers[title].id = id;
        }
        scopeService.markers = scopeService.markers;
        return title;
    };

    /**
     * Set the variable `value` as value for the given key `id` in the `markerValues` object.
     */
    var setMarkerOption = function(id, value) {
        $scope.markerValues[id] = value;
    }

    $scope.isEmpty = function(obj) {
        if (typeof obj === 'undefined') return false;
        return Object.keys(obj).length == 0;
    }

    $scope.updateUnlinkedFilter = function(id, isContext) {
        if (typeof isContext === 'undefined' || !isContext) {
            //console.log($scope.unlinkedFilter[id]);
        } else {
            //console.log($scope.unlinkedFilter.contexts[id]);
        }
    }

    /**
     * Toggle if the marker should be draggable or not
     */
    $scope.togglePositionLock = function() {
        scopeService.markers[$scope.markerValues.title].draggable = $scope.markerValues.locked;
    }

    /**
     * Store current marker values `markerValues` in the database
     * current marker id is stored in `activeMarker`
     */
    $scope.updateEntry = function() {
        $scope.markerValues.id = $scope.activeMarker;
        if (typeof $scope.markerValues.id === 'undefined') {
            $scope.markerValues.id = -1;
        }
        var title = $scope.markerValues.title;
        var formData = new FormData();
        var opts = {};
        var ignores = ['locked', 'images', 'sources'];
        for (var key in $scope.markerValues) {
            if ($scope.markerValues.hasOwnProperty(key)) {
                if(ignores.indexOf(key) >= 0) continue;
                var value = $scope.markerValues[key];
                if (typeof value === 'object') {
                    formData.append(key, angular.toJson(value));
                } else {
                    formData.append(key, value);
                }
                opts[key] = value;
            }
        }
        //update marker values
        httpPostFactory('api/context/add', formData, function(callback) {
            var newId = parseInt(callback.cid, 10);
            setMarkerOption('id', newId);
            opts.id = newId;
            $scope.activeMarker = newId;
            scopeService.markers[title]['myOptions'] = opts;
            scopeService.markers[title].message = '<h5>' + scopeService.markers[title]['myOptions'].name + '</h5>';
            //after storing new values, update default values (`markerDefaultValues`)
            markerDefaultValues = angular.extend({}, $scope.markerValues);
        });
    }

    /**
     * Reset current marker values to the initial values `markerDefaultValues`
     */
    $scope.resetEntry = function() {
        scopeService.markers[$scope.markerValues.title].lat = markerDefaultValues.lat;
        scopeService.markers[$scope.markerValues.title].lng = markerDefaultValues.lng;
        $scope.markerValues = angular.extend({}, markerDefaultValues);
    }

    /**
     * Remove current marker from db. Opens a confirm dialog beforehand.
     */
    $scope.removeEntry = function() {
        $scope.deleteModalFields = {
            name: $scope.markerValues.name
        }
        var modalInstance = $uibModal.open({
            templateUrl: 'layouts/delete-confirm.html',
            //windowClass: 'wide-modal',
            controller: function($uibModalInstance) {
                $scope.cancel = function(result) {
                    $uibModalInstance.dismiss('cancel');
                },
                $scope.deleteConfirmed = function() {
                    $uibModalInstance.dismiss('ok');
                    delete scopeService.markers[$scope.markerValues.title];
                    httpGetFactory('api/context/delete/' + $scope.activeMarker, function(callback) {});
                    resetMarkerOpts();
                }
            },
            scope: $scope
        });
        modalInstance.result.then(function(selectedItem) {}, function() {});
    }

    $scope.addListEntry = function(aid, oid, text, arr) {
        var index = aid + '_' + (oid || '');
        var tmpArr = $scope.$eval(arr);
        var inp = $scope.$eval(text);
        if(typeof tmpArr[index] == 'undefined') tmpArr[index] = [];
        tmpArr[index].push({
            'name': inp[index]
        });
        inp[index] = '';
        /*if(typeof arr === 'undefined') arr = [];
        arr.push({
            'name': text
        });*/
        /*var arr = [];
        console.log($scope.fields[index]);
        arr.push({
            'name': $scope.input[index]
        });
        $scope.input[index] = "";
        if (typeof $scope.editEntry[index] === 'undefined') $scope.editEntry[index] = [];
        $scope.editEntry[index].push(false);
        if (typeof $scope.markerValues[index] !== 'undefined') arr = $scope.markerValues[index].concat(arr);
        setMarkerOption(index, arr);*/
    }

    $scope.editListEntry = function(ctid, aid, $index, val, tableIndex) {
        $scope.cancelEditListEntry();
        var name = ctid + "_" + aid;
        $scope.currentEditName = name;
        $scope.currentEditIndex = $index;
        if (typeof tableIndex !== 'undefined') {
            $scope.currentEditCol = tableIndex;
            $scope.editEntry[name][$index][tableIndex] = true;
        } else {
            $scope.editEntry[name][$index] = true;
        }
        $scope.initialListVal = val;
    }

    $scope.cancelEditListEntry = function() {
        if (typeof $scope.currentEditName !== 'undefined' && typeof $scope.currentEditIndex !== 'undefined') {
            if (typeof $scope.currentEditCol !== 'undefined') {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex][$scope.currentEditCol] = false;
                $scope.markerValues[$scope.currentEditName].selectedEpochs[$scope.currentEditIndex][$scope.currentEditCol] = $scope.initialListVal;
            } else {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex] = false;
                $scope.markerValues[$scope.currentEditName][$scope.currentEditIndex] = $scope.initialListVal;
            }
        }
        $scope.currentEditName = undefined;
        $scope.currentEditIndex = undefined;
        $scope.currentEditCol = undefined;
        $scope.initialListVal = undefined;
    }

    $scope.storeEditListEntry = function() {
        if (typeof $scope.currentEditName !== 'undefined' && typeof $scope.currentEditIndex !== 'undefined') {
            if (typeof $scope.currentEditCol !== 'undefined') {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex][$scope.currentEditCol] = false;
            } else {
                $scope.editEntry[$scope.currentEditName][$scope.currentEditIndex] = false;
            }
        }
        $scope.currentEditName = undefined;
        $scope.currentEditIndex = undefined;
        $scope.currentEditCol = undefined;
        $scope.initialListVal = undefined;
    }

    $scope.removeListItem = function(aid, oid, arr, $index) {
        var index = aid + '_' + (oid || '');
        var tmpArr = $scope.$eval(arr);
        tmpArr[index].splice($index, 1);
        //var name = aid + "_" + oid;
        //$scope.markerValues[name].splice($index, 1);
    }

    $scope.toggleList = function(ctid, aid) {
        var index = ctid + "_" + aid;
        $scope.hideLists[index] = !$scope.hideLists[index];
    }

    $scope.updateFileDesc = function(fd) {
        scopeService.filedesc = fd;
    }
    $scope.updateDates = function(dts) {
        scopeService.datetable = dts;
    }
    $scope.updateEpochs = function(epochs) {
        scopeService.epochs = epochs;
    }

    $scope.updateInput = function($event) {
        setMarkerOption($event.target.id, $event.target.value);
    }

    $scope.updateSelectInput = function($model) {
        setMarkerOption($model.$name, $model.$modelValue);
    }

    $scope.updateMSelectInput = function($select) {
        $model = $select.ngModel;
        $scope.markerValues[$model.$name] = $select.selected;
    }

    /**
     * Stores all children of the given root element `current` in a tree structure in a json object.
     */
    var createContextTree = function(current) {
        if (typeof $scope.roots[current.realId] === 'undefined') return current;
        var children = $scope.roots[current.realId].slice();
        delete $scope.roots[current.realId];
        for (var i = 0; i < children.length; i++) {
            var ctx = children[i];
            var elem = {
                title: ctx.name,
                clickable: true,
                typeId: ctx.type,
                typeName: ctx.typename,
                typeLabel: ctx.typelabel,
                ctid: ctx.ctid,
                realId: ctx.id,
                root_cid: ctx.root_cid,
                data: {}
            };
            angular.forEach(ctx.data, function(value, key) {
                var aid = value['a_id'];
                var attr = {};
                attr.ctid = ctx.ctid
                attr.aid = aid;
                if(value.datatype == 'list') {
                    if(typeof elem.data[aid] == 'undefined') attr.value = [];
                    else attr.value = elem.data[aid].value;
                    attr.value.push({
                        'name': value['str_val']
                    });
                } else {
                    attr.value = value['str_val'];
                }
                //attr.value = value['str_val'];
                //elem.data.push(attr);
                elem.data[aid] = attr;
            });
            if (ctx.type == 0) {
                elem.fields = $scope.ctxtRefs[elem.typeName].slice();
                elem.children = [];
                elem.id = $scope.sideNav.contextIds++;
                current.children.push(elem);
                var idx = current.children.length - 1;
                current.children[idx] = createContextTree(current.children[idx]);
            } else if (ctx.type == 1) {
                elem.fields = $scope.artiRefs[elem.typeName].slice();
                elem.id = $scope.sideNav.contextIds++;
                current.children.push(elem);
            }
        }
        return current;
    }

    /**
     * Retrieves the children for the current active marker `$scope.activeMarker` and stores them in a tree structure using `createContextTree`.
     */
    var getContextHistory = function() {
        $scope.sideNav.contextHistory = [];
        var pId = $scope.sideNav.contextIds++;
        var realId = $scope.activeMarker;
        var title = scopeService.markers[$scope.markerKey]['myOptions'].name;
        $scope.sideNav.contextHistory.push({
            id: pId,
            realId: realId,
            title: title,
            typeId: 0,
            clickable: true,
            children: []
        });
        httpGetFactory('api/context/get/children/' + $scope.activeMarker, function(roots) {
            $scope.roots = roots;
            if (typeof roots[realId] !== 'undefined' && roots[realId].length > 0) {
                $scope.stuffNav.setHarrisTab();
            } else {
                $scope.stuffNav.setNewTab();
            }
            $scope.sideNav.contextHistory[0] = createContextTree($scope.sideNav.contextHistory[0]);
        });
        $scope.currentContext = $scope.sideNav.contextHistory[0];
    }

    /**
     * Writes the current existing markers to a temporary csv file which can then be downloaded by the user.
     */
    $scope.write2csv = function() {
        var data = typeof scopeService.markers !== 'object' ? JSON.parse(scopeService.markers) : scopeService.markers;

        var out = '';
        for (var i in data) {
            var row = '';
            var marker = data[i];
            var curr = {
                "Land (modern)": marker.country,
                "Stadt": marker.city,
                "Art": marker.type,
                "Gebäude (+Maße)/Standort": marker.building,
                "Name": marker.name,
                "Datierung": marker.date,
                "Epoche": marker.epoch,
                "lon": marker.lat,
                "lat": marker.lng,
            }
            for (var idx in curr) {
                row += idx + ',';
            }
            row = row.slice(0, -1);
            out += row + escape('\n');
            break;
        }
        for (var i in data) {
            var row = '';
            var marker = data[i];
            var curr = {
                "Land (modern)": marker.country,
                "Stadt": marker.city,
                "Art": marker.type,
                "Gebäude (+Maße)/Standort": marker.building,
                "Name": marker.name,
                "Datierung": marker.date,
                "Epoche": marker.epoch,
                "lon": marker.lat,
                "lat": marker.lng,
            }
            for (var idx in curr) {
                var obj = curr[idx];
                if (typeof obj === 'undefined') {
                    obj = '';
                } else if (typeof obj === 'string') {
                    obj = obj.replace(/\'/g, "");
                    obj = obj.replace(/\"/g, "");
                }
                row += '"' + obj + '",';
            }
            row = row.slice(0, -1);
            out += row + escape('\n');
        }
        if (out == '') {
            alert("Invalid data");
            return;
        }

        var filename = "bib_out.csv";
        var uri = 'data:text/csv;charset=utf-8,' + out;
        var link = document.createElement("a");
        link.href = uri;
        link.style = "visibility:hidden";
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    $scope.storeLib = function() {
        if(typeof $scope.output !== 'undefined') updateMarkerOpts($scope.output);
        storeLib($scope.markerValues);
    }

    var storeLib = function(opts) {
        opts.ctid = 10;
        updateMarkerOpts(opts);
        $scope.updateEntry();
    }

    /**
     * Updates the markerValues array with values from the given `opts` array
     */
    var updateMarkerOpts = function(opts) {
        angular.extend($scope.markerValues, opts);
        if (typeof opts.lat != 'undefined' && typeof opts.lng != 'undefined') updateMarkerPos(opts.lat, opts.lng);
    }

    var updateMarkerPos = function(lat, lng) {
        $scope.markerValues.lat = lat;
        $scope.markerValues.lng = lng;
    }

    var resetMarkerOpts = function() {
        $scope.markerValues = {};
        $scope.activeMarker = -1;
        $scope.markerActive = false;
    }

    $scope.hasMarkers = function() {
        if (typeof scopeService.markers === 'undefined') return false;
        return Object.keys(scopeService.markers).length > 0;
    }

    $scope.subNav.submitImport = function(isValid) {
        alert("submitImport is not implemented!");
        return;
        //TODO
        if ($scope.subNav.valueImp) {
            $scope.addPointFormSubmitted = true;
            if (!isValid) return;
            var latlng = {
                lat: Number($scope.subNav.values.x),
                lng: Number($scope.subNav.values.y)
            }
            var iconOpts = {
                //className: 'marker-point',
                className: 'fa fa-fw fa-circle-o',
                iconSize: [20, 20]
            }
            var opts = {
                lat: latlng.lat,
                lng: latlng.lng
            }
            resetMarkerOpts();
            $scope.markerValues.name = $scope.subNav.values.name;
            opts.title = addMarker(latlng, iconOpts, $scope.subNav.values.name);
            storeLib(opts);
            angular.extend($scope.map.center, {
                lat: opts.lat,
                lng: opts.lng
            });
        } else if ($scope.subNav.fileImp) {
            httpPostFactory('get-markers.php', $scope.subNav.values.fileData, function(callback) {
                var markers = callback;
                for (var p in markers) {
                    if (markers.hasOwnProperty(p)) {
                        markers[p]['icon'] = {
                            type: 'div',
                            iconSize: [20, 20],
                            //className: 'marker-point'
                            className: 'fa fa-fw fa-circle-o'
                        }
                        markers[p]['draggable'] = false;
                        markers[p]['message'] = '<h5>' + markers[p]['message'] + '</h5>'
                    }
                };
                scopeService.markers = scopeService.markers = markers;
            });
            httpPostFactory('upload-test.php', new FormData(), function(callback) {
                $scope.filedesc = callback.split('\\n');
                $scope.updateFileDesc($scope.filedesc);
            });
            httpPostFactory('get-dates.php', new FormData(), function(callback) {
                $scope.datetable = callback;
                $scope.updateDates($scope.datetable);
                //epochs
                var dts = $scope.datetable;
                var props = {};
                $scope.epochs = [];
                for (var i = 0; i < dts.length; i++) {
                    var lbl = dts[i].deflabel;
                    if (props.hasOwnProperty(lbl)) {
                        continue;
                    }
                    $scope.epochs.push(lbl);
                    props[lbl] = 1;
                }
                $scope.epochs.sort();
                $scope.updateEpochs($scope.epochs);
            });
        }
        $scope.subNav.fileImp = false;
        $scope.subNav.valueImp = false;
        $scope.subNav.values = {};
    }

    $scope.subNav.triggerFileSelect = function() {
        document.querySelector('#importFile').click();
    }

    $scope.subNav.triggerCreateMarker = function() {
        document.querySelector('.leaflet-draw-draw-marker').click();
        $scope.sideNav.setMapTab();
    }

    $scope.setTestingState = function() {
        $state.go('testing');
    }

    $scope.setSpacialistState = function() {
        $state.go('spacialist');
    }
}]);

spacialistApp.directive('upload', function(httpPostFactory, scopeService) {
    return {
        restrict: 'A',
        scope: false,
        link: function(scope, element, attr) {
            element.bind('change', function() {
                var formData = new FormData();
                formData.append('file', element[0].files[0]);
                scope.subNav.values = {
                    fileName: angular.element(this).val()
                }
                scope.subNav.values.fileData = formData;
                scope.subNav.fileImp = true;
                scope.subNav.valueImp = false;
            });
        }
    }
});
