<template>
    <modal :name="id" width="80%" height="auto" :scrollable="true" classes="of-visible">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Geodata</h5>
                <button type="button" class="close" aria-label="Close" @click.prevent="hide">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body row">
                <div class="col-md-2">
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
                <div class="col-md-10">
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
                            <i class="fas fa-fw fa-file-import"></i> Select files or drop here
                        </span>
                    </file-upload>
                    <div v-if="files.length" class="text-left col-md-3">
                        <h5>
                            Selected Files
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
                    <hr v-if="files.length" />
                    <component v-if="files.length"
                        :epsg="'EPSG:'+mapEpsg.epsg"
                        :is="activeImportType"
                        :files="files"
                        :after-parse="onParsedResults">
                    </component>
                    <hr v-if="mapLayers && parsed" />
                    <div class="row modal-map" v-if="mapLayers && parsed">
                        <div class="col-md-8">
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
                                <dt class="col-md-6 text-right"># of Features</dt>
                                <dd class="col-md-6">
                                    {{ featureCollection.features.length }}
                                </dd>
                                <dt class="col-md-6 text-right">EPSG-Code</dt>
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
                                    Confirm Import
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" @click="hide">
                    <i class="fas fa-fw fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    Vue.component('map-csv-importer', require('./MapGisImportCsv.vue'));
    Vue.component('map-kml-importer', require('./MapGisImportKml.vue'));
    Vue.component('map-shape-importer', require('./MapGisImportShape.vue'));
    Vue.component('map-geojson-importer', require('./MapGisImportGeoJson.vue'));

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
                    srid: srid
                };
                $http.post('map', data).then(reponse => {
                    this.$showToast('Import finished', `${collection.features.length} features added.`, 'success');
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
                parsed: false
            }
        },
        computed: {
            activeImportType: function() {
                return `map-${this.activeTab}-importer`;
            },
            hasMultipleFiles: function() {
                return this.activeTab == 'shape';
            },
            allowedMimeTypes: function() {
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
            allowedExtensions: function() {
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
