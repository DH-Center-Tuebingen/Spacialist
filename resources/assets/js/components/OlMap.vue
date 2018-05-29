<template>
    <div class="d-flex flex-column justify-content-between h-100">
        <div class="d-flex flex-row justify-content-between">
            <div>
                <button type="button" class="btn btn-sm" :class="{'btn-primary': drawType == 'Point', 'btn-outline-primary': drawType != 'Point'}" @click="toggleDrawType('Point')">
                    <i class="fas fa-fw fa-map-marker-alt"></i>
                </button>
                <button type="button" class="btn btn-sm" :class="{'btn-primary': drawType == 'LineString', 'btn-outline-primary': drawType != 'LineString'}" @click="toggleDrawType('LineString')">
                    <i class="fas fa-fw fa-road"></i>
                </button>
                <button type="button" class="btn btn-sm" :class="{'btn-primary': drawType == 'Polygon', 'btn-outline-primary': drawType != 'Polygon'}" @click="toggleDrawType('Polygon')">
                    <i class="fas fa-fw fa-object-ungroup"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" v-show="interactionMode != 'modify'" @click="setInteractionMode('modify')">
                    <i class="fas fa-fw fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" v-show="interactionMode == 'modify'" @click="updateFeatures">
                    <i class="fas fa-fw fa-check"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" v-show="interactionMode == 'modify'" @click="cancelUpdateFeatures">
                    <i class="fas fa-fw fa-times"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" v-show="interactionMode != 'delete'" @click="setInteractionMode('delete')">
                    <i class="fas fa-fw fa-trash"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-success" v-show="interactionMode == 'delete'" @click="deleteFeatures">
                    <i class="fas fa-fw fa-check"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" v-show="interactionMode == 'delete'" @click="cancelDeleteFeatures">
                    <i class="fas fa-fw fa-times"></i>
                </button>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" v-show="linkPossible" @click="link(selectedFeature, selectedEntity)">
                    <i class="fas fa-fw fa-link"></i> Link to {{ selectedEntity.name }}
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary" v-show="unlinkPossible" @click="unlink(selectedFeature, linkedEntity)">
                    <i class="fas fa-fw fa-unlink"></i> <span v-if="linkedEntity">Unlink from {{ linkedEntity.name }}</span>
                </button>
            </div>
        </div>
        <div class="mt-2 col px-0">
            <div id="map" class="map w-100 h-100"></div>
            <div id="popup" :data-title="overlayTitle" :data-content="overlayContent"></div>
            <div id="hover-popup" class="tooltip"></div>
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
    import OverviewMap from 'ol/control/overviewmap';
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

    import Group from 'ol/layer/group';
    import TileLayer from 'ol/layer/tile';
    import VectorLayer from 'ol/layer/vector';

    import OSM from 'ol/source/osm';
    import Vector from 'ol/source/vector';

    import Circle from 'ol/style/circle';
    import Fill from 'ol/style/fill';
    import Stroke from 'ol/style/stroke';
    import Style from 'ol/style/style';

    import proj4 from 'proj4';

    import LayerSwitcher from 'ol-ext/control/LayerSwitcher';
    import '../../sass/ol-ext-layerswitcher.scss';

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
            epsg: {
                required: false,
                type: Object
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
            },
            selectedEntity: {
                type: Object,
                required: false
            }
        },
        mounted() {
            const vm = this;

            proj.setProj4(proj4);
            vm.initMapProjection();

            if(vm.initWkt.length && vm.initGeojson.length) {
                console.error('init-wkt and init-geojson provided. They are not allowed at once.');
                return;
            }
            // wait for DOM to be rendered
            vm.$nextTick(function() {
                vm.vector = new VectorLayer({
                    baseLayer: false,
                    displayInLayerSwitcher: true,
                    title: 'Draw Layer',
                    visible: true,
                    layer: 'draw',
                    source: new Vector({
                        wrapX: false
                    }),
                    style: vm.createStyle()
                });
                if(vm.initWkt.length) {
                    // TODO Support several layers for WKT
                    let layer = new VectorLayer({
                        baseLayer: false,
                        displayInLayerSwitcher: true,
                        title: 'All Entities',
                        visible: true,
                        layer: 'entity',
                        source: new Vector({
                            wrapX: false
                        }),
                        style: vm.createStyle()
                    });
                    vm.entityLayers.push(layer);
                    let source = layer.getSource();
                    vm.initWkt.forEach(wkt => {
                        const geom = vm.wktFormat.readGeometry(wkt, {
                            featureProjection: 'EPSG:3857'
                        });
                        source.addFeature(new Feature({geometry: geom}));
                    });
                } else if(vm.initGeojson.length) {
                    let geojsonLayers = {};
                    vm.initGeojson.forEach(geojson => {
                        let feature = vm.geoJsonFormat.readFeature(geojson.geom, {
                            featureProjection: 'EPSG:3857'
                        });
                        feature.setProperties(geojson.props);
                        if(geojson.props.color) {
                            feature.setStyle(vm.createStyle(geojson.props.color));
                        }
                        const layerId = geojson.props.layer_id;
                        if(!geojsonLayers[layerId]) {
                            geojsonLayers[layerId] = new VectorLayer({
                                baseLayer: false,
                                displayInLayerSwitcher: true,
                                title: geojson.props.layer_name,
                                visible: true,
                                layer: 'entity',
                                source: new Vector({
                                    wrapX: false
                                }),
                                style: vm.createStyle()
                            });
                        }
                        let source = geojsonLayers[layerId].getSource();
                        source.addFeature(feature);
                    });
                    for(let k in geojsonLayers) {
                        vm.entityLayers.push(geojsonLayers[k]);
                    }
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

                vm.baselayersGroup = new Group({
                    title: 'Base Layers',
                    openInLayerSwitcher: true,
                    layers: [
                        new TileLayer({
                            title: 'OpenStreetMap',
                            baseLayer: true,
                            displayInLayerSwitcher: true,
                            visible: true,
                            layer: 'osm',
                            source: new OSM({
                                wrapX: false
                            })
                        }),
                    ]
                });
                vm.overlaysGroup = new Group({
                    title: 'Overlays',
                    openInLayerSwitcher: true,
                    layers: []
                });
                vm.entityLayersGroup = new Group({
                    title: 'Entity Layers',
                    openInLayerSwitcher: true,
                    layers: vm.entityLayers
                });
                vm.extent = vm.getEntityExtent();

                vm.map = new Map({
                    controls: control.defaults().extend([
                        new FullScreen(),
                        new LayerSwitcher(),
                        new OverviewMap(),
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
                    layers: [vm.baselayersGroup, vm.overlaysGroup, vm.entityLayersGroup],
                    target: 'map',
                    view: new View({
                        center: [0, 0],
                        projection: 'EPSG:3857',
                        zoom: 2
                    })
                });
                if(vm.extent.length) {
                    vm.map.getView().fit(vm.extent);
                }

                vm.overlay = new Overlay({
                    element: document.getElementById('popup')
                });
                vm.hoverPopup = new Overlay({
                    element: document.getElementById('hover-popup'),
                    offset: [8, 0] // it's a kind of magic!
                });
                vm.map.addOverlay(vm.overlay);
                vm.map.addOverlay(vm.hoverPopup);

                vm.map.on('pointermove', function(e) {
                    if(e.dragging) return;

                    const element = vm.hoverPopup.getElement();
                    const feature = vm.getFeatureForEvent(e);
                    if(feature != vm.lastHoveredFeature) {
                        $(element).tooltip('dispose');
                        // Reset lastHoveredFeature if no feature selected
                        if(!feature) vm.lastHoveredFeature = null;
                    } else {
                        // same feature, no update needed
                        return;
                    }
                    if(feature) {
                        vm.lastHoveredFeature = feature;
                        const geometry = feature.getGeometry();
                        const props = feature.getProperties();
                        const coords = extent.getCenter(geometry.getExtent());
                        vm.hoverPopup.setPosition(coords);

                        const geomName = `Geometry #${props.id}`;
                        const title = props.entity ?
                            `${geomName} (${props.entity.name})` :
                            geomName;
                        $(element).tooltip({
                            placement: 'bottom',
                            animation: true,
                            html: true,
                            title: title
                        });
                        $(element).tooltip('show');
                    }
                });

                // Update popover position on map render (e.g. pan, zoom)
                vm.map.on('postrender', function(e) {
                    if(!vm.overlay) return;
                    const element = vm.overlay.getElement();
                    let popover = $(element).data('bs.popover');
                    if(!popover) return;
                    let popper = popover._popper;
                    if(!popper) return;
                    popper.scheduleUpdate();
                });

                vm.map.on('click', function(e) {
                    const element = vm.overlay.getElement();
                    $(element).popover('dispose');
                    vm.selectedFeature = {};
                    // if one mode is active, do not open popup
                    if(vm.draw.getActive() || vm.modify.getActive() || vm.delete.getActive()) {
                        return;
                    }
                    const feature = vm.getFeatureForEvent(e);
                    if(feature) {
                        const geometry = feature.getGeometry();
                        const props = feature.getProperties();
                        const coords = extent.getCenter(geometry.getExtent());
                        vm.overlay.setPosition(coords);

                        vm.selectedFeature = feature;
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
            initMapProjection() {
                const vm = this;

                if(!vm.epsg) {
                    return;
                }
                const name = `EPSG:${vm.epsg.epsg}`;
                proj4.defs(name, vm.epsg.proj4);
                const projection = proj.get(name);
                proj.addProjection(projection);
            },
            getEntityExtent() {
                const layers = this.entityLayersGroup.getLayers();
                let entityExtent;
                layers.forEach(l => {
                    const source = l.getSource();
                    if(source) {
                        const sourceExtent = source.getExtent();
                        if(!entityExtent) {
                            entityExtent = sourceExtent;
                        } else {
                            entityExtent = extent.extend(entityExtent, sourceExtent);
                        }
                    }
                });
                return entityExtent;
            },
            getFeatureForEvent(e) {
                const features = this.map.getFeaturesAtPixel(e.pixel, {
                    hitTolerance: 5
                });
                if(features) return features[0];
                return;
            },
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
            link(feature, entity) {
                const vm = this;
                if(!vm.linkPossible) return;
                const props = feature.getProperties();
                const gid = props.id;
                const eid = entity.id;
                vm.$http.post(`/api/map/link/${gid}/${eid}`, {}).then(function(response) {
                    feature.setProperties({
                        entity: Object.assign({}, entity)
                    });
                });
            },
            unlink(feature, entity) {
                const vm = this;
                if(!vm.unlinkPossible) return;
                const props = feature.getProperties();
                const gid = props.id;
                const eid = entity.id;
                vm.$http.delete(`/api/map/link/${gid}/${eid}`, {}).then(function(response) {
                    feature.setProperties({
                        entity: {}
                    });
                });
            },
            geometryToTable(g) {
                if(!g) return '<table class="table table-striped table-borderless table-sm"></table>';
                let coordHtml = '<table class="table table-striped table-borderless table-sm"><tbody>';
                const coords = g.getCoordinates();
                switch(g.getType()) {
                    case 'Point':
                        coordHtml += this.coordinateToTableRow(coords)
                        break;
                    case 'LineString':
                    case 'MultiPoint':
                        coords.forEach(c => {
                            coordHtml += this.coordinateToTableRow(c);
                        });
                        break;
                    case 'Polygon':
                    case 'MultiLineString':
                        coords.forEach(cg => {
                            cg.forEach(c => {
                                coordHtml += this.coordinateToTableRow(c);
                            });
                        });
                        break;
                    case 'MultiPolygon':
                        coords.forEach(cg => {
                            cg.forEach(cg2 => {
                                cg2.forEach(c => {
                                    coordHtml += this.coordinateToTableRow(c);
                                });
                            });
                        });
                        break;
                }
                coordHtml += '</tbody></table>';
                return coordHtml;
            },
            coordinateToTableRow(c) {
                if(!c[0] || !c[1]) return;
                const transCoord = proj.transform(c, 'EPSG:3857', `EPSG:${this.epsg.epsg}`);
                const row = `<tr>
                    <td class="text-left">${transCoord[0].toFixed(4)}</td>
                    <td class="text-right">${transCoord[1].toFixed(4)}</td>
                </tr>`;
                return row;
            },
            updatePopup(f) {
                const vm = this;
                if(!f.getId) return;
                const props = f.getProperties();
                const geometry = f.getGeometry();
                const geomName = `Geometry #${props.id}`;

                if(props.entity) {
                    vm.overlayTitle = `${geomName} (${props.entity.name})`;
                } else {
                    vm.overlayTitle = geomName;
                }

                const coordHtml = vm.geometryToTable(geometry);
                vm.overlayContent =
                    `<dl class="mb-0">
                        <dt>Type</dt>
                        <dd>${geometry.getType()}</dd>
                        <dt>Coordinates in EPSG:${this.epsg.epsg}</dt>
                        <dd>${coordHtml}</dd>
                    </dl>`;

                // Wait for variables to be updated
                vm.$nextTick(function() {
                    const element = vm.overlay.getElement();
                    $(element).popover({
                        'placement': 'top',
                        'animation': true,
                        'html': true,
                        'container': vm.viewport || '#map'
                    });
                    $(element).popover('show');
                });
            }
        },
        data() {
            return {
                drawType: '',
                interactionMode: '',
                map: {},
                baselayersGroup: {},
                overlaysGroup: {},
                entityLayersGroup: {},
                entityLayers: [],
                vector: {}, // TODO replace
                overlay: {},
                overlayTitle: '',
                overlayContent: '',
                hoverPopup: {},
                lastHoveredFeature: {},
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
        },
        computed: {
            viewport: function() {
                if(!this.map) return;
                const target = this.map.getTarget();
                if(!target) return;
                const container = document.getElementById(target);
                if(!container) return;
                const viewports = container.getElementsByClassName('ol-viewport');
                if(!viewports) return;
                if(!viewports.length) return;
                return viewports[0];
            },
            unlinkPossible: function() {
                const vm = this;
                if(!vm.selectedFeature.getProperties) {
                    return false;
                }
                const props = vm.selectedFeature.getProperties();
                return props.entity && !!props.entity.id;
            },
            linkPossible: function() {
                const vm = this;
                if(!vm.selectedFeature.getProperties) return false;
                const props = vm.selectedFeature.getProperties();
                if(props.entity && !!props.entity.id) return false;
                if(vm.selectedEntity.id) {
                    if(vm.selectedEntity.geodata_id) {
                        // already linked
                        return false;
                    } else {
                        return true;
                    }
                }
                return false;
            },
            linkedEntity: function() {
                const vm = this;
                if(!vm.selectedFeature) return;
                if(!vm.selectedFeature.getProperties) return;
                const props = vm.selectedFeature.getProperties();
                return props.entity;
            }
        },
        watch: {
            selectedFeature: function(newFeature, oldFeature) {
                this.updatePopup(this.selectedFeature);
            },
            selectedEntity: function(newEntity, oldEntity) {
                this.updatePopup(this.selectedFeature);
            }
        }
    }
</script>
