<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-md"
        name="edit-entity-type-modal"
    >
        <div class="sp-modal-content sp-modal-content-md">
            <div class="modal-header">
                <h5 class="modal-title fs-heading fw-bold">
                    {{
                        t('global.settings.title')
                    }}

                </h5>
                <span class="ms-2">
                    {{ translateConcept(entityType.thesaurus_url) }}
                </span>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body nonscrollable">
                <form
                    id="edit-entity-type-form"
                    name="edit-entity-type-form"
                    class="d-flex flex-column gap-3"
                    role="form"
                    @submit.prevent="confirmEdit()"
                >

                    <div class="row">
                        <div class="offset-3 col row align-items-center">
                            <div class="form-check form-switch">
                                <input
                                    id="entity-type-root-toggle"
                                    v-model="properties.is_root"
                                    class="form-check-input"
                                    type="checkbox"
                                >
                                <label
                                    class="form-check-label"
                                    for="entity-type-root-toggle"
                                >
                                    {{ t('main.datamodel.detail.properties.top_level') }}
                                </label>
                            </div>
                        </div>
                        <div class="col align-items-center">
                            <div class="row align-items-center">
                                <label
                                    for="entity-color"
                                    style="width: min-content;"
                                >
                                    {{ t('global.color') }}
                                </label>
                                <div class="col align-items-center">
                                    <input
                                        v-model="properties.color"
                                        type="color"
                                        class="form-control form-control-color w-100"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label
                            for="dme-allowed-sub-entity-types-select"
                            class="col-form-label col-md-3 text-end"
                        >
                            {{ t('main.datamodel.detail.properties.sub_types') }}
                        </label>
                        <div class="col-md-9 d-flex">
                            <multiselect
                                id="dme-allowed-sub-entity-types-select"
                                v-model="properties.sub_entity_types"
                                :object="true"
                                :mode="'tags'"
                                :label="'thesaurus_url'"
                                :track-by="'thesaurus_url'"
                                :value-prop="'id'"
                                :options="entityStore.getMinimalEntityTypes"
                                :close-on-select="false"
                                :close-on-deelect="false"
                                :placeholder="t('global.select.placeholder')"
                            >
                                <template #option="{ option }">
                                    {{ translateConcept(option.thesaurus_url) }}
                                </template>
                                <template #tag="{ option, handleTagRemove, disabled }">
                                    <div class="multiselect-tag">
                                        {{ translateConcept(option.thesaurus_url) }}
                                        <span
                                            v-if="!disabled"
                                            class="multiselect-tag-remove"
                                            @click.prevent
                                            @mousedown.prevent.stop="handleTagRemove(option, $event)"
                                        >
                                            <span class="multiselect-tag-remove-icon" />
                                        </span>
                                    </div>
                                </template>
                            </multiselect>
                            <div
                                class="btn-group"
                                role="group"
                            >
                                <button
                                    type="button"
                                    class="btn btn-outline-success btn-sm"
                                    @click="addAll"
                                >
                                    <i class="fas fa-fw fa-tasks" />
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <label
                            class="col-form-label col-3 text-end"
                            for="label"
                        >
                            {{ t('global.label') }}
                        </label>
                        <div class="col-9">
                            <simple-search
                                :endpoint="searchLabel"
                                :key-fn="getConceptLabel"
                                :value="properties.label"
                                @selected="labelSelected"
                            />
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    form="edit-entity-type-form"
                    :disabled="!isDirty"
                >
                    <i class="fas fa-fw fa-save" /> {{ t('global.save') }}
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
        reactive,
        toRefs,
        watch,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        searchLabel,
    } from '@/api.js';

    import {
        translateConcept,
        getConceptLabel,
        getConcept,
    } from '@/helpers/helpers.js';

    import useEntityStore from '@/bootstrap/stores/entity';

    export default {
        props: {
            entityType: {
                required: true,
                type: Object,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();

            const entityStore = useEntityStore();

            function getDirtyValues() {
                const dirtyValues = {};

                if(properties.is_root !== props.entityType.is_root) {
                    dirtyValues.is_root = properties.is_root;
                }
                if(properties.color !== props.entityType.color) {
                    dirtyValues.color = properties.color;
                }
                const oldSubEntityTypeIds = props.entityType.sub_entity_types.map(et => et.id).toSorted();
                const newSubEntityTypeIds = properties.sub_entity_types.map(et => et.id).toSorted();
                
                if(JSON.stringify(oldSubEntityTypeIds) !== JSON.stringify(newSubEntityTypeIds)) {
                    dirtyValues.sub_entity_types = properties.sub_entity_types;
                }
                if(properties?.label?.thesaurus_url && properties.label.thesaurus_url !== props.entityType.thesaurus_url) {
                    dirtyValues.thesaurus_url = properties.label.thesaurus_url;
                }

                return dirtyValues;
            }

            const isDirty = computed(_ => {
                const dirtyValues = getDirtyValues();
                console.log('isDirty check', dirtyValues);
                return Object.keys(dirtyValues).length > 0;
            });

            // FUNCTIONS
            const confirmEdit = _ => {
                context.emit('confirm', getDirtyValues());
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };
            const labelSelected = label => {
                properties.label = label;

                // We need to set the thesaurus_url when the
                // concept_url is given only.
                if(label.concept_url) {
                    properties.label.thesaurus_url = label.concept_url;
                }

            };
            const resetProperties = () => {
                const entityType = props.entityType;

                const concept = {
                    ...getConcept(entityType.thesaurus_url)
                };

                if(!concept.labels) {
                    concept.labels = [{
                        label: concept.label,
                    }];
                }

                Object.assign(properties, {
                    is_root: entityType.is_root,
                    color: entityType.color,
                    sub_entity_types: entityType.sub_entity_types.slice(),
                    label: concept,
                });
            };

            const addAll = _ => {
                properties.sub_entity_types = entityStore.getMinimalEntityTypes.slice();
            };

            const properties = reactive({
                is_root: null,
                color: null,
                sub_entity_types: null,
                label: null,
            });
            resetProperties();

            // WATCHER
            watch(props.entityType, (newValue, oldValue) => {
                resetProperties();
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                getConceptLabel,
                searchLabel,
                isDirty,
                // PROPS
                // LOCAL
                addAll,
                confirmEdit,
                closeModal,
                labelSelected,
                // STATE
                entityStore,
                properties,
            };
        },
    };
</script>