<template>
    <div class="h-100 d-flex flex-column">
        <h4 class="d-flex flex-row gap-2 align-items-center">
            {{ t('main.importer.title') }}
            <button
                v-show="state.contentRead"
                type="button"
                class="btn btn-outline-danger btn-sm"
                @click="removeFile()"
            >
                <i class="fas fa-fw fa-times" />
                {{ t('global.remove_file') }}
            </button>
        </h4>
        <div
            v-if="state.contentRead"
            class="col d-flex flex-column gap-2"
        >
            <csv-table
                :content="state.content"
                :small="true"
                :linenumbers="true"
                @parse="e => extractColumns(e)"
            />
            <div class="flex-grow-1 scroll-y-auto scroll-x-hidden">
                <form
                    id="import-data-form"
                    class="row g-3"
                    name="import-data-form"
                    @submit.prevent="confirmImport()"
                >
                    <div class="col-md-4 col-sm-12">
                        <label
                            for="import-entity-type"
                            class="form-label"
                        >
                            {{ t('main.importer.selected_entity_type') }}
                        </label>
                        <multiselect
                            id="import-entity-type"
                            v-model="state.postData.entityType"
                            :classes="multiselectResetClasslist"
                            :value-prop="'id'"
                            :label="'thesaurus_url'"
                            :track-by="'id'"
                            :object="true"
                            :mode="'single'"
                            :options="availableEntityTypes"
                            :placeholder="t('global.select.placeholder')"
                            :hide-selected="true"
                            @select="onEntityTypeSelected"
                        >
                            <template #option="{ option }">
                                {{ translateConcept(option.thesaurus_url) }}
                            </template>
                            <template #singlelabel="{ value }">
                                <div class="multiselect-single-label">
                                    {{ translateConcept(value.thesaurus_url) }}
                                </div>
                            </template>
                        </multiselect>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <label
                            for="import-entity-name"
                            class="form-label"
                        >
                            {{ t('main.importer.column_entity_name') }}
                            <span
                                v-if="state.stats.entityName.missing > 0"
                                :title="t('main.importer.missing_required_values', {
                                    miss: state.stats.entityName.missing,
                                    total: state.stats.entityName.total,
                                }, state.stats.entityName.total)"
                            >
                                <i class="fas fa-fw fa-exclamation-circle text-danger" />
                            </span>
                            <span
                                v-else-if="state.stats.entityName.missing == 0"
                                :title="t('main.importer.no_missing_values')"
                            >
                                <i class="fas fa-fw fa-check-circle text-success" />
                            </span>
                        </label>
                        <multiselect
                            id="import-entity-name"
                            v-model="state.postData.entityName"
                            :classes="multiselectResetClasslist"
                            :object="false"
                            :mode="'single'"
                            :options="state.availableColumns"
                            :placeholder="t('global.select.placeholder')"
                            :hide-selected="true"
                            @select="e => onColumnSelected(e, 'name', 'main')"
                        />
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <label
                            for="import-entity-parent"
                            class="form-label"
                        >
                            {{ t('main.importer.column_entity_parent') }}
                            <span
                                v-if="state.stats.entityParent.missing > 0"
                                :title="t('main.importer.missing_non_required_values', {
                                    miss: state.stats.entityParent.missing,
                                    total: state.stats.entityParent.total,
                                }, state.stats.entityParent.total)"
                            >
                                <i class="fas fa-fw fa-exclamation-circle text-warning" />
                            </span>
                            <span
                                v-else-if="state.stats.entityParent.missing == 0"
                                :title="t('main.importer.no_missing_values')"
                            >
                                <i class="fas fa-fw fa-check-circle text-success" />
                            </span>
                        </label>
                        <multiselect
                            id="import-entity-parent"
                            v-model="state.postData.entityParent"
                            :classes="multiselectResetClasslist"
                            :object="false"
                            :mode="'single'"
                            :options="state.availableColumns"
                            :placeholder="t('global.select.placeholder')"
                            :hide-selected="true"
                            @select="e => onColumnSelected(e, 'parent', 'main')"
                        />
                    </div>
                    <hr>
                    <div
                        v-if="hasPlugins()"
                        class="mt-0"
                    >
                        <a
                            href="#"
                            class="text-body text-decoration-none fw-bold"
                            @click.prevent="state.showPluginTab = !state.showPluginTab"
                        >
                            Plugin-specific fields
                            <span v-show="state.showPluginTab">
                                <i class="fas fa-fw fa-caret-up" />
                            </span>
                            <span v-show="!state.showPluginTab">
                                <i class="fas fa-fw fa-caret-down" />
                            </span>
                        </a>
                        <div
                            v-if="state.showPluginTab"
                            class="row g-3"
                        >
                            <div
                                v-if="hasPlugin('Map')"
                                class="col-md-3 col-sm-6"
                            >
                                <label
                                    for="import-entity-plugin-map-geodata"
                                    class="form-label"
                                >
                                    Geo information to upload & link
                                    <span
                                        v-if="state.stats.plugins['map.geodata'] && state.stats.plugins['map.geodata'].missing > 0"
                                        :title="t('main.importer.missing_non_required_values', {
                                            miss: state.stats.plugins['map.geodata'].missing,
                                            total: state.stats.plugins['map.geodata'].total,
                                        }, state.stats.plugins['map.geodata'].total)"
                                    >
                                        <i class="fas fa-fw fa-exclamation-circle text-warning" />
                                    </span>
                                    <span
                                        v-else-if="state.stats.plugins['map.geodata'] && state.stats.plugins['map.geodata'].missing == 0"
                                        :title="t('main.importer.no_missing_values')"
                                    >
                                        <i class="fas fa-fw fa-check-circle text-success" />
                                    </span>
                                </label>
                                <multiselect
                                    id="import-entity-plugin-map-geodata"
                                    v-model="state.postData.plugins['map.geodata']"
                                    :classes="multiselectResetClasslist"
                                    :object="false"
                                    :mode="'single'"
                                    :options="state.availableColumns"
                                    :placeholder="t('global.select.placeholder')"
                                    :hide-selected="true"
                                    @select="e => onColumnSelected(e, 'map.geodata', 'plugin')"
                                />
                            </div>
                            <div
                                v-if="hasPlugin('Map')"
                                class="col-1"
                            >
                                <label
                                    for="import-entity-plugin-map-epsg"
                                    class="form-label"
                                >
                                    EPSG-Code
                                </label>
                                <input
                                    id="import-entity-plugin-map-epsg"
                                    v-model="state.postData.plugins['map.epsg']"
                                    class="form-control"
                                    type="text"
                                >
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="col-6 col-sm-12" />
                    <div
                        v-if="state.postData.entityType"
                        class="row"
                    >
                        <template
                            v-for="(attr, i) in state.availableAttributes"
                            :key="i"
                        >
                            <div class="col-md-3 col-sm-6 d-flex align-items-center justify-content-end gap-2">
                                <span
                                    v-if="state.stats.attributes[attr.id] && state.stats.attributes[attr.id].missing > 0"
                                    :title="t('main.importer.missing_non_required_values', {
                                        miss: state.stats.attributes[attr.id].missing,
                                        total: state.stats.attributes[attr.id].total,
                                    }, state.stats.attributes[attr.id].total)"
                                >
                                    <i class="fas fa-fw fa-exclamation-circle text-warning" />
                                </span>
                                <span
                                    v-else-if="state.stats.attributes[attr.id] && state.stats.attributes[attr.id].missing == 0"
                                    :title="`You are all set. No missing values for this option.`"
                                >
                                    <i class="fas fa-fw fa-check-circle text-success" />
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
                                    v-model="state.postData.attributes[attr.id]"
                                    :classes="multiselectResetClasslist"
                                    :object="false"
                                    :mode="'single'"
                                    :options="state.availableColumns"
                                    :placeholder="t('global.select.placeholder')"
                                    :hide-selected="true"
                                    @select="e => onColumnSelected(e, attr.id, 'attr')"
                                />
                            </div>
                            <hr
                                v-if="i % 2 == 1"
                                class="my-3"
                            >
                        </template>
                    </div>
                </form>
            </div>
            <div>
                <button
                    v-if="!state.uploading"
                    type="submit"
                    class="btn btn-outline-primary"
                    form="import-data-form"
                    :disabled="state.dataMissing || state.uploading"
                >
                    {{ t('main.importer.import_btn') }}
                </button>
                <button
                    v-else
                    type="button"
                    class="btn btn-outline-primary d-flex gap-2 align-items-center"
                    disabled
                >
                    <Spinner />
                    {{ $("global.uploading") }}
                </button>
            </div>
        </div>
        <div
            v-else
            class="col d-flex flex-column gap-2"
        >
            <alert
                :message="t('main.importer.upload_csv_info')"
                :type="'info'"
                :icontext="t('global.information')"
            />
            <file-upload
                :ref="el => attrRef = el"
                v-model="state.files"
                class="rounded border-dashed flex-grow-1 d-flex flex-column gap-2 justify-content-center align-items-center clickable"
                accept="text/plain,text/csv"
                extensions="dsv,csv,tsv"
                :directory="false"
                :multiple="false"
                :drop="true"
                @input-file="addFile"
            >
                <i class="fas fa-fw fa-file-upload fa-5x" />
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
        hasPlugin,
        hasPlugins,
    } from '@/helpers/helpers.js';

    import {
        showImportError,
    } from '@/helpers/modal.js';

    import {stringSimilarity} from 'string-similarity-js';
    import Spinner from './Spinner.vue';

    export default {
    components: { 
        Spinner 
    },
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
        const guessColumns = _ => {
            for(const attr of state.availableAttributes) {
                const bestMatch = {
                    col: null,
                    score: 0,
                };
                const name = translateConcept(attr.thesaurus_url);
                for(const col of state.availableColumns) {
                    const score = stringSimilarity(name, col);
                    if(score > bestMatch.score) {
                        bestMatch.col = col;
                        bestMatch.score = score;
                    }
                }
                if(bestMatch.col) {
                    state.postData.attributes[attr.id] = bestMatch.col;
                    onColumnSelected(bestMatch.col, attr.id, 'attr');
                }
            }
        };
        const onEntityTypeSelected = e => {
            state.availableAttributes = store.getters.entityTypeAttributes(e.id).filter(eta => eta.datatype != 'system-separator');
            guessColumns();
        };
        const onColumnSelected = (e, key, type) => {
            const emptyValues = state.fileData.filter(fd => fd[e] == '');
            const stats = {
                missing: emptyValues.length,
                total: state.fileData.length,
            };
            if(type == 'main') {
                if(key == 'name') {
                    state.stats.entityName = stats;
                } else if(key == 'parent') {
                    state.stats.entityParent = stats;
                }
            } else if(type == 'attr') {
                state.stats.attributes[key] = stats;
            } else if(type == 'plugin') {
                state.stats.plugins[key] = stats;
            }
        };
        const confirmImport = async _ => {
            const data = new FormData();
            state.uploading = true;
            data.append('file', state.files[0].file);
            data.append('metadata', JSON.stringify({
                delimiter: state.fileDelimiter,
            }));
            const postData = {
                name_column: state.postData.entityName,
                entity_type_id: state.postData.entityType.id,
                attributes: state.postData.attributes,
                plugins: state.postData.plugins,
            };
            if(!!state.postData.entityParent) {
                postData['parent_column'] = state.postData.entityParent;
            }

            try {
                data.append('data', JSON.stringify(postData));
            
                const responseData = await importEntityData(data);
            
                for(let i = 0; i < responseData.length; i++) {
                    store.dispatch('addEntity', responseData[i]);
                }
                
                toast.$toast(t('main.importer.success', {
                    cnt: responseData.length
                }, responseData.length), '', {
                    duration: 2500,
                    autohide: true,
                    channel: 'success',
                    icon: true,
                    simple: true,
                });
            } catch(e) {
                if(!e.response){
                    console.error(e);
                } else {
                    showImportError(e.response.data);
                }
            }
            state.uploading = false;
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
                plugins: {},
            },
            postData: {
                entityType: null,
                entityName: null,
                entityParent: null,
                attributes: {},
                plugins: {},
            },
            dataMissing: computed(_ => {
                return !state.postData.entityType || !state.postData.entityName || state.stats.entityName.missing > 0;
            }),
            uploading: false,
        });
        onMounted(_ => {
            readContent();
        });
        watch(_ => state.files, async (newValue, oldValue) => {
            if(newValue) {
                readContent();
            }
        });
        // RETURN
        return {
            t,
            // HELPERS
            translateConcept,
            multiselectResetClasslist,
            hasPlugin,
            hasPlugins,
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
    }
}
</script>
