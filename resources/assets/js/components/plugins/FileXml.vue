<template>
    <div>
        <button class="btn btn-outline-secondary" @click="toggleCodeHighlighting()">
            <i class="fas fa-fw fa-underline"></i> Toggle Highlighting
        </button>
        <button class="btn btn-outline-secondary" v-if="isHtml" @click="toggleHtmlRendering()">
            <i class="fab fa-fw fa-html5"></i> Toggle HTML-Rendering
        </button>
        <iframe v-if="enableHtml" :src="file.url" class="mt-2 border-0 d-block w-100 modal-content-80"></iframe>
        <pre v-highlightjs="content" class="text-left modal-content-80 mb-0 mt-2" v-else><code class="text-wrap" :class="{nohighlight: disableHighlighting}"></code></pre>
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
            toggleHtmlRendering() {
                this.enableHtml = !this.enableHtml;
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
                enableHtml: false,
                content: ''
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
