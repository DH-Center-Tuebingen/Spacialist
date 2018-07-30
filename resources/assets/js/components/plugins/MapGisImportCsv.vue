<template>
    <div>
        <form class="form-inline mb-3" @submit.prevent="parse">
            <div class="form-group mr-2">
                <label for="delimiter" class="sr-only">Delimiter</label>
                <input type="text" class="form-control" id="delimiter" v-model="delimiter" />
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" id="header" v-model="hasHeader" />
                <label class="form-check-label" for="header">Header Row</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" id="point-type-xy" value="point" v-model="pointType" />
                <label class="form-check-label" for="point-type-xy">Points</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" id="point-type-wkt" value="wkt" v-model="pointType" />
                <label class="form-check-label" for="point-type-wkt">WKT</label>
            </div>
            <div class="form-group mr-2" v-if="pointType == 'point'">
                <label for="x" class="sr-only">Longitude Field</label>
                <multiselect
                    label="label"
                    track-by="id"
                    v-model="longitude"
                    :allowEmpty="false"
                    :closeOnSelect="true"
                    :hideSelected="true"
                    :multiple="false"
                    :options="selectionColumns">
                </multiselect>
            </div>
            <div class="form-group mr-2" v-if="pointType == 'point'">
                <label for="y" class="sr-only">Latitude Field</label>
                <multiselect
                    label="label"
                    track-by="id"
                    v-model="latitude"
                    :allowEmpty="false"
                    :closeOnSelect="true"
                    :hideSelected="true"
                    :multiple="false"
                    :options="selectionColumns">
                </multiselect>
            </div>
            <div class="form-group mr-2" v-if="pointType == 'wkt'">
                <label for="y" class="sr-only">WKT Field</label>
                <multiselect
                    label="label"
                    track-by="id"
                    v-model="wkt"
                    :allowEmpty="false"
                    :closeOnSelect="true"
                    :hideSelected="true"
                    :multiple="false"
                    :options="selectionColumns">
                </multiselect>
            </div>
            <div class="form-group mr-2">
                <label for="epsg-code" class="sr-only">EPSG-Code</label>
                <input type="text" class="form-control" id="epsg-code" v-model="epsgCode" />
            </div>
            <button type="submit" class="btn btn-primary" :disabled="infoMissing">
                Parse
            </button>
        </form>
        <div class="text-center">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="showDataPreview = !showDataPreview">
                <span v-if="showDataPreview">
                    Hide Data Preview
                </span>
                <span v-else>
                    Show Data Preview
                </span>
            </button>
        </div>
        <csv-table class="mt-2" v-if="files.length && showDataPreview"
            :content="fileContent"
            :delimiter="delimiter"
            :header="hasHeader"
            :length="10"
            :small="true">
        </csv-table>
    </div>
</template>

<script>
    import csv2geojson from 'csv2geojson';
    let d3 = require('d3-dsv');

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
                this.fileContent = this.fileReader.readAsText(this.selectedFile.file);
            },
            updateContent(files) {
                if(files.length) {
                    this.selectedFile = files[0];
                    this.readContent();
                }
                this.selectedFile = {};
                this.fileContent = '';
            },
            parse() {
                if(this.infoMissing) return;
                // Hide preview on parse, so visualization is in focus
                this.showDataPreview = false;
                let content = this.fileContent;
                // If csv has no header, we add one, because csv2geojson needs one
                if(!this.hasHeader) {
                    let header = this.selectionColumns.join(this.delimiter);
                    content = `${header}\n${content}`;
                }
                csv2geojson.csv2geojson(content, {
                    lonfield: this.longitude.label,
                    latfield: this.latitude.label,
                    delimiter: this.delimiter
                }, (error, data) => {
                    if(error) {
                        // TODO throw
                        console.log("todo...");
                        return;
                    }
                    if(this.afterParse) {
                        this.afterParse(data, this.epsgCode);
                    }
                });
            }
        },
        data() {
            return {
                fileContent: '',
                fileReader: new FileReader(),
                delimiter: ',',
                hasHeader: true,
                pointType: 'point',
                longitude: {},
                latitude: {},
                epsgCode: 'EPSG:3857',
                showDataPreview: false
            }
        },
        computed: {
            dsv: function() {
                let delimiter = this.delimiter || ',';
                return d3.dsvFormat(delimiter);
            },
            infoMissing: function() {
                return !this.delimiter ||
                    !this.epsgCode ||
                    (
                        this.pointType == 'point' &&
                        (!this.longitude || !this.longitude.id) || (!this.latitude || !this.latitude.id)
                    ) ||
                    (
                        this.pointType == 'wkt' &&
                        (!this.wkt || !this.wkt.id)
                    );
            },
            selectionColumns: function() {
                if(!this.fileContent.length) return [];
                const firstRow = this.fileContent.split('\n')[0];
                const firstColumns = this.dsv.parseRows(firstRow)[0];
                if(this.hasHeader) {
                    let ctr = 0;
                    return firstColumns.map(c => {
                        return {
                            id: ++ctr,
                            label: c
                        };
                    });
                } else {
                    let columns = [];
                    for(let i=0; i<firstColumns.length; i++) {
                        columns.push({
                            id: i+1,
                            label: `Column ${i+1}`
                        });
                    }
                    return columns;
                }
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
