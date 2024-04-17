<template>
    <div class="data-importer d-flex flex-column overflow-hidden">
        <header>
            <h4>{{ t(`main.importer.title`) }}</h4>
        </header>
        <div class="layout flex-grow-1 overflow-hidden">
            <div class="file-preview d-flex flex-column overflow-hidden gap-4">
                <file-upload
                    v-if="!state.fileLoaded"
                    :model="state.files"
                    class="rounded border-dashed flex-grow-1 d-flex flex-column gap-2 justify-content-center align-items-center clickable"
                    accept="text/plain,text/csv"
                    extensions="dsv,csv,tsv"
                    :directory="false"
                    :multiple="false"
                    :drop="true"
                    @input-file="addFile"
                >
                    <div class="info fw-bold fs-5">
                        {{ t('main.importer.drop_csv_file') }}
                    </div>
                </file-upload>
                <csv-table
                    v-else
                    class="flex-grow-1"
                    :content="state.content"
                    :small="true"
                    @parse="e => extractColumns(e)"
                />
                <footer>
                    <button
                        v-show="state.fileLoaded"
                        type="button"
                        class="btn btn-outline-danger btn-sm"
                        @click="removeFile()"
                    >
                        <i class="fas fa-fw fa-times" />
                        {{ t('global.remove_file') }}
                    </button>
                </footer>
            </div>
            <div class="controls">
                <div class="card">
                    <header :class="cardHeaderClasses">
                        {{ t('main.importer.entity_settings') }}
                        <div class="toolbox" />
                    </header>
                    <div class="card-body">
                        <EntityImporterSettings
                            v-if="state.fileLoaded"
                            v-model:entityType="entitySettings.entityType"
                            v-model:entityName="entitySettings.entityName"
                            v-model:entity-parent="entitySettings.entityParent"
                            :stats="state.stats"
                            :available-entity-types="availableEntityTypes"
                            :available-columns="state.availableColumns"
                            :disabled="state.uploading"
                            @update:entity-name="onNameColumnChanged"
                            @update:entity-parent="onParentColumnChanged"
                        />
                        <div
                            v-else
                            class="alert alert-primary"
                        >
                            {{ t("main.importer.info.entity_settings_require_file") }}
                        </div>
                    </div>
                </div>
                <div class="card">
                    <header :class="cardHeaderClasses">
                        {{ t('main.importer.attribute_mapping') }}
                        <div class="toolbox" />
                    </header>
                    <div class="card-body">
                        <EntityAttributeMapping
                            v-if="state.fileLoaded && !!entitySettings.entityType"
                            v-model:attribute-mapping="attributeSettings.mapping"
                            :available-columns="state.availableColumns"
                            :available-attributes="state.availableAttributes"
                            :stats="state.stats"
                            :disabled="state.uploading"
                            @row-changed="onAttributeMappingSelected"
                        />
                        <div
                            v-else-if="!state.fileLoaded"
                            class="alert alert-primary"
                        >
                            {{ t("main.importer.info.entity_settings_require_file") }}
                        </div>
                        <div
                            v-else
                            class="alert alert-primary"
                        >
                            {{ t("main.importer.info.entity_type_has_no_attributes") }}
                        </div>
                    </div>
                </div>
                <footer class="d-flex align-items-center justify-content-between">
                    <span class="status text-danger">
                        {{ state.error }}
                    </span>
                    <LoadingButton
                        :loading="state.uploading"
                        class="btn btn-primary"
                        :disabled="!canImport || state.uploading"
                        @click="confirmImport"
                    >
                        {{ t(`main.importer.import_btn`) }}
                    </LoadingButton>
                </footer>
            </div>
        </div>
    </div>
</template>


