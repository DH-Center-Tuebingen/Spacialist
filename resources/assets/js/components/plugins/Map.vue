<template>
    <div class="h-100" v-if="dataInitialized">
        <ol-map
            :epsg="epsg"
            :init-geojson="geojson"
            :layers="layers"
            :on-deleteend="deleteFeatures"
            :on-drawend="addFeature"
            :on-modifyend="updateFeatures"
            :reset="false"
            :selected-entity="context">
        </ol-map>
    </div>
    <div v-else>
        Loading map data&hellip;
    </div>
</template>

<script>
    import GeoJSON from 'ol/format/geojson';

    export default {
        props: {
            context: {
                type: Object,
                required: true
            },
            preferences: {
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
                vm.epsg = vm.preferences['prefs.map-projection'];
                vm.dataInitialized = false;
                vm.$http.get('/api/map').then(function(response) {
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
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            getProperties(geodata) {
                return {
                    id: geodata.id,
                    entity: geodata.context
                };
            },
            deleteFeatures(features, wkt) {
                const vm = this;
                features.forEach(f => {
                    vm.$http.delete(`/api/map/${f.getProperties().id}`).then(function(response) {

                    }).catch(function(error) {
                        vm.$throwError(error);
                    });
                });
            },
            addFeature(feature, wkt) {
                const vm = this;
                const collection = vm.geoJsonFormat.writeFeatures([feature]);
                const srid = 3857;
                const data = {
                    collection: collection,
                    srid: srid
                };
                vm.$http.post('/api/map', data).then(function(response) {
                    if(response.data.length) {
                        const geodata = response.data[0];
                        // TODO update feature
                        feature.setProperties(vm.getProperties(geodata));
                    }
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            updateFeatures(features, wkt) {
                const vm = this;
                features.forEach(f => {
                    let data = {
                        feature: vm.geoJsonFormat.writeFeature(f),
                        srid: 3857
                    };
                    vm.$http.patch(`/api/map/${f.getProperties().id}`, data).then(function(response) {

                    }).catch(function(error) {
                        vm.$throwError(error);
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
