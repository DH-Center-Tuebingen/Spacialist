<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-lg"
        name="csv-uploader-modal"
    >
        <div class="sp-modal-content sp-modal-content-lg">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('main.csv.uploader.title')
                    }}
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body">
                <file-upload
                    v-if="state.fileContent === ''"
                    ref="upload"
                    v-model="state.fileQueue"
                    accept="text/csv"
                    class="w-100"
                    :directory="false"
                    :drop="true"
                    :multiple="false"
                    @input-file="inputFile"
                >
                    <div
                        class="d-flex flex-row justify-content-center align-items-center border border-success border-3 rounded-3"
                        style="height: 150px; border-style: dashed !important;"
                    >
                        <i class="fas fa-fw fa-file-upload fa-3x" />
                    </div>
                </file-upload>
                <csv-table
                    v-else
                    :content="state.fileContent"
                    :small="true"
                    :options="true"
                    :removable="true"
                    @parse="getParsedData"
                    @remove="state.fileContent = ''"
                />
            </div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-outline-success"
                    data-bs-dismiss="modal"
                    :disabled="!state.canConfirm"
                    @click="confirmCsv()"
                >
                    <i class="fas fa-fw fa-check" /> {{ t('global.create') }}
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                >
                    <i class="fas fa-fw fa-times" /> {{ t('global.cancel') }}
                </button>
            </div>
        </div>
    </vue-final-modal>
</template>

<script>
    import {
        computed,
        onMounted,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    export default {
        emits: ['confirm', 'closing'],
        setup(props, context) {
            const { t } = useI18n();

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
                context.emit('confirm', state.parsedCsv);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };

            // DATA
            const state = reactive({
                fileReader: new FileReader(),
                fileContent: '',
                fileQueue: [],
                parsedCsv: null,
                canConfirm: computed(_ => !!state.parsedCsv),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.fileReader.onload = e => {
                    state.fileContent = e.target.result;
                };
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
            };
        },
    };
</script>