<script>
    import {
        computed,
        reactive,
        watch,
    } from 'vue';

    import store from '%store';

    import { stringSimilarity } from 'string-similarity-js';

    import VueUploadComponent from 'vue-upload-component';
    import CsvTable from '../CsvTable.vue';
    import EntityImporterSettings from '@/components/tools/importer/EntityImporterSettings.vue';
    import EntityAttributeMapping from '../tools/importer/EntityAttributeMapping.vue';

    import { useI18n } from 'vue-i18n';
    import { useToast } from '@/plugins/toast.js';

    import {
        importEntityData,
    } from '@/api.js';

    import {
        showImportError,
    } from '@/helpers/modal.js';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        useOptionalLocalStorage
    } from '../../composables/local-storage';
    import { onMounted } from 'vue';
    import LoadingButton from '../forms/button/LoadingButton.vue';
    import { nextTick } from 'process';

    export default {
        components: {
            CsvTable,
            FileUpload: VueUploadComponent,
            EntityImporterSettings,
            EntityAttributeMapping,
            LoadingButton,
        },
        props: {
            debug: {
                type: Boolean,
                required: false,
                default: false
            },
        },
        setup(props, context) {
            const { t } = useI18n();

            let toast = null;
            onMounted(() => {
                nextTick(() => {
                    toast = useToast();
                });
            });

            // FUNCTIONS

            const fileReader = new FileReader();
            fileReader.onload = e => {
                state.fileLoaded = true;
                state.content = e.target.result;
                state.error = '';

                fileContent.value = e.target.result;
                saveFileContent();
            };

            fileReader.onerror = e => {
                resetFileReader(t('main.importer.file_read'));
            };

            fileReader.onabort = e => {
                resetFileReader(t('main.importer.file_read_abort'));
            };

            function resetFileReader(error = '') {
                state.content = '';
                state.fileLoaded = false;
                state.error = error;
            }

            const readContent = _ => {
                if(!state.files || state.files.length == 0) {
                    resetFileReader();
                } else {
                    fileReader.readAsText(state.files[0].file);
                }
            };

            const extractColumns = data => {
                state.availableColumns = data.header;
                state.fileData = data.data;
                state.fileDelimiter = data.delimiter;


                /**
                 * Required for the multselect to work properly.
                 */
                nextTick(() => {
                    guessNameColumn();
                    guessParentColumn();
                    guessAttributeMapping();
                });

            };

            const minimumGuessScore = 0.5;

            const guessFrom = function (arr, target) {
                const bestMatch = {
                    col: null,
                    score: 0,
                };
                for(const col of arr) {
                    const score = stringSimilarity(target, col);
                    if(score > bestMatch.score) {
                        bestMatch.col = col;
                        bestMatch.score = score;
                    }
                }

                return bestMatch;
            };

            const guessEntitySetting = (reactive, key, nameList, callback) => {

                const setAndAvailable = Boolean(reactive[key] && state.availableColumns.indexOf(reactive[key]) != -1);

                // If the parent column is already set and available, we don't need to guess
                if(setAndAvailable) {
                    return;
                }

                // If the parent column is not available (anymore), we need to reset it
                if(!setAndAvailable && reactive[key]) {
                    reactive[key] = null;
                    return;
                }

                let bestGuess = {
                    col: null,
                    score: 0,
                };
                for(const name of nameList) {
                    const guess = guessFrom(state.availableColumns, name);
                    if(guess.score > bestGuess.score) {
                        bestGuess = guess;
                    }
                }
                if(bestGuess.col && bestGuess.score > minimumGuessScore) {
                    reactive[key] = bestGuess.col;
                    callback(bestGuess.col);
                }

                return bestGuess;
            };

            const guessNameColumn = _ => {
                guessEntitySetting(entitySettings, 'entityName', ['name', 'key'], onNameColumnChanged);
            };

            const guessParentColumn = _ => {
                guessEntitySetting(entitySettings, 'entityParent', ['parent', 'path'], onParentColumnChanged);
            };

            const guessAttributeMapping = _ => {
                for(const attr of state.availableAttributes) {
                    if(attributeSettings.mapping[attr.id]) {
                        continue;
                    }

                    const name = translateConcept(attr.thesaurus_url);
                    const bestGuess = guessFrom(state.availableColumns, name);

                    if(bestGuess.col && bestGuess.score > minimumGuessScore) {
                        attributeSettings.mapping[attr.id] = bestGuess.col;
                        onAttributeMappingSelected(attr.id, bestGuess.col);
                    }
                }
            };

            const onEntityTypeSelected = e => {
                if(!e || isNaN(e.id)) {
                    state.availableAttributes = [];
                    return;
                } else {
                    state.availableAttributes = store.getters.entityTypeAttributes(e.id) || [];
                }
                guessAttributeMapping();
            };

            const onNameColumnChanged = attributeValue => {
                const stats = determineColumnStats(attributeValue);
                state.stats.entityName = stats;
            };

            const onParentColumnChanged = (attributeValue) => {
                const stats = determineColumnStats(attributeValue);
                state.stats.entityParent = stats;
            };

            const onAttributeMappingSelected = (attributeId, attributeValue) => {
                const stats = determineColumnStats(attributeValue);
                state.stats.attributes[attributeId] = stats;
            };

            const determineColumnStats = (columnName) => {
                if(state.availableColumns.indexOf(columnName) == -1) {
                    return {
                        missing: 0,
                        total: 0,
                    };
                }

                const emptyValues = state.fileData.filter(fd => fd[columnName] == '');
                return {
                    missing: emptyValues.length,
                    total: state.fileData.length,
                };
            };

            const confirmImport = _ => {
                state.uploading = true;
                const data = new FormData();
                data.append('file', state.files[0].file);
                data.append('metadata', JSON.stringify({
                    delimiter: state.fileDelimiter,
                }));
                const postData = {
                    name_column: entitySettings.entityName,
                    entity_type_id: entitySettings.entityType.id,
                    attributes: attributeSettings.mapping,
                };
                if(!!entitySettings.entityParent) {
                    postData['parent_column'] = entitySettings.entityParent;
                }
                data.append('data', JSON.stringify(postData));

                importEntityData(data).then(data => {
                    for(let i = 0; i < data.length; i++) {
                        store.dispatch('addEntity', data[i]);
                    }
                    toast.$toast(t('main.importer.success', {
                        cnt: data.length
                    }, data.length), '', {
                        duration: 2500,
                        autohide: true,
                        channel: 'success',
                        icon: true,
                        simple: true,
                    });
                }).catch(e => {
                    showImportError({
                        message: e.response.data.error,
                        data: e.response.data.data,
                    });
                }).finally(_ => {
                    state.uploading = false;
                });
            };
            const addFile = (file) => {
                if(file) {
                    state.files = [file];
                    readContent();
                }
            };

            const storageSettings = {
                fileContent: props.debug,
                entitySettings: props.debug,
                attributeMapping: props.debug,
            };

            // DATA

            const {
                value: fileContent,
                reset: resetFileContent,
                save: saveFileContent
            } = useOptionalLocalStorage(
                storageSettings.fileContent,
                'importer_file_content', '');

            const {
                value: entitySettings,
                reset: resetEntitySettings
            } = useOptionalLocalStorage(
                storageSettings.entitySettings,
                'importer_entity_settings', {
                entityType: null,
                entityName: null,
                entityParent: null,
            });

            const {
                value: attributeSettings,
                reset: resetAttributeSettings
            } = useOptionalLocalStorage(
                storageSettings.attributeMapping,
                'importer_attribute_mapping', {
                autoComplete: true,
                mapping: {},
            });

            const removeFile = _ => {
                state.files = [];
                state.fileData = [];
                state.content = '';
                state.error = '';
                state.fileLoaded = false;
                state.availableColumns = [];
                state.availableAttributes = [];
                state.stats.entityName = { missing: 0, total: 0 };
                state.stats.entityParent = { missing: 0, total: 0 };
                state.stats.attributes = {};

                resetFileContent();
                resetEntitySettings();
                resetAttributeSettings();
            };

            const availableEntityTypes = computed(() => Object.values(store.getters.entityTypes));
            const state = reactive({
                files: [],
                fileData: [],
                fileDelimiter: '',
                content: fileContent,
                fileLoaded: false,
                availableColumns: [],
                availableAttributes: [],
                stats: {
                    entityName: { missing: 0, total: 0 },
                    entityParent: { missing: 0, total: 0 },
                    attributes: {},
                },
                dataMissing: computed(_ => {
                    return !entitySettings.entityType || !entitySettings.entityName || state.stats.entityName.missing > 0;
                }),
                uploading: false,
            });

            onMounted(() => {
                if(fileContent.value) {
                    state.fileLoaded = true;
                }
            });

            watch(() => entitySettings.entityType, entityType => {
                onEntityTypeSelected(entityType);
            });

            const canImport = computed(_ => {
                return state.fileLoaded && !state.dataMissing;
            });

            const cardHeaderClasses = ['card-header', 'd-flex', 'justify-content-between'];

            // RETURN
            return {
                t,
                // LOCAL
                canImport,
                cardHeaderClasses,
                extractColumns,
                onEntityTypeSelected,
                onAttributeMappingSelected,
                onNameColumnChanged,
                onParentColumnChanged,
                confirmImport,
                addFile,
                removeFile,
                // PROPS
                // STATE
                availableEntityTypes,
                state,
                entitySettings,
                attributeSettings,
                storageSettings,
            };
        },
    };
</script>

<style>
    .data-importer {

        th,
        td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 10rem;
        }

        .table-responsive {
            overflow-x: visible !important;
        }
    }
</style>


<style scoped>
    .controls {
        display: grid;
        grid-template-columns: minmax(1fr);
        grid-template-rows: min-content minmax(min-content, 1fr) minmax(min-content, 1rem);
        gap: 1rem;
    }

    .data-importer {
        height: 100%;
        background-color: whitesmoke;
    }

    .layout {
        display: grid;
        grid-template-columns: 2fr minmax(300px, 1fr);
        gap: 1rem;
    }

    .file-preview {
        max-height: 100%;
    }
</style>