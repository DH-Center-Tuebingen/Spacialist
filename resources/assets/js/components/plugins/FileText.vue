<template>
    <div class="modal-content-80-fix d-flex flex-column justify-content-start align-items-center">
        <div>
            <button class="btn btn-outline-secondary" @click="toggleEditMode()">
                <i class="fas fa-fw fa-edit"></i> Toggle Edit mode
            </button>
            <button class="btn btn-outline-success" @click="updateFile(file, content)" v-if="fileEdited">
                <i class="fas fa-fw fa-save"></i> Save
            </button>
            <button class="btn btn-outline-secondary" v-show="!csv.render && !markdown.render" @click="toggleCodeHighlighting()">
                <i class="fas fa-fw fa-underline"></i> Toggle Highlighting
            </button>
            <button class="btn btn-outline-secondary" v-if="isCsv" @click="toggleCsvRendering()">
                <i class="fas fa-fw fa-table"></i> Toggle CSV-Rendering
            </button>
            <button class="btn btn-outline-secondary" v-if="isMarkdown" @click="toggleMarkdownRendering()">
                <i class="fas fa-fw fa-exchange-alt"></i> Toggle Markdown-Rendering
            </button>
        </div>
        <div class="d-flex mt-2 col px-0">
            <div class="col px-1" v-if="editMode">
                <textarea v-validate="" name="editTextarea" class="w-100 h-100 p-2" v-model="content"></textarea>
            </div>
            <div class="col px-1">
                <pre class="mb-0 h-100" v-show="!csv.render && !markdown.render" v-highlightjs="content"><code class="h-100 text-wrap word-break-all" :class="{nohighlight: disableHighlighting}"></code></pre>
                <div class="mt-2 h-100 d-flex flex-column" v-if="isCsv && csv.render">
                    <form class="form-inline">
                        <div class="form-group mx-2">
                            <label for="delimiter" class="col-form-label mr-1">Delimiter</label>
                            <input type="text" class="form-control" id="delimiter" v-model="csv.delimiter" placeholder="Delimiter (',', ';', '|', ...)" />
                        </div>
                        <div class="form-check mx-2">
                            <input type="checkbox" class="form-check-input" id="has-header" v-model="csv.hasHeader" />
                            <label class="form-check-label" for="has-header">
                                Header?
                            </label>
                        </div>
                        <div class="form-group mx-2">
                            <label for="row-count" class="col-form-label mr-1">Display rows</label>
                            <input type="text" class="form-control" id="row-count" v-model.number="csv.rows" placeholder="Row Count" />
                        </div>
                    </form>
                    <csv-table class="mt-2" v-if="isCsv && csv.render"
                        :content="content"
                        :delimiter="csv.delimiter"
                        :header="csv.hasHeader"
                        :length="csv.rows">
                    </csv-table>
                </div>
                <div class="mt-2" v-if="isMarkdown && markdown.render">
                    <vue-markdown
                        :source="content"
                        :watches="['show','html','breaks','linkify','emoji','typographer','toc']"
                        table-class="table table-striped table-hover">
                    </vue-markdown>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import VueMarkdown from 'vue-markdown';
    import VueHighlightJS from 'vue-highlightjs';

    Vue.use(VueHighlightJS);

    export default {
        components: {
            VueMarkdown
        },
        props: {
            file: {
                required: true,
                type: Object
            },
            storageConfig: {
                required: false,
                type: Object,
                default: () => new Object()
            }
        },
        mounted() {
            this.setFileContent();
        },
        methods: {
            toggleCodeHighlighting() {
                this.disableHighlighting = !this.disableHighlighting;
            },
            toggleEditMode() {
                this.editMode = !this.editMode;
            },
            toggleCsvRendering() {
                if(!this.isCsv) return;
                this.csv.render = !this.csv.render;
            },
            toggleMarkdownRendering() {
                if(!this.isMarkdown) return;
                this.markdown.render = !this.markdown.render;
            },
            setFileContent() {
                const vm = this;
                vm.$http.get(vm.file.url, vm.storageConfig).then(function(response) {
                    let data;
                    if(typeof response.data == 'object') {
                        data = JSON.stringify(response.data, null, 4);
                    } else {
                        data = response.data;
                    }
                    vm.content = data;
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            updateFile(file, content) {
                const vm = this;
                let id = file.id;
                let blob = new Blob([content], {type: file.mime_type});
                let data = new FormData();
                data.append('file', blob, file.name);
                vm.$http.post('/file/'+id+'/patch', data, {
                    headers: { 'content-type': false }
                }).then(function(response) {
                    Vue.set(vm.file, 'modified', response.data.modified);
                    vm.setPristine();
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            setPristine() {
                this.$validator.flag('editTextarea', {
                    dirty: false,
                    pristine: true
                });
            }
        },
        data() {
            return {
                disableHighlighting: false,
                csv: {
                    render: false,
                    delimiter: ',',
                    hasHeader: true,
                    rows: 10
                },
                markdown: {
                    render: false
                },
                content: '',
                editMode: false
            }
        },
        computed: {
            isCsv: function() {
                switch(this.file.mime_type) {
                    case 'text/comma-separated-values':
                    case 'text/csv':
                        return true;
                }
                return this.file.name.endsWith('.csv');
            },
            isMarkdown: function() {
                switch(this.file.mime_type) {
                    case 'text/x-markdown':
                    case 'text/markdown':
                        return true;
                }
                let extensions = ['.md', '.mkd', '.markdown'];
                for(let i=0; i<extensions.length; i++) {
                    let e = extensions[i];
                    if(this.file.name.endsWith(e)) {
                        return true;
                    }
                }
                return false;
            },
            fileEdited: function() {
                if(this.fields['editTextarea']) {
                    return this.fields['editTextarea'].dirty;
                }
                return false;
            }
        }
    }
</script>
