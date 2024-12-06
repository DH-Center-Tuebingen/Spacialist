<template>
    <div class="data-importer h-100 d-flex flex-column overflow-hidden">
        <header>
            <h4>{{ t(`main.importer.title`) }}</h4>
        </header>
        <div
            class="layout flex-grow-1 overflow-hidden"
            :class="state.csvTableCollapsed ? 'extended' : 'limited'"
        >
            <div class="controls">
                <div class="card overflow-y-auto">
                    <header :class="cardHeaderClasses">
                        {{ t('main.importer.entity_settings') }}
                        <div class="toolbox" />
                    </header>
                    <div class="card-body overflow-y-auto mh-100">
                        <EntityImporterSettings
                            v-if="state.fileLoaded"
                            v-model:entityType="entitySettings.entityType"
                            v-model:entityName="entitySettings.entityName"
                            v-model:entity-parent="entitySettings.entityParent"
                            :stats="state.stats"
                            :available-entity-types="availableEntityTypes"
                            :available-columns="state.availableColumns"
                            :disabled="state.inputsDissabled"
                            @update:entity-name="onNameColumnChanged"
                            @update:entity-parent="onParentColumnChanged"
                        />
                        <Alert
                            v-else
                            :message="t('main.importer.info.entity_settings_require_file')"
                            type="info"
                        />
                    </div>
                </div>
                <div class="card overflow-y-auto">
                    <header :class="cardHeaderClasses">
                        {{ t('main.importer.attribute_mapping') }}
                        <div class="toolbox" />
                    </header>
                    <div class="card-body overflow-y-auto mh-100">
                        <EntityAttributeMapping
                            v-if="state.fileLoaded && !!entitySettings.entityType"
                            v-model:attribute-mapping="attributeSettings.mapping"
                            :available-columns="state.availableColumns"
                            :available-attributes="state.availableAttributes"
                            :stats="state.stats"
                            :disabled="state.inputsDissabled"
                            @row-changed="onAttributeMappingSelected"
                        />
                        <Alert
                            v-else
                            type="info"
                            :message="t('main.importer.info.entity_type_has_no_attributes')"
                        />
                    </div>
                </div>
                <div class="card overflow-y-auto">
                    <header :class="cardHeaderClasses">
                        {{ t('main.importer.import_settings') }}
                    </header>
                    <div class="card-body overflow-y-auto mh-100">
                        <importer-update-state
                            v-if="state.validated"
                            :conflict="state.validationData.conflict"
                            :update="state.validationData.update"
                            :create="state.validationData.create"
                            :imported="state.imported"
                            :errors="state.validationErrors"
                        />
                        <Alert
                            v-else
                            :message="t('main.importer.validation.waiting')"
                            type="info"
                        />
                    </div>

                    <footer class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex btn-group">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                :disabled="!state.fileLoaded"
                                @click="removeFile()"
                            >
                                {{ t('global.remove_file') }}
                            </button>
                            <button
                                class="btn btn-sm btn-outline-danger"
                                :disabled="!state.fileLoaded"
                                @click="cancelImport"
                            >
                                {{ t(`global.reset`) }}
                            </button>
                        </div>
                        <LoadingButton
                            v-if="!state.validated"
                            :loading="state.validating"
                            class="btn btn-sm btn-outline-primary"
                            :disabled="!canValidate || state.validating"
                            @click="validate"
                        >
                            <template #icon>
                                <i class="fas fa-check me-1" />
                            </template>
                            {{ t(`main.importer.validate`) }}
                        </LoadingButton>
                        <div
                            v-else
                            class="btn-group"
                        >
                            <button
                                class="btn btn-sm btn-outline-primary"
                                @click="resetValidation"
                            >
                                {{ t(`global.edit`) }}
                            </button>
                            <LoadingButton
                                :loading="state.uploading"
                                class="btn btn-sm btn-outline-primary"
                                :disabled="!canImport || state.uploading"
                                @click="upload"
                            >
                                {{ t(`main.importer.import_btn`) }}
                            </LoadingButton>
                        </div>
                    </footer>
                </div>
            </div>

            <div class="file-preview d-flex flex-column overflow-hidden mh-100 gap-4">
                <file-upload
                    v-if="!state.fileLoaded"
                    :model="state.files"
                    class="rounded border-dashed flex-grow-1 d-flex flex-column gap-3 justify-content-center align-items-center clickable"
                    accept="text/plain,text/csv"
                    extensions="dsv,csv,tsv"
                    :directory="false"
                    :multiple="false"
                    :drop="true"
                    @input-file="addFile"
                >
                    <i class="fas fa-fw fa-file-upload fa-4x" />
                    <div class="info fw-bold fs-5">
                        {{ t('main.importer.drop_csv_file') }}
                    </div>
                </file-upload>
                <csv-table
                    v-else
                    ref="csvTableRef"
                    class="flex-grow-1"
                    :content="state.content"
                    :small="true"
                    @parse="e => extractColumns(e)"
                />
            </div>
        </div>
    </div>
