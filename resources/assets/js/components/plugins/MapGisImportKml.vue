<template>
    <div>
        <form class="form-inline mb-3" @submit.prevent="parse">
            <div class="form-group mr-2">
                <label for="epsg-code" class="sr-only">EPSG-Code</label>
                <input type="text" class="form-control" id="epsg-code" v-model="epsgCode" />
            </div>
            <button type="submit" class="btn btn-primary" :disabled="infoMissing">
                Parse
            </button>
        </form>
    </div>
</template>

<script>
    // const toGeoJSON = require('togeojson');
    // const zip = require('zipjs-browserify');
    import toGeoJSON from '@mapbox/togeojson';
    import zip from 'zipjs-browserify/vendor/zip.js';

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
            zip.zip.workerScriptsPath = 'node_modules/zipjs-browserify/vendor/';
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
                    this.fileReader.readAsDataURL(this.selectedFile.file);
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
                    // TODO unzip, then parseKmlContent
                    let reader = new zip.zip.Data64URIReader(this.fileContent);
                    zip.zip.createReader(reader, reader => {
                        reader.getEntries(entries => {
                            if(entries.length) {
                                entries.forEach(e => {
                                    if(e.directory) return;
                                    if(e.filename.endsWith('.kml')) {
                                        e.getData(new zip.zip.TextWriter(), content => {
                                            const collection = this.parseKmlContent(content);
                                            if(this.afterParse) {
                                                this.afterParse(collection, this.epsgCode);
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    }, error => {
                        console.log(error);
                        this.$throwError(error);
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
                fileContent: '',
                isKmz: false,
                fileReader: new FileReader(),
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
