<template>
    <modal :name="id" width="80%" height="85%" classes="of-visible" @before-open="init">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ $t('plugins.files.modal.clipboard.title', {
                        name: file.name
                    }) }}
                </h5>
                <button type="button" class="btn-close" aria-label="Close" @click.prevent="hide">
                </button>
            </div>
            <div class="modal-body col row">
                <div class="col h-100 border-end">
                    <div class="mb-0 h-100 d-flex flex-column" v-if="type == 'text'">
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-primary" @click="toggleEditMode">
                                {{ $t('plugins.files.modal.clipboard.toggle_edit_mode') }}
                            </button>
                        </div>
                        <div class="col px-0 mt-2 overflow-hidden">
                            <pre class="text-start w-100 h-100 mb-0" v-if="!editMode" v-highlightjs="content"><code class="h-100 text-prewrap word-break-all"></code></pre>
                            <textarea class="w-100 h-100 p-2" v-else v-model="content" @input="fileEdited = true"></textarea>
                        </div>
                    </div>
                    <div v-else-if="type == 'image'" class="h-100 d-flex flex-row justify-content-center align-items-center">
                        <img class="mw-100 mh-100" :src="content" />
                    </div>
                    <p v-else class="alert alert-info">
                        {{ $t('plugins.files.modal.clipboard.no_preview') }}
                    </p>
                </div>
                <div class="col">
                    <h4>{{ $t('plugins.files.modal.clipboard.file_info') }}</h4>
                    <i class="fas fa-fw fa-file"></i> {{ file.size | bytes }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success" @click="confirm">
                    <i class="fas fa-fw fa-check"></i> {{ $t('global.confirm') }}
                </button>
                <button type="button" class="btn btn-secondary" @click="hide">
                    <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
                </button>
            </div>
        </div>
    </modal>
</template>

<script>
    import VueHighlightJS from 'vue-highlightjs';
    import 'highlight.js/styles/atom-one-dark.css';

    Vue.use(VueHighlightJS);

    export default {
        props: {
            id: {
                required: false,
                type: String,
                default: 'file-confirm-upload-modal'
            }
        },
        mounted() {},
        methods: {
            init(event) {
                this.editMode = false;
                this.fileEdited = false;
                this.content = '';

                this.file = event.params.file;

                let reader = new FileReader();
                reader.onload = e => {
                    this.content = reader.result;
                };
                if(this.type == 'text') {
                    reader.readAsText(this.file);
                } else if(this.type == 'image') {
                    reader.readAsDataURL(this.file);
                }
            },
            toggleEditMode() {
                this.editMode = !this.editMode;
            },
            confirm() {
                let uplFile;
                if(this.fileEdited) {
                    uplFile = new File([this.content], this.file.name, {
                        type: this.file.type
                    });
                } else {
                    uplFile = this.file;
                }
                this.$emit('confirm', {
                    file: uplFile
                });
                this.hide();
            },
            hide() {
                this.$modal.hide(this.id);
            }
        },
        data() {
            return {
                file: {},
                content: '',
                fileEdited: false,
                editMode: false
            }
        },
        computed: {
            type() {
                if(!this.file || !this.file.type) return 'none';
                if(this.file.type.match('image.*')) return 'image';
                if(this.file.type.match('text.*')) return 'text';
                return 'none';
            }
        }
    }
</script>
