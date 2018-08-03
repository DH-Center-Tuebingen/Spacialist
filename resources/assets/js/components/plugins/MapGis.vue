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
                        <a href="#" class="list-group-item list-group-item-action d-flex flex-row justify-content-between align-items-center" v-for="l in selectedLayers" @click.prevent="" @dblclick.prevent="removeLayerFromSelection(l)" @contextmenu.prevent="$refs.layerMenu.open($event, {layer: l})">
                            <span>
                                {{ getName(l) }}
                            </span>
                            <span class="badge badge-primary" v-if="showFeatureCounts[l.id]">
                                {{ getFeatureCount(l) }}
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <ol-map
                init-projection="EPSG:4326"
                :draw-disabled="true"
                :epsg="epsg"
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
            getFeatureCount(layer) {
                const lg = this.layerGeometries[layer.id];
                return lg ? lg.length : 0;
            },
            toggleFeatureCount(layer) {
                if(this.showFeatureCounts[layer.id]) {
                    Vue.delete(this.showFeatureCounts, layer.id);
                } else {
                    Vue.set(this.showFeatureCounts, layer.id, true);
                }
            },
            exportLayer(layer, type) {
                const srid = this.epsg.epsg;
                let url = `map/export/${layer.id}?srid=${srid}`;
                if(type) {
                    url += `&type=${type}`;
                }
                $http.get(url).then(response => {
                    let ext;
                    switch(type) {
                        case 'csv':
                        case 'wkt':
                            ext = 'csv';
                            break;
                        case 'kml':
                            ext = 'kml';
                            break;
                        case 'kmz':
                            ext = 'kmz';
                            break;
                        case 'gml':
                            ext = 'gml';
                            break;
                        case 'geojson':
                            ext = 'json';
                            break;
                        default:
                            ext = 'json';
                            break;
                    }
                    this.$createDownloadLink(response.data, `export-${srid}.${ext}`, true, response.headers['content-type']);
                });
            },
            addLayerToSelection(layer) {
                Vue.set(this.selectedLayers, layer.id, Object.assign({}, layer));

                $http.get(`map/layer/${layer.id}/geometry`).then(response => {
                    let newGeometries = [];
                    response.data.forEach(g => {
                        const geoObject = {
                            geom: g.geom,
                            props: this.getProperties(g)
                        };
                        newGeometries.push(geoObject);
                    });
                    Vue.set(this.layerGeometries, layer.id, newGeometries);
                });
            },
            removeLayerFromSelection(layer) {
                Vue.delete(this.selectedLayers, layer.id);
                Vue.delete(this.layerGeometries, layer.id);
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
                epsg: this.$getPreference('prefs.map-projection'),
                layers: [],
                selectedLayers: {},
                showFeatureCounts: {},
                mapLayers: {},
                layerGeometries: {},
                importModalId: 'map-gis-import-modal',
                contextMenu: [
                    {
                        label: 'Zoom to layer',
                        iconClasses: 'fas fa-fw fa-search-plus',
                        iconContent: '',
                        callback: layer => {
                            //
                        }
                    },
                    {
                        label: 'Export layer',
                        iconClasses: 'fas fa-fw fa-download',
                        iconContent: '',
                        callback: layer => this.exportLayer(layer)
                    },
                    {
                        label: 'Toggle feature count',
                        iconClasses: 'fas fa-fw fa-calculator',
                        iconContent: '',
                        callback: layer => this.toggleFeatureCount(layer)
                    },
                    {
                        label: 'Properties',
                        iconClasses: 'fas fa-fw fa-sliders-h',
                        iconContent: '',
                        callback: layer => {
                            //
                        }
                    },
                ]
            }
        },
        computed: {
            mergedLayers: function() {
                return { ...this.selectedLayers, ...this.mapLayers };
            },
            geometries: function() {
                return Object.values(this.layerGeometries).flat();
            }
        }
    }
</script>
