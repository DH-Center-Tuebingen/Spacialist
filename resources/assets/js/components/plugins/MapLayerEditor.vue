<template>
    <div class="row h-100" v-if="initFinished">
        <div class="col-md-3 h-100 d-flex flex-column">
            <h5>Baselayer</h5>
            <layer-list
                class="flex-grow-1 scroll-y-auto"
                :add-new="_ => onAddNewLayer(false)"
                :layer="baselayer"
                :on-select="onLayerSelect">
            </layer-list>
            <h5 class="mt-3">Overlays</h5>
            <layer-list
                class="flex-grow-1 scroll-y-auto"
                :add-new="_ => onAddNewLayer(true)"
                :layer="overlays"
                :on-select="onLayerSelect">
            </layer-list>
        </div>
        <div class="col-md-6 h-100">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
    export default {
        beforeRouteEnter(to, from, next) {
            $http.get('map/layer').then(response => {
                next(vm => vm.init(response.data));
            });
        },
        mounted() {},
        methods: {
            init(layers) {
                this.initFinished = false;
                this.baselayer = layers.baselayers;
                this.overlays = layers.overlays;
                this.initFinished = true;
            },
            onAddNewLayer(is_overlay) {
                const vm = this;
                const data = {
                    name: 'Unnamed Layer',
                    is_overlay: is_overlay
                };
                vm.$http.post('/map/layer', data).then(function(response) {
                    if(is_overlay) vm.overlays.push(response.data);
                    else vm.baselayer.push(response.data);
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            onLayerSelect(layer) {
                this.$router.push({
                    name: 'ldetail',
                    params: {
                        id: layer.id
                    }
                });
            },
        },
        data() {
            return {
                initFinished: false,
                baselayer: [],
                overlays: []
            }
        }
    }
</script>
