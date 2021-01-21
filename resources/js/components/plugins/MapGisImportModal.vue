<template>
    <modal :name="id" width="80%" height="85%" classes="of-visible">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ $t('plugins.map.gis.import.title') }}
                </h5>
                <button type="button" class="close" aria-label="Close" @click.prevent="hide">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body row col">
                <div class="col-md-2 h-100">
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item">
                            <a class="nav-link" :class="{active: activeTab == 'csv'}" @click.prevent="setActiveTab('csv')" href="#">CSV</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{active: activeTab == 'kml'}" @click.prevent="setActiveTab('kml')" href="#">KML/KMZ</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{active: activeTab == 'shape'}" @click.prevent="setActiveTab('shape')" href="#">Shape</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{active: activeTab == 'geojson'}" @click.prevent="setActiveTab('geojson')" href="#">GeoJSON</a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-10 h-100 d-flex flex-column">
                    <file-upload
                        class="w-100"
                        id="map-gis-file-upload"
                        post-action="map"
                        ref="upload"
                        v-model="files"
                        :accept="allowedMimeTypes"
                        :directory="false"
                        :drop="true"
                        :extensions="allowedExtensions"
                        :multiple="true"
                        @input-file="onFileInput">
                        <span class="btn btn-outline-primary text-center">
                            <i class="fas fa-fw fa-file-import"></i> {{ $t('plugins.map.gis.import.files.button') }}
                        </span>
                    </file-upload>
                    <div v-if="files.length" class="text-start w-25">
                        <h5>
                            {{ $t('plugins.map.gis.import.files.selected') }}
                            <small class="clickable" @click="showFileList = !showFileList">
                                <span v-show="showFileList">
                                    <i class="fas fa-fw fa-caret-up"></i>
                                </span>
                                <span v-show="!showFileList">
                                    <i class="fas fa-fw fa-caret-down"></i>
                                </span>
                            </small>
                        </h5>
                        <ul class="list-group" v-show="showFileList">
                            <li class="list-group-item px-2 py-1" v-for="file in files">
                                <span>{{ file.name }}</span>
                                <button type="button" class="close" aria-label="Remove File" @click="onFileRemove(file)">
                                    <span aria-hidden="true" style="vertical-align: text-top;">&times;</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div v-if="files.length">
                        <hr />
                    </div>
                    <component v-if="files.length"
                        :epsg="'EPSG:'+mapEpsg.epsg"
                        :is="activeImportType"
                        :files="files"
                        :after-parse="onParsedResults">
                    </component>
                    <div v-if="mapLayers && parsed">
                        <hr />
                        <form class="form-inline mb-3">
                            <div class="form-group me-2 overlay-all">
                                <label for="root_element" class="sr-only">
                                    {{ $t('plugins.map.gis.import.metadata.root_element') }}
                                </label>
                                <entity-search
                                    v-validate=""
                                    id="root_element"
                                    name="root_element"
                                    placeholder="plugins.map.gis.import.metadata.root_element"
                                    :on-select="selection => metadata.root_element = selection"
                                    :value="metadata.root_element.name">
                                </entity-search>
                            </div>
                            <div class="form-group me-2 overlay-all">
                                <label for="type" class="sr-only">
                                    {{ $t('plugins.map.gis.import.metadata.entity_type') }}
                                </label>
                                <entity-type-search
                                    v-validate=""
                                    id="type"
                                    name="type"
                                    placeholder="plugins.map.gis.import.metadata.entity_type"
                                    :on-select="selection => metadata.type = selection"
                                    :value="$translateConcept(metadata.type.thesaurus_url)">
                                </entity-type-search>
                            </div>
                            <div class="form-group me-2 overlay-all">
                                <label for="name_column" class="sr-only">
                                    {{ $t('plugins.map.gis.import.metadata.name_column') }}
                                </label>
                                <multiselect
                                    id="name_column"
                                    name="name_column"
                                    v-model="metadata.name_column"
                                    :closeOnSelect="true"
                                    :hideSelected="false"
                                    :multiple="false"
                                    :options="possibleNameColumns"
                                    :placeholder="$t('plugins.map.gis.import.metadata.name_column')"
                                    :select-label="$t('global.select.select')"
                                    :deselect-label="$t('global.select.deselect')">
                                </multiselect>
                            </div>
                        </form>
                        <hr />
                    </div>
                    <div class="row modal-map col" v-if="mapLayers && parsed">
                        <div class="col-md-8 h-100">
                            <ol-map
                                :draw-disabled="true"
                                :epsg="mapEpsg"
                                :init-collection="featureCollection"
                                :init-projection="featureProjection"
                                :layers="mapLayers">
                            </ol-map>
                        </div>
                        <div class="col-md-4 d-flex flex-column justify-content-center">
                            <dl class="row my-2">
                                <dt class="col-md-6 text-end">
                                    {{ $t('plugins.map.gis.import.feature_count') }}
                                </dt>
                                <dd class="col-md-6">
                                    {{ featureCollection.features.length }}
                                </dd>
                                <dt class="col-md-6 text-end">
                                    {{ $t('plugins.map.gis.import.epsg') }}
                                </dt>
                                <dd class="col-md-6">
                                    {{ featureProjection }}
                                </dd>
                            </dl>
                            <div class="text-center">
                                <button type="button" class="btn btn-success" @click.prevent="importCollection(featureCollection, featureProjection)">
                                    <span class="fa-stack d-inline">
                                        <i class="fas fa-download"></i>
                                        <i class="fas fa-check text-dark" data-fa-transform="shrink-2 left-12 down-7"></i>
                                    </span>
                                    <span class="stacked-icon-text">
                                        {{ $t('plugins.map.gis.import.files.confirm') }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="hide">
                    <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    import MapGisImportCsv from './MapGisImportCsv.vue';
    import MapGisImportKml from './MapGisImportKml.vue';
    import MapGisImportShape from './MapGisImportShape.vue';
    import MapGisImportGeoJson from './MapGisImportGeoJson.vue';

    export default {
        props: {
            id: {
                required: false,
                type: String,
                default: 'map-gis-import-modal'
            },
            layers: {
                required: false,
                type: Object
            }
        },
        components: {
            'map-csv-importer': MapGisImportCsv,
            'map-kml-importer': MapGisImportKml,
            'map-shape-importer': MapGisImportShape,
            'map-geojson-importer': MapGisImportGeoJson
        },
        beforeMount() {
            this.mapLayers = {
                ...this.layers,
                '99999': {
                    id: 99999,
                    name: 'Preview',
                    type: 'unlinked',
                    visible: true,
                    is_overlay: true,
                    opacity: 1,
                    color: '#00FF00'
                }
            }
        },
        mounted() {
            this.mapEpsg = this.$getPreference('prefs.map-projection');
        },
        methods: {
            hide() {
                this.$modal.hide(this.id);
            },
            onFileInput(newFile, oldFile) {
                if(!this.hasMultipleFiles && newFile && !oldFile) {
                    this.files = [];
                    this.files.push(newFile);
                }
                this.parsed = false;
            },
            onFileRemove(file) {
                this.$refs.upload.remove(file);
            },
            onParsedResults(result, epsg) {
                this.parsed = false;
                this.$nextTick(() => {
                    this.featureCollection = result;
                    this.featureProjection = epsg;
                    this.parsed = true;
                });
            },
            importCollection(collection, epsgCode) {
                const srid = epsgCode.split(':')[1];
                const data = {
                    collection: JSON.stringify(collection),
                    srid: srid,
                    metadata: JSON.stringify({
                        name_column: this.metadata.name_column,
                        root_element_id: (this.metadata.root_element) ? this.metadata.root_element.id : -1,
                        entity_type_id: (this.metadata.type) ? this.metadata.type.id : -1
                    })
                };
                $http.post('map', data).then(reponse => {
                    this.$showToast(
                        this.$t('plugins.map.gis.toasts.imported.title'),
                        this.$t('plugins.map.gis.toasts.imported.msg', {
                            cnt: collection.features.length
                        }),
                        'success'
                    );
                });
            },
            setActiveTab(id) {
                if(this.activeTab != id) {
                    this.featureCollection = {};
                    this.files = [];
                    this.parsed = false;
                    this.activeTab = id;
                }
            }
        },
        data() {
            return {
                activeTab: 'csv',
                featureCollection: {},
                featureProjection: 'EPSG:3857',
                files: [],
                showFileList: true,
                mapEpsg: {},
                mapLayers: {},
                parsed: false,
                metadata: {
                    name_column: '',
                    root_element: {name: ''},
                    type: {thesaurus_url: ''}
                }
            }
        },
        computed: {
            possibleNameColumns() {
                if(this.featureCollection.features && this.featureCollection.features.length) {
                    console.log("feature collection found!");
                    const f = this.featureCollection.features[0];
                    const p = Object.keys(f.properties);
                    console.log(p);
                    return p;
                }
                console.log("not found...");
                return [];
            },
            activeImportType() {
                return `map-${this.activeTab}-importer`;
            },
            hasMultipleFiles() {
                return this.activeTab == 'shape';
            },
            allowedMimeTypes() {
                switch(this.activeTab) {
                    case 'csv':
                        return 'text/csv';
                    case 'kml':
                        return 'application/vnd.google-earth.kml+xml, application/vnd.google-earth.kmz';
                    case 'shape':
                        return 'application/octet-stream,application/dbf,application/dbase';
                    case 'geojson':
                        return 'application/geo+json,application/vnd.geo+json,application/json';
                }
            },
            allowedExtensions() {
                switch(this.activeTab) {
                    case 'csv':
                        return ['csv'];
                    case 'kml':
                        return ['kml', 'kmz'];
                    case 'shape':
                        return ['shp', 'dbf', 'shx', 'prj'];
                    case 'geojson':
                        return ['json', 'geojson'];
                }
            }
        }
    }
</script>
