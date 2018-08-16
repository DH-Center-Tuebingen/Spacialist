<template>
    <div class="h-100 d-flex flex-column justify-content-start align-items-center">
        <p class="alert alert-info" v-html="$t('plugins.files.modal.detail.undef.info', {mime: file.mime_type, name: file.name})">
        </p>
        <a :href="file.url" :download="file.name" target="_blank">
            <i class="fas fa-fw fa-file-download fa-5x mb-2"></i>
            <h4>
                {{ $t('global.download-name', {name: file.name}) }}
            </h4>
        </a>
        <div v-if="supportsHtmlRendering && !htmlLoaded">
            <hr />
            <button type="button" class="btn btn-outline-secondary my-2" @click="loadAsHtml">
                {{ $t('plugins.files.modal.undef.as-html') }}
            </button>
            <div>
                <h5>
                    {{ $t('plugins.files.modal.undef.html-info.title') }}
                </h5>
                <p class="alert font-italic mx-5">
                    {{ $t('plugins.files.modal.undef.html-info.desc') }}
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
