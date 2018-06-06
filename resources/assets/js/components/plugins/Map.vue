<template>
    <div class="h-100" v-if="dataInitialized">
        <ol-map
            :epsg="epsg"
            :init-geojson="geojson"
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
            concepts: {
                type: Object,
                required: true
            },
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
                    vm.contextTypes = mapData.contextTypes;
                    vm.contexts = mapData.contexts;
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
                const vm = this;
                let props = {
                    id: geodata.id,
                    entity: geodata.context
                };
                let layer;
                if(geodata.context) {
                    layer = vm.getLayer(geodata.context.context_type_id);
                    props.layer_id = layer.context_type_id;
                    const ct = vm.getContextType(geodata.context);
                    if(ct) {
                        props.layer_name = vm.$translateConcept(vm.concepts, ct.thesaurus_url);
                    }
                } else {
                    layer = vm.getUnlinkedLayer();
                    props.layer_id = layer.id;
                    props.layer_name = 'Unlinked';
                }
                if(layer) {
                    props.color = layer.color;
                }
                return props;
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
                return this.contextTypes[context.context_type_id];
            },
            deleteFeatures(features, wkt) {
                const vm = this;
                features.forEach(f => {
                    vm.$http.delete(`/api/map/${f.getProperties().id}`);
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
                        // feature.setProperties(vm.getProperties(geodata));
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
                    vm.$http.patch(`/api/map/${f.getProperties().id}`, data);
                });
            }
        },
        data() {
            return {
                dataInitialized: false,
                epsg: {},
                layers:{},
                contextTypes: {},
                contexts: {},
                geodata: {},
                geojson: [],
                geoJsonFormat: new GeoJSON()
            }
        }
    }
</script>
