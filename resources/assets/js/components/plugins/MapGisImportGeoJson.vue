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
                this.fileContent = JSON.parse(e.target.result);
            }
            this.updateContent(this.files);
        },
        methods: {
            readContent() {
                this.fileReader.readAsText(this.selectedFile.file);
            },
            updateContent(files) {
                if(files.length) {
                    this.selectedFile = files[0];
                    this.readContent();
                } else {
                    this.selectedFile = {};
                    this.fileContent = '';
                }
            },
            parse() {
                if(this.infoMissing) return;
                if(this.afterParse) {
                    this.afterParse(this.fileContent, this.epsgCode);
                }
            }
        },
        data() {
            return {
                fileContent: '',
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
