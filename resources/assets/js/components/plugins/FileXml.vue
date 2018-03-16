<template>
    <div class="modal-content-80-fix d-flex flex-column justify-content-start align-items-center">
        <div>
            <button class="btn btn-outline-secondary" @click="toggleEditMode()">
                <i class="fas fa-fw fa-edit"></i> Toggle Edit mode
            </button>
            <button class="btn btn-outline-secondary" v-show="!renderHtml" @click="toggleCodeHighlighting()">
                <i class="fas fa-fw fa-underline"></i> Toggle Highlighting
            </button>
            <button class="btn btn-outline-secondary" v-if="isHtml" @click="toggleHtmlRendering()">
                <i class="fab fa-fw fa-html5"></i> Toggle HTML-Rendering
            </button>
        </div>
        <div class="d-flex mt-2 w-100 h-100">
            <div class="col px-1" v-if="editMode">
                <textarea class="w-100 h-100 p-2" v-model="content"></textarea>
            </div>
            <div class="col px-1">
                <pre class="mb-0 h-100" v-highlightjs="content" v-if="!renderHtml"><code class="text-wrap" :class="{nohighlight: disableHighlighting}"></code></pre>
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
                let vm = this;
                vm.$http.get(vm.file.url).then(function(response) {
                    vm.content = response.data;
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
            }
        }
    }
</script>
