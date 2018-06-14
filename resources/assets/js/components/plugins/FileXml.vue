<template>
    <div class="modal-content-80-fix d-flex flex-column justify-content-start align-items-center">
        <div>
            <button class="btn btn-outline-secondary" @click="toggleEditMode()">
                <i class="fas fa-fw fa-edit"></i> Toggle Edit mode
            </button>
            <button class="btn btn-outline-success" @click="updateFile(file, content)" v-if="fileEdited">
                <i class="fas fa-fw fa-save"></i> Save
            </button>
            <button class="btn btn-outline-secondary" v-show="!renderHtml" @click="toggleCodeHighlighting()">
                <i class="fas fa-fw fa-underline"></i> Toggle Highlighting
            </button>
            <button class="btn btn-outline-secondary" v-if="isHtml" @click="toggleHtmlRendering()">
                <i class="fab fa-fw fa-html5"></i> Toggle HTML-Rendering
            </button>
        </div>
        <div class="d-flex mt-2 w-100 h-100 w-100">
            <div class="col px-1" v-if="editMode">
                <textarea v-validate="" name="editTextarea" class="w-100 h-100 p-2" v-model="content"></textarea>
            </div>
            <div class="col px-1">
                <pre class="mb-0 h-100" v-highlightjs="content" v-if="!renderHtml"><code class="h-100 text-wrap" :class="{nohighlight: disableHighlighting}"></code></pre>
                <iframe v-else :srcdoc="content" class="border-0 h-100 w-100"></iframe>
            </div>
        </div>

    </div>
</template>

<script>
    import VueHighlightJS from 'vue-highlightjs';

    export default {
        components: {
            VueHighlightJS
        },
        props: {
            file: {
                required: true,
                type: Object
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
            toggleHtmlRendering() {
                this.renderHtml = !this.renderHtml;
            },
            setFileContent() {
                const vm = this;
                vm.$http.get(vm.file.url).then(function(response) {
                    vm.content = response.data;
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
                vm.$http.post('/api/file/'+id+'/patch', data, {
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
                renderHtml: false,
                content: '',
                editMode: false
            }
        },
        computed: {
            isHtml: function() {
                switch(this.file.mime_type) {
                    case 'application/xhtml+xml':
                    case 'text/html':
                        return true;
                }
                let extensions = ['.htm', '.html', '.shtml', '.xhtml'];
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