</template>


<script>
    import {
        computed,
        nextTick,
        onMounted,
        reactive,
        ref,
        watch,
    } from 'vue';

    import useEntityStore from '@/bootstrap/stores/entity.js';

    import { stringSimilarity } from 'string-similarity-js';

    import EntityAttributeMapping from '@/components/tools/importer/EntityAttributeMapping.vue';
    import EntityImporterSettings from '@/components/tools/importer/EntityImporterSettings.vue';
    import ImporterUpdateState from '@/components/tools/importer/ImporterUpdateState.vue';
    import LoadingButton from '@/components/forms/button/LoadingButton.vue';

    import { useI18n } from 'vue-i18n';
    import { useToast } from '@/plugins/toast.js';

    import {
        importEntityData,
        validateEntityData,
    } from '@/api.js';

    import {
        showImportError,
    } from '@/helpers/modal.js';

    import {
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        handleUnhandledErrors
    } from '@/bootstrap/http.js';


    export default {
        components: {
            EntityImporterSettings,
            EntityAttributeMapping,
            LoadingButton,
            ImporterUpdateState,
        },
        setup(props, context) {
            const { t } = useI18n();
            const entityStore = useEntityStore();

            const csvTableRef = ref(null);

            let toast = null;
            onMounted(() => {
                nextTick(() => {
                    toast = useToast();
                });
            });

            // FUNCTIONS

            const fileReader = new FileReader();
            fileReader.onload = e => {
                if(state.utf8Content && e.target.result.includes('�')) {
                    // if utf-8 parsed content contains � try to re-read it in latin-2
                    state.encoding = 'ISO-8859-2';
                    readContent();
                    return;
                }
                state.fileLoaded = true;
                state.content = e.target.result;
                state.error = '';
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
                    fileReader.readAsText(state.files[0].file, state.encoding);
                }
            };

            const extractColumns = data => {
                state.availableColumns = data.header;
                state.fileData = data.data;
                state.hasHeaderRow = data.hasHeaderRow;
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
                    state.availableAttributes = entityStore.getEntityTypeAttributes(e.id, true) || [];
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

            const buildFormData = _ => {
                const data = new FormData();
                data.append('file', state.files[0].file);
                data.append('metadata', JSON.stringify({
                    delimiter: state.fileDelimiter,
                    has_header_row: state.hasHeaderRow,
                    encoding: state.encoding,
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
                return data;
            };

            const validate = _ => {
                state.validating = true;
                const formData = buildFormData();
                validateEntityData(formData)
                    .then(data => {
                        state.validated = true;
                        state.error = '';
                        state.validationData = data.summary;
                        state.validationErrors = data.errors;
                    })
                    .catch(axiosError => {
                        console.error(axiosError);
                    })
                    .finally(_ => state.validating = false);
            };

            const upload = _ => {
                state.uploading = true;
                const formData = buildFormData();

                importEntityData(formData).then(data => {
                    state.imported = true;
                    for(let i = 0; i < data.length; i++) {
                        entityStore.add(data[i]);
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
                }).catch(axiosError => {
                    handleUnhandledErrors(axiosError, (e) => {
                        showImportError({
                            message: e.response.data.error,
                            data: e.response.data.data,
                        });
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

            const entitySettings = reactive({
                entityType: null,
                entityName: null,
                entityParent: null,
            });

            function resetEntitySettings() {
                state.stats.entityName = { missing: 0, total: 0 };
                state.stats.entityParent = { missing: 0, total: 0 };
                entitySettings.entityType = null;
                entitySettings.entityName = null;
                entitySettings.entityParent = null;
            }

            const attributeSettings = reactive({
                // autoComplete: true,
                mapping: {},
            });

            function resetAttributeSettings() {
                state.stats.attributes = {};
                attributeSettings.mapping = {};
            }

            //
            const resetImportState = _ => {
                resetValidation();
                resetImport();
            };

            const cancelImport = _ => {
                resetSettings();
                resetImportState();
            };

            const resetImport = _ => {
                state.uploading = false;
                state.imported = false;
            };

            const resetValidation = _ => {
                state.validating = false;
                state.validated = false;
                state.validationData = {
                    create: 0,
                    update: 0,
                    conflict: 0,
                    total: 0
                };

                resetImport();
            };

            const resetSettings = _ => {
                resetEntitySettings();
                resetAttributeSettings();
            };

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

                cancelImport();
            };

            const availableEntityTypes = computed(_ => Object.values(entityStore.entityTypes));
            const state = reactive({
                files: [],
                fileData: [],
                fileDelimiter: '',
                hasHeaderRow: true,
                content: '',
                encoding: 'UTF-8',
                fileLoaded: false,
                availableColumns: [],
                availableAttributes: [],
                stats: {
                    entityName: { missing: 0, total: 0 },
                    entityParent: { missing: 0, total: 0 },
                    attributes: {},
                },
                utf8Content: computed(_ => state.encoding == 'UTF-8'),
                dataMissing: computed(_ => {
                    return !entitySettings.entityType || !entitySettings.entityName || state.stats.entityName.missing > 0;
                }),
                inputsDissabled: computed(_ => {
                    return state.validated || state.uploading || state.imported;
                }),
                csvTableCollapsed: computed(_ => state.fileLoaded && !csvTableRef.value?.csvSettings?.showPreview),
                uploading: false,
                imported: false,
                validating: false,
                validated: false,
                validationErrors: [],
                validationData: {
                    create: 0,
                    update: 0,
                    conflict: 0,
                    total: 0
                },
            });

            watch(() => entitySettings.entityType, entityType => {
                onEntityTypeSelected(entityType);
            });

            const canValidate = computed(_ => {
                return state.fileLoaded && !state.dataMissing && !state.validated;
            });

            const validationIsValid = computed(_ => {
                // If there are multiple matches for single rows.
                if(state.validationData.conflict > 0) {
                    return false;
                }

                // If there are no values at all
                if(state.validationData.create == 0 && state.validationData.update == 0) {
                    return false;
                }

                return true;
            });

            const canImport = computed(_ => {
                return state.fileLoaded && !state.dataMissing && state.validated && validationIsValid.value && !state.uploading && !state.imported;
            });

            const cardHeaderClasses = ['card-header', 'd-flex', 'justify-content-between'];

            // RETURN
            return {
                t,
                // LOCAL
                canImport,
                canValidate,
                cardHeaderClasses,
                addFile,
                cancelImport,
                extractColumns,
                onAttributeMappingSelected,
                onEntityTypeSelected,
                onNameColumnChanged,
                onParentColumnChanged,
                removeFile,
                upload,
                validate,
                resetValidation,
                // STATE
                availableEntityTypes,
                state,
                entitySettings,
                attributeSettings,
                // REFS
                csvTableRef,
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

        .entity-attribute-mapping {
            display: grid;
            grid-auto-columns: minmax();
        }
    }
</style>



<style
    scoped
    lang="scss"
>
    .controls {
        display: grid;
        grid-template-columns: 1fr 2fr 1fr;
        gap: 1rem;
        position: relative;
        overflow: hidden;
    }

    .layout {
        display: grid;
        grid-template-rows: 1fr min-content;
        gap: 1rem;

        &.limited {
            grid-template-rows: fit-content(50%) minmax(min-content, 1fr);
        }
    }

    .entity-attribute-mapping {
        display: grid !important;
        grid-template-columns: 1fr 1fr 1fr;
        align-items: flex-end;
    }

    @media screen and (max-width: 1920px) {
        .entity-attribute-mapping {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 768px) {

        .card {
            overflow-y: visible !important;
        }

        .file-preview,
        .controls {
            grid-template-columns: 1fr;
            overflow-y: auto;
            padding: 1rem;
            border: 1px solid var(--bs-border-color-translucent);
            border-radius: var(--bs-border-radius);
        }

        .entity-attribute-mapping {
            grid-template-columns: 1fr;
        }
    }
</style>