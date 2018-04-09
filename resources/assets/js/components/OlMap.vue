<template>
    <div class="d-flex flex-column justify-content-between h-100">
        <div>
            <button type="button" class="btn btn-xs" :class="{'btn-primary': drawType == 'Point', 'btn-outline-primary': drawType != 'Point'}" @click="toggleDrawType('Point')">
                <i class="fas fa-fw fa-map-marker-alt"></i>
            </button>
            <button type="button" class="btn btn-xs" :class="{'btn-primary': drawType == 'LineString', 'btn-outline-primary': drawType != 'LineString'}" @click="toggleDrawType('LineString')">
                <i class="fas fa-fw fa-road"></i>
            </button>
            <button type="button" class="btn btn-xs" :class="{'btn-primary': drawType == 'Polygon', 'btn-outline-primary': drawType != 'Polygon'}" @click="toggleDrawType('Polygon')">
                <i class="fas fa-fw fa-object-ungroup"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-info" v-show="interactionMode != 'modify'" @click="setInteractionMode('modify')">
                <i class="fas fa-fw fa-edit"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-success" v-show="interactionMode == 'modify'" @click="updateFeatures">
                <i class="fas fa-fw fa-check"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-danger" v-show="interactionMode == 'modify'" @click="cancelUpdateFeatures">
                <i class="fas fa-fw fa-times"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-danger" v-show="interactionMode != 'delete'" @click="setInteractionMode('delete')">
                <i class="fas fa-fw fa-trash"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-success" v-show="interactionMode == 'delete'" @click="deleteFeatures">
                <i class="fas fa-fw fa-check"></i>
            </button>
            <button type="button" class="btn btn-xs btn-outline-danger" v-show="interactionMode == 'delete'" @click="cancelDeleteFeatures">
                <i class="fas fa-fw fa-times"></i>
            </button>
        </div>
        <div class="mt-2 col px-0">
            <div id="map" class="map w-100 h-100"></div>
            <div id="popup"></div>
        </div>
    </div>
</template>

