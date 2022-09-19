<template>
    <div class="h-100 d-flex flex-column">
        <h4 class="d-flex flex-row gap-2 align-items-center">
            {{ t('main.importer.title') }}
            <button v-show="state.contentRead" type="button" class="btn btn-outline-danger btn-sm" @click="removeFile()">
                <i class="fas fa-fw fa-times"></i>
                {{ t('global.remove_file') }}
            </button>
        </h4>
        <div class="col d-flex flex-column gap-2" v-if="state.contentRead">
            <csv-table
                :content="state.content"
                :small="true"
                :linenumbers="true"
                @parse="e => extractColumns(e)"
            />
            <div class="flex-grow-1 scroll-y-auto scroll-x-hidden">
                <form class="row g-3" id="import-data-form" name="import-data-form" @submit.prevent="confirmImport()">
                    <div class="col-md-4 col-sm-12">
                        <label for="import-entity-type" class="form-label">
                            {{ t('main.importer.selected_entity_type') }}
                        </label>
                        <multiselect
                            id="import-entity-type"
                            :classes="multiselectResetClasslist"
                            :valueProp="'id'"
                            :label="'thesaurus_url'"
                            :track-by="'id'"
                            :object="true"
                            :mode="'single'"
                            :options="availableEntityTypes"
                            :placeholder="t('global.select.placeholder')"
                            :hideSelected="true"
                            v-model="state.postData.entityType"
                            @select="onEntityTypeSelected">
                            <template v-slot:option="{ option }">
                                {{ translateConcept(option.thesaurus_url) }}
                            </template>
                            <template v-slot:singlelabel="{ value }">
                                <div class="multiselect-single-label">
                                    {{ translateConcept(value.thesaurus_url) }}
                                </div>
                            </template>
                        </multiselect>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <label for="import-entity-name" class="form-label">
                            {{ t('main.importer.column_entity_name') }}
                            <span v-if="state.stats.entityName.missing > 0" :title="t('main.importer.missing_required_values', {
                                        miss: state.stats.entityName.missing,
                                        total: state.stats.entityName.total,
                                    }, state.stats.entityName.total)">
                                <i class="fas fa-fw fa-exclamation-circle text-danger"></i>
                            </span>
                            <span v-else-if="state.stats.entityName.missing == 0" :title="t('main.importer.no_missing_values')">
                                <i class="fas fa-fw fa-check-circle text-success"></i>
                            </span>
                        </label>
                        <multiselect
                            id="import-entity-name"
                            :classes="multiselectResetClasslist"
                            :object="false"
                            :mode="'single'"
                            :options="state.availableColumns"
                            :placeholder="t('global.select.placeholder')"
                            :hideSelected="true"
                            v-model="state.postData.entityName"
                            @select="e => onColumnSelected(e, 'name', true)">
                        </multiselect>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <label for="import-entity-parent" class="form-label">
                            {{ t('main.importer.column_entity_parent') }}
                            <span v-if="state.stats.entityParent.missing > 0" :title="t('main.importer.missing_non_required_values', {
                                        miss: state.stats.entityParent.missing,
                                        total: state.stats.entityParent.total,
                                    }, state.stats.entityParent.total)">
                                <i class="fas fa-fw fa-exclamation-circle text-warning"></i>
                            </span>
                            <span v-else-if="state.stats.entityParent.missing == 0" :title="t('main.importer.no_missing_values')">
                                <i class="fas fa-fw fa-check-circle text-success"></i>
                            </span>
                        </label>
                        <multiselect
                            id="import-entity-parent"
                            :classes="multiselectResetClasslist"
                            :object="false"
                            :mode="'single'"
                            :options="state.availableColumns"
                            :placeholder="t('global.select.placeholder')"
                            :hideSelected="true"
                            v-model="state.postData.entityParent"
                            @select="e => onColumnSelected(e, 'parent', true)">
                        </multiselect>
                    </div>
                    <hr>
                    <div class="col-6 col-sm-12"></div>
                    <div v-if="state.postData.entityType" class="row">
                        <template v-for="(attr, i) in state.availableAttributes" :key="i">
                            <div class="col-md-3 col-sm-6 d-flex align-items-center justify-content-end gap-2">
                                <span v-if="state.stats.attributes[attr.id] && state.stats.attributes[attr.id].missing > 0" :title="t('main.importer.missing_non_required_values', {
                                        miss: state.stats.attributes[attr.id].missing,
                                        total: state.stats.attributes[attr.id].total,
                                    }, state.stats.attributes[attr.id].total)">
                                    <i class="fas fa-fw fa-exclamation-circle text-warning"></i>
                                </span>
                                <span v-else-if="state.stats.attributes[attr.id] && state.stats.attributes[attr.id].missing == 0" :title="`You are all set. No missing values for this option.`">
                                    <i class="fas fa-fw fa-check-circle text-success"></i>
                                </span>
                                <span class="text-body">
                                    {{ translateConcept(attr.thesaurus_url) }}
                                </span>
                                <span class="text-muted small">
                                    {{ t(`global.attributes.${attr.datatype}`) }}
                                </span>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <multiselect
                                    :id="`input-data-column-${attr.id}`"
                                    :classes="multiselectResetClasslist"
                                    :object="false"
                                    :mode="'single'"
                                    :options="state.availableColumns"
                                    :placeholder="t('global.select.placeholder')"
                                    :hideSelected="true"
                                    v-model="state.postData.attributes[attr.id]"
                                    @select="e => onColumnSelected(e, attr.id)">
                                </multiselect>
                            </div>
                            <hr v-if="i % 2 == 1" class="my-3" />
                        </template>
                    </div>
                </form>
            </div>
            <div>
                <button type="submit" class="btn btn-outline-primary" form="import-data-form" :disabled="state.dataMissing">
                    {{ t('main.importer.import_btn') }}
                </button>
            </div>
        </div>
        <div class="col d-flex flex-column gap-2" v-else>
            <alert
                :message="t('main.importer.upload_csv_info')"
                :type="'info'"
                :icontext="t('global.information')"
                />
            <file-upload
                class="rounded border-dashed flex-grow-1 d-flex flex-column gap-2 justify-content-center align-items-center clickable"
                accept="text/plain,text/csv"
                extensions="dsv,csv,tsv"
                v-model="state.files"
                :directory="false"
                :multiple="false"
                :drop="true"
                :ref="el => attrRef = el"
                @input-file="addFile">
                    <i class="fas fa-fw fa-file-upload fa-5x"></i>
                    <h5>
                        {{ t('main.importer.drop_csv_file') }}
                    </h5>
            </file-upload>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
        onMounted,
        watch,
    } from 'vue';

    import store from '@/bootstrap/store.js';
    import { useI18n } from 'vue-i18n';

    import { useToast } from '@/plugins/toast.js';

    import {
        importEntityData,
    } from '@/api.js';

    import {
        translateConcept,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';

    import {
        showImportError,
    } from '@/helpers/modal.js';

    export default {
        setup(props, context) {
            const { t } = useI18n();
            const toast = useToast();
            // FETCH

            // FUNCTIONS
            const readContent = _ => {
                if(!state.files || state.files.length == 0) {
                    state.content = '';
                    state.contentRead = false;
                } else {
                    fileReader.readAsText(state.files[0].file);
                }
            };
            const extractColumns = data => {
                state.availableColumns = data.header;
                state.fileData = data.data;
                state.fileDelimiter = data.delimiter;
            };
            const onEntityTypeSelected = e => {
                state.availableAttributes = store.getters.entityTypeAttributes(e.id);
            };
            const onColumnSelected = (e, key, isNonAttribute = false) => {
                const emptyValues = state.fileData.filter(fd => fd[e] == '');
                const stats = {
                    missing: emptyValues.length,
                    total: state.fileData.length,
                };
                if(isNonAttribute) {
                    if(key == 'name') {
                        state.stats.entityName = stats;
                    } else if(key == 'parent') {
                        state.stats.entityParent = stats;
                    }
                } else {
                    state.stats.attributes[key] = stats;
                }
            };
            const confirmImport = _ => {
                const data = new FormData();
                data.append('file', state.files[0].file);
                data.append('metadata', JSON.stringify({
                    delimiter: state.fileDelimiter,
                }));
                const postData = {
                    name_column: state.postData.entityName,
                    entity_type_id: state.postData.entityType.id,
                    attributes: state.postData.attributes,
                };
                if(!!state.postData.entityParent) {
                    postData['parent_column'] = state.postData.entityParent;
                }
                data.append('data', JSON.stringify(postData));

                importEntityData(data).then(data => {
                    for(let i=0; i<data.length; i++) {
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
                });
            };
            const addFile = (newFile, oldFile) => {
                // if(Boolean(newFile) !== Boolean(oldFile) || oldFile.error !== newFile.error) {
                //     state.fileList.push(newFile);
                // }
            };
            const removeFile = _ => {
                state.files = [];
                state.fileData = [];
                state.content = '';
                state.contentRead = false;
                state.availableColumns = [];
                state.availableAttributes = [];
                state.stats.entityName = '';
                state.stats.entityParent = '';
                state.stats.attributes = {};
                state.postData.entityType = null;
                state.postData.entityName = null;
                state.postData.entityParent = null;
                state.postData.attributes = {};
            };

            // DATA
            const fileReader = new FileReader();
            fileReader.onload = e => {
                state.content = e.target.result;
                state.contentRead = true;
            };
            const attrRef = ref({});
            const availableEntityTypes = Object.values(store.getters.entityTypes);
            const state = reactive({
                files: [],
                fileData: [],
                fileDelimiter: '',
                content: '',
                contentRead: false,
                availableColumns: [],
                availableAttributes: [],
                stats: {
                    entityName: '',
                    entityParent: '',
                    attributes: {},
                },
                postData: {
                    entityType: null,
                    entityName: null,
                    entityParent: null,
                    attributes: {},
                },
                dataMissing: computed(_ => {
                    return !state.postData.entityType || !state.postData.entityName || state.stats.entityName.missing > 0;
                }),
            });

            onMounted(_ => {
                readContent();
            });

            watch(_ => state.files,
                async(newValue, oldValue) => {
                    if(newValue) {
                        readContent();
                    }
                }
            );

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                multiselectResetClasslist,
                // LOCAL
                extractColumns,
                onEntityTypeSelected,
                onColumnSelected,
                confirmImport,
                addFile,
                removeFile,
                // PROPS
                // STATE
                attrRef,
                availableEntityTypes,
                state,
            };
        },
    }
</script>
