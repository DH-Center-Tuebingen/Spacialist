<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-lg"
        v-model="state.show"
        name="csv-uploader-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    t('main.csv.uploader.title')
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body">
            <file-upload
                accept="text/csv"
                class="w-100"
                ref="upload"
                v-model="state.fileQueue"
                :directory="false"
                :drop="true"
                :multiple="false"
                @input-file="inputFile">
                    <div class="d-flex flex-row justify-content-center align-items-center border border-success border-3 rounded-3" style="height: 150px; border-style: dashed !important;">
                        <i class="fas fa-fw fa-file-upload fa-3x"></i>
                    </div>
            </file-upload>
            <csv-table
                :content="state.fileContent"
                :small="true"
                :options="true"
                @parse="getParsedData" />
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline-success" data-bs-dismiss="modal" :disabled="!state.canConfirm" @click="confirmCsv()">
                <i class="fas fa-fw fa-check"></i> {{ t('global.create') }}
            </button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" @click="closeModal()">
                <i class="fas fa-fw fa-times"></i> {{ t('global.cancel') }}
            </button>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
        toRefs,
    } from 'vue';
    
    import { useI18n } from 'vue-i18n';

    export default {
        emits: ['confirm', 'closing'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                data,
            } = toRefs(props);

            // FUNCTIONS
            const inputFile = (newFile, oldFile) => {
                if(!!newFile) {
                    parseFile(newFile);
                }
            };
            const parseFile = (file, component) => {
                    state.fileReader.readAsText(file.file);
            };
            const getParsedData = e => {
                state.parsedCsv = e;
            };
            const confirmCsv = _ => {
                state.show = false;
                context.emit('confirm', state.parsedCsv);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                show: false,
                fileReader: new FileReader(),
                fileContent: '',
                fileQueue: [],
                parsedCsv: null,
                canConfirm: computed(_ => !!state.parsedCsv),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
                state.fileReader.onload = e => {
                    state.fileContent = e.target.result;
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                // PROPS
                // LOCAL
                inputFile,
                parseFile,
                getParsedData,
                confirmCsv,
                closeModal,
                // STATE
                state,
            }
        },
    }
</script>
