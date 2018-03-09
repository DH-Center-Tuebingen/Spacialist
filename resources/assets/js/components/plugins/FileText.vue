<template>
    <div>
        <button class="btn btn-outline-secondary" @click="toggleCodeHighlighting()">
            <i class="fas fa-fw fa-underline"></i> Toggle Highlighting
        </button>
        <button class="btn btn-outline-secondary" v-if="isCsv" @click="toggleCsvRendering()">
            <i class="fas fa-fw fa-table"></i> Toggle CSV-Rendering
        </button>
        <button class="btn btn-outline-secondary" v-if="isMarkdown" @click="toggleMarkdownRendering()">
            <i class="fas fa-fw fa-exchange-alt"></i> Toggle Markdown-Rendering
        </button>
        <pre v-highlightjs="content" class="text-left modal-content-80 mb-0 mt-2"><code class="text-wrap" :class="{nohighlight: disableHighlighting}"></code></pre>
    </div>
</template>

<script>
    export default {
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
                content: ''
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
                    case 'text/comma-separated-values':
                    case 'text/csv':
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
            }
        }
    }
</script>
