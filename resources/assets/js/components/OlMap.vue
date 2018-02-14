<template>
    <div id="map" class="map"></div>
</template>

<script>
    import 'ol/ol.css';
    import Map from 'ol/map';
    import View from 'ol/view';
    import TileLayer from 'ol/layer/tile';
    import OSM from 'ol/source/osm';
    import control from 'ol/control';

    export default {
        mounted() {
            // wait for DOM to be rendered
            this.$nextTick(function() {
                new Map({
                    controls: control.defaults(),
                    layers: [
                        new TileLayer({
                            source: new OSM()
                        })
                    ],
                    target: 'map',
                    view: new View({
                        center: [0, 0],
                        zoom: 2
                    })
                });
            });
        }
    }
</script>
