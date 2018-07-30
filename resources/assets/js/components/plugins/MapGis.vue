<template>
    <div class="h-100 row of-hidden" v-if="initFinished">
        <div class="col-md-2 h-100 d-flex flex-column">
            <button type="button" class="btn btn-default" @click.prevent="openImportModal">
                <i class="fas fa-fw fa-download"></i> Import Geodata
            </button>
            <div class="col d-flex flex-column h-100 pt-2">
                <div class="col of-hidden d-flex flex-column">
                    <h4>Available Layers</h4>
                    <div class="list-group scroll-y-auto col">
                        <a href="#" class="list-group-item list-group-item-action" v-for="l in layers" @click.prevent="" @dblclick.prevent="addLayerToSelection(l)">
                            {{ getName(l) }}
                        </a>
                    </div>
                </div>
                <div class="col of-hidden d-flex flex-column pt-2">
                    <h4>Selected Layers</h4>
                    <p class="alert alert-info" v-show="!selectedLayers.length">
                        Use <kbd>Double Click</kbd> to add an <i>Available Layer</i> and again to remove it.
                    </p>
                    <div class="list-group scroll-y-auto col">
                        <a href="#" class="list-group-item list-group-item-action" v-for="l in selectedLayers" @click.prevent="" @dblclick.prevent="removeLayerFromSelection(l)">
                            {{ getName(l) }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <ol-map
                :layers="mergedLayers">
            </ol-map>
        </div>

        <map-gis-import-modal
            :id="importModalId"
            :layers="mapLayers">
        </map-gis-import-modal>
    </div>
</template>

<script>
    Vue.component('map-gis-import-modal', require('./MapGisImportModal.vue'));

    export default {
        beforeRouteEnter(to, from, next) {
            let mapLayers;
            $http.get('map/layer?basic=true&d=true').then(response => {
                mapLayers = response.data;
                return $http.get('map/layer/entity')
            }).then(response => {
                next(vm => vm.init(mapLayers, response.data));
            });
        },
        mounted() {},
        methods: {
            init(mapLayers, entityLayers) {
                this.initFinished = false;
                this.layers = entityLayers;
                this.mapLayers = { ...mapLayers.baselayers, ...mapLayers.overlays };
                this.initFinished = true;
            },
            getName(layer) {
                if(layer.name) {
                    return layer.name
                }
                if(layer.context_type) {
                    return this.$translateConcept(layer.context_type.thesaurus_url);
                }
                return 'No Title';
            },
            addLayerToSelection(layer) {
                Vue.set(this.selectedLayers, layer.id, Object.assign({}, layer));
            },
            removeLayerFromSelection(layer) {
                Vue.delete(this.selectedLayers, layer.id);
            },
            openImportModal() {
                this.$modal.show(this.importModalId);
            }
        },
        data() {
            return {
                initFinished: false,
                layers: [],
                selectedLayers: {},
                mapLayers: {},
                importModalId: 'map-gis-import-modal'
            }
        },
        computed: {
            mergedLayers: function() {
                return { ...this.selectedLayers, ...this.mapLayers };
            }
        }
    }
</script>
