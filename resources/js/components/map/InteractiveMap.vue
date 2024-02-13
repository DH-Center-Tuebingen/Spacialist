<template>
    <div class="h-100"> 
        <div
            :id="state.mapId"
            class="map h-100"
        >
            <div class="d-flex flex-column ol-bar ol-right ol-bottom">
                <div
                    v-if="drawing"
                    class="d-flex flex-column align-items-end"
                >
                    <button
                        type="button"
                        class="btn btn-fab rounded-circle"
                        :class="{'btn-primary': actionState.drawType == 'Point', 'btn-outline-primary': actionState.drawType != 'Point'}"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.point.desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="toggleDrawType('Point')"
                    >
                        <i class="fas fa-fw fa-map-marker-alt" />
                    </button>
                    <button
                        type="button"
                        class="btn btn-fab rounded-circle"
                        :class="{'btn-primary': actionState.drawType == 'LineString', 'btn-outline-primary': actionState.drawType != 'LineString'}"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.linestring.desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="toggleDrawType('LineString')"
                    >
                        <i class="fas fa-fw fa-road" />
                    </button>
                    <button
                        type="button"
                        class="btn btn-fab rounded-circle"
                        :class="{'btn-primary': actionState.drawType == 'Polygon', 'btn-outline-primary': actionState.drawType != 'Polygon'}"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.polygon.desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="toggleDrawType('Polygon')"
                    >
                        <i class="fas fa-fw fa-draw-polygon" />
                    </button>
                    <button
                        v-show="actionState.interactionMode != 'modify'"
                        type="button"
                        class="btn btn-fab rounded-circle btn-outline-info"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.modify.desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="setInteractionMode('modify')"
                    >
                        <i class="fas fa-fw fa-edit" />
                    </button>
                    <button
                        v-show="actionState.interactionMode == 'modify'"
                        type="button"
                        class="btn btn-fab rounded-circle btn-outline-success"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.modify.pos_desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="updateFeatures()"
                    >
                        <i class="fas fa-fw fa-check" />
                    </button>
                    <button
                        v-show="actionState.interactionMode == 'modify'"
                        type="button"
                        class="btn btn-fab rounded-circle btn-outline-danger"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.modify.neg_desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="updateFeatures(false)"
                    >
                        <i class="fas fa-fw fa-times" />
                    </button>
                    <button
                        v-show="actionState.interactionMode != 'delete'"
                        type="button"
                        class="btn btn-fab rounded-circle btn-outline-danger"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.delete.desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="setInteractionMode('delete')"
                    >
                        <i class="fas fa-fw fa-trash" />
                    </button>
                    <button
                        v-show="actionState.interactionMode == 'delete'"
                        type="button"
                        class="btn btn-fab rounded-circle btn-outline-success"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.delete.pos_desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="deleteFeatures()"
                    >
                        <i class="fas fa-fw fa-check" />
                    </button>
                    <button
                        v-show="actionState.interactionMode == 'delete'"
                        type="button"
                        class="btn btn-fab rounded-circle btn-outline-danger"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.delete.neg_desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="deleteFeatures(false)"
                    >
                        <i class="fas fa-fw fa-times" />
                    </button>
                    <button
                        type="button"
                        class="btn btn-fab rounded-circle"
                        :class="{'btn-primary': actionState.measure.active, 'btn-outline-primary': !actionState.measure.active}"
                        data-bs-toggle="popover"
                        :data-content="t('main.map.draw.measure.desc')"
                        data-trigger="hover"
                        data-placement="bottom"
                        @click="toggleMeasurements()"
                    >
                        <i class="fas fa-fw fa-ruler-combined" />
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div
        :id="actionState.popupIds.p"
        class="popup popover ol-popover bs-popover-top"
        role="tooltip"
    >
        <h4 class="popover-header d-flex flex-row gap-2 justify-content-between align-items-center">
            <div>
                <slot
                    name="title"
                    :feature="actionState.overlayData.feature"
                    :default-title="actionState.overlayData.title"
                    :default-subtitle="actionState.overlayData.subtitle"
                >
                    <span class="fw-medium">
                        {{ actionState.overlayData.title }}
                    </span>
                    <span v-if="actionState.overlayData.subtitle">
                        ({{ actionState.overlayData.subtitle }})
                    </span>
                </slot>
            </div>
            <div>
                <slot
                    name="action"
                    :post-action-hook="data => postAction(data, actionState.overlayData.feature)"
                />
            </div>
        </h4>
        <div class="popover-body">
            <dl class="mb-0">
                <dt>
                    {{ t('global.type') }}
                </dt>
                <dd>
                    {{ actionState.overlayData.type }}
                </dd>
                <template v-if="actionState.overlayIsPolygon || actionState.overlayIsLine">
                    <dt>
                        <span v-if="actionState.overlayIsLine">
                            {{ t('main.map.length') }}
                        </span>
                        <span v-else-if="actionState.overlayIsPolygon">
                            {{ t('main.map.area') }}
                        </span>
                    </dt>
                    <dd>
                        <span
                            data-bs-toggle="tooltip"
                            :title="`${actionState.overlayData.size.in_m}${actionState.overlayData.size.unit}`"
                        >
                            {{ actionState.overlayData.size.combined }}
                        </span>
                    </dd>
                </template>
                <dt
                    class="clickable"
                    @click="actionState.overlayData.showCoordinates = !actionState.overlayData.showCoordinates"
                >
                    {{
                        t('main.map.coords_in_epsg', {
                            epsg: state.epsgCode
                        })
                    }}
                    <span class="fw-normal">
                        ({{ actionState.overlayCoordinatesAsList.length }})
                    </span>
                    <span v-show="actionState.overlayData.showCoordinates">
                        <i class="fas fa-fw fa-caret-up" />
                    </span>
                    <span v-show="!actionState.overlayData.showCoordinates">
                        <i class="fas fa-fw fa-caret-down" />
                    </span>
                </dt>
                <dd class="mb-0 mh-300p scroll-y-auto">
                    <div v-if="actionState.overlayData.showCoordinates">
                        <table class="table table-striped table-borderless table-sm mb-0">
                            <tbody>
                                <tr
                                    v-for="(c, i) in actionState.overlayCoordinatesAsList"
                                    :key="i"
                                >
                                    <td class="text-start">
                                        <input
                                            v-if="actionState.coordinateEditMode"
                                            v-model.number="actionState.overlayCoordinateEdit[0]"
                                            type="number"
                                            class="form-control form-control-sm"
                                            step="0.000001"
                                        >
                                        <span v-else>
                                            {{ toFixed(c.x, 4) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <input
                                            v-if="actionState.coordinateEditMode"
                                            v-model.number="actionState.overlayCoordinateEdit[1]"
                                            type="number"
                                            class="form-control form-control-sm"
                                            step="0.000001"
                                        >
                                        <span v-else>
                                            {{ toFixed(c.y, 4) }}
                                        </span>
                                    </td>
                                    <td
                                        v-if="actionState.overlayIsPoint"
                                        class="text-end clickable"
                                    >
                                        <div
                                            v-if="actionState.coordinateEditMode"
                                            class="d-flex flex-row gap-1"
                                        >
                                            <a
                                                href=""
                                                @click.prevent="confirmOverlayCoordinateEditing(true, actionState.overlayData.feature)"
                                            >
                                                <i class="fas fa-fw fa-check" />
                                            </a>
                                            <a
                                                href=""
                                                @click.prevent="confirmOverlayCoordinateEditing(false)"
                                            >
                                                <i class="fas fa-fw fa-times" />
                                            </a>
                                        </div>
                                        <a
                                            v-else
                                            href=""
                                            @click.prevent="enableOverlayCoordinateEditing(c)"
                                        >
                                            <i class="fas fa-fw fa-edit" />
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </dd>
            </dl>
        </div>
        <div class="arrow ol-arrow" />
    </div>
    <div
        :id="actionState.popupIds.h"
        class="tooltip"
        role="tooltip"
    />
    <div
        :id="actionState.popupIds.m"
        class="tooltip tooltip-measure bs-tooltip-bottom"
        role="tooltip"
    >
        <div class="tooltip-arrow" />
        <div class="tooltip-inner">
            {{ actionState.measure.length }}
        </div>
    </div>
</template>

<script>
    import {
        computed,
        nextTick,
        onMounted,
        reactive,
        toRefs,
        watch,
    } from 'vue';
    
    import { useI18n } from 'vue-i18n';

    import {
        Tooltip,
        Popover,
    } from 'bootstrap';

    import 'ol/ol.css';
    import Collection from 'ol/Collection';
    import { defaults as defaultControls } from 'ol/control.js';
    import { defaults as defaultInteractions } from 'ol/interaction';
    import Feature from 'ol/Feature';
    import Map from 'ol/Map';
    import {unByKey} from 'ol/Observable.js';
    import View from 'ol/View';
    import Overlay from 'ol/Overlay';
    import { transform as transformProj } from 'ol/proj';
    import {getArea, getLength} from 'ol/sphere';

    import FullScreen from 'ol/control/FullScreen';
    import OverviewMap from 'ol/control/OverviewMap';
    import Rotate from 'ol/control/Rotate';
    import ScaleLine from 'ol/control/ScaleLine';

    import {never as neverCond, shiftKeyOnly, platformModifierKeyOnly} from 'ol/events/condition';

    import { getCenter as getExtentCenter, extend as extendExtent} from 'ol/extent';

    import Draw from 'ol/interaction/Draw';
    import DragRotate from 'ol/interaction/DragRotate';
    import DragZoom from 'ol/interaction/DragZoom';
    import Modify from 'ol/interaction/Modify';
    import PinchRotate from 'ol/interaction/PinchRotate';
    import PinchZoom from 'ol/interaction/PinchZoom';
    import Select from 'ol/interaction/Select';
    import Snap from 'ol/interaction/Snap';

    import Group from 'ol/layer/Group';

    import LayerSwitcher from 'ol-ext/control/LayerSwitcher';

    import '@/../sass/ol-ext-layerswitcher.scss';

    import {
        createNewLayer,
        createOptionBasedStyle,
        createStyle,
        createVectorLayer,
        formatLengthArea,
        registerProjection,
        getGeoJsonFormat,
        getWktFormat,
    } from '@/helpers/map.js';

    import {
        getEntityType,
        getTs,
        translateConcept,
        _throttle,
    } from '@/helpers/helpers.js';

    import {
        toFixed,
    } from '@/helpers/filters.js';

    export default {
        props: {
            selection: {
                type: Number,
                required: false,
                default: null,
            },
            layers: {
                type: Object,
                required: true,
            },
            stylePerLayer: {
                type: Boolean,
                required: false,
                default: true,
            },
            data: {
                type: Object,
                required: false,
                default: () => ({}),
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
            extent: {
                type: Object,
                required: false,
                default: () => ({}),
            },
            provider: {
                type: String,
                required: false,
                default: 'ol',
            },
            triggerDataRescan: {
                type: String,
                required: false,
                default: null,
            },
            titleFn: {
                type: Function,
                required: false,
                default: null,
            },
        },
        emits: [
            'added', 
            'deleted',
            'modified',
            'select',
        ],
        setup(props, context) {
            const { t } = useI18n();

            const {
                selection,
                layers,
                stylePerLayer,
                data,
                projection,
                inputProjection,
                resetEach,
                extent,
                drawing,
                triggerDataRescan,
                titleFn,
            } = toRefs(props);

            // FUNCTIONS
            const setOverlay = feature => {
                const geometry = feature.getGeometry();
                const props = feature.getProperties();
                const coords = getExtentCenter(geometry.getExtent());
                let title = t('main.map.geometry_name', {id: props.id});
                let subtitle = '';
                const sizes = {
                    in_m: 0,
                    unit: '',
                    combined: '',
                };

                if(props.entity) {
                    subtitle = title;
                    title = props.entity_name;
                }

                switch(geometry.getType()) {
                    case 'LineString':
                    case 'MultiLineString':
                        sizes.in_m = getLength(geometry);
                        sizes.unit = 'm';
                        sizes.combined = formatLengthArea(sizes.in_m, 2);
                        break;
                    case 'Polygon':
                    case 'MultiPolygon':
                        sizes.in_m = getArea(geometry);
                        sizes.unit = 'm²';
                        sizes.combined = formatLengthArea(sizes.in_m, 2, true);
                        break;
                }

                actionState.overlay.setPosition(coords);
                actionState.overlayData = {
                    title: title,
                    subtitle: subtitle,
                    size: sizes,
                    feature: feature,
                    type: geometry.getType(),
                    coordinates: geometry.getCoordinates(),
                };

                actionState.bsOverlay.show();
            };
            const transformCoordinates = (c, clist, rev = false) => {
                if(!c[0] || !c[1]) {
                    return null;
                }
                let fromEpsg = rev ? state.epsgCode : 'EPSG:3857';
                let toEpsg = rev ? 'EPSG:3857' : state.epsgCode;
                const transCoord = transformProj(c, fromEpsg, toEpsg);
                const fixedCoord = {
                    x: transCoord[0],
                    y: transCoord[1]
                };
                if(clist) {
                    clist.push(fixedCoord);
                }
                return fixedCoord;
            };
            const getFeaturesAtEvent = (e, tolerance = 5, first = true) => {
                const features = state.map.getFeaturesAtPixel(e.pixel, {
                    hitTolerance: tolerance,
                });

                return features ? (first ? features[0] : features) : null;
            };
            const getEntityExtent = _ => {
                const layers = state.mapLayerGroups.entity.getLayers();
                let entityExtent = null;
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
            };
            const setExtent = (to = null) => {
                const newExtent = to || getEntityExtent() || defaultExtent;

                for(let i=0; i<newExtent.length; i++) {
                    if(newExtent[i] == Infinity || newExtent[i] == -Infinity) {
                        newExtent[i] = defaultExtent[i];
                    }
                }

                state.extent = newExtent;
                state.map.getView().fit(state.extent, {
                    duration: 250,
                    padding: [25, 25, 25, 25],
                    maxZoom: 19, // set max zoom on fit to max osm zoom level
                });
            };
            const getAssociatedLayer = feature => {
                const layerGroup = state.mapLayerGroups.entity.getLayers().getArray();
                if(data.value.format == 'wkt') {
                    for(let i=0; i<layerGroup.length; i++) {
                        if(layerGroup[i].get('layer_id')) {
                            return layerGroup[i];
                        }
                    }
                }
                return null;
            };
            const anyInteractionActive = _ => {
                return drawing.value && (
                    actionState.draw.getActive() ||
                    actionState.modify.getActive() ||
                    actionState.delete.getActive()
                );
            };
            const drawFeature = feature => {
                const layer = getAssociatedLayer(feature);
                let cnt = -1;
                if(layer) {
                    const source = layer.getSource();
                    if(resetEach.value) {
                        if(source.getFeatures().length) {
                            source.clear();
                        }
                    }
                    source.addFeature(feature);
                    cnt = source.getFeatures().length;
                }

                if(actionState.snap) {
                    actionState.snap.addFeature(feature);
                }
                const wktStr = wktFormat.writeFeature(feature, {
                    featureProjection: 'EPSG:3857',
                    dataProjection: state.inputEpsgCode,
                });
                const data = {
                    feature: feature,
                    wkt: wktStr,
                };
                if(cnt > -1) {
                    data.count = cnt;
                }
                context.emit('added', data);
            };
            const initializeLayers = _ => {
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
                            state.mapEntityLayers.push(
                                createVectorLayer({
                                    show: true,
                                    title: layerName,
                                    type: l.type,
                                    visible: l.visible,
                                    opacity: parseFloat(l.opacity),
                                    color: l.color,
                                    layer: 'entity',
                                    layer_id: l.id,
                                })
                            );
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
                    title: t('main.map.entity_layers'),
                    openInLayerSwitcher: true,
                    layers: state.mapEntityLayers,
                });
            };
            const initializeProjections = async _ => {
                // If desired epsg code is default, skip initialization
                if(projection.value != 3857) {
                    await registerProjection(projection.value);
                }

                await registerProjection(inputProjection.value);
            };
            const getLayerForFeature = f => {
                const p = f.getProperties();
                if(p.entity) {
                    const et = Object.values(layers.value).find(l => l.entity_type_id == p.entity_type_id);
                    return Object.values(state.mapEntityLayers).find(l => l.getProperties().layer_id == et.id);
                } else {
                    return Object.values(state.mapEntityLayers).find(l => l.getProperties().type.toLowerCase() == 'unlinked');
                }
            };
            const removeFeatureFromLayer = f => {
                const layer = getLayerForFeature(f);
                if(layer) {
                    layer.getSource().removeFeature(f);
                }
            };
            const getLayerBy = (prop, value) => {
                const layers = [
                    ...state.mapLayerGroups.base.getLayers().getArray(),
                    ...state.mapLayerGroups.overlay.getLayers().getArray(),
                    ...state.mapLayerGroups.entity.getLayers().getArray(),
                ];

                return layers.find(l => l.getProperties()[prop] == value);
            };
            const computeStyleAndLayerForFeature = (f, add = true) => {
                const layer = getLayerForFeature(f);
                let finalStyle = null;
                const layerProps = layer.getProperties();
                const color = layerProps.color;
                
                const p = f.getProperties();
                if(stylePerLayer.value) {
                    if(!state.layerStyleCache[layerProps.layer_id]) {
                        state.layerStyleCache[layerProps.layer_id] = createStyle(color);
                    }

                    finalStyle = state.layerStyleCache[layerProps.layer_id];
                } else {
                    const styleOptions = {
                        forFeature: f,
                    };

                    if(p.style) {
                        styleOptions.symbolStyle = p.style.symbol;
                    }
                    if(p.label) {
                        styleOptions.labelStyle = p.label;
                    }
                    if(p.charts) {
                        console.log('should load charts');
                    }
                    finalStyle = createStyle(color, 2, styleOptions);
                }
                f.setStyle(finalStyle);
                f.setProperties({
                    layer_id: layerProps.layer_id,
                }, true);
                if(add) {
                    layer.getSource().addFeature(f);
                }
                state.featureList[p.id] = f;
            };
            const loadWktData = features => {
                // As WKT can not have any metadata, we do not know which layer this should be displayed on
                // Thus we create a separate layer for it
                const wktLayer = createVectorLayer({
                    show: true,
                    title: t('global.all_entities'),
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
                features.forEach(f => {
                    const feature = geojsonFormat.readFeature(f.geom, {
                        featureProjection: 'EPSG:3857',
                        dataProjection: state.inputEpsgCode,
                    });
                    // TODO confirm
                    feature.setProperties(f.props);
                    // TODO ...
                });
            };
            const loadCollectionData = collection => {
                const features = geojsonFormat.readFeatures(collection, {
                    featureProjection: 'EPSG:3857',
                    dataProjection: state.inputEpsgCode,
                });
                features.forEach(f => {
                    computeStyleAndLayerForFeature(f);
                });
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
                        loadCollectionData(data.value.collection);
                        break;
                }
            };
            const reinitializeData = _ => {
                const layers = [
                    ...state.mapLayerGroups.base.getLayers().getArray(),
                    ...state.mapLayerGroups.overlay.getLayers().getArray(),
                    ...state.mapLayerGroups.entity.getLayers().getArray(),
                ];

                layers.forEach(l => {
                    l.getSource().clear();
                });

                state.featureList = {};

                initializeData();
                setExtent();
            };
            const updateLayerGroups = _ => {
                state.mapLayerGroups.base.setLayers(new Collection(state.mapBaselayers));
                state.mapLayerGroups.overlay.setLayers(new Collection(state.mapOverlays));
                state.mapLayerGroups.entity.setLayers(new Collection(state.mapEntityLayers));
                setExtent();
            };
            const initializeMapEvents = _ => {
                state.map.on('pointermove', _throttle(e => {
                    if(e.dragging) return;
                    if(anyInteractionActive()) return;
                    if(actionState.measure.active) return;

                    const feature = getFeaturesAtEvent(e);
                    if(feature != actionState.hoveredFeature) {
                        // Dispose tooltip from hover popup
                        if(!!actionState.hoveredFeature) {
                            // actionState.bsPopup.hide();
                            actionState.bsPopup.dispose();
                        }
                        if(!feature) {
                            actionState.hoveredFeature = null;
                        }
                    } else {
                        // same feature, no update needed
                        return;
                    }
                    if(!!feature) {
                        actionState.hoveredFeature = feature;
                        const geometry = feature.getGeometry();
                        const props = feature.getProperties();
                        const coords = getExtentCenter(geometry.getExtent());
                        actionState.popup.setPosition(coords);

                        let title = t('main.map.geometry_name', {id: props.id});
                        if(titleFn.value) {
                            const fromFn = titleFn.value(feature);
                            if(fromFn) {
                                title = fromFn;
                            }
                        } else {
                            if(props.entity) {
                                title = `${props.entity_name} (${title})`;
                            }
                        }

                        actionState.bsPopup = new Tooltip(actionState.popup.getElement(), {
                            container: state.viewport || '#map',
                            placement: 'top',
                            animation: true,
                            html: false,
                            title: title,
                        });
                        // actionState.popup.getElement().setAttribute('data-bs-original-title', title);
                        actionState.bsPopup.show();
                    }
                }, 200));
                state.map.on('postrender', _throttle(e => {
                    if(!actionState.bsPopup || !actionState.hoveredFeature) return;

                    actionState.bsPopup.update();
                }, 200));
                state.map.on('singleclick', e => {
                    if(anyInteractionActive()) return;
                    if(actionState.measure.active) return;

                    const feature = getFeaturesAtEvent(e);
                    context.emit('select', feature);
                });
            };
            const getLineLength = geometry => {
                const length = getLength(geometry);
                let str;
                if(length >= 1000) {
                    str = `${(length / 1000).toFixed(2)} km`;
                } else {
                    str = `${length.toFixed(2)} m`;
                }
                return str;
            };
            const initMeasureInteraction = _ => {
                actionState.measure.layer = createVectorLayer({
                    show: false,
                    title: 'Measure Layer',
                    visible: true,
                    opacity: 1,
                    color: '#000000',
                    layer: 'measure',
                    layer_id: 'measure-layer',
                });
                state.map.addLayer(actionState.measure.layer);
                actionState.measure.draw = new Draw({
                    source: actionState.measure.layer.getSource(),
                    type: 'LineString',
                    style: createOptionBasedStyle({
                        default: {
                            color: 'rgba(255, 255, 255, 0.2',
                            width: 2,
                        },
                        fill: {},
                        stroke: {
                            color: 'rgba(0, 0, 0, 0.5',
                            dash: [10, 10],
                        },
                        image: {
                            stroke: {
                                color: 'rgba(0, 0, 0, 0.2',
                            },
                            fill: {},
                        }
                    }),
                });
                actionState.measure.tooltipElement = document.getElementById(actionState.popupIds.m);
                actionState.measure.tooltip = new Overlay({
                    element: actionState.measure.tooltipElement,
                    offset: [0, 5]
                });
                state.map.addOverlay(actionState.measure.tooltip);
            };
            const addMeasureInteraction = _ => {
                state.map.addInteraction(actionState.measure.draw);
                let measureListener;
                actionState.measure.draw.on('drawstart', event => {
                    actionState.measure.tooltipElement.classList.add('show');
                    // Remove existing measures
                    actionState.measure.layer.getSource().clear();
                    let coords = event.coordinate;
                    measureListener = event.feature.getGeometry().on('change', ce => {
                        let geom = ce.target;
                        coords = geom.getLastCoordinate();
                        // actionState.measure.bsPopup.dispose();
                        actionState.measure.tooltip.setPosition(coords);
                        actionState.measure.length = getLineLength(geom);
                    });
                });
                actionState.measure.draw.on('drawend', event => {
                    // actionState.measure.tooltipElement.classList.remove('show');
                    unByKey(measureListener);
                });
            };
            const removeMeasureInteraction = _ => {
                actionState.measure.tooltipElement.classList.remove('show');
                actionState.measure.layer.getSource().clear();
                state.map.removeInteraction(actionState.measure.draw);
            };
            const toggleMeasurements = _ => {
                actionState.measure.active = !actionState.measure.active;

                if(actionState.measure.active) {
                    addMeasureInteraction();
                } else {
                    removeMeasureInteraction();
                }
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
                    uneditedFeatures: {},
                    editedFeatures: {},
                    editCntr: 0,
                    modify: {},
                    select: {},
                    init: _ => {
                        actionState.modify.select = new Select({
                            hitTolerance: 5,
                            toggleCondition: neverCond,
                            wrapX: false
                        });
                        state.map.addInteraction(actionState.modify.select);
                        actionState.modify.modify = new Modify({
                            features: actionState.modify.select.getFeatures(),
                            wrapX: false
                        });
                        state.map.addInteraction(actionState.modify.modify);
                        actionState.modify.setEvents();
                        actionState.modify.editedFeatures = {};
                        actionState.modify.uneditedFeatures = {};
                        actionState.modify.editCntr = 0;
                    },
                    setEvents: _ => {
                        actionState.modify.select.on('select', event => {
                            // config allows only one selected feature
                            if(event.selected.length > 0) {
                                const f = event.selected[0];
                                actionState.modify.editedFeatures[f.ol_uid] = f;
                                if(!actionState.modify.uneditedFeatures[f.ol_uid]) {
                                    actionState.modify.uneditedFeatures[f.ol_uid] = f.clone();
                                    actionState.modify.editCntr++;
                                }
                            }
                        });
                    },
                    getActive: _ => {
                        return actionState.modify.select.getActive() || actionState.modify.modify.getActive();
                    },
                    setActive: (active, cancelled) => {
                        actionState.modify.select.setActive(active);
                        actionState.modify.modify.setActive(active);

                        if(!active && actionState.modify.editCntr > 0) {
                            if(cancelled) {
                                for(let k in actionState.modify.editedFeatures) {
                                    const uneditedGeometry = actionState.modify.uneditedFeatures[k].getGeometry();
                                    actionState.modify.editedFeatures[k].setGeometry(uneditedGeometry);
                                }
                            } else {
                                context.emit('modified', {
                                    features: Object.values(actionState.modify.editedFeatures),
                                });
                            }
                        }

                        actionState.modify.editedFeatures = {};
                        actionState.modify.uneditedFeatures = {};
                        actionState.modify.select.getFeatures().clear();
                        actionState.modify.editCntr = 0;
                    },
                };
                actionState.delete = {
                    deletedFeatures: {},
                    delCntr: 0,
                    init: _ => {
                        actionState.delete.select = new Select({
                            hitTolerance: 5,
                            toggleCondition: neverCond,
                            wrapX: false,
                            style: null,
                        });
                        state.map.addInteraction(actionState.delete.select);
                        actionState.delete.setEvents();
                        actionState.delete.deletedFeatures = {};
                        actionState.delete.delCntr = 0;
                    },
                    setEvents: _ => {
                        actionState.delete.select.on('select', event => {
                            // config allows only one selected feature
                            if(event.selected.length) {
                                const f = event.selected[0];
                                actionState.delete.deletedFeatures[f.ol_uid] = f.clone();
                                actionState.delete.select.getLayer(f)
                                    .getSource()
                                    .removeFeature(f);
                                actionState.delete.select.getFeatures().clear();
                                actionState.delete.delCntr++;
                            }
                        });
                    },
                    getActive: _ => {
                        return actionState.delete.select.getActive();
                    },
                    setActive: (active, cancelled) => {
                        actionState.delete.select.setActive(active);

                        if(!active && actionState.delete.delCntr > 0) {
                            if(cancelled) {
                                // If delete was cancelled, readd deleted features
                                for(let k in actionState.delete.deletedFeatures) {
                                    const delFeature = actionState.delete.deletedFeatures[k];
                                    const fromLayer = getLayerBy('layer_id', delFeature.getProperties().layer_id);
                                    if(fromLayer) {
                                        fromLayer.getSource().addFeature(delFeature);
                                    }
                                }
                            } else {
                                context.emit('deleted', {
                                    features: Object.values(actionState.delete.deletedFeatures),
                                });
                            }
                        }

                        actionState.delete.select.getFeatures().clear();
                        actionState.delete.deletedFeatures = {};
                        actionState.delete.delCntr = 0;
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

                initMeasureInteraction();
                if(actionState.measure.active) {
                    addMeasureInteraction();
                }

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
            };
            const confirmOverlayCoordinateEditing = (confirm, feature) => {
                if(confirm) {
                    const geom = feature.getGeometry();
                    geom.setCoordinates(actionState.overlayCoordinateEdit);
                    feature.setGeometry(geom);
                    actionState.overlayData.coordinates = geom.getCoordinates();
                    context.emit('modified', {
                        features: [feature],
                    });
                } else {
                    // cancel
                }
                actionState.coordinateEditMode = false;
                actionState.overlayCoordinateEdit = [];
            };
            const getFeaturesInExtent = layer => {
                const allLayers = !layer;
                const currentExtent = state.map.getView().calculateExtent(state.map.getSize());
                const extentFeatures = [];
                const layers = allLayers ? state.mapLayerGroups.entity.getLayers() : [layer];
                layers.forEach(lg => {
                    const src = lg.getSource();
                    if(src && !!src.forEachFeatureInExtent) {
                        src.forEachFeatureInExtent(currentExtent, f => {
                            extentFeatures.push(f);
                        });
                    }
                });
                return new Collection(extentFeatures);
            };
            const updateSnap = features => {
                if(!!actionState.snap) {
                    state.map.removeInteraction(actionState.snap);
                }
                if(!!features) {
                    actionState.snap = new Snap({
                        features: features
                    });
                    state.map.addInteraction(actionState.snap);
                }
            };
            const enableOverlayCoordinateEditing = c => {
                actionState.overlayCoordinateEdit = [c.x, c.y];
                actionState.coordinateEditMode = true;
            };
            // Interactions (Draw, Modify, Delete)
            const setInteractionMode = (mode, cancelled, drawMode) => {
                const currMode = actionState.interactionMode;
                actionState.interactionMode = mode;
                // only set snap features, when mode is set and not delete
                // because delete does not need snapping
                if(!!mode && mode != 'delete') {
                    updateSnap(getFeaturesInExtent());
                } else {
                    updateSnap();
                }

                switch(mode) {
                    case 'draw':
                        actionState.draw.setActive(true, drawMode);
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
                    setInteractionMode('draw', true, type);
                } else if(currType == type) {
                    setInteractionMode('');
                }

                if(actionState.draw.getActive()) {
                    actionState.draw.setActive(true, type);
                }
            };
            const updateFeatures = (confirm = true) => {
                if(confirm) {
                    // Update Features
                    setInteractionMode('');
                } else {
                    // Cancel Update
                    setInteractionMode('', true);
                }
            };
            const deleteFeatures = (confirm = true) => {
                if(confirm) {
                    // Delete Features
                    setInteractionMode('');
                } else {
                    // Cancel delete
                    setInteractionMode('', true);
                }
            };
            const postAction = (data, feature) => {
                feature.setProperties(data.properties);
                if(data.recreateStyle && stylePerLayer.value) {
                    removeFeatureFromLayer(feature);
                    computeStyleAndLayerForFeature(feature);
                    setOverlay(feature);
                }
            };

            // DATA
            // EPSG:3857 bounds (taken from epsg.io/3857)
            const defaultExtent = [-20026376.39, -20048966.10, 20026376.39, 20048966.10];
            const wktFormat = getWktFormat();
            const geojsonFormat = getGeoJsonFormat();
            const state = reactive({
                map: null,
                extent: defaultExtent,
                mapId: `interactive-map-container-${getTs()}`,
                mapLayerGroups: {
                    base: null,
                    overlay: null,
                    entity: null,
                },
                mapOverlays: [],
                mapBaselayers: [],
                mapEntityLayers: [],
                layerStyleCache: {},
                featureList: {},
                epsgCode: computed(_ => `EPSG:${projection.value}`),
                inputEpsgCode: computed(_ => `EPSG:${inputProjection.value}`),
                viewport: computed(_ => {
                    if(!state.map) return;
                    const target = state.map.getTarget();
                    if(!target) return;
                    const container = document.getElementById(target);
                    if(!container) return;
                    const viewports = container.getElementsByClassName('ol-viewport');
                    if(!viewports) return;
                    if(!viewports.length) return;
                    return viewports[0];
                }),
                // Can not watch on data itself or array, thus track length
                dataFeatureLength: computed(_ => {
                    if(data.value.format == 'collection') {
                        return data.value.collection.features.length;
                    } else {
                        return data.value.features.length;
                    }
                }),
            });
            const actionState = reactive({
                overlay: null,
                bsOverlay: null,
                popup: null,
                bsPopup: null,
                overlayData: {},
                coordinateEditMode: false,
                overlayCoordinateEdit: [],
                measure: {
                    active: false,
                    layer: null,
                    draw: {},
                    tooltipElement: null,
                    tooltip: null,
                    length: '0m',
                },
                hoveredFeature: null,
                drawType: '',
                interactionMode: '',
                drawingLayer: null,
                draw: {},
                modify: {},
                delete: {},
                snap: {},
                popupIds: computed(_ => {
                    return {
                        p: `${state.mapId}-popup`,
                        h: `${state.mapId}-hover-popup`,
                        m: `${state.mapId}-measure-popup`,
                    }
                }),
                overlayOpen: computed(_ => Object.keys(actionState.overlayData).length > 0),
                overlayIsPoint: computed(_ => {
                    return actionState.overlayOpen && (
                        actionState.overlayData.type == 'Point' ||
                        actionState.overlayData.type == 'MultiPoint'
                    );
                }),
                overlayIsLine: computed(_ => {
                    return actionState.overlayOpen && (
                        actionState.overlayData.type == 'LineString' ||
                        actionState.overlayData.type == 'MultiLineString'
                    );
                }),
                overlayIsPolygon: computed(_ => {
                    return actionState.overlayOpen && (
                        actionState.overlayData.type == 'Polygon' ||
                        actionState.overlayData.type == 'MultiPolygon'
                    );
                }),
                overlayCoordinatesAsList: computed(_ => {
                    if(!actionState.overlayData.coordinates) return [];

                    const cl = actionState.overlayData.coordinates;
                    const coordList = [];

                    switch(actionState.overlayData.type) {
                        case 'Point':
                            transformCoordinates(cl, coordList);
                            break;
                        case 'LineString':
                        case 'MultiPoint':
                            cl.forEach(c => {
                                transformCoordinates(c, coordList);
                            });
                            break;
                        case 'Polygon':
                        case 'MultiLineString':
                            cl.forEach(cg => {
                                cg.forEach(c => {
                                    transformCoordinates(c, coordList);
                                });
                            });
                            break;
                        case 'MultiPolygon':
                            cl.forEach(cg => {
                                cg.forEach(innerCg => {
                                    innerCg.forEach(c => {
                                        transformCoordinates(c, coordList);
                                    });
                                });
                            });
                            break;
                    }

                    return coordList;
                }),
            })

            // ON MOUNTED
            onMounted(async _ => {
                await initializeProjections();

                nextTick(_ => {
                    initializeLayers();
                    initializeData();

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
                        layers: [
                            state.mapLayerGroups.base,
                            state.mapLayerGroups.overlay,
                            state.mapLayerGroups.entity,
                        ],
                        target: state.mapId,
                        view: new View({
                            center: [0, 0],
                            projection: 'EPSG:3857',
                            zoom: 2,
                            extent: state.extent,
                        }),
                    });

                    const overlayElem = document.getElementById(actionState.popupIds.p);
                    const hoverElem = document.getElementById(actionState.popupIds.h);
                    actionState.bsOverlay = new Popover(overlayElem);
                    actionState.bsPopup = new Tooltip(hoverElem, {
                        container: state.viewport || '#map',
                        placement: 'top',
                        animation: true,
                        html: false,
                        title: 'None',
                    });

                    actionState.overlay = new Overlay({
                        element: overlayElem,
                        positioning: 'bottom-center',
                        autoPan: true,
                        autoPanAnimation: {
                            duration: 250,
                        },
                    });
                    actionState.popup = new Overlay({
                        element: hoverElem,
                        positioning: 'bottom-center',
                        autoPan: false,
                    });
                    state.map.addOverlay(actionState.overlay);
                    state.map.addOverlay(actionState.popup);

                    updateLayerGroups();
                    initializeDrawFeatures();
                    initializeMapEvents();
                });
            });

            // WATCHER
            watch(_ => selection.value, (newValue, oldValue) => {
                    const feature = state.featureList[newValue];
                    if(!!feature) {
                        setOverlay(feature);
                    } else {
                        actionState.overlay.setPosition();
                        actionState.bsOverlay.hide();
                        actionState.overlayData = {};
                        // vm.selectedFeature = {};
                    }
            });

            watch(_ => extent.value, (newValue, oldValue) => {
                const layers = [
                    ...state.mapLayerGroups.base.getLayers().getArray(),
                    ...state.mapLayerGroups.overlay.getLayers().getArray(),
                    ...state.mapLayerGroups.entity.getLayers().getArray(),
                ];
                const id = newValue.id;

                let bbox = [];
                switch(newValue.type) {
                    case 'bbox':
                        bbox = newValue.bbox;
                        break;
                    case 'layer':
                        for(let i=0; i<layers.length; i++) {
                            const l = layers[i];
                            const p = l.getProperties();
                            if(p.layer_id == id) {
                                bbox = l.getSource().getExtent();
                                break;
                            }
                        }
                        break;
                    case 'layer_by_feature':
                        let found = false;

                        for(let i=0; i<layers.length; i++) {
                            const l = layers[i];
                            const s = l.getSource();
                            const features = s.getFeatures();
                            for(let j=0; j<features.length; j++) {
                                const f = features[j];
                                if(f.getProperties().id == id) {
                                    bbox = s.getExtent();
                                    found = true;
                                    break;
                                }
                            }
                            if(found) {
                                break;
                            }
                        }
                        break;
                    default:
                        // only bbox or layer type is supported
                        return;
                }

                if(!bbox || bbox.length == 0) {
                    return;
                }

                setExtent(bbox);
            });

            watch(_ => state.dataFeatureLength, (newValue, oldValue) => {
                reinitializeData();
            });

            watch(_ => triggerDataRescan.value, (newValue, oldValue) => {
                reinitializeData();
            });

            // RETURN
            return {
                t,
                // HELPERS
                toFixed,
                // LOCAL
                confirmOverlayCoordinateEditing,
                enableOverlayCoordinateEditing,
                toggleMeasurements,
                setInteractionMode,
                toggleDrawType,
                updateFeatures,
                deleteFeatures,
                postAction,
                // STATE
                state,
                actionState,
            }
            
        },
    }
</script>
