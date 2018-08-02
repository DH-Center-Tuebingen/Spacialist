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
                        <a href="#" class="list-group-item list-group-item-action" v-for="l in layers" @click.prevent="" @dblclick.prevent="addLayerToSelection(l)" @contextmenu.prevent="">
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
                        <a href="#" class="list-group-item list-group-item-action" v-for="l in selectedLayers" @click.prevent="" @dblclick.prevent="removeLayerFromSelection(l)" @contextmenu.prevent="$refs.layerMenu.open($event, {layer: l})">
                            {{ getName(l) }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <ol-map
                init-projection="EPSG:4326"
                :draw-disabled="true"
                :init-geojson="geometries"
                :layers="mergedLayers">
            </ol-map>
        </div>

        <map-gis-import-modal
            :id="importModalId"
            :layers="mapLayers">
        </map-gis-import-modal>

        <vue-context ref="layerMenu" class="context-menu-wrapper">
            <ul class="list-group list-group-vue-context" slot-scope="_">
                <li class="list-group-item list-group-item-vue-context" v-for="entry in contextMenu" @click.prevent="entry.callback(_.data.layer)">
                    <i :class="entry.iconClasses">{{entry.iconContent}}</i> {{entry.label}}
                </li>
            </ul>
        </vue-context>
    </div>
</template>

<script>
    Vue.component('map-gis-import-modal', require('./MapGisImportModal.vue'));
    import { VueContext } from 'vue-context';

    export default {
        components: {
            VueContext
        },
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
                const data = {
                    layers: Object.keys(this.selectedLayers)
                };
                $http.post('map/geometry/layer', data).then(response => {
                    let newGeometries = [];
                    response.data.forEach(g => {
                        const geoObject = {
                            geom: g.geom,
                            props: this.getProperties(g)
                        };
                        newGeometries.push(geoObject);
                    });
                    this.geometries = newGeometries;
                });
            },
            removeLayerFromSelection(layer) {
                Vue.delete(this.selectedLayers, layer.id);
                const data = {
                    layers: Object.keys(this.selectedLayers)
                };
                $http.post('map/geometry/layer', data).then(response => {
                    let newGeometries = [];
                    response.data.forEach(g => {
                        const geoObject = {
                            geom: g.geom,
                            props: this.getProperties(g)
                        };
                        newGeometries.push(geoObject);
                    });
                    this.geometries = newGeometries;
                });
            },
            getProperties(geodata) {
                return {
                    id: geodata.id,
                    entity: geodata.context
                };
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
                geometries: [],
                importModalId: 'map-gis-import-modal',
                contextMenu: [
                    {
                        label: 'Zoom to layer',
                        iconClasses: 'fas fa-fw fa-search-plus',
                        iconContent: '',
                        callback: function(layer) {
                            //
                        }
                    },
                    {
                        label: 'Export layer',
                        iconClasses: 'fas fa-fw fa-download',
                        iconContent: '',
                        callback: function(layer) {
                            //
                        }
                    },
                    {
                        label: 'Toggle feature count',
                        iconClasses: 'fas fa-fw fa-calculator',
                        iconContent: '',
                        callback: function(layer) {
                            //
                        }
                    },
                    {
                        label: 'Properties',
                        iconClasses: 'fas fa-fw fa-sliders-h',
                        iconContent: '',
                        callback: function(layer) {
                            //
                        }
                    },
                ]
            }
        },
        computed: {
            mergedLayers: function() {
                return { ...this.selectedLayers, ...this.mapLayers };
            }
        }
    }
</script>
