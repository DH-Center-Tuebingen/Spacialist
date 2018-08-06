<template>
    <div class="d-flex flex-column justify-content-between h-100">
        <div class="d-flex flex-row justify-content-between">
            <div v-if="!drawDisabled">
                <button type="button" class="btn btn-sm" :class="{'btn-primary': drawType == 'Point', 'btn-outline-primary': drawType != 'Point'}" @click="toggleDrawType('Point')">
                    <i class="fas fa-fw fa-map-marker-alt"></i>
                </button>
                <button type="button" class="btn btn-sm" :class="{'btn-primary': drawType == 'LineString', 'btn-outline-primary': drawType != 'LineString'}" @click="toggleDrawType('LineString')">
                    <i class="fas fa-fw fa-road"></i>
                </button>
                <button type="button" class="btn btn-sm" :class="{'btn-primary': drawType == 'Polygon', 'btn-outline-primary': drawType != 'Polygon'}" @click="toggleDrawType('Polygon')">
                    <i class="fas fa-fw fa-draw-polygon"></i>
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
            <div :id="id" class="map w-100 h-100"></div>
            <div :id="id+'-popup'" :data-title="overlayTitle" :data-content="overlayContent"></div>
            <div :id="id+'-hover-popup'" class="tooltip"></div>
        </div>
    </div>
</template>

<script>
    import 'ol/ol.css';
    import Collection from 'ol/Collection';
    import {defaults as defaultControls} from 'ol/control.js';
    import { getCenter as getExtentCenter, extend as extendExtent} from 'ol/extent';
    import Feature from 'ol/Feature';
    import Graticule from 'ol/Graticule';
    import { defaults as defaultInteractions } from 'ol/interaction';
    import Map from 'ol/Map';
    import Overlay from 'ol/Overlay';
    import { get as getProjection, addProjection, transform as transformProj } from 'ol/proj';
    import { register as registerProj } from 'ol/proj/proj4';
    import View from 'ol/View';

    import FullScreen from 'ol/control/FullScreen';
    import OverviewMap from 'ol/control/OverviewMap';
    import Rotate from 'ol/control/Rotate';
    import ScaleLine from 'ol/control/ScaleLine';

    import {never as neverCond, shiftKeyOnly, platformModifierKeyOnly} from 'ol/events/condition';

    import WKT from 'ol/format/WKT';
    import GeoJSON from 'ol/format/GeoJSON';

    import DragRotate from 'ol/interaction/DragRotate';
    import DragZoom from 'ol/interaction/DragZoom';
    import Draw from 'ol/interaction/Draw';
    import Modify from 'ol/interaction/Modify';
    import PinchRotate from 'ol/interaction/PinchRotate';
    import PinchZoom from 'ol/interaction/PinchZoom';
    import Select from 'ol/interaction/Select';
    import Snap from 'ol/interaction/Snap';

    import Group from 'ol/layer/Group';
    import TileLayer from 'ol/layer/Tile';
    import VectorLayer from 'ol/layer/Vector';

    import OSM from 'ol/source/OSM';
    import TileImage from 'ol/source/TileImage';
    import TileWMS from 'ol/source/TileWMS';
    import Vector from 'ol/source/Vector';

    import CircleStyle from 'ol/style/Circle';
    import Fill from 'ol/style/Fill';
    import Stroke from 'ol/style/Stroke';
    import Style from 'ol/style/Style';

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
            initCollection: {
                required: false,
                type: Object
            },
            initProjection: {
                required: false,
                type: String,
                default: 'EPSG:3857'
            },
            epsg: {
                required: false,
                type: Object
            },
            layers: {
                required: true,
                type: Object
            },
            drawDisabled: {
                required: false,
                type: Boolean,
                default: false
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
                required: false,
                default: _ => new Object()
            },
            zoomTo: {
                type: Number,
                required: false
            }
        },
        beforeMount() {
            this.initMapProjection();
            this.setDraw();
        },
        methods: {
            setDraw() {
                if(this.drawDisabled) return;
                const vm = this;

                vm.vector = new VectorLayer({
                    baseLayer: false,
                    displayInLayerSwitcher: false,
                    title: 'Draw Layer',
                    visible: true,
                    layer: 'draw',
                    source: new Vector({
                        wrapX: false
                    }),
                    style: vm.createStyle()
                });

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
                            toggleCondition: neverCond,
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
                            toggleCondition: neverCond,
                            wrapX: false
                        });
                        vm.map.addInteraction(this.select);

                        this.setEvents();
                        vm.delete.deletedFeatures = [];
                    },
                    setEvents: function() {
                        this.select.on('select', function(event) {
                            // config allows only one selected feature
                            if(event.selected.length) {
                                let feature = event.selected[0];
                                vm.delete.deletedFeatures.push(feature);
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
                            if(cancelled && vm.delete.deletedFeatures.length) {
                                // If delete was cancelled, readd deleted features
                                let source = vm.vector.getSource();
                                source.addFeatures(vm.delete.deletedFeatures);
                            }
                            vm.delete.deletedFeatures = [];
                        }
                    },
                    getDeletedFeatures: function() {
                        // return list of cloned features
                        let features = [];
                        vm.delete.deletedFeatures.forEach(feature => {
                            features.push(feature.clone());
                        });
                        return features;
                    }
                };
            },
            resetLayers() {
                this.overlays = {};
                this.baselayers = {};
            },
            initLayers() {
                for(let k in this.layers) {
                    const l = this.layers[k];
                    if(l.is_overlay) {
                        this.overlays[k] = l;
                    } else {
                        this.baselayers[k] = l;
                    }
                }
            },
            resetLayerData() {
                this.baselayerLayers = [];
                this.overlayLayers = [];
                this.entityLayers = [];
            },
            initLayerData() {
                const vm = this;

                if(vm.initWkt.length && vm.initGeojson.length) {
                    vm.$showErrorModal('init-wkt and init-geojson provided. They are not allowed at once.');
                    return;
                }

                let geojsonLayers = {};
                for(let k in vm.overlays) {
                    const l = vm.overlays[k];
                    if(!l.context_type_id && l.type != 'unlinked') {
                        vm.overlayLayers.push(vm.createNewLayer(l));
                        continue;
                    };
                    const layerId = l.id;
                    let layerName;
                    if(l.context_type_id) {
                        const ct = vm.getContextTypeById(l.context_type_id);
                        if(ct) {
                            layerName = vm.$translateConcept(ct.thesaurus_url);
                        }
                    } else {
                        layerName = 'Unlinked';
                    }
                    geojsonLayers[layerId] = new VectorLayer({
                        baseLayer: false,
                        displayInLayerSwitcher: true,
                        title: layerName,
                        visible: l.visible,
                        opacity: l.opacity,
                        layer: 'entity',
                        layer_id: l.id,
                        source: new Vector({
                            wrapX: false
                        }),
                        style: vm.createStyle(l.color)
                    });
                }
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
                            featureProjection: 'EPSG:3857',
                            dataProjection: vm.initProjection
                        });
                        source.addFeature(new Feature({geometry: geom}));
                    });
                } else if(vm.initGeojson.length) {
                    vm.initGeojson.forEach(geojson => {
                        let feature = vm.geoJsonFormat.readFeature(geojson.geom, {
                            featureProjection: 'EPSG:3857',
                            dataProjection: vm.initProjection
                        });
                        feature.setProperties(geojson.props);
                        let layer;
                        if(geojson.props.entity) {
                            layer = vm.getLayer(geojson.props.entity.context_type_id);
                        } else {
                            layer = vm.getUnlinkedLayer();
                        }
                        const layerId = layer.id;
                        let source = geojsonLayers[layerId].getSource();
                        source.addFeature(feature);
                    });
                } else if(vm.initCollection) {
                    let layer = vm.getUnlinkedLayer();
                    let source = geojsonLayers[layer.id].getSource();
                    let features = vm.geoJsonFormat.readFeatures(vm.initCollection, {
                        featureProjection: 'EPSG:3857',
                        dataProjection: vm.initProjection
                    });
                    source.addFeatures(features);
                }
                for(let k in geojsonLayers) {
                    vm.entityLayers.push(geojsonLayers[k]);
                }

                for(let k in vm.baselayers) {
                    const l = vm.baselayers[k];
                    vm.baselayerLayers.push(vm.createNewLayer(l));
                }
            },
            updateLayerGroups() {
                this.baselayersGroup.setLayers(new Collection(this.baselayerLayers));
                this.overlaysGroup.setLayers(new Collection(this.overlayLayers));
                this.entityLayersGroup.setLayers(new Collection(this.entityLayers));
                this.setExtent();
            },
            initDraw() {
                if(this.drawDisabled) return;
                this.draw.init();
                this.modify.init();
                this.delete.init();
                this.draw.setActive(false);
                this.modify.setActive(false);
                this.delete.setActive(false);

                // TODO add draw layer to map
                // this.entityLayers.push(vm.vector);

                this.snap = new Snap({
                    features: this.getSnapFeatures()
                });
                this.map.addInteraction(this.snap);

                // Event Listeners
                this.draw.Point.on('drawend', event => {
                    this.drawFeature(event.feature);
                });
                this.draw.LineString.on('drawend', event => {
                    this.drawFeature(event.feature);
                });
                this.draw.Polygon.on('drawend', event => {
                    this.drawFeature(event.feature);
                });
            },
            init() {
                const vm = this;

                vm.initLayers();

                // wait for DOM to be rendered
                vm.$nextTick(function() {
                    vm.initLayerData();

                    vm.baselayersGroup = new Group({
                        title: 'Base Layers',
                        openInLayerSwitcher: true,
                        layers: vm.baselayerLayers
                    });
                    vm.overlaysGroup = new Group({
                        title: 'Overlays',
                        openInLayerSwitcher: true,
                        layers: vm.overlayLayers
                    });
                    vm.entityLayersGroup = new Group({
                        title: 'Entity Layers',
                        openInLayerSwitcher: true,
                        layers: vm.entityLayers
                    });

                    vm.map = new Map({
                        controls: defaultControls().extend([
                            new FullScreen(),
                            new LayerSwitcher(),
                            new OverviewMap(),
                            new Rotate(),
                            new ScaleLine()
                        ]),
                        interactions: defaultInteractions().extend([
                            new DragRotate({
                                condition: platformModifierKeyOnly
                            }),
                            new DragZoom({
                                condition: shiftKeyOnly
                            }),
                            new PinchRotate(),
                            new PinchZoom(),
                        ]),
                        layers: [vm.baselayersGroup, vm.overlaysGroup, vm.entityLayersGroup],
                        target: vm.id,
                        view: new View({
                            center: [0, 0],
                            projection: 'EPSG:3857',
                            zoom: 2
                        })
                    });
                    vm.setExtent();

                    vm.overlay = new Overlay({
                        element: document.getElementById(`${vm.id}-popup`)
                    });
                    vm.hoverPopup = new Overlay({
                        element: document.getElementById(`${vm.id}-hover-popup`),
                        offset: [8, 0] // it's a kind of magic!
                    });
                    vm.map.addOverlay(vm.overlay);
                    vm.map.addOverlay(vm.hoverPopup);

                    vm.map.on('pointermove', function(e) {
                        if(e.dragging) return;
                        if(!vm.drawDisabled && vm.draw.getActive()) return;

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
                            const coords = getExtentCenter(geometry.getExtent());
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
                        if(!vm.drawDisabled && (vm.draw.getActive() || vm.modify.getActive() || vm.delete.getActive())) {
                            return;
                        }
                        const feature = vm.getFeatureForEvent(e);
                        if(feature) {
                            const geometry = feature.getGeometry();
                            const props = feature.getProperties();
                            const coords = getExtentCenter(geometry.getExtent());
                            vm.overlay.setPosition(coords);

                            vm.selectedFeature = feature;
                        } else {
                            vm.selectedFeature = {};
                        }
                    });

                    vm.initDraw();

                    vm.options.graticule = new Graticule({
                        showLabels: true,
                        strokeStyle: new Stroke({
                            color: 'rgba(0, 0, 0, 0.75)',
                            width: 2,
                            lineDash: [0.5, 4]
                        })
                    });
                    // vm.options.graticule.setMap(vm.map);
                });
            },
            initMapProjection() {
                if(!this.epsg) {
                    this.init();
                    return;
                }
                const name = `EPSG:${this.epsg.epsg}`;
                proj4.defs(name, this.epsg.proj4);
                registerProj(proj4);
                const projection = getProjection(name);
                addProjection(projection);
                if(this.initProjection != name && this.initProjection != 'EPSG:4326' && this.initProjection != 'EPSG:3857') {
                    const srid = this.initProjection.split(':')[1];
                    $http.get(`map/epsg/${srid}`).then(response => {
                        proj4.defs(this.initProjection, response.data.proj4text);
                        registerProj(proj4);
                        const projection = getProjection(this.initProjection);
                        addProjection(projection);

                        this.init();
                    });
                } else {
                    this.init();
                }
            },
            createNewLayer(l) {
                let source;
                switch(l.type) {
                    case 'xyz':
                        source = new TileImage({
                            url: l.url,
                            attributions: l.attribution,
                            wrapX: false
                        });
                        break;
                    case 'wms':
                        source = new TileWMS({
                            url: l.url,
                            attributions: l.attribution,
                            wrapX: false,
                            serverType: 'geoserver',
                            params: {
                                layers: l.layers,
                                tiled: true
                            }
                        });
                        break;
                    default:
                        source = new OSM({
                            wrapX: false
                        });
                        break;
                }
                return new TileLayer({
                    title: l.name,
                    baseLayer: !l.is_overlay,
                    displayInLayerSwitcher: true,
                    visible: l.visible,
                    opacity: l.opacity,
                    layer: 'osm',
                    source: source
                });
            },
            getLayer(ctid) {
                for(let k in this.layers) {
                    if(this.layers[k].context_type_id == ctid) {
                        return this.layers[k];
                    }
                }
                return;
            },
            getUnlinkedLayer() {
                for(let k in this.layers) {
                    if(this.layers[k].type == 'unlinked') {
                        return this.layers[k];
                    }
                }
                return;
            },
            getContextType(context) {
                if(!context) return;
                return this.getContextTypeById(context.context_type_id);
            },
            getContextTypeById(ctid) {
                return this.$getEntityType(ctid);
            },
            getSnapFeatures() {
                const vm = this;
                const layers = vm.entityLayersGroup.getLayers();
                let features = [];
                layers.forEach(lg => {
                    const src = lg.getSource();
                    if(src) {
                        features = features.concat(src.getFeatures());
                    }
                });
                return new Collection(features);
            },
            setExtent(extent = null) {
                if(!extent) extent = this.getEntityExtent();
                if(!extent) {
                    extent = this.defaultExtent;
                } else {
                    for(let i=0; i<extent.length; i++) {
                        if(extent[i] == Infinity || extent[i] == -Infinity) {
                            extent[i] = this.defaultExtent[i];
                        }
                    }
                }
                this.extent = extent;
                this.map.getView().fit(this.extent);
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
                            entityExtent = extendExtent(entityExtent, sourceExtent);
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
                    image: new CircleStyle({
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
                this.snap.addFeature(feature);
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
                const vm = this;
                const features = vm.delete.getDeletedFeatures();
                let wktFeatures = features.map(f => {
                    vm.snap.removeFeature(f);
                    vm.wktFormat.writeFeature(f);
                });
                vm.onDeleteend(features, wktFeatures);
                vm.setInteractionMode('');
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
                vm.$http.post(`/map/link/${gid}/${eid}`, {}).then(function(response) {
                    feature.setProperties({
                        entity: Object.assign({}, entity)
                    });
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            unlink(feature, entity) {
                const vm = this;
                if(!vm.unlinkPossible) return;
                const props = feature.getProperties();
                const gid = props.id;
                const eid = entity.id;
                vm.$http.delete(`/map/link/${gid}/${eid}`, {}).then(function(response) {
                    feature.setProperties({
                        entity: {}
                    });
                }).catch(function(error) {
                    vm.$throwError(error);
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
                const transCoord = transformProj(c, 'EPSG:3857', `EPSG:${this.epsg.epsg}`);
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
                id: `map-${Date.now()}`,
                drawType: '',
                interactionMode: '',
                map: {},
                baselayers: {},
                overlays: {},
                overlayLayers: [],
                baselayerLayers: [],
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
                geoJsonFormat: new GeoJSON(),
                // EPSG:3857 bounds (taken from epsg.io/3857)
                defaultExtent: [-20026376.39, -20048966.10, 20026376.39, 20048966.10]
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
            },
            layers: function(newLayers, oldLayers) {
                this.resetLayers();
                this.initLayers();
            },
            initWkt: function(newWkt, oldWkt) {
                this.resetLayerData();
                this.initLayerData();
                this.updateLayerGroups();
            },
            initGeojson: function(newGeojson, oldGeojson) {
                this.resetLayerData();
                this.initLayerData();
                this.updateLayerGroups();
            },
            initCollection: function(newCollection, oldCollection) {
                this.resetLayerData();
                this.initLayerData();
                this.updateLayerGroups();
            },
            zoomTo: function(newZoomLayerId, oldZoomLayerId) {
                if(!newZoomLayerId) return;
                const layers = this.entityLayersGroup.getLayers().getArray();
                const zoomLayer = layers.find(l => l.get('layer_id') == newZoomLayerId);
                if(!zoomLayer) return;
                const zoomLayerSource = zoomLayer.getSource();
                if(!zoomLayerSource) return;
                this.setExtent(zoomLayerSource.getExtent());
            }
        }
    }
</script>
