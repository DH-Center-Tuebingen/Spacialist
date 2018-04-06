<template>
    <div class="h-100" v-if="dataInitialized">
        <ol-map
            :reset="false"
            :init-geojson="geojson">
        </ol-map>
    </div>
    <div v-else>
        Loading map data&hellip;
    </div>
</template>

<script>
    export default {
        mounted() {
            this.initData();
        },
        methods: {
            initData() {
                const vm = this;
                vm.dataInitialized = false;
                vm.$http.get('/api/map').then(function(response) {
                    const mapData = response.data;
                    let geom;
                    for(let k in mapData.geodata) {
                        geom = mapData.geodata[k].geom;
                        vm.geojson.push(geom);
                    }
                    vm.dataInitialized = true;
                });
            }
        },
        data() {
            return {
                dataInitialized: false,
                geojson: []
            }
        }
    }
</script>
