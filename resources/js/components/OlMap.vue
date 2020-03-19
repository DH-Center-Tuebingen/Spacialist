<template>
    <div class="h-100">
        <div class="h-100 px-0">
            <div :id="id" class="map w-100 h-100">
                <div class="d-flex flex-column ol-bar ol-right ol-bottom">
                    <div v-if="!drawDisabled" class="d-flex flex-column align-items-end">
                        <button type="button" class="btn btn-sm p-1" :class="{'btn-primary': drawType == 'Point', 'btn-outline-primary': drawType != 'Point'}" data-toggle="popover" :data-content="$t('main.map.draw.point.desc')" data-trigger="hover" data-placement="bottom" @click="toggleDrawType('Point')">
                            <i class="fas fa-fw fa-map-marker-alt"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-1" :class="{'btn-primary': drawType == 'LineString', 'btn-outline-primary': drawType != 'LineString'}" data-toggle="popover" :data-content="$t('main.map.draw.linestring.desc')" data-trigger="hover" data-placement="bottom" @click="toggleDrawType('LineString')">
                            <i class="fas fa-fw fa-road"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-1" :class="{'btn-primary': drawType == 'Polygon', 'btn-outline-primary': drawType != 'Polygon'}" data-toggle="popover" :data-content="$t('main.map.draw.polygon.desc')" data-trigger="hover" data-placement="bottom" @click="toggleDrawType('Polygon')">
                            <i class="fas fa-fw fa-draw-polygon"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-1 btn-outline-info" v-show="interactionMode != 'modify'" data-toggle="popover" :data-content="$t('main.map.draw.modify.desc')" data-trigger="hover" data-placement="bottom" @click="setInteractionMode('modify')">
                            <i class="fas fa-fw fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-1 btn-outline-success" v-show="interactionMode == 'modify'" data-toggle="popover" :data-content="$t('main.map.draw.modify.pos-desc')" data-trigger="hover" data-placement="bottom" @click="updateFeatures">
                            <i class="fas fa-fw fa-check"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-1 btn-outline-danger" v-show="interactionMode == 'modify'" data-toggle="popover" :data-content="$t('main.map.draw.modify.neg-desc')" data-trigger="hover" data-placement="bottom" @click="cancelUpdateFeatures">
                            <i class="fas fa-fw fa-times"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-1 btn-outline-danger" v-show="interactionMode != 'delete'" data-toggle="popover" :data-content="$t('main.map.draw.delete.desc')" data-trigger="hover" data-placement="bottom" @click="setInteractionMode('delete')">
                            <i class="fas fa-fw fa-trash"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-1 btn-outline-success" v-show="interactionMode == 'delete'" data-toggle="popover" :data-content="$t('main.map.draw.delete.pos-desc')" data-trigger="hover" data-placement="bottom" @click="deleteFeatures">
                            <i class="fas fa-fw fa-check"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-1 btn-outline-danger" v-show="interactionMode == 'delete'" data-toggle="popover" :data-content="$t('main.map.draw.delete.neg-desc')" data-trigger="hover" data-placement="bottom" @click="cancelDeleteFeatures">
                            <i class="fas fa-fw fa-times"></i>
                        </button>
                        <button type="button" class="btn btn-sm p-1 btn-outline-primary" data-toggle="popover" :data-content="$t('main.map.draw.measure.desc')" data-trigger="hover" data-placement="bottom" @click="toggleMeasurements">
                            <i class="fas fa-fw fa-ruler-combined"></i>
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm p-1 btn-outline-primary" v-show="linkPossible" @click="link(selectedFeature, selectedEntity)">
                            <i class="fas fa-fw fa-link"></i> {{ $t('global.link-to', {name: selectedEntity.name}) }}
                        </button>
                        <button type="button" class="btn btn-sm p-1 btn-outline-primary" v-show="unlinkPossible" @click="unlink(selectedFeature, linkedEntity)">
                            <i class="fas fa-fw fa-unlink"></i> <span v-if="linkedEntity">
                                {{ $t('global.unlink-from', {name: linkedEntity.name}) }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            <div :id="id+'-popup'" class="popup popover ol-popover bs-popover-top">
                <h4 class="popover-header">
                    <span class="font-weight-medium">
                        {{ overlayInfo.name }}
                    </span>
                    <span v-if="overlayInfo.subname">
                        ({{ overlayInfo.subname }})
                    </span>
                </h4>
                <div class="popover-body">
                    <dl class="mb-0">
                        <dt>
                            {{ $t('global.type') }}
                        </dt>
                        <dd>
                            {{ overlayInfo.type }}
                        </dd>
                        <template v-if="isPolygonOverlay || isLineOverlay">
                            <dt>
                                <span v-if="isLineOverlay">
                                    {{ $t('main.map.length') }}
                                </span>
                                <span v-else-if="isPolygonOverlay">
                                    {{ $t('main.map.area') }}
                                </span>
                            </dt>
                            <dd>
                                {{ overlayInfo.size }}
                            </dd>
                        </template>
                        <dt class="clickable" @click="overlayInfo.showCoordinates = !overlayInfo.showCoordinates">
                            {{
                                $t('main.map.coords-in-epsg', {
                                    epsg: epsg.epsg
                                })
                            }}
                            <span class="font-weight-normal">
                                ({{ coordinateList.length }})
                            </span>
                            <span v-show="overlayInfo.showCoordinates">
                                <i class="fas fa-fw fa-caret-up"></i>
                            </span>
                            <span v-show="!overlayInfo.showCoordinates">
                                <i class="fas fa-fw fa-caret-down"></i>
                            </span>
                        </dt>
                        <dd class="mb-0 mh-300p scroll-y-auto">
                            <div v-if="overlayInfo.showCoordinates">
                                <table class="table table-striped table-borderless table-sm mb-0">
                                    <tbody>
                                        <tr v-for="c in coordinateList">
                                            <td class="text-left">
                                                <input type="number" class="form-control form-control-sm" step="0.000001" v-model.number="overlayInfo.editCoordinates[0]" v-if="overlayInfo.pointEditEnabled" />
                                                <span v-else>
                                                    {{ c.x | toFixed(4) }}
                                                </span>
                                            </td>
                                            <td class="text-right">
                                                <input type="number" class="form-control form-control-sm" step="0.000001" v-model.number="overlayInfo.editCoordinates[1]" v-if="overlayInfo.pointEditEnabled" />
                                                <span v-else>
                                                    {{ c.y | toFixed(4) }}
                                                </span>
                                            </td>
                                            <td class="text-right clickable" v-if="isPointOverlay">
                                                <div v-show="overlayInfo.pointEditEnabled">
                                                    <a href="" @click.prevent="confirmPointCoordEdit">
                                                        <i class="fas fa-fw fa-check" ></i>
                                                    </a>
                                                    <a href="" @click.prevent="cancelPointCoordEdit">
                                                        <i class="fas fa-fw fa-times" ></i>
                                                    </a>
                                                </div>
                                                <a href="" @click.prevent="enablePointCoordEdit" v-show="!overlayInfo.pointEditEnabled">
                                                    <i class="fas fa-fw fa-edit" ></i>
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </dd>
                    </dl>
                </div>
                <div class="arrow ol-arrow"></div>
            </div>
            <div :id="id+'-hover-popup'" class="tooltip"></div>
            <div :id="id+'-measure-popup'" class="tooltip tooltip-measure"></div>
        </div>
    </div>
</template>

<script>
    import { EventBus } from '../event-bus.js';

    import 'ol/ol.css';
    import Collection from 'ol/Collection';
    import {defaults as defaultControls} from 'ol/control.js';
    import { getCenter as getExtentCenter, extend as extendExtent} from 'ol/extent';
    import Feature from 'ol/Feature';
    import Graticule from 'ol/layer/Graticule';
    import { defaults as defaultInteractions } from 'ol/interaction';
    import Map from 'ol/Map';
    import {unByKey} from 'ol/Observable.js';
    import Overlay from 'ol/Overlay';
    import { get as getProjection, addProjection, transform as transformProj } from 'ol/proj';
    import { register as registerProj } from 'ol/proj/proj4';
    import {getArea, getLength} from 'ol/sphere.js';
    import View from 'ol/View';

    import FullScreen from 'ol/control/FullScreen';
    import OverviewMap from 'ol/control/OverviewMap';
    import Rotate from 'ol/control/Rotate';
    import ScaleLine from 'ol/control/ScaleLine';

    import {never as neverCond, shiftKeyOnly, platformModifierKeyOnly} from 'ol/events/condition';

    import WKT from 'ol/format/WKT';
    import GeoJSON from 'ol/format/GeoJSON';

    import Point from 'ol/geom/Point';

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

    import BingMaps from 'ol/source/BingMaps';
    import OSM from 'ol/source/OSM';
    import TileImage from 'ol/source/TileImage';
    import TileWMS from 'ol/source/TileWMS';
    import Vector from 'ol/source/Vector';

    import CircleStyle from 'ol/style/Circle';
    import Fill from 'ol/style/Fill';
    import Stroke from 'ol/style/Stroke';
    import Style from 'ol/style/Style';
    import Text from 'ol/style/Text';

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
            layerStyles: {
                required: false,
                type: Object
            },
            layerLabels: {
                required: false,
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
            // Enable popovers
            $(function () {
                $('[data-toggle="popover"]').popover();
            });
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
                    modifiedFeatures: {},
                    modifyActive: {},
                    originalFeatures: [],
                    selectedFeature: undefined,
                    select: {},
                    modify: {},
                    changeState: '',
                    init: function() {
                        vm.modify.select = new Select({
                            hitTolerance: 5,
                            toggleCondition: neverCond,
                            wrapX: false
                        });
                        vm.map.addInteraction(vm.modify.select);

                        vm.modify.modify = new Modify({
                            features: vm.modify.select.getFeatures(),
                            wrapX: false
                        });
                        vm.map.addInteraction(vm.modify.modify);

                        vm.modify.modifiedFeatures = {};
                        vm.modify.setEvents();
                        vm.modify.modifyActive = false;
                        vm.modify.originalFeatures = {};
                        vm.modify.selectedFeature = undefined;
                    },
                    setEvents: function() {
                        vm.modify.select.on('change:active', function(event) {
                        });
                        vm.modify.select.on('select', function(event) {
                            // config allows only one selected feature
                            if(event.selected.length) {
                                const f = event.selected[0];
                                vm.modify.modifiedFeatures[f.ol_uid] = f;
                                vm.modify.selectedFeature = f;
                                let layer;
                                const ent = f.get('entity');
                                if(ent) {
                                    layer = vm.getLayer(ent.entity_type_id)
                                } else {
                                    layer = vm.getUnlinkedLayer();
                                }
                                if(!vm.modify.originalFeatures[f.ol_uid]) {
                                    vm.modify.originalFeatures[f.ol_uid] = {
                                        feature: f.clone(),
                                        layer: layer.id
                                    };
                                }
                            } else {
                                vm.modify.selectedFeature = undefined;
                            }
                        });
                        vm.modify.modify.on('change:active', function(event) {
                            vm.modify.modifyActive = !event.oldValue;
                            if(!vm.modify.modifyActive && vm.modify.changeState == 'cancel') {
                                for(let k in vm.modify.modifiedFeatures) {
                                    const org = vm.modify.originalFeatures[k];
                                    vm.modify.modifiedFeatures[k].setGeometry(org.feature.getGeometry());
                                }
                            }
                            vm.modify.changeState = '';
                            vm.modify.modifiedFeatures = {};
                            vm.modify.originalFeatures = {};
                            vm.modify.selectedFeature = undefined;
                        });
                        vm.modify.modify.on('modifyend', function(event) {
                        });
                    },
                    getActive: function() {
                        return vm.modify.select.getActive() || vm.modify.modify.getActive();
                    },
                    setActive: function(active, cancelled) {
                        vm.modify.select.setActive(active);
                        vm.modify.modify.setActive(active);
                        if(!active) {
                            if(cancelled && vm.modify.originalFeatures.length) {
                                // If modify was cancelled, reset features to state before modify start
                                let source = vm.vector.getSource();
                                source.clear();
                                source.addFeatures(vm.modify.originalFeatures);
                            }
                            vm.modify.originalFeatures = [];
                        }
                    },
                    getModifiedFeatures: function() {
                        // return list of cloned features
                        let features = [];
                        for(let k in vm.modify.modifiedFeatures) {
                            features.push(vm.modify.modifiedFeatures[k].clone());
                        }
                        return features;
                    },
                    setChangeState(state) {
                        vm.modify.changeState = state;
                    }
                };
                vm.delete = {
                    deletedFeatures: {},
                    init: function() {
                        vm.delete.select = new Select({
                            hitTolerance: 5,
                            toggleCondition: neverCond,
                            wrapX: false
                        });
                        vm.map.addInteraction(vm.delete.select);

                        vm.delete.setEvents();
                        vm.delete.deletedFeatures = {};
                    },
                    setEvents: function() {
                        vm.delete.select.on('select', function(event) {
                            // config allows only one selected feature
                            if(event.selected.length) {
                                const f = event.selected[0];
                                let olLayer = vm.delete.select.getLayer(f);
                                vm.delete.deletedFeatures[f.ol_uid] = {
                                    feature: f.clone(),
                                    layer: olLayer.get('layer_id')
                                };
                                olLayer.getSource().removeFeature(f);
                                vm.delete.select.getFeatures().clear();
                            }
                        });
                    },
                    getActive: function() {
                        return vm.delete.select.getActive();
                    },
                    setActive: function(active, cancelled) {
                        vm.delete.select.setActive(active);
                        if(!active) {
                            if(cancelled) {
                                // If delete was cancelled, readd deleted features
                                for(let k in vm.delete.deletedFeatures) {
                                    const org = vm.delete.deletedFeatures[k];
                                    let layer = vm.getEntityLayerById(org.layer);
                                    layer.getSource().addFeature(org.feature);
                                }
                            }
                            vm.delete.deletedFeatures = {};
                        }
                    },
                    getDeletedFeatures: function() {
                        // return list of cloned features
                        let features = [];
                        for(let k in vm.delete.deletedFeatures) {
                            features.push(vm.delete.deletedFeatures[k].feature);
                        }
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
                    vm.$showErrorModal(vm.$t('main.map.init-error'));
                    return;
                }

                let geojsonLayers = {};
                for(let k in vm.overlays) {
                    const l = vm.overlays[k];
                    if(!l.entity_type_id && l.type != 'unlinked') {
                        vm.overlayLayers.push(vm.createNewLayer(l));
                        continue;
                    };
                    const layerId = l.id;
                    let layerName;
                    if(l.entity_type_id) {
                        const ct = vm.getEntityTypeById(l.entity_type_id);
                        if(ct) {
                            layerName = vm.$translateConcept(ct.thesaurus_url);
                        }
                    } else {
                        layerName = vm.$t('global.unlinked');
                    }
                    geojsonLayers[layerId] = new VectorLayer({
                        baseLayer: false,
                        displayInLayerSwitcher: true,
                        title: layerName,
                        visible: l.visible,
                        opacity: parseFloat(l.opacity),
                        color: l.color,
                        layer: 'entity',
                        layer_id: l.id,
                        source: new Vector({
                            wrapX: false
                        })
                    });
                }
                if(vm.initWkt.length) {
                    // TODO Support several layers for WKT
                    let layer = new VectorLayer({
                        baseLayer: false,
                        displayInLayerSwitcher: true,
                        title: vm.$t('global.all-entities'),
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
                            layer = vm.getLayer(geojson.props.entity.entity_type_id);
                        } else {
                            layer = vm.getUnlinkedLayer();
                        }
                        const layerId = layer.id;
                        const color = geojsonLayers[layerId].getProperties().color;
                        let source = geojsonLayers[layerId].getSource();
                        const defaultStyle = vm.createStyle(color);
                        vm.featureStyles[geojson.props.id] = {
                            default: defaultStyle,
                            label: null,
                            style: null
                        };
                        feature.setStyle(vm.featureStyles[geojson.props.id].default);
                        source.addFeature(feature);
                        vm.features[geojson.props.id] = feature;
                    });
                } else if(vm.initCollection) {
                    let layer = vm.getUnlinkedLayer();
                    const color = geojsonLayers[layer.id].getProperties().color;
                    let source = geojsonLayers[layer.id].getSource();
                    let features = vm.geoJsonFormat.readFeatures(vm.initCollection, {
                        featureProjection: 'EPSG:3857',
                        dataProjection: vm.initProjection
                    });
                    features.forEach(f => {
                        f.setStyle(vm.createStyle(color));
                    })
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

                this.initMeasureInteraction();
                if(this.measurementActive) {
                    this.addMeasureInteraction();
                }

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
                        title: vm.$tc('main.map.baselayer', 2),
                        openInLayerSwitcher: true,
                        layers: vm.baselayerLayers
                    });
                    vm.overlaysGroup = new Group({
                        title: vm.$tc('main.map.overlay', 2),
                        openInLayerSwitcher: true,
                        layers: vm.overlayLayers
                    });
                    vm.entityLayersGroup = new Group({
                        title: vm.$t('main.map.entity-layers'),
                        openInLayerSwitcher: true,
                        layers: vm.entityLayers
                    });

                    vm.overlay = new Overlay({
                        element: document.getElementById(`${vm.id}-popup`),
                        positioning: 'bottom-center',
                        autoPan: true,
                        autoPanAnimation: {
                            duration: 250
                        }
                    });
                    vm.hoverPopup = new Overlay({
                        element: document.getElementById(`${vm.id}-hover-popup`),
                        offset: [2, 5]
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
                            zoom: 2,
                            extent: vm.defaultExtent
                        }),
                        overlays: [
                            vm.overlay,
                            vm.hoverPopup
                        ]
                    });
                    vm.setExtent();

                    vm.map.on('pointermove', function(e) {
                        if(e.dragging) return;
                        if(
                            !vm.drawDisabled &&
                            (
                                vm.draw.getActive() ||
                                vm.modify.getActive() ||
                                vm.delete.getActive()
                            )
                        ) return;
                        if(vm.measurementActive) return;

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

                            const geomName = vm.$t('main.map.geometry-name', {id: props.id});
                            const title = props.entity ?
                                `${props.entity.name} (${geomName})` :
                                geomName;
                            $(element).tooltip({
                                container: vm.viewport || '#map',
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
                        if(!vm.hoverPopup) return;
                        const element = vm.hoverPopup.getElement();
                        let popover = $(element).data('bs.tooltip');
                        if(!popover) return;
                        let popper = popover._popper;
                        if(!popper) return;
                        popper.scheduleUpdate();
                    });

                    vm.map.on('singleclick', function(e) {
                        if(!vm.drawDisabled && (vm.draw.getActive() || vm.modify.getActive() || vm.delete.getActive())) {
                            return;
                        }
                        if(vm.measurementActive) return;

                        const feature = vm.getFeatureForEvent(e);
                        if(feature) {
                            vm.selectedFeature = feature;
                            vm.$emit('feature-selected', {
                                feature: feature,
                                properties: feature.getProperties()
                            });
                        } else {
                            vm.selectedFeature = {};
                        }
                    });

                    // Set map render state as soon as map is rendered
                    vm.map.once('rendercomplete', e => {
                        vm.mapRendered = true;
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
                    if (vm.selectedEntity && vm.selectedEntity.geodata_id) {
                        vm.selectedFeature = vm.features[vm.selectedEntity.geodata_id];
                    }
                    vm.onMapReady(_ => {
                        vm.updatePopup(vm.selectedFeature);
                    });
                });
                if(EventBus) {
                    EventBus.$on('entity-update', this.handleEntityUpdate);
                    EventBus.$on('entity-deleted', this.handleEntityDelete);
                }
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
                    $httpQueue.add(() => $http.get(`map/epsg/${srid}`).then(response => {
                        proj4.defs(this.initProjection, response.data.proj4text);
                        registerProj(proj4);
                        const projection = getProjection(this.initProjection);
                        addProjection(projection);

                        this.init();
                    }));
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
                    case 'bing':
                        source = new BingMaps({
                            key: l.api_key,
                            /*
                            # Supported
                            - Aerial
                            - AerialWithLabels (Deprecated)
                            - AerialWithLabelsOnDemand
                            - CanvasDark
                            - CanvasLight
                            - CanvasGray
                            - RoadOnDemand
                            # Not Supported?
                            - Birdseye
                            - BirdseyeWithLabels
                            - BirdseyeV2
                            - BirdseyeV2WithLabels
                            - Streetside
                             */
                            imagerySet: l.layer_type,
                            wrapX: false,
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
                    opacity: parseFloat(l.opacity),
                    layer: 'osm',
                    source: source
                });
            },
            getEntityLayers() {
                return this.entityLayersGroup.getLayers().getArray();
            },
            getEntityLayerFeatures(id) {
                let features = [];
                let layers;
                if(!id) {
                    layers = this.getEntityLayers();
                } else {
                    layers = [this.getEntityLayerById(id)];
                }
                layers.forEach(l => {
                    const src = l.getSource();
                    features = features.concat(src.getFeatures());
                });
                return features;
            },
            getEntityLayerById(id) {
                const layers = this.entityLayersGroup.getLayers().getArray();
                return layers.find(l => l.get('layer_id') == id);
            },
            getLayer(ctid) {
                for(let k in this.layers) {
                    if(this.layers[k].entity_type_id == ctid) {
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
            getEntityType(entity) {
                if(!entity) return;
                return this.getEntityTypeById(entity.entity_type_id);
            },
            getEntityTypeById(ctid) {
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
            updateStyles(feature) {
                const id = feature.getProperties().id;
                let styles = this.featureStyles[id];
                let appliedStyles = [];
                if(styles.style) {
                    appliedStyles.push(styles.style);
                } else {
                    appliedStyles.push(styles.default);
                }
                if(styles.label) {
                    appliedStyles.push(styles.label);
                }
                feature.setStyle(appliedStyles)
            },
            parseAndApplyStyle(options, feature) {
                let style = {};
                let text = {};
                let r, g, b, a;
                if(options.font) {
                    const fontSize = options.font.size || 11;
                    let fontTrans;
                    let fontStyle, fontWeight;
                    switch(options.font.transform.id) {
                        case 'capitalize':
                            fontTrans = 'small-caps';
                            break;
                        default:
                            fontTrans = '';
                            break;
                    }
                    switch(options.font.style.id) {
                        case 'bold':
                            fontStyle = '';
                            fontWeight = 'bold';
                            break;
                        case 'italic':
                            fontStyle = 'italic';
                            fontWeight = '';
                            break;
                        case 'oblique':
                            fontStyle = 'oblique';
                            fontWeight = '';
                            break;
                        case 'bold-italic':
                            fontStyle = 'italic';
                            fontWeight = 'bold';
                            break;
                        case 'bold-oblique':
                            fontStyle = 'oblique';
                            fontWeight = 'bold';
                            break;
                        default:
                            fontStyle = '';
                            fontWeight = '';
                            break;
                    }
                    text.font = `${fontStyle} ${fontTrans} ${fontWeight} ${fontSize}px sans-serif`;
                    [r, g, b] = this.$rgb2hex(options.font.color);
                    a = 1 - options.font.transparency;
                    text.fill = new Fill({
                        color: `rgba(${r}, ${g}, ${b}, ${a})`
                    });
                }
                if(options.buffer) {
                    [r, g, b] = this.$rgb2hex(options.buffer.color);
                    a = 1 - options.buffer.transparency;
                    text.stroke = new Stroke({
                        color: `rgba(${r}, ${g}, ${b}, ${a})`,
                        width: options.buffer.size
                    });
                }
                if(options.background) {
                    [r, g, b] = this.$rgb2hex(options.background.colors.fill);
                    a = 1 - options.background.transparency;
                    text.backgroundFill = new Fill({
                        color: `rgba(${r}, ${g}, ${b}, ${a})`
                    });
                    [r, g, b] = this.$rgb2hex(options.background.colors.border);
                    text.backgroundStroke = new Stroke({
                        color: `rgba(${r}, ${g}, ${b}, ${a})`,
                        width: options.background.borderSize
                    });
                    let x = options.background.sizes.x || 0;
                    let y = options.background.sizes.y || 0;
                    text.padding = [y, x, y, x];
                }
                if(options.position) {
                    text.offsetX = options.position.offsets.x || 0;
                    text.offsetY = options.position.offsets.y || 0;
                    let align;
                    let baseline;
                    switch(options.position.placement.id) {
                        case 'top':
                            align = 'center';
                            baseline = 'bottom';
                            break;
                        case 'right':
                            align = 'right';
                            baseline = 'middle';
                            break;
                        case 'bottom':
                            align = 'center';
                            baseline = 'top';
                            break;
                        case 'left':
                            align = 'left';
                            baseline = 'middle';
                            break;
                        case 'center':
                            align = 'center';
                            baseline = 'bottom';
                            break;
                        case 'top-right':
                            align = 'right';
                            baseline = 'bottom';
                            break;
                        case 'top-left':
                            align = 'left';
                            baseline = 'bottom';
                            break;
                        case 'bottom-right':
                            align = 'right';
                            baseline = 'top';
                            break;
                        case 'bottom-left':
                            align = 'left';
                            baseline = 'top';
                            break;
                        default:
                            align = 'center';
                            baseline = 'middle';
                            break;
                    }
                    text.textAlign = align;
                    text.textBaseline = baseline;
                }
                const featureText = options.getText(feature);
                text.text = featureText.toString();
                style.text = new Text(text);
                const id = feature.getProperties().id;
                this.featureStyles[id].label = new Style(style);
                this.updateStyles(feature);
            },
            applyLabels() {
                // Reset all label styles
                const allLayers = this.getEntityLayers();
                allLayers.forEach(l => {
                    const layerId = l.get('layer_id');
                    if(!this.layerLabels[layerId]) {
                        let features = l.getSource().getFeatures();
                        features.forEach(f => {
                            const id = f.getProperties().id;
                            let styles = this.featureStyles[id];
                            styles.label = null;
                            this.updateStyles(f);
                        });
                    }
                });
                for(let i in this.layerLabels) {
                    let features = this.getEntityLayerFeatures(i);
                    features.forEach(f => {
                        this.parseAndApplyStyle(this.layerLabels[i], f);
                    });
                }
            },
            applyStyles() {
                // Reset all style styles
                const allLayers = this.getEntityLayers();
                allLayers.forEach(l => {
                    const layerId = l.get('layer_id');
                    if(!this.layerStyles[layerId]) {
                        let features = l.getSource().getFeatures();
                        features.forEach(f => {
                            const id = f.getProperties().id;
                            let styles = this.featureStyles[id];
                            styles.style = null;
                            this.updateStyles(f);
                        });
                    }
                });
                for(let i in this.layerStyles) {
                    const opts = this.layerStyles[i];
                    let features = this.getEntityLayerFeatures(i);
                    let equalBucketSize = 0;
                    let buckets = {};
                    let categories = 0;
                    let index = 0;
                    let nullValueFound = false;
                    if(!features.length) continue;

                    if(opts.style.id == 'color') {let tr, tg, tb;
                        let r, g, b;
                        [r, g, b] = this.$rgb2hex(opts.color.to);
                        const color = { r: r, g: g, b: b };
                        features.forEach(f => {
                            this.applyStyle(f, color, opts);
                        });
                        continue;
                    }

                    const ctid = features[0].getProperties().entity.entity_type_id;
                    $httpQueue.add(() => $http.get(`entity/entity_type/${ctid}/data/${opts.attribute_id}`).then(response => {
                        const data = response.data;
                        let values = [];
                        features.forEach(f => {
                            const eid = f.getProperties().entity.id;
                            const value = data[eid] ? data[eid].value : null;
                            if(!value && value !== 0 && !nullValueFound) {
                                nullValueFound = true;
                                categories++;
                            } else {
                                if(opts.style.id == 'categorized') {
                                    if(!buckets[value] && buckets[value] !== 0) {
                                        buckets[value] = index;
                                        index++;
                                        categories++;
                                    }
                                }
                            }
                            values.push({
                                value: value,
                                feature: f
                            });
                        });

                        if(opts.style.id == 'categorized') {
                            values.sort((a, b) => a.value < b.value);
                        } else if(opts.style.id == 'graduated') {
                            categories = opts.classes;
                            values.sort((a, b)  => a.value-b.value);
                            const min = values[0].value;
                            const max = values[values.length-1].value;
                            if(opts.mode.id == 'equal_interval') {
                                const bucketSize = (max-min)/categories;
                                buckets = [];
                                for(let i=1; i<=categories; i++) {
                                    buckets.push(min + bucketSize*i);
                                }
                            }
                        }
                        let fr, fg, fb;
                        let tr, tg, tb;
                        [fr, fg, fb] = this.$rgb2hex(opts.color.from);
                        [tr, tg, tb] = this.$rgb2hex(opts.color.to);
                        const from = { r: fr, g: fg, b: fb };
                        const to = { r: tr, g: tg, b: tb };
                        const gradients = this.getGradients(from, to, categories);
                        let currentGradient;
                        let overallBucketCount = 0;
                        let currentBucket = 0;
                        let currentBucketCount = 0;
                        let currentBucketSize =  Math.floor(
                            (currentBucket+1)*(1/categories) * values.length
                        );
                        values.forEach(v => {
                            if(opts.style.id == 'categorized') {
                                if(!v.value && v.value !== 0) {
                                    currentGradient = gradients[gradients.length-1];
                                } else {
                                    currentGradient = gradients[buckets[v.value]];
                                }
                            } else if(opts.style.id == 'graduated') {
                                if(opts.mode.id == 'equal_interval') {
                                    for(let i=0; i<buckets.length; i++) {
                                        if(v.value < buckets[i]) {
                                            currentGradient = gradients[i];
                                            break;
                                        }
                                    }
                                } else if(opts.mode.id == 'quantile') {
                                    if(currentBucketCount == currentBucketSize && currentBucket < gradients.length-1) {
                                        currentBucketCount = 0;
                                        currentBucket++;
                                        currentBucketSize = Math.floor(
                                            (currentBucket+1)*(1/categories) * values.length
                                        ) - overallBucketCount;
                                    }
                                    currentGradient = gradients[currentBucket];
                                    currentBucketCount++;
                                    overallBucketCount++;
                                }
                            }
                            this.applyStyle(v.feature, currentGradient, opts);
                        });
                    }));
                }
            },
            applyStyle(feature, color, styleOptions) {
                const rgb = `rgba(${color.r}, ${color.g}, ${color.b}, ${1-styleOptions.transparency})`;
                const id = feature.getProperties().id;
                this.featureStyles[id].style = this.createStyle(rgb, styleOptions.size);
                this.updateStyles(feature);
            },
            getGradients(from, to, classes) {
                const stepWeight = classes > 1 ? 1/(classes-1) : 0;
                let colors = [];
                for(let i=0; i<classes; i++) {
                    let current = {...from};
                    for(let k of ['r', 'g', 'b']) {
                        current[k] = Math.round(current[k] + ( (stepWeight*i) * (to[k] - from[k]) ) );
                    }
                    colors.push(current);
                }
                return colors;
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
                this.map.getView().fit(this.extent, {
                    maxZoom: 19 // set max zoom on fit to max osm zoom level
                });
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
            // if map ready, exec callback (cb)
            // otherwise wait for rendercomplete event to call cb
            onMapReady(cb) {
                if(this.mapRendered) {
                    cb();
                } else {
                    this.map.once('rendercomplete', e => {
                        cb();
                    });
                }
            },
            getFeatureForEvent(e) {
                const features = this.map.getFeaturesAtPixel(e.pixel, {
                    hitTolerance: 5
                });
                return features ? features[0] : null;
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
                    if(cancelled) {
                        this.modify.setChangeState('cancel');
                    }
                    this.drawType = '';
                    this.interactionMode = '';
                    this.draw.setActive(false);
                    this.delete.setActive(false, cancelled);
                    this.modify.setActive(false, cancelled);
                }
            },
            createStyle(color = '#ffcc33', width = 2) {
                let polygonFillColor;
                let r, g, b, a;
                const fillAlphaMultiplier = 0.2;
                if(color.startsWith('#')) {
                    [r, g, b] = this.$rgb2hex(color);
                    a = 1 * fillAlphaMultiplier;
                } else if(color.startsWith('rgba')) {
                    // cut off rgba and () and get alpha value
                    [r, g, b, a] = color.substring(5, color.length-1).split(',');
                    a *= fillAlphaMultiplier;
                }
                polygonFillColor = `rgba(${r}, ${g}, ${b}, ${a})`;
                return new Style({
                    fill: new Fill({
                        color: polygonFillColor
                    }),
                    stroke: new Stroke({
                        color: color,
                        width: width
                    }),
                    image: new CircleStyle({
                        radius: width*3,
                        fill: new Fill({
                            color: color
                        }),
                        stroke: new Stroke({
                            color: 'rgba(0, 0, 0, 0.2)',
                            width: 2
                        })
                    })
                });
            },
            drawFeature(feature) {
                this.snap.addFeature(feature);
                this.onDrawend(feature, this.wktFormat.writeFeature(feature, {
                    featureProjection: 'EPSG:3857',
                    dataProjection: this.initProjection
                }))
                    .then(newFeature => {
                        if(!newFeature) return;
                        this.vector.getSource().removeFeature(feature);
                        let layer;
                        const ent = newFeature.get('entity');
                        if(ent) {
                            layer = this.getLayer(ent.entity_type_id)
                        } else {
                            layer = this.getUnlinkedLayer();
                        }
                        // FIXME while initWkt is only one layer
                        const entLayer =
                            this.initWkt.length ?
                                this.getEntityLayers()[0] :
                                this.getEntityLayerById(layer.id);
                        const fid = newFeature.get('id');
                        const defaultStyle = this.createStyle(layer ? layer.color : undefined);
                        this.featureStyles[fid] = {
                            default: defaultStyle,
                            label: null,
                            style: null
                        };
                        newFeature.setStyle(this.featureStyles[fid].default);
                        let source = entLayer.getSource();
                        if(this.reset) {
                            if(source.getFeatures().length) {
                                source.clear();
                            }
                        }
                        source.addFeature(newFeature);
                        this.features[fid] = newFeature;
                    });
            },
            updateFeatures() {
                const features = this.modify.getModifiedFeatures();
                let wktFeatures = features.map(f => this.wktFormat.writeFeature(f));
                this.onModifyend(features, wktFeatures);
                this.setInteractionMode('');
            },
            cancelUpdateFeatures() {
                this.setInteractionMode('', true);
            },
            deleteFeatures() {
                const features = this.delete.getDeletedFeatures();
                let wktFeatures = features.map(f => {
                    this.snap.removeFeature(f);
                    this.wktFormat.writeFeature(f);
                });
                this.onDeleteend(features, wktFeatures);
                this.setInteractionMode('');
            },
            cancelDeleteFeatures() {
                this.setInteractionMode('', true);
            },
            enablePointCoordEdit() {
                this.overlayInfo.editCoordinates = [
                    this.coordinateList[0].x,
                    this.coordinateList[0].y
                ];
                this.overlayInfo.pointEditEnabled = true;
            },
            cancelPointCoordEdit() {
                this.overlayInfo.editCoordinates = null;
                this.overlayInfo.pointEditEnabled = false;
            },
            confirmPointCoordEdit() {
                const newCoord = this.transformCoordinates([
                    this.overlayInfo.editCoordinates[0],
                    this.overlayInfo.editCoordinates[1]
                ], undefined, true);
                this.overlayInfo.pointEditEnabled = false;
                this.overlayInfo.feature.setGeometry(new Point([
                    newCoord.x, newCoord.y
                ]));
                const features = [this.overlayInfo.feature];
                const wktFeatures = features.map(f => this.wktFormat.writeFeature(f));
                this.onModifyend(features, wktFeatures);
                this.updatePopup(this.overlayInfo.feature);
            },
            initMeasureInteraction() {
                this.measureSource = new Vector({
                    wrapX: false
                });
                this.measureLayer = new VectorLayer({
                    baseLayer: false,
                    displayInLayerSwitcher: false,
                    title: 'Measure Layer',
                    visible: true,
                    layer: 'measure',
                    source: this.measureSource,
                    style: this.createStyle('#000')
                });
                this.map.addLayer(this.measureLayer);
            },
            addMeasureInteraction() {
                this.measureDraw = new Draw({
                    source: this.measureSource,
                    type: 'LineString',
                    style: new Style({
                        fill: new Fill({
                            color: 'rgba(255, 255, 255, 0.2)'
                        }),
                        stroke: new Stroke({
                            color: 'rgba(0, 0, 0, 0.5)',
                            lineDash: [10, 10],
                            width: 2
                        }),
                        image: new CircleStyle({
                            radius: 5,
                            stroke: new Stroke({
                                color: 'rgba(0, 0, 0, 0.2)'
                            }),
                            fill: new Fill({
                                color: 'rgba(255, 255, 255, 0.2)'
                            })
                        })
                    })
                });
                this.map.addInteraction(this.measureDraw);

                this.measureTooltipElement = document.getElementById(`${this.id}-measure-popup`);
                this.measureTooltip = new Overlay({
                    element: this.measureTooltipElement,
                    offset: [0, 5]
                });
                this.map.addOverlay(this.measureTooltip);

                let measureListener;
                this.measureDraw.on('drawstart', event => {
                    // Remove existing measures
                    this.measureSource.clear();
                    this.drawnFeature = event.feature;
                    let coords = event.coordinate;
                    measureListener = this.drawnFeature.getGeometry().on('change', ce => {
                        let geom = ce.target;
                        coords = geom.getLastCoordinate();
                        $(this.measureTooltipElement).tooltip('dispose');
                        this.measureTooltip.setPosition(coords);
                        $(this.measureTooltipElement).tooltip({
                            container: this.viewport || '#map',
                            placement: 'bottom',
                            title: this.getLineLength(geom)
                        });
                        $(this.measureTooltipElement).tooltip('show');
                    });
                });
                this.measureDraw.on('drawend', event => {
                    this.measureTooltipElement.className = 'tooltip tooltip-static';
                    this.measureFeature = null;
                    unByKey(measureListener);
                });
            },
            removeMeasureInteraction() {
                $(this.measureTooltipElement).tooltip('dispose');
                this.measureSource.clear();
                this.map.removeInteraction(this.measureDraw);
                this.measureDraw = null;
                this.measureFeature = null;
                this.measureTooltip = null;
                this.measureTooltipElement = null;
            },
            toggleMeasurements() {
                this.measurementActive = !this.measurementActive;

                if(this.measurementActive) {
                    this.addMeasureInteraction();
                } else {
                    this.removeMeasureInteraction();
                }
            },
            getLineLength(geom) {
                const length = getLength(geom);
                let str;
                if(length >= 100) {
                    str = `${(length / 1000).toFixed(2)} km`;
                } else {
                    str = `${length.toFixed(2)} m`;
                }
                return str;
            },
            link(feature, entity) {
                if(!this.linkPossible) return;
                const props = feature.getProperties();
                const gid = props.id;
                const eid = entity.id;
                $http.post(`/map/link/${gid}/${eid}`, {}).then(response => {
                    feature.setProperties({
                        entity: Object.assign({}, entity)
                    });
                    const layer = this.getLayer(entity.entity_type_id);
                    this.featureStyles[feature.get('id')].default = this.createStyle(layer.color);
                    this.updateStyles(feature);
                    this.$emit('update:link', gid, eid);
                });
            },
            unlink(feature, entity) {
                if(!this.unlinkPossible) return;
                const props = feature.getProperties();
                const gid = props.id;
                const eid = entity.id;
                $http.delete(`/map/link/${gid}/${eid}`, {}).then(response => {
                    this.afterUnlink(feature, eid);
                });
            },
            afterUnlink(feature, eid) {
                feature.setProperties({
                    entity: null
                });
                const layer = this.getUnlinkedLayer();
                this.featureStyles[feature.get('id')].default = this.createStyle(layer.color);
                this.updateStyles(feature);
                this.$emit('update:link', null, eid);
            },
            transformCoordinates(c, clist, rev = false) {
                if(!c[0] || !c[1]) {
                    return null;
                };
                let fromEpsg = rev ? `EPSG:${this.epsg.epsg}` : 'EPSG:3857';
                let toEpsg = rev ? 'EPSG:3857' : `EPSG:${this.epsg.epsg}`;
                const transCoord = transformProj(c, fromEpsg, toEpsg);
                const fixedCoord = {
                    x: transCoord[0],
                    y: transCoord[1]
                };
                if(clist) {
                    clist.push(fixedCoord);
                }
                return fixedCoord;
            },

            updatePopup(f) {
                const element = this.overlay.getElement();
                if(!f.getId) {
                    $(element).popover('dispose');
                    this.overlay.setPosition(undefined);
                    return;
                };
                const props = f.getProperties();
                const geometry = f.getGeometry();
                const geomName = this.$t('main.map.geometry-name', {id: props.id});
                const coords = getExtentCenter(geometry.getExtent());
                this.overlay.setPosition(coords);

                if(props.entity) {
                    this.overlayInfo.name = props.entity.name;
                    this.overlayInfo.subname = geomName;
                } else {
                    this.overlayInfo.name = geomName;
                    this.overlayInfo.subname = '';
                }

                const length = this.$options.filters.length;
                let geometryInfo;
                switch(geometry.getType()) {
                    case 'LineString':
                    case 'MultiLineString':
                        this.overlayInfo.size = length(geometry.getLength()*1000, 2);
                        break;
                    case 'Polygon':
                    case 'MultiPolygon':
                        this.overlayInfo.size =  length(geometry.getArea()*1000, 2, true);
                        break;
                }

                this.overlayInfo.feature = f;
                this.overlayInfo.type = geometry.getType();
                this.overlayInfo.coordinates = geometry.getCoordinates();

                $(element).popover('show');
            },
            handleEntityDelete(e) {
                const id = e.entity.id;
                if(!id) return;
                const features = this.getEntityLayerFeatures();
                let feature = features.find(f => {
                    const props = f.getProperties();
                    return props.entity && props.entity.id == id;
                });
                if(feature) {
                    this.afterUnlink(feature, id);
                }
            },
            handleEntityUpdate(e) {
                const id = e.entity_id;
                switch(e.type) {
                    case 'name':
                        const features = this.getEntityLayerFeatures();
                        let feature = features.find(f => {
                            const props = f.getProperties();
                            return props.entity && props.entity.id == id;
                        });
                        if(feature) {
                            let entity = feature.getProperties().entity;
                            entity.name = e.value;
                            feature.set('entity', entity);
                        }
                    break;
                    default:
                        vm.$throwError({message: `Unknown event type ${e.type} received.`});
                }
            }
        },
        data() {
            return {
                id: `map-${Date.now()}`,
                drawType: '',
                interactionMode: '',
                map: {},
                mapRendered: false,
                baselayers: {},
                overlays: {},
                overlayLayers: [],
                baselayerLayers: [],
                baselayersGroup: {},
                overlaysGroup: {},
                entityLayersGroup: {},
                entityLayers: [],
                features: {},
                vector: {}, // TODO replace
                overlay: {},
                overlayInfo: {
                    name: '',
                    subname: '',
                    type: '',
                    size: '',
                    coordinates: [],
                    feature: null,
                    showCoordinates: false,
                    pointEditEnabled: false,
                    editCoordinates: null
                },
                hoverPopup: {},
                lastHoveredFeature: {},
                modify: {},
                draw: {},
                measurementActive: false,
                measureDraw: {},
                measureFeature: {},
                measureSource: {},
                measureLayer: {},
                measureTooltip: {},
                measureTooltipElement: {},
                delete: {},
                snap: {},
                options: {},
                extent: [],
                featureStyles: {},
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
            },
            coordinateList() {
                const cs = this.overlayInfo.coordinates;
                let coordList = [];
                switch(this.overlayInfo.type) {
                    case 'Point':
                        this.transformCoordinates(cs, coordList);
                        break;
                    case 'LineString':
                    case 'MultiPoint':
                        cs.forEach(c => {
                            this.transformCoordinates(c, coordList);
                        });
                        break;
                    case 'Polygon':
                    case 'MultiLineString':
                        cs.forEach(cg => {
                            cg.forEach(c => {
                                this.transformCoordinates(c, coordList);
                            });
                        });
                        break;
                    case 'MultiPolygon':
                        cs.forEach(cg => {
                            cg.forEach(innerCg => {
                                innerCg.forEach(c => {
                                    this.transformCoordinates(c, coordList);
                                });
                            });
                        });
                        break;
                }
                return coordList;
            },
            isPointOverlay() {
                return this.overlayInfo.type == 'Point' || this.overlayInfo.type == 'MultiPoint'
            },
            isLineOverlay() {
                return this.overlayInfo.type == 'LineString' || this.overlayInfo.type == 'MultiLineString'
            },
            isPolygonOverlay() {
                return this.overlayInfo.type == 'Polygon' || this.overlayInfo.type == 'MultiPolygon'
            }
        },
        watch: {
            selectedFeature: function(newFeature, oldFeature) {
                this.onMapReady(_ => {
                    this.updatePopup(this.selectedFeature);
                });
            },
            selectedEntity: function(newEntity, oldEntity) {
                    if (newEntity.geodata_id) {
                        this.selectedFeature = this.features[newEntity.geodata_id];
                    } else {
                        // new entity is not linked to any geodata
                        if (oldEntity.geodata_id) {
                            // old entity was linked to geodata
                            // therefore we want to deselect the (linked) feature
                            this.selectedFeature = {};
                        }
                    }
            },
            'selectedEntity.geodata_id': function(newId, oldId) {
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
            layerLabels: function(newLabels, oldLabels) {
                this.applyLabels();
            },
            layerStyles: function(newStyles, oldStyles) {
                this.applyStyles();
            },
            zoomTo: function(newZoomLayerId, oldZoomLayerId) {
                if(!newZoomLayerId) return;
                const zoomLayer = this.getEntityLayerById(newZoomLayerId);
                if(!zoomLayer) return;
                const zoomLayerSource = zoomLayer.getSource();
                if(!zoomLayerSource) return;
                this.setExtent(zoomLayerSource.getExtent());
            }
        }
    }
</script>
