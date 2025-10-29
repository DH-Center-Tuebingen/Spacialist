<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content"
        name="edit-attribute-modal"
    >
        <div class="sp-modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <template v-if="state.attribute.is_system">
                        {{
                            t('main.entity.modals.edit.title_attribute', {
                                name: t('global.attributes.system-separator')
                            })
                        }}
                    </template>
                    <template v-else>
                        {{
                            t('main.entity.modals.edit.title_attribute', {
                                name: translateConcept(state.attribute.thesaurus_url)
                            })
                        }}
                    </template>
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    aria-label="Close"
                    data-bs-dismiss="modal"
                    @click="closeModal()"
                />
            </div>
            <div class="modal-body overflow-hidden d-flex flex-column">
                <template v-if="state.attribute.is_system">
                    <div class="mb-3 row">
                        <label class="col-form-label text-end col-md-2">
                            {{ t('global.text') }}:
                        </label>
                        <div class="col-md-10">
                            <simple-search
                                :endpoint="searchLabel"
                                :key-fn="getConceptLabel"
                                @selected="handleSeparatorRename"
                            />
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="mb-3 row">
                        <label class="col-form-label text-end col-md-2">
                            {{ t('global.label') }}:
                        </label>
                        <div class="col-md-10">
                            <input
                                type="text"
                                class="form-control"
                                :value="translateConcept(state.attribute.thesaurus_url)"
                                disabled
                            >
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-form-label text-end col-md-2">
                            {{ t('global.type') }}:
                        </label>
                        <div class="col-md-10">
                            <input
                                type="text"
                                class="form-control"
                                :value="getLabel(state.attribute.datatype)"
                                disabled
                            >
                        </div>
                    </div>
                    <div class="row">
                        <label
                            class="col-form-label text-end col-md-2"
                            for="attribute-check-required"
                        >
                            {{ t('global.required') }}:
                        </label>
                        <div class="col-md-10 d-flex align-items-center">
                            <div class="form-check form-switch">
                                <input
                                    id="attribute-check-required"
                                    class="form-check-input"
                                    type="checkbox"
                                    v-model="state.required"
                                >
                            </div>
                        </div>
                    </div>
                </template>
                <hr>
                <h5 class="text-center">
                    {{ t('global.dependency.title') }}
                </h5>
                <DependencyForm
                    v-model="state.dependency"
                    :options="state.supportedEntityTypeAttributes"
                />
                <hr>
                <div class="row">
                    <h5 class="text-center">
                        {{ t('global.width') }}
                    </h5>
                    <div class="d-flex justify-content-between">
                        <span>50%</span>
                        <span>100%</span>
                    </div>
                    <input
                        id="attribute-width-slider"
                        v-model.number="state.width"
                        type="range"
                        class="form-range px-3"
                        min="50"
                        max="100"
                        step="50"
                    >
                </div>
            </div>
            <div class="modal-footer">
                <button
                    type="submit"
                    class="btn btn-outline-success"
                    :disabled="!state.isValid"
                    @click="confirmEdit()"
                >
                    <i class="fas fa-fw fa-save" /> {{ t('global.update') }}
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

    import useAttributeStore from '@/bootstrap/stores/attribute.js';
    import useEntityStore from '@/bootstrap/stores/entity.js';

    import {
        searchLabel,
    } from '@/api.js';

    import {
        getConceptLabel,
        getEntityTypeAttribute,
        getEntityTypeDependencies,
        translateConcept,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';

    import {
        getEmptyGroup,
        getInputTypeClass,
        formatDependency,
    } from '@/helpers/dependencies.js';

    import DependencyForm from '@/components/entity/dependency/DependencyForm.vue';

    export default {
        components: {
            DependencyForm,
        },
        props: {
            attributeId: {
                required: true,
                type: Number,
            },
            entityTypeId: {
                required: true,
                type: Number,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const attributeStore = useAttributeStore();
            const entityStore = useEntityStore();

            // FUNCTIONS
            const validateDependencyRule = rule => {
                const valuesAreSet = rule.attribute?.id && rule.operator?.id;
                return valuesAreSet && (rule.operator.no_parameter || rule.value != null);
            };

            const confirmEdit = _ => {
                const data = {
                    dependency: state.dependency,
                };

                // Check for changes in width metadata
                if(state.attribute.pivot &&
                    (!state.attribute.pivot.metadata || state.width != state.attribute.pivot.metadata.width)
                ) {
                    data.metadata = {
                        width: state.width,
                    };
                }
                if(state.attribute.pivot &&
                    (!state.attribute.pivot.metadata || state.required != state.attribute.pivot.metadata.required)
                ) {
                    data.metadata = {
                        required: state.required,
                    };
                }
                if(state.attribute.is_system && state.separatorTitle && (!state.attribute.pivot.metadata?.title || state.separatorTitle != state.attribute.pivot.metadata.title)
                ) {
                    data.metadata = {
                        title: state.separatorTitle,
                    };
                }

                context.emit('confirm', data);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };
            const handleSeparatorRename = label => {
                if(label === null) {
                    state.separatorTitle = null;
                } else if(label.concept_url) {
                    state.separatorTitle = label.concept_url;
                } else {
                    console.error('Invalid separator label', label);
                }
            };
            const getLabel = datatype => {
                if(attributeStore.isFromPlugin(datatype)) {
                    return t(attributeStore.getPluginAttributeLabel(datatype));
                } else {
                    return t(`global.attributes.${datatype}`)
                }
            };

            // DATA
            const state = reactive({
                separatorTitle: '',
                dependency: {
                    or: true,
                    groups: [getEmptyGroup(false)],
                },
                width: 100,
                required: false,
                isValid: computed(_ => {
                    return state.dependency.groups.every(group => {
                        return group.rules.length == 0 || group.rules.every(rule => validateDependencyRule(rule));
                    });
                }),
                attribute: {},
                inputTypeClass: computed(_ => getInputTypeClass(state.attribute.datatype)),
                supportedEntityTypeAttributes: computed(_ => {
                    const attributeSelection = entityStore.getEntityTypeAttributes(props.entityTypeId);
                    const supportedEntityTypeAttributes = attributeSelection.filter(a => {
                        return a.id != props.attributeId && getInputTypeClass(a.datatype) != 'unsupported';
                    });
                    return supportedEntityTypeAttributes;
                }),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.attribute = getEntityTypeAttribute(props.entityTypeId, props.attributeId);
                const currentDependency = getEntityTypeDependencies(props.entityTypeId, props.attributeId);
                if(currentDependency) {
                    state.dependency = formatDependency(currentDependency);
                }
                if(state.attribute?.pivot?.metadata) {
                    state.width = state.attribute.pivot.metadata.width || 100;
                    state.required = state.attribute.pivot.metadata?.required === true;
                    if(state.attribute.is_system) {
                        state.separatorTitle = state.attribute.pivot.metadata.title;
                    }
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                searchLabel,
                getConceptLabel,
                translateConcept,
                multiselectResetClasslist,
                // PROPS
                // LOCAL
                getInputTypeClass,
                confirmEdit,
                closeModal,
                handleSeparatorRename,
                getLabel,
                // STATE
                state,
            };
        },
    };
</script>