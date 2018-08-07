<template>
    <div class="modal-content-80-fix d-flex flex-column justify-content-start align-items-center">
        <p class="alert alert-info">
            Filetype of <span class="font-italic">{{file.name}}</span> is currently not supported by Spacialist.
            If you need support for this kind of filetype, please create an <a href="https://github.com/eScienceCenter/Spacialist/issues/new">issue on GitHub<sup><i class="fas fa-fw fa-external-link-alt"></i></sup></a>
        </p>
        <a :href="file.url" :download="file.name" target="_blank">
            <i class="fas fa-fw fa-file-download fa-5x mb-2"></i>
            <h4>Download {{file.name}}</h4>
        </a>
        <div v-if="supportsHtmlRendering && !htmlLoaded">
            <hr />
            <button type="button" class="btn btn-outline-secondary my-2" @click="loadAsHtml">
                Load Content as HTML
            </button>
            <div>
                <h5>What's this?</h5>
                <p class="alert font-italic mx-5">
                    Since this filetype is unsupported, there is no native way in Spacialist to display it's content. Though, it supports simple text rendering to (at least) view the content without downloading it.
                </p>
            </div>
        </div>
        <div class="w-100 h-100" v-if="htmlLoaded">
            <iframe class="w-100 h-100 border-0 mx-2 pb-2" :srcdoc="htmlContent"></iframe>
        </div>
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
        },
        methods: {
            loadAsHtml: function() {
                const vm = this;
                const id = vm.file.id;
                vm.$http.get(`/file/${id}/as_html`).then(function(response) {
                    let data;
                    if(typeof response.data == 'object') {
                        data = JSON.stringify(response.data, null, 4);
                    } else {
                        data = response.data;
                    }
                    vm.htmlContent = data;
                    vm.htmlLoaded = true;
                });
            }
        },
        data() {
            return {
                htmlLoaded: false,
                htmlContent: ''
            }
        },
        computed: {
            supportsHtmlRendering: function() {
                switch(this.file.category) {
                    case 'document':
                    case 'spreadsheet':
                    case 'presentation':
                        return true;
                    default:
                        return false;
                }
            }
        }
    }
</script>
