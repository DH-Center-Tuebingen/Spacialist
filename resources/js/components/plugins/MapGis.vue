<template>
    <div class="h-100 row overflow-hidden" v-if="initFinished">
        <div class="col-md-2 h-100 d-flex flex-column">
            <button type="button" class="btn btn-outline-secondary" @click.prevent="openImportModal">
                <i class="fas fa-fw fa-download"></i> {{ $t('plugins.map.gis.import.button') }}
            </button>
            <div class="col d-flex flex-column h-100 pt-2">
                <div class="col overflow-hidden d-flex flex-column">
                    <h4>
                        {{ $t('plugins.map.gis.available-layers') }}
                    </h4>
                    <div class="list-group scroll-y-auto col">
                        <a href="#" class="list-group-item list-group-item-action" v-for="l in layers" @click.prevent="" @dblclick.prevent="addLayerToSelection(l)" @contextmenu.prevent="">
                            {{ getName(l) }}
                        </a>
                    </div>
                </div>
                <div class="col overflow-hidden d-flex flex-column pt-2">
                    <h4>{{ $t('plugins.map.gis.selected-layers') }}</h4>
                    <p class="alert alert-info" v-show="!Object.keys(selectedLayers).length" v-html="$t('plugins.map.gis.info')">

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
                :layers="mergedLayers"
                :layer-labels="labels"
                :layer-styles="styles"
                :zoom-to="zoomLayerId">
            </ol-map>
        </div>

        <map-gis-import-modal
            :id="importModalId"
            :layers="mapLayers">
        </map-gis-import-modal>

        <map-gis-properties-modal
            :id="propertiesModalId"
            :on-update="applyPropertyModifications">
        </map-gis-properties-modal>

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
    import MapGisImportModal from './MapGisImportModal.vue';
    import MapGisPropertiesModal from './MapGisPropertiesModal.vue';
    import { VueContext } from 'vue-context';

    export default {
        components: {
            VueContext,
            'map-gis-import-modal': MapGisImportModal,
            'map-gis-properties-modal': MapGisPropertiesModal
        },
        beforeRouteEnter(to, from, next) {
            let mapLayers;
            $httpQueue.add(() => $http.get('map/layer?basic=true&d=true').then(response => {
                mapLayers = response.data;
                return $http.get('map/layer/entity')
            }).then(response => {
                next(vm => vm.init(mapLayers, response.data));
            }));
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
                if(layer.entity_type) {
                    return this.$translateConcept(layer.entity_type.thesaurus_url);
                }
                return this.$t('plugins.map.untitled');
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
            applyPropertyModifications(layer, options) {
                switch(options.type) {
                    case 'labeling':
                        if(!Object.keys(options.data).length) {
                            delete this.tmpLabels[layer.id];
                        } else {
                            this.tmpLabels[layer.id] = options.data;
                        }
                        Vue.set(this, 'labels', {...this.tmpLabels});
                        break;
                    case 'styling':
                        if(!Object.keys(options.data).length) {
                            delete this.tmpStyles[layer.id];
                        } else {
                            this.tmpStyles[layer.id] = options.data;
                        }
                        Vue.set(this, 'styles', {...this.tmpStyles});
                        break;
                }
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
                delete this.tmpLabels[layer.id];
                delete this.tmpStyles[layer.id];
            },
            getProperties(geodata) {
                return {
                    id: geodata.id,
                    entity: geodata.entity
                };
            },
            openImportModal() {
                this.$modal.show(this.importModalId);
            },
            openPropertiesModal(layer) {
                this.$modal.show(this.propertiesModalId, {
                    layer: layer
                });
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
                labels: {},
                tmpLabels: {},
                styles: {},
                tmpStyles: {},
                zoomLayerId: 0,
                importModalId: 'map-gis-import-modal',
                propertiesModalId: 'map-gis-properties-modal',
                contextMenu: [
                    {
                        label: this.$t('plugins.map.gis.menu.zoom-to'),
                        iconClasses: 'fas fa-fw fa-search-plus',
                        iconContent: '',
                        callback: layer => {
                            this.zoomLayerId = layer.id;
                        }
                    },
                    {
                        label: this.$t('plugins.map.gis.menu.export-layer'),
                        iconClasses: 'fas fa-fw fa-download',
                        iconContent: '',
                        callback: layer => this.exportLayer(layer)
                    },
                    {
                        label: this.$t('plugins.map.gis.menu.toggle-feature'),
                        iconClasses: 'fas fa-fw fa-calculator',
                        iconContent: '',
                        callback: layer => this.toggleFeatureCount(layer)
                    },
                    {
                        label: this.$t('plugins.map.gis.menu.properties'),
                        iconClasses: 'fas fa-fw fa-sliders-h',
                        iconContent: '',
                        callback: layer => this.openPropertiesModal(layer)
                    },
                ]
            }
        },
        computed: {
            mergedLayers: function() {
                return { ...this.selectedLayers, ...this.mapLayers };
            },
            geometries: function() {
                return [].concat(...Object.values(this.layerGeometries));
            }
        }
    }
</script>
