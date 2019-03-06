<template>
    <div class="h-100 d-flex flex-column justify-content-start align-items-center">
        <div>
            <button class="btn btn-outline-secondary" @click="toggleEditMode()">
                <i class="fas fa-fw fa-edit"></i> {{ $t('plugins.files.modal.detail.toggle-edit') }}
            </button>
            <button class="btn btn-outline-success" @click="updateFile(file, content)" v-if="fileEdited">
                <i class="fas fa-fw fa-save"></i> {{ $t('global.save') }}
            </button>
            <button class="btn btn-outline-secondary" v-show="!renderHtml" @click="toggleCodeHighlighting()">
                <i class="fas fa-fw fa-underline"></i> {{ $t('plugins.files.modal.detail.toggle-highlight') }}
            </button>
            <button class="btn btn-outline-secondary" v-if="isHtml" @click="toggleHtmlRendering()">
                <i class="fab fa-fw fa-html5"></i> {{ $t('plugins.files.modal.detail.toggle-html') }}
            </button>
        </div>
        <div class="d-flex mt-2 w-100 col px-0">
            <div class="col px-1" v-if="editMode">
                <textarea v-validate="" name="editTextarea" class="w-100 h-100 p-2" v-model="content"></textarea>
            </div>
            <div class="col px-1">
                <pre class="mb-0 h-100 text-left" v-highlightjs="content" v-if="!renderHtml"><code class="h-100 text-prewrap" :class="{nohighlight: disableHighlighting}"></code></pre>
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
            toggleHtmlRendering() {
                this.renderHtml = !this.renderHtml;
            },
            setFileContent() {
                const vm = this;
                $httpQueue.add(() => vm.$http.get(vm.file.url, vm.storageConfig).then(function(response) {
                    vm.content = response.data;
                }));
            },
            updateFile(file, content) {
                this.$emit('update-file-content', {
                    file: file,
                    content: content,
                    onSuccess: this.onUpdateFile
                });
            },
            onUpdateFile(response, file) {
                Vue.set(file, 'modified', response.data.modified);
                Vue.set(file, 'modified_unix', response.data.modified_unix);
                this.setPristine();
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
        },
        watch: {
            file(newFile, oldFile) {
                this.setFileContent();
            }
        }
    }
</script>
