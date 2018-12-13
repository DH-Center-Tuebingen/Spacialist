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
    import shp from 'shpjs';

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
                Vue.set(this.fileContents, this.loadedFileType, e.target.result);
                if(this.loadedFileType == 'qpj') {
                    const data = {
                        srtext: this.fileContents[this.loadedFileType]
                    };
                    $http.post(`map/epsg/text`, data).then(response => {
                        this.epsgCode = `EPSG:${response.data.srid}`;
                    });
                }
            };
            this.updateContent(this.files);
        },
        methods: {
            readContent(files) {
                this.readNextFile(files);
            },
            readNextFile(files, i = 0) {
                if(i >= files.length) return;
                const file = files[i].file;
                const ext = file.name.substring(file.name.length - 3);
                this.loadedFileType = ext;
                this.fileReader.onloadend = _ => {
                    this.readNextFile(files, i+1);
                };
                switch(ext) {
                    case 'shp':
                    case 'dbf':
                        this.fileReader.readAsArrayBuffer(file);
                        break;
                    case 'prj':
                    case 'qpj':
                        this.fileReader.readAsText(file);
                        break;
                }
            },
            updateContent(files) {
                this.fileContents.shp = null;
                this.fileContents.dbf = null;
                this.fileContents.prj = null;
                this.fileContents.qpj = null;
                if(files.length) {
                    this.readContent(files);
                }
            },
            parse() {
                if(this.infoMissing) return;
                const geojson = shp.combine([
                    shp.parseShp(this.fileContents.shp, this.fileContents.prj),
                    shp.parseDbf(this.fileContents.dbf)
                ]);
                if(this.afterParse) {
                    this.afterParse(geojson, this.epsgCode);
                }
            }
        },
        data() {
            return {
                fileContents: {
                    shp: null,
                    dbf: null,
                    prj: null,
                    qpj: null
                },
                fileReader: new FileReader(),
                loadedFileType: '',
                epsgCode: 'EPSG:3857'
            }
        },
        computed: {
            infoMissing: function() {
                return !this.epsgCode || !this.fileContents.shp || !this.fileContents.dbf;
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