<script>
    import 'ol/ol.css';
    import control from 'ol/control';
    import Coordinate from 'ol/coordinate';
    import extent from 'ol/extent';
    import Feature from 'ol/feature';
    import Graticule from 'ol/graticule';
    import interaction from 'ol/interaction';
    import Map from 'ol/map';
    import Overlay from 'ol/overlay';
    import proj from 'ol/proj';
    import View from 'ol/view';

    import FullScreen from 'ol/control/fullscreen';
    import Rotate from 'ol/control/rotate';
    import ScaleLine from 'ol/control/scaleline';

    import condition from 'ol/events/condition';

    import WKT from 'ol/format/wkt';
    import GeoJSON from 'ol/format/geojson';

    import DragRotate from 'ol/interaction/dragrotate';
    import DragZoom from 'ol/interaction/dragzoom';
    import Draw from 'ol/interaction/draw';
    import Modify from 'ol/interaction/modify';
    import PinchRotate from 'ol/interaction/pinchrotate';
    import PinchZoom from 'ol/interaction/pinchzoom';
    import Select from 'ol/interaction/select';
    import Snap from 'ol/interaction/snap';

    import TileLayer from 'ol/layer/tile';
    import VectorLayer from 'ol/layer/vector';

    import OSM from 'ol/source/osm';
    import Vector from 'ol/source/vector';

    import Circle from 'ol/style/circle';
    import Fill from 'ol/style/fill';
    import Stroke from 'ol/style/stroke';
    import Style from 'ol/style/style';

    export default {
        props: {
            reset: {
                required: false,
                type: Boolean,
                default: false
            },
            initWkt: {
                required: false,
                type: Array,
                default: _ => []
            },
            initGeojson: {
                required: false,
                type: Array,
                default: _ => []
            },
            onDeleteend: {
                required: false,
                type: Function,
                default: features => features
            },
            onDrawend: {
                required: false,
                type: Function,
                default: feature => feature
            },
            onModifyend: {
                required: false,
                type: Function,
                default: features => features
            }
        },
        mounted() {
            let vm = this;
            if(vm.initWkt.length && vm.initGeojson.length) {
                console.error('init-wkt and init-geojson provided. They are not allowed at once.');
                return;
            }
            // wait for DOM to be rendered
            vm.$nextTick(function() {
                vm.vector = new VectorLayer({
                    source: new Vector({
                        wrapX: false
                    }),
                    style: vm.createStyle()
                });
                let source = vm.vector.getSource();
                if(vm.initWkt.length) {
                    vm.initWkt.forEach(wkt => {
                        const geom = vm.wktFormat.readGeometry(wkt);
                        source.addFeature(new Feature({geometry: geom}));
                    });
                    vm.extent = vm.vector.getSource().getExtent();
                } else if(vm.initGeojson.length) {
                    vm.initGeojson.forEach(geojson => {
                        let feature = vm.geoJsonFormat.readFeature(geojson.geom);
                        feature.setProperties(geojson.props);
                        if(geojson.props.color) {
                            feature.setStyle(vm.createStyle(geojson.props.color));
                        }
                        source.addFeature(feature);
                    });
                    vm.extent = vm.vector.getSource().getExtent();
                }

                vm.draw = {
                    init: function() {
                        vm.map.addInteraction(this.Point);
                        this.Point.setActive(false);
                        vm.map.addInteraction(this.LineString);
                        this.LineString.setActive(false);
                        vm.map.addInteraction(this.Polygon);
                        this.Polygon.setActive(false);
                    },
                    Point: new Draw({
                        source: vm.vector.getSource(),
                        type: 'Point'
                    }),
                    LineString: new Draw({
                        source: vm.vector.getSource(),
                        type: 'LineString'
                    }),
                    Polygon: new Draw({
                        source: vm.vector.getSource(),
                        type: 'Polygon'
                    }),
                    getActive: function() {
                        return this.activeType ? this[this.activeType].getActive() : false;
                    },
                    setActive: function(active, type) {
                        if(active) {
                            this.activeType && this[this.activeType].setActive(false);
                            this[type].setActive(true, type);
                            this.activeType = type;
                        } else {
                            this.activeType && this[this.activeType].setActive(false);
                            this.activeType = null;
                        }
                    }
                };
                vm.modify = {
                    init: function() {
                        this.select = new Select({
                            hitTolerance: 5,
                            toggleCondition: condition.never,
                            wrapX: false
                        });
                        vm.map.addInteraction(this.select);

                        this.modify = new Modify({
                            features: this.select.getFeatures(),
                            wrapX: false
                        });
                        vm.map.addInteraction(this.modify);

                        this.modifiedFeatures = {};
                        this.setEvents();
                        this.modifyActive = false;
                        this.originalFeatures = [];
                        this.selectedFeature = undefined;
                    },
                    setEvents: function() {
                        let selectedFeatures = this.select.getFeatures();

                        this.select.on('change:active', function(event) {
                            selectedFeatures.forEach(selectedFeatures.remove, selectedFeatures);
                        }, this);
                        this.select.on('select', function(event) {
                            // config allows only one selected feature
                            if(event.selected.length) {
                                this.selectedFeature = event.selected[0];
                            } else {
                                this.selectedFeature = undefined;
                            }
                        }, this);
                        this.modify.on('change:active', function(event) {
                            this.modifyActive = !event.oldValue;
                            if(this.modifyActive) {
                                let source = vm.vector.getSource()
                                let features = source.getFeatures();
                                features.forEach(feature => {
                                    this.originalFeatures.push(feature.clone());
                                });
                            } else {
                                this.modifiedFeatures = {};
                            }
                        }, this);
                        this.modify.on('modifyend', function(event) {
                            let newFeature = event.features.getArray()[0];
                            this.modifiedFeatures[newFeature.ol_uid] = newFeature;
                        }, this);
                    },
                    getActive: function() {
                        return this.select.getActive() || this.modify.getActive();
                    },
                    setActive: function(active, cancelled) {
                        this.select.setActive(active);
                        this.modify.setActive(active);
                        if(!active) {
                            if(cancelled && this.originalFeatures.length) {
                                // If modify was cancelled, reset features to state before modify start
                                let source = vm.vector.getSource();
                                source.clear();
                                source.addFeatures(this.originalFeatures);
                            }
                            this.originalFeatures = [];
                        }
                    },
                    getModifiedFeatures: function() {
                        // return list of cloned features
                        let features = [];
                        for(let k in this.modifiedFeatures) {
                            features.push(this.modifiedFeatures[k].clone());
                        }
                        return features;
                    }
                };
                vm.delete = {
                    init: function() {
                        this.select = new Select({
                            hitTolerance: 5,
                            toggleCondition: condition.never,
                            wrapX: false
                        });
                        vm.map.addInteraction(this.select);

                        this.setEvents();
                        this.deletedFeatures = [];
                    },
                    setEvents: function() {
                        this.select.on('select', function(event) {
                            // config allows only one selected feature
                            if(event.selected.length) {
                                let feature = event.selected[0];
                                this.deletedFeatures.push(feature);
                                let source = vm.vector.getSource();
                                source.removeFeature(feature);
                            }
                        }, this);
                    },
                    getActive: function() {
                        return this.select.getActive();
                    },
                    setActive: function(active, cancelled) {
                        this.select.setActive(active);
                        if(!active) {
                            if(cancelled && this.deletedFeatures.length) {
                                // If delete was cancelled, readd deleted features
                                let source = vm.vector.getSource();
                                source.addFeatures(this.deletedFeatures);
                            }
                            this.deletedFeatures = [];
                        }
                    },
                    getDeletedFeatures: function() {
                        // return list of cloned features
                        let features = [];
                        this.deletedFeatures.forEach(feature => {
                            features.push(feature.clone());
                        });
                        return features;
                    }
                };

                vm.map = new Map({
                    controls: control.defaults().extend([
                        new FullScreen(),
                        new Rotate(),
                        new ScaleLine()
                    ]),
                    interactions: interaction.defaults().extend([
                        new DragRotate({
                            condition: condition.platformModifierKeyOnly
                        }),
                        new DragZoom({
                            condition: condition.shiftKeyOnly
                        }),
                        new PinchRotate(),
                        new PinchZoom(),
                    ]),
                    layers: [
                        new TileLayer({
                            source: new OSM({
                                wrapX: false
                            })
                        }),
                        vm.vector
                    ],
                    target: 'map',
                    view: new View({
                        center: [0, 0],
                        projection: 'EPSG:4326',
                        extent: [-180, -90, 180, 90],
                        // extent: proj.transformExtent([-180, -90, 180, 90], 'EPSG:4326', 'EPSG:3857'),
                        zoom: 2
                    })
                });
                if(vm.extent.length) {
                    vm.map.getView().fit(vm.extent);
                }

                vm.overlay = new Overlay({
                    element: document.getElementById('popup')
                });
                vm.map.addOverlay(vm.overlay);

                vm.map.on('click', function(e) {
                    const element = vm.overlay.getElement();
                    $(element).popover('dispose');
                    // if one mode is active, do not open popup
                    if(vm.draw.getActive() || vm.modify.getActive() || vm.delete.getActive()) {
                        return;
                    }
                    let features = vm.map.getFeaturesAtPixel(e.pixel);
                    if(features) {
                        let feature = features[0];
                        let geometry = feature.getGeometry();
                        let props = feature.getProperties();
                        let coords = extent.getCenter(geometry.getExtent());
                        vm.overlay.setPosition(coords);
                        let transformGeom = geometry.clone().transform('EPSG:4326', 'EPSG:3857');
                        const coordHtml = vm.geometryToList(transformGeom);
                        const content = `<dl>
                        <dt>Type</dt>
                        <dd>${transformGeom.getType()}</dd>
                        <dt>Coordinates</dt>
                        <dd>${coordHtml}</dd>
                        </dl>`;

                        vm.selectedFeature.id = props.id;
                        $(element).popover({
                            placement: 'top',
                            animation: true,
                            html: true,
                            content: content,
                            title: `Geometry #${vm.selectedFeature.id}`
                        });
                        $(element).popover('show');
                    } else {
                        vm.selectedFeature = {};
                    }
                });

                vm.draw.init();
                vm.modify.init();
                vm.delete.init();
                vm.draw.setActive(false);
                vm.modify.setActive(false);
                vm.delete.setActive(false);

                vm.snap = new Snap({
                    source: vm.vector.getSource()
                });
                vm.map.addInteraction(vm.snap);

                vm.options.graticule = new Graticule({
                    showLabels: true,
                    strokeStyle: new Stroke({
                        color: 'rgba(0, 0, 0, 0.75)',
                        width: 2,
                        lineDash: [0.5, 4]
                    })
                });
                vm.options.graticule.setMap(vm.map);

                // Event Listeners
                vm.draw.Point.on('drawend', function(event) {
                    vm.drawFeature(event.feature);
                }, vm);
                vm.draw.LineString.on('drawend', function(event) {
                    vm.drawFeature(event.feature);
                }, vm);
                vm.draw.Polygon.on('drawend', function(event) {
                    vm.drawFeature(event.feature);
                }, vm);
            });
        },
        methods: {
            toggleDrawType(type) {
                let oldType = this.drawType;
                this.drawType = type;
                if(this.interactionMode != 'draw') {
                    this.setInteractionMode('draw', true);
                } else if(oldType == type) {
                    this.setInteractionMode('');
                }
                this.draw.getActive() && this.draw.setActive(true, this.drawType);
            },
            setInteractionMode(mode, cancelled) {
                let oldMode = this.interactionMode;
                this.interactionMode = mode;
                if(mode == 'draw') {
                    this.draw.setActive(true, this.drawType);
                    this.modify.setActive(false, cancelled);
                    this.delete.setActive(false, cancelled);
                } else if(mode == 'modify') {
                    this.drawType = '';
                    this.draw.setActive(false);
                    this.modify.setActive(true);
                    this.delete.setActive(false, oldMode == 'delete');
                } else if(mode == 'delete') {
                    this.drawType = '';
                    this.draw.setActive(false);
                    this.modify.setActive(false, oldMode == 'modify');
                    this.delete.setActive(true);
                } else {
                    this.drawType = '';
                    this.interactionMode = '';
                    this.draw.setActive(false);
                    this.delete.setActive(false, cancelled);
                    this.modify.setActive(false, cancelled);
                }
            },
            createStyle(color) {
                const defaultColor = '#ffcc33';
                const activeColor = color || defaultColor;
                return new Style({
                    fill: new Fill({
                        color: 'rgba(255, 255, 255, 0.2)'
                    }),
                    stroke: new Stroke({
                        color: color || activeColor,
                        width: 2
                    }),
                    image: new Circle({
                        radius: 7,
                        fill: new Fill({
                            color: color || activeColor
                        }),
                        stroke: new Stroke({
                            color: 'rgba(0, 0, 0, 0.2)',
                            width: 2
                        })
                    })
                })
            },
            drawFeature(feature) {
                if(this.reset) {
                    let source = this.vector.getSource();
                    if(source.getFeatures().length) {
                        source.clear();
                    }
                }
                this.onDrawend(feature, this.wktFormat.writeFeature(feature));
            },
            updateFeatures() {
                const features = this.modify.getModifiedFeatures();
                let wktFeatures = features.map(f => this.wktFormat.writeFeature(f), this);
                this.onModifyend(features, wktFeatures);
                this.setInteractionMode('');
            },
            cancelUpdateFeatures() {
                this.setInteractionMode('', true);
            },
            deleteFeatures() {
                const features = this.delete.getDeletedFeatures();
                let wktFeatures = features.map(f => this.wktFormat.writeFeature(f), this);
                this.onDeleteend(features, wktFeatures);
                this.setInteractionMode('');
            },
            cancelDeleteFeatures() {
                this.setInteractionMode('', true);
            },
            geometryToList(g) {
                let coordHtml = '<ul class="list-group list-group-flush">';
                const coords = g.getCoordinates();
                switch(g.getType()) {
                    case 'Point':
                        coordHtml += this.coordinateToListElement(coords)
                        break;
                    case 'LineString':
                    case 'MultiPoint':
                        coords.forEach(c => {
                            coordHtml += this.coordinateToListElement(c);
                        });
                        break;
                    case 'Polygon':
                    case 'MultiLineString':
                        coords.forEach(cg => {
                            cg.forEach(c => {
                                coordHtml += this.coordinateToListElement(c);
                            });
                        });
                        break;
                    case 'MultiPolygon':
                        coords.forEach(cg => {
                            cg.forEach(cg2 => {
                                cg2.forEach(c => {
                                    coordHtml += this.coordinateToListElement(c);
                                });
                            });
                        });
                        break;
                }
                coordHtml += '</ul>';
                return coordHtml;
            },
            coordinateToListElement(c) {
                return '<li class="list-group-item pl-0">'+Coordinate.toStringXY(c, 4)+'</li>';
            }
        },
        data() {
            return {
                drawType: '',
                interactionMode: '',
                map: {},
                overlay: {},
                vector: {},
                modify: {},
                draw: {},
                delete: {},
                snap: {},
                options: {},
                extent: [],
                selectedFeature: {},
                wktFormat: new WKT(),
                geoJsonFormat: new GeoJSON()
            }
        }
    }
</script>
