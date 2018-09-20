<template>
    <div class="h-100" v-if="dataInitialized">
        <ol-map
            :epsg="epsg"
            :init-geojson="geojson"
            :init-projection="'EPSG:4326'"
            :layers="layers"
            :event-bus="eventBus"
            :on-deleteend="deleteFeatures"
            :on-drawend="addFeature"
            :on-modifyend="updateFeatures"
            :reset="false"
            :selected-entity="selectedEntity"
            v-on:update:link="(geoId, entityId) => $emit('update:link', geoId, entityId)">
        </ol-map>
    </div>
    <div v-else v-html="$t('plugins.map.tab.loading')">
    </div>
</template>

<script>
    import GeoJSON from 'ol/format/GeoJSON';

    export default {
        props: {
            selectedEntity: {
                type: Object,
                required: true
            },
            eventBus: {
                type: Object,
                required: true
            }
        },
        mounted() {
            this.initData();
        },
        methods: {
            initData() {
                const vm = this;
                vm.epsg = vm.$getPreference('prefs.map-projection');
                vm.dataInitialized = false;
                vm.$http.get('/map').then(function(response) {
                    const mapData = response.data;
                    vm.layers = mapData.layers;
                    vm.geodata = mapData.geodata;
                    for(let k in vm.geodata) {
                        const curr = vm.geodata[k];
                        let geo = {
                            geom: curr.geom,
                            props: vm.getProperties(curr)
                        };
                        vm.geojson.push(geo);
                    }
                    vm.dataInitialized = true;
                });
            },
            getProperties(geodata) {
                return {
                    id: geodata.id,
                    entity: geodata.entity
                };
            },
            deleteFeatures(features, wkt) {
                const vm = this;
                features.forEach(f => {
                    vm.$http.delete(`/map/${f.getProperties().id}`).then(function(response) {

                    });
                });
            },
            addFeature(feature, wkt) {
                const collection = this.geoJsonFormat.writeFeatures([feature]);
                const srid = 3857;
                const data = {
                    collection: collection,
                    srid: srid
                };
                return $http.post('/map', data).then(response => {
                    if(response.data.length) {
                        const geodata = response.data[0];
                        // TODO update feature
                        feature.setProperties(this.getProperties(geodata));
                        return feature;
                    } else {
                        return;
                    }
                });
            },
            updateFeatures(features, wkt) {
                const vm = this;
                features.forEach(f => {
                    let data = {
                        feature: vm.geoJsonFormat.writeFeature(f),
                        srid: 3857
                    };
                    vm.$http.patch(`/map/${f.getProperties().id}`, data).then(function(response) {

                    });
                });
            }
        },
        data() {
            return {
                dataInitialized: false,
                epsg: {},
                layers:{},
                geodata: {},
                geojson: [],
                geoJsonFormat: new GeoJSON()
            }
        }
    }
</script>
