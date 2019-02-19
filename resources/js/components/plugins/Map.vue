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
            @update:link="(geoId, entityId) => $emit('update:link', geoId, entityId)"
            @feature-selected="handleFeatureSelection">
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
                this.epsg = this.$getPreference('prefs.map-projection');
                this.dataInitialized = false;
                $httpQueue.add(() => $http.get('/map').then(response => {
                    const mapData = response.data;
                    // empty objects returned as json by php are []
                    // thus if no layers exist, set to real empty {}
                    if(Array.isArray(mapData.layers) && !mapData.layers.length) {
                        this.layers = {};
                    } else {
                        this.layers = mapData.layers;
                    }
                    this.geodata = mapData.geodata;
                    for(let k in this.geodata) {
                        const curr = this.geodata[k];
                        let geo = {
                            geom: curr.geom,
                            props: this.getProperties(curr)
                        };
                        this.geojson.push(geo);
                    }
                    this.dataInitialized = true;
                }));
            },
            getProperties(geodata) {
                return {
                    id: geodata.id,
                    entity: geodata.entity
                };
            },
            deleteFeatures(features, wkt) {
                features.forEach(f => {
                    $httpQueue.add(() => $http.delete(`/map/${f.getProperties().id}`).then(response => {

                    }));
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
            },
            handleFeatureSelection(e) {
                const p = e.properties;
                if(p.entity) {
                    this.$router.push({
                        append: true,
                        name: 'entitydetail',
                        params: {
                            id: p.entity.id
                        },
                        query: this.$route.query
                    });
                }
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
