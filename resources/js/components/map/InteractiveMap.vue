<template>
    <div>
        <div :id="state.mapId" class="map h-100">
            <div class="d-flex flex-column ol-bar ol-right ol-bottom">
                <div v-if="drawing" class="d-flex flex-column align-items-end">
                    <button type="button" class="btn btn-fab rounded-circle" :class="{'btn-primary': actionState.drawType == 'Point', 'btn-outline-primary': actionState.drawType != 'Point'}" data-bs-toggle="popover" :data-content="t('main.map.draw.point.desc')" data-trigger="hover" data-placement="bottom" @click="toggleDrawType('Point')">
                        <i class="fas fa-fw fa-map-marker-alt"></i>
                    </button>
                    <button type="button" class="btn btn-fab rounded-circle" :class="{'btn-primary': actionState.drawType == 'LineString', 'btn-outline-primary': actionState.drawType != 'LineString'}" data-bs-toggle="popover" :data-content="t('main.map.draw.linestring.desc')" data-trigger="hover" data-placement="bottom" @click="toggleDrawType('LineString')">
                        <i class="fas fa-fw fa-road"></i>
                    </button>
                    <button type="button" class="btn btn-fab rounded-circle" :class="{'btn-primary': actionState.drawType == 'Polygon', 'btn-outline-primary': actionState.drawType != 'Polygon'}" data-bs-toggle="popover" :data-content="t('main.map.draw.polygon.desc')" data-trigger="hover" data-placement="bottom" @click="toggleDrawType('Polygon')">
                        <i class="fas fa-fw fa-draw-polygon"></i>
                    </button>
                    <!-- <button type="button" class="btn btn-fab rounded-circle btn-outline-info" v-show="interactionMode != 'modify'" data-bs-toggle="popover" :data-content="t('main.map.draw.modify.desc')" data-trigger="hover" data-placement="bottom" @click="setInteractionMode('modify')">
                        <i class="fas fa-fw fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-fab rounded-circle btn-outline-success" v-show="interactionMode == 'modify'" data-bs-toggle="popover" :data-content="t('main.map.draw.modify.pos-desc')" data-trigger="hover" data-placement="bottom" @click="updateFeatures">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-fab rounded-circle btn-outline-danger" v-show="interactionMode == 'modify'" data-bs-toggle="popover" :data-content="t('main.map.draw.modify.neg-desc')" data-trigger="hover" data-placement="bottom" @click="cancelUpdateFeatures">
                        <i class="fas fa-fw fa-times"></i>
                    </button>
                    <button type="button" class="btn btn-fab rounded-circle btn-outline-danger" v-show="interactionMode != 'delete'" data-bs-toggle="popover" :data-content="t('main.map.draw.delete.desc')" data-trigger="hover" data-placement="bottom" @click="setInteractionMode('delete')">
                        <i class="fas fa-fw fa-trash"></i>
                    </button>
                    <button type="button" class="btn btn-fab rounded-circle btn-outline-success" v-show="interactionMode == 'delete'" data-bs-toggle="popover" :data-content="t('main.map.draw.delete.pos-desc')" data-trigger="hover" data-placement="bottom" @click="deleteFeatures">
                        <i class="fas fa-fw fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-fab rounded-circle btn-outline-danger" v-show="interactionMode == 'delete'" data-bs-toggle="popover" :data-content="t('main.map.draw.delete.neg-desc')" data-trigger="hover" data-placement="bottom" @click="cancelDeleteFeatures">
                        <i class="fas fa-fw fa-times"></i>
                    </button>
                    <button type="button" class="btn btn-fab rounded-circle btn-outline-primary" data-bs-toggle="popover" :data-content="t('main.map.draw.measure.desc')" data-trigger="hover" data-placement="bottom" @click="toggleMeasurements">
                        <i class="fas fa-fw fa-ruler-combined"></i>
                    </button> -->
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';
    
    import { useI18n } from 'vue-i18n';

    import 'ol/ol.css';
    import Collection from 'ol/Collection';
    import { defaults as defaultControls } from 'ol/control.js';
    import { defaults as defaultInteractions } from 'ol/interaction';
    import Feature from 'ol/Feature';
    import Map from 'ol/Map';
    import View from 'ol/View';

    import FullScreen from 'ol/control/FullScreen';
    import OverviewMap from 'ol/control/OverviewMap';
    import Rotate from 'ol/control/Rotate';
    import ScaleLine from 'ol/control/ScaleLine';

    import {never as neverCond, shiftKeyOnly, platformModifierKeyOnly} from 'ol/events/condition';

    import WKT from 'ol/format/WKT';
    import GeoJSON from 'ol/format/GeoJSON';

    import Draw from 'ol/interaction/Draw';
    import DragRotate from 'ol/interaction/DragRotate';
    import DragZoom from 'ol/interaction/DragZoom';
    import PinchRotate from 'ol/interaction/PinchRotate';
    import PinchZoom from 'ol/interaction/PinchZoom';

    import Group from 'ol/layer/Group';

    import LayerSwitcher from 'ol-ext/control/LayerSwitcher';

    import '../../../sass/ol-ext-layerswitcher.scss';

    import {
        createNewLayer,
        createStyle,
        createVectorLayer,
        registerProjection,
    } from '../../helpers/map.js';

    import {
      getEntityType,
        getTs,
        translateConcept
    } from '../../helpers/helpers.js';

    export default {
        props: {
            layers: {
                type: Object,
                required: true,
            },
            data: {
                type: Object,
                required: false,
                default: {},
            },
            projection: {
                type: Number,
                required: false,
                default: 3857,
            },
            inputProjection: {
                type: Number,
                required: false,
                default: 3857,
            },
            resetEach: {
                type: Boolean,
                required: false,
                default: false,
            },
            drawing: {
                type: Boolean,
                required: false,
                default: true,
            },
            provider: {
                type: String,
                required: false,
                default: 'ol',
            },
        },
        emits: ['added'],
        setup(props, context) {
            const { t } = useI18n();

            const {
                layers,
                data,
                projection,
                inputProjection,
                resetEach,
                drawing,
            } = toRefs(props);

            // FUNCTIONS
            const getAssociatedLayer = feature => {
                if(data.value.format == 'wkt') {
                    const layerGroup = state.mapLayerGroups.entity.getLayers().getArray();
                    for(let i=0; i<layerGroup.length; i++) {
                        if(layerGroup[i].get('layer_id')) {
                            return layerGroup[i];
                        }
                    }
                }
                return null;
            };
            const drawFeature = feature => {
                const layer = getAssociatedLayer(feature);
                if(!layer) return;
                const source = layer.getSource();
                if(resetEach.value) {
                    if(source.getFeatures().length) {
                        source.clear();
                    }
                }
                source.addFeature(feature);
                const wktStr = wktFormat.writeFeature(feature, {
                    featureProjection: 'EPSG:3857',
                    dataProjection: state.inputEpsgCode,
                });
                context.emit('added', {
                    feature: feature,
                    wkt: wktStr,
                    count: source.getFeatures().length,
                });
            };
            const initializeLayers = _ => {
                let geojsonLayers = {};
                for(let k in layers.value) {
                    const l = layers.value[k];
                    if(l.is_overlay) {
                        // check if layer is not an entity layer
                        // and not the default unlinked layer
                        if(!l.entity_type_id && l.type != 'unlinked') {
                            state.mapOverlays.push(createNewLayer(l));
                        } else {
                            let layerName = '';
                            if(l.entity_type_id) {
                                const et = getEntityType(l.entity_type_id);
                                if(!!et) {
                                    layerName = translateConcept(et.thesaurus_url);
                                }
                            } else {
                                layerName = t('global.unlinked');
                            }
                            geojsonLayers[l.id] = createVectorLayer({
                                show: true,
                                title: layerName,
                                visible: l.visible,
                                opacity: parseFloat(l.opacity),
                                color: l.color,
                                layer: 'entity',
                                layer_id: l.id,
                            });
                            state.mapEntityLayers.push(geojsonLayers[l.id]);
                        }
                    } else {
                        state.mapBaselayers.push(createNewLayer(l));
                    }
                }

                state.mapLayerGroups.base = new Group({
                    title: t('main.map.baselayer', 2),
                    openInLayerSwitcher: true,
                    layers: state.mapBaselayers,
                });
                state.mapLayerGroups.overlay = new Group({
                    title: t('main.map.overlay', 2),
                    openInLayerSwitcher: true,
                    layers: state.mapOverlays,
                });
                state.mapLayerGroups.entity = new Group({
                    title: t('main.map.entity-layers'),
                    openInLayerSwitcher: true,
                    layers: state.mapEntityLayers,
                });
            };
            const initializeProjections = async _ => {
                // If desired epsg code is default, skip initialization
                if(projection.value == 3857) {
                    return;
                }

                await registerProjection(projection.value);
                await registerProjection(inputProjection.value);
            };
            const loadWktData = features => {
                // As WKT can not have any metadata, we do not know which layer this should be displayed on
                // Thus we create a separate layer for it
                const wktLayer = createVectorLayer({
                    show: true,
                    title: t('global.all-entities'),
                    visible: true,
                    opacity: 1,
                    layer: 'entity',
                    layer_id: 'wkt_layer',
                });

                state.mapEntityLayers.push(wktLayer);
                const source = wktLayer.getSource();

                for(let i=0; i<features.length; i++) {
                    const geom = wktFormat.readGeometry(features[i], {
                        featureProjection: 'EPSG:3857',
                        dataProjection: state.inputEpsgCode,
                    });
                    source.addFeature(new Feature({geometry: geom}));
                }
            };
            const loadGeojsonData = features => {

            };
            const loadCollectionData = features => {

            };
            const initializeData = _ => {
                if(!data.value || (!data.value.format && !data.value.features)) {
                    return;
                }

                switch(data.value.format) {
                    case 'wkt':
                        loadWktData(data.value.features);
                        break;
                    case 'geojson':
                        loadGeojsonData(data.value.features);
                        break;
                    case 'collection':
                        loadCollectionData(data.value.features);
                        break;
                }
            };
            const updateLayerGroups = _ => {
                state.mapLayerGroups.base.setLayers(new Collection(state.mapBaselayers));
                state.mapLayerGroups.overlay.setLayers(new Collection(state.mapOverlays));
                state.mapLayerGroups.entity.setLayers(new Collection(state.mapEntityLayers));
            };
            const initializeDrawFeatures = _ => {
                // If drawing is disabled, skip initializing it
                if(!drawing.value) {
                    return;
                }

                actionState.drawingLayer = createVectorLayer();

                actionState.draw = {
                    init: _ => {
                        state.map.addInteraction(actionState.draw.Point);
                        actionState.draw.Point.setActive(false);
                        state.map.addInteraction(actionState.draw.LineString);
                        actionState.draw.LineString.setActive(false);
                        state.map.addInteraction(actionState.draw.Polygon);
                        actionState.draw.Polygon.setActive(false);
                    },
                    getActive: _ => {
                        return actionState.draw.activeType ? actionState.draw[actionState.draw.activeType].getActive() : false;
                    },
                    setActive: (active, type) => {
                        actionState.draw.activeType && actionState.draw[actionState.draw.activeType].setActive(false);
                        if(active) {
                            actionState.draw[type].setActive(true, type);
                        }
                        actionState.draw.activeType = active ? type : null;
                    },
                    activeType: null,
                    Point: new Draw({
                        source: actionState.drawingLayer.getSource(),
                        type: 'Point'
                    }),
                    LineString: new Draw({
                        source: actionState.drawingLayer.getSource(),
                        type: 'LineString'
                    }),
                    Polygon: new Draw({
                        source: actionState.drawingLayer.getSource(),
                        type: 'Polygon'
                    }),
                };
                actionState.modify = {
                    init: _ => {
                    },
                    getActive: _ => {
                    },
                    setActive: (active, type) => {
                    },
                };
                actionState.delete = {
                    init: _ => {
                    },
                    getActive: _ => {
                    },
                    setActive: (active, type) => {
                    },
                };

                actionState.draw.init();
                actionState.modify.init();
                actionState.delete.init();
                actionState.draw.setActive(false);
                actionState.modify.setActive(false);
                actionState.delete.setActive(false);

                // TODO add draw layer to map
                // this.entityLayers.push(vm.vector);

                // this.initMeasureInteraction();
                // if(this.measurementActive) {
                //     this.addMeasureInteraction();
                // }

                // Event Listeners
                actionState.draw.Point.on('drawend', event => {
                    drawFeature(event.feature);
                });
                actionState.draw.LineString.on('drawend', event => {
                    drawFeature(event.feature);
                });
                actionState.draw.Polygon.on('drawend', event => {
                    drawFeature(event.feature);
                });
                
                console.log("action state draw", actionState.draw);
            };
            // Interactions (Draw, Modify, Delete)
            const setInteractionMode = (mode, type, cancelled) => {
                const currMode = actionState.interactionMode;
                actionState.interactionMode = mode;
                // only set snap features, when mode is set and not delete
                // because delete does not need snapping
                if(!!mode && mode != 'delete') {
                    // this.updateSnap(this.getFeaturesInExtent());
                } else {
                    // this.updateSnap();
                }

                switch(mode) {
                    case 'draw':
                        actionState.draw.setActive(true, type);
                        actionState.modify.setActive(false, cancelled);
                        actionState.delete.setActive(false, cancelled);
                        break;
                    case 'modify':
                        actionState.drawType = '';
                        actionState.draw.setActive(false);
                        actionState.modify.setActive(true);
                        actionState.delete.setActive(false, currMode == 'delete');
                        break;
                    case 'delete':
                        actionState.drawType = '';
                        actionState.draw.setActive(false);
                        actionState.modify.setActive(false, currMode == 'modify');
                        actionState.delete.setActive(true);
                        break;
                    default:
                        if(cancelled) {
                            actionState.modify.setChangeState('cancel');
                        }
                        actionState.drawType = '';
                        actionState.interactionMode = '';
                        actionState.draw.setActive(false);
                        actionState.delete.setActive(false, cancelled);
                        actionState.modify.setActive(false, cancelled);
                        break;
                }
            };
            const toggleDrawType = type => {
                const currType = actionState.drawType;
                actionState.drawType = type;
                if(actionState.interactionMode != 'draw') {
                    setInteractionMode('draw', type, true);
                } else if(currType == type) {
                    setInteractionMode('');
                }
                actionState.draw.getActive() && actionState.draw.setActive(true, type);
            }

            // DATA
            // EPSG:3857 bounds (taken from epsg.io/3857)
            const defaultExtent = [-20026376.39, -20048966.10, 20026376.39, 20048966.10];
            const wktFormat = new WKT();
            const geojsonFormat = new GeoJSON();
            const state = reactive({
                map: null,
                mapId: `interactive-map-container-${getTs()}`,
                mapLayerGroups: {
                    base: null,
                    overlay: null,
                    entity: null,
                },
                mapOverlays: [],
                mapBaselayers: [],
                mapEntityLayers: [],
                epsgCode: computed(_ => `EPSG:${projection.value}`),
                inputEpsgCode: computed(_ => `EPSG:${inputProjection.value}`),
            });
            const actionState = reactive({
                drawType: '',
                interactionMode: '',
                drawingLayer: null,
                draw: {},
                modify: {},
                delete: {},
            })

            // ON MOUNTED
            onMounted(async _ => {
                initializeLayers();
                await initializeProjections();
                initializeData();
                updateLayerGroups();

                state.map = new Map({
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
                    layers: [state.mapLayerGroups.base, state.mapLayerGroups.overlay, state.mapLayerGroups.entity],
                    target: state.mapId,
                    view: new View({
                        center: [0, 0],
                        projection: 'EPSG:3857',
                        zoom: 2,
                        extent: defaultExtent
                    }),
                    overlays: [
                        // vm.overlay,
                        // vm.hoverPopup
                    ]
                });

                initializeDrawFeatures();
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                drawing,
                // LOCAL
                toggleDrawType,
                // STATE
                state,
                actionState,
            }
            
        },
    }
</script>
