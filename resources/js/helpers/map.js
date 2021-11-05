import { getMapLayers, getMapProjection } from '../api';

import proj4 from 'proj4';
import {
    get as getProjection,
    addProjection,
} from 'ol/proj';
import {
    register as registerProj
} from 'ol/proj/proj4';

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

import { rgb2hex } from './helpers';

export function formatLengthArea(value, precision = 2, isArea = false) {
    if(!value) return value;

    const length = parseFloat(value);

    if(!isFinite(value) || isNaN(length)) {
        return length;
    }
    let unit;
    let factor;
    if(isArea) {
        if(length < 0.00001) {
            unit = 'mm²';
            factor = 100000;
        } else if(length < 0.01) {
            unit = 'cm²';
            factor = 10000;
        } else if(length < 100) {
            unit = 'm²';
            factor = 1;
        } else if(length < 100000) {
            unit = 'ha';
            factor = 0.0001;
        } else {
            unit = 'km²';
            factor = 0.000001;
        }
    } else {
        if(length < 0.01) {
            unit = 'mm';
            factor = 1000;
        } else if(length < 1) {
            unit = 'cm';
            factor = 100;
        } else if(length < 1000) {
            unit = 'm';
            factor = 1;
        } else {
            unit = 'km';
            factor = 0.001;
        }
    }

    const sizeInUnit = length * factor;
    return `${sizeInUnit.toFixed(precision)} ${unit}`;
};

export function createStyle(color = '#ffcc33', width = 2) {
    let polygonFillColor;
    let r, g, b, a;
    const fillAlphaMultiplier = 0.2;
    if(color.startsWith('#')) {
        [r, g, b] = rgb2hex(color);
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
};

export async function getLayers() {
    const data = await getMapLayers();
    const layers = {};
    for(let i=0; i<data.baselayers.length; i++) {
        const l = data.baselayers[i];
        layers[l.id] = l;
    }
    for(let i=0; i<data.overlays.length; i++) {
        const l = data.overlays[i];
        layers[l.id] = l;
    }
    return layers;
}

export function createNewLayer(layerData) {
    const isBaseLayer = !layerData.is_overlay;
    const isVisible = layerData.visible;
    const opacity = parseFloat(layerData.opacity);
    const url = layerData.url;
    const attribution = layerData.attribution;
    const apiKey = layerData.api_key;
    const layers = layerData.layers;
    const layerType = layerData.layer_type;
    let source;
    
    switch(layerData.type) {
        case 'xyz':
            source = new TileImage({
                url: url,
                attributions: attribution,
                wrapX: false,
            });
            break;
        case 'wms':
            source = new TileWMS({
                url: url,
                attributions: attribution,
                wrapX: false,
                serverType: 'geoserver',
                params: {
                    layers: layers,
                    tiled: true,
                },
            });
            break;
        case 'bing':
            source = new BingMaps({
                key: apiKey,
                wrapX: false,
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
                imagerySet: layerType,
            })
            break;
        default:
            source = new OSM({
                wrapX: false,
            });
            break;
    }

    return new TileLayer({
        layer: 'osm',
        title: layerData.name,
        baseLayer: isBaseLayer,
        displayInLayerSwitcher: true,
        visible: isVisible,
        opacity: opacity,
        source: source
    });
};

export function createVectorLayer(data = {}) {
    return new VectorLayer({
        baseLayer: false,
        displayInLayerSwitcher: !!data.show,
        title: data.title || 'Draw Layer',
        type: data.type || 'undefined',
        visible: !!data.visible,
        opacity: data.opacity ? parseFloat(data.opacity) : 0,
        color: data.color,
        layer: data.layer || 'draw',
        layer_id: data.layer_id,
        source: new Vector({
            wrapX: false
        }),
        style: createStyle(),
    });
};

export async function registerProjection(srid) {
    // Only register projection, if different from included projections
    if(srid == 4326 || srid == 3857) {
        return new Promise(r => r(epsg));
    }
    const epsg = `EPSG:${srid}`;
    getMapProjection(srid).then(data => {
        proj4.defs(epsg, data.proj4text);
        registerProj(proj4);
        addProjection(getProjection(epsg));
        return new Promise(r => r(epsg));
    });
};