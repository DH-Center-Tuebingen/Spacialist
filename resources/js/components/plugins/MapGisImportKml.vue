<template>
    <div>
        <form class="form-inline mb-3" @submit.prevent="parse">
            <div class="form-group mr-2">
                <label for="epsg-code" class="sr-only">
                    {{ $t('plugins.map.gis.import.epsg') }}
                </label>
                <input type="text" class="form-control" id="epsg-code" v-model="epsgCode" />
            </div>
            <button type="submit" class="btn btn-primary" :disabled="infoMissing">
                {{ $t('global.parse') }}
            </button>
        </form>
    </div>
</template>

<script>
    import toGeoJSON from '@mapbox/togeojson';
    const JSZip = require("jszip");

    export default {
        props: {
            epsg: {
                required: false,
                type: String
            },
            files: {
                required: true,
                type: Array
            },
            afterParse: {
                required: false,
                type: Function
            }
        },
        mounted() {
            if(this.epsg) {
                this.epsgCode = this.epsg;
            }
            this.fileReader.onload = e => {
                this.fileContent = e.target.result;
            }
            this.updateContent(this.files);
        },
        methods: {
            readContent() {
                // KMZ is zip, thus read as Data URL
                if(this.isKmz) {
                    this.fileContent = this.selectedFile.file;
                } else {
                    this.fileReader.readAsText(this.selectedFile.file);
                }
            },
            updateContent(files) {
                if(files.length) {
                    this.selectedFile = files[0];
                    this.isKmz = this.selectedFile.name.endsWith('.kmz') || this.selectedFile.type == 'application/vnd.google-earth.kmz';
                    this.readContent();
                } else {
                    this.selectedFile = {};
                    this.fileContent = '';
                }
            },
            parseKmlContent(data) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, 'text/xml');
                return toGeoJSON.kml(doc);
            },
            parse() {
                if(this.infoMissing) return;
                if(this.isKmz) {
                    this.zip.loadAsync(this.fileContent).then(zipInstance => {
                        for(let k in zipInstance.files) {
                            const file = zipInstance.files[k];
                            if(file.dir) continue;
                            if(file.name.endsWith('.kml')) {
                                file.async('string').then(content => {
                                    const collection = this.parseKmlContent(content);
                                    if(this.afterParse) {
                                        this.afterParse(collection, this.epsgCode);
                                    }
                                });
                                break;
                            }
                        }
                    });
                } else {
                    const collection = this.parseKmlContent(this.fileContent);
                    if(this.afterParse) {
                        this.afterParse(collection, this.epsgCode);
                    }
                }
            }
        },
        data() {
            return {
                selectedFile: {},
                fileContent: '',
                isKmz: false,
                fileReader: new FileReader(),
                zip: new JSZip(),
                epsgCode: 'EPSG:3857'
            }
        },
        computed: {
            infoMissing: function() {
                return !this.epsgCode;
            }
        },
        watch: {
            files: function(newFiles) {
                this.updateContent(newFiles);
                return;
            }
        }
    }
</script>
