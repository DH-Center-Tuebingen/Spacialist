<template>
    <vue-final-modal
        class="modal-container modal"
        content-class="sp-modal-content sp-modal-content-sm"
        name="edit-attribute-modal"
    >
        <div class="sp-modal-content sp-modal-content-sm">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{
                        t('main.entity.modals.edit.title_attribute', {
                            name: translateConcept(state.attribute.thesaurus_url)
                        })
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
            <div class="modal-body nonscrollable">
                <h5 class="text-center">
                    {{ t('global.dependency') }}
                </h5>
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
                            :value="t(`global.attributes.${state.attribute.datatype}`)"
                            disabled
                        >
                    </div>
                </div>
                <div class="mb-3 row">
                    <label class="col-form-label col-md-2">
                        {{ t('global.depends_on') }}:
                    </label>
                    <div class="col-md-10">
                        <multiselect
                            v-model="state.dependency.attribute"
                            :classes="multiselectResetClasslist"
                            :value-prop="'id'"
                            :label="'thesaurus_url'"
                            :track-by="'id'"
                            :object="true"
                            :mode="'single'"
                            :hide-selected="true"
                            :options="state.selection"
                            :placeholder="t('global.select.placeholder')"
                            @change="dependantSelected"
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
                        <multiselect
                            v-if="state.attributeSelected"
                            v-model="state.dependency.operator"
                            class="mt-2"
                            :classes="multiselectResetClasslist"
                            :value-prop="'id'"
                            :label="'label'"
                            :track-by="'id'"
                            :mode="'single'"
                            :object="true"
                            :hide-selected="true"
                            :options="state.operatorList"
                            :placeholder="t('global.select.placeholder')"
                            @change="operatorSelected"
                        />
                        <div
                            v-if="state.attributeSelected && state.operatorSelected"
                            class="mt-2"
                        >
                            <div
                                v-if="state.inputType == 'boolean'"
                                class="form-check form-switch"
                            >
                                <input
                                    id="dependency-boolean-value"
                                    v-model="state.dependency.value"
                                    type="checkbox"
                                    class="form-check-input"
                                >
                            </div>
                            <input
                                v-else-if="state.inputType == 'number'"
                                v-model.number="state.dependency.value"
                                type="number"
                                class="form-control"
                                :step="state.dependency.attribute.datatype == 'double' ? 0.01 : 1"
                            >
                            <multiselect
                                v-else-if="state.inputType == 'select'"
                                v-model="state.dependency.value"
                                :classes="multiselectResetClasslist"
                                :value-prop="'id'"
                                :label="'concept_url'"
                                :track-by="'id'"
                                :hide-selected="true"
                                :mode="'single'"
                                :options="state.dependantOptions"
                                :placeholder="t('global.select.placeholder')"
                            >
                                <template #option="{ option }">
                                    {{ translateConcept(option.concept_url) }}
                                </template>
                                <template #singlelabel="{ value }">
                                    <div class="multiselect-single-label">
                                        {{ translateConcept(value.concept_url) }}
                                    </div>
                                </template>
                            </multiselect>
                            <input
                                v-else
                                v-model="state.dependency.value"
                                type="text"
                                class="form-control"
                            >
                        </div>
                    </div>
                </div>
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
        toRefs,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import store from '@/bootstrap/store.js';

    import {
        getAttribute,
        getEntityTypeAttribute,
        getEntityTypeDependencies,
        translateConcept,
        multiselectResetClasslist,
    } from '@/helpers/helpers.js';

    export default {
        props: {
            attributeId: {
                required: true,
                type: Number,
            },
            entityTypeId: {
                required: true,
                type: Number,
            },
            attributeSelection: {
                required: true,
                type: Array,
            },
        },
        emits: ['closing', 'confirm'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                attributeId,
                entityTypeId,
                attributeSelection,
            } = toRefs(props);

            // FUNCTIONS
            const convertDependencyObject = dep => {
                const converted = {
                    attribute: null,
                    operator: null,
                    value: null,
                };
                const keys = Object.keys(dep);
                if(keys.length != 1) {
                    return converted;
                }
                const depId = keys[0];
                const data = dep[depId];
                converted.attribute = getAttribute(depId);
                converted.operator = operators.find(o => o.label == data.operator);
                converted.value = data.value;
                return converted;
            };
            const confirmEdit = _ => {
                const data = {};

                const oldDep = convertDependencyObject(state.activeDependency);
                const currDep = state.dependency;
                // simple check if there was a change from no dep -> dep or vice versa
                if(
                    (oldDep.attribute && !currDep.attribute) ||
                    (!oldDep.attribute && currDep.attribute)
                ) {
                    data.dependency = state.dependency;
                } else if(oldDep.attribute && currDep.dependency) {
                    // or else check if dep is set, but has different values
                    if(
                        oldDep.attribute.id != currDep.attribute.id ||
                        oldDep.operator.id != currDep.operator.id ||
                        oldDep.value != currDep.value
                    ) {
                        data.dependency = state.dependency;
                    }
                }

                // Check for changes in width metadata
                if(state.attribute.pivot &&
                    (!state.attribute.pivot.metadata || state.width != state.attribute.pivot.metadata.width)
                ) {
                    data.metadata = {
                        width: state.width,
                    };
                }

                context.emit('confirm', data);
            };
            const closeModal = _ => {
                context.emit('closing', false);
            };
            const dependantSelected = e => {
            };
            const operatorSelected = e => {
            };
            const getInputTypeClass = datatype => {
                switch(datatype) {
                        case 'string':
                        case 'stringf':
                        case 'geography':
                        case 'iconclass':
                        case 'rism':
                        case 'serial':
                            return 'text';
                        case 'double':
                        case 'integer':
                        case 'percentage':
                            return 'number';
                        case 'boolean':
                            return 'boolean';
                        case 'date':
                            return 'date';
                        case 'string-sc':
                        case 'string-mc':
                            return 'select';
                        // TODO handle entity attributes
                        case 'entity':
                        case 'entity-mc':
                            // return 'entity';
                        case 'epoch':
                        case 'timeperiod':
                        case 'dimension':
                        case 'list':
                        case 'table':
                        case 'sql':
                        default:
                            return 'unsupported';
                    }
            };

            // DATA
            const operators = [
                {
                    id: 1,
                    label: '=',
                },
                {
                    id: 2,
                    label: '!=',
                },
                {
                    id: 3,
                    label: '<',
                },
                {
                    id: 4,
                    label: '>',
                },
            ];
            const state = reactive({
                dependency: {
                    attribute: null,
                    operator: null,
                    value: null,
                },
                width: 100,
                dependantOptions: computed(_ => {
                    if(state.attributeSelected && state.operatorSelected && state.inputType == 'select') {
                        return store.getters.attributeSelections[state.dependency.attribute.id];
                    } else {
                        return [];
                    }
                }),
                isValid: computed(_ => {
                    return (
                        state.dependency.attribute && state.dependency.operator && state.dependency.value
                    ) || (
                        !state.dependency.attribute && !state.dependency.operator && !state.dependency.value
                    );
                }),
                operatorList: computed(_ => {
                    if(!state.attributeSelected) return [];

                    switch(state.dependency.attribute.datatype) {
                        case 'epoch':
                        case 'timeperiod':
                        case 'dimension':
                        case 'list':
                        case 'table':
                        case 'sql':
                            return [];
                        // TODO handle entity attributes
                        case 'entity':
                        case 'entity-mc':
                            return [];
                        case 'string':
                        case 'stringf':
                        case 'string-sc':
                        case 'string-mc':
                        case 'geography':
                        case 'iconclass':
                        case 'rism':
                        case 'serial':
                            return operators.filter(o => {
                                switch(o.id) {
                                    case 1:
                                    case 2:
                                        return true;
                                    default:
                                        return false;
                                }
                            });
                        case 'double':
                        case 'integer':
                        case 'date':
                        case 'percentage':
                            return operators.filter(o => {
                                switch(o.id) {
                                    case 1:
                                    case 2:
                                    case 3:
                                    case 4:
                                        return true;
                                    default:
                                        return false;
                                }
                            });
                        case 'boolean':
                            return operators.filter(o => {
                                switch(o.id) {
                                    case 1:
                                        return true;
                                    default:
                                        return false;
                                }
                            });
                    }
                }),
                inputType: computed(_ => {
                    if(!state.attributeSelected || !state.operatorSelected) return 'unsupported';

                    return getInputTypeClass(state.dependency.attribute.datatype);
                }),
                attribute: computed(_ => getEntityTypeAttribute(entityTypeId.value, attributeId.value)),
                activeDependency: computed(_ => getEntityTypeDependencies(entityTypeId.value, attributeId.value)),
                selection: computed(_ => {
                    return attributeSelection.value.filter(a => {
                        return a.id != attributeId.value && getInputTypeClass(a.datatype) != 'unsupported';
                    });
                }),
                attributeSelected: computed(_ => state.dependency.attribute && state.dependency.attribute.id),
                operatorSelected: computed(_ => state.dependency.operator && state.dependency.operator.id),
            });

            // ON MOUNTED
            onMounted(_ => {
                if(!!state.activeDependency) {
                    state.dependency = convertDependencyObject(state.activeDependency);
                }
                if(state.attribute.pivot && state.attribute.pivot.metadata) {
                    state.width = state.attribute.pivot.metadata.width || 100;
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
                multiselectResetClasslist,
                // PROPS
                // LOCAL
                confirmEdit,
                closeModal,
                dependantSelected,
                operatorSelected,
                // STATE
                state,
            }
        },
    }
</script>