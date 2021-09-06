<template>
    <vue-final-modal
        classes="modal-container"
        content-class="sp-modal-content sp-modal-content-sm"
        v-model="state.show"
        name="edit-attribute-modal">
        <div class="modal-header">
            <h5 class="modal-title">
                {{
                    t('main.entity.modals.edit.title_attribute', {
                        name: translateConcept(state.attribute.thesaurus_url)
                    })
                }}
            </h5>
            <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal" @click="closeModal()">
            </button>
        </div>
        <div class="modal-body nonscrollable">
            <div class="mb-3 row">
                <label class="col-form-label text-end col-md-2">
                    {{ t('global.label') }}:
                </label>
                <div class="col-md-10">
                    <input type="text" class="form-control" :value="translateConcept(state.attribute.thesaurus_url)" disabled />
                </div>
            </div>
            <div class="mb-3 row">
                <label class="col-form-label text-end col-md-2">
                    {{ t('global.type') }}:
                </label>
                <div class="col-md-10">
                    <input type="text" class="form-control" :value="t(`global.attributes.${state.attribute.datatype}`)" disabled />
                </div>
            </div>
            <div class="row">
                <label class="col-form-label col-md-2">
                    {{ t('global.depends_on') }}:
                </label>
                <div class="col-md-10">
                    <multiselect
                        :valueProp="'id'"
                        :label="'thesaurus_url'"
                        :track-by="'id'"
                        :object="true"
                        :mode="'single'"
                        :hideSelected="true"
                        :options="state.selection"
                        :placeholder="t('global.select.placeholder')"
                        v-model="state.dependency.attribute"
                        @change="dependantSelected">
                        <template v-slot:option="{ option }">
                            {{ translateConcept(option.thesaurus_url) }}
                        </template>
                        <template v-slot:singlelabel="{ value }">
                            <div class="px-2">
                                {{ translateConcept(value.thesaurus_url) }}
                            </div>
                        </template>
                    </multiselect>
                    <multiselect
                        v-if="state.attributeSelected"
                        class="mt-2"
                        :valueProp="'id'"
                        :label="'label'"
                        :track-by="'id'"
                        :mode="'single'"
                        :object="true"
                        :hideSelected="true"
                        :options="state.operatorList"
                        :placeholder="t('global.select.placeholder')"
                        v-model="state.dependency.operator"
                        @change="operatorSelected">
                    </multiselect>
                    <div
                        v-if="state.attributeSelected && state.operatorSelected"
                        class="mt-2">
                        <div class="form-check form-switch" v-if="state.inputType == 'boolean'">
                            <input type="checkbox" class="form-check-input" id="dependency-boolean-value" v-model="state.dependency.value" />
                        </div>
                        <input type="number" class="form-control" :step="state.dependency.attribute.datatype == 'double' ? 0.01 : 1" v-else-if="state.inputType == 'number'" v-model.number="state.dependency.value" />
                        <multiselect
                            v-else-if="state.inputType == 'select'"
                            :valueProp="'id'"
                            :label="'concept_url'"
                            :track-by="'id'"
                            :hideSelected="true"
                            :mode="'single'"
                            :options="depends.values"
                            :placeholder="t('global.select.placeholder')"
                            v-model="state.dependency.value">
                        </multiselect>
                        <input type="text" class="form-control" v-else v-model="state.dependency.value" />
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success" :disabled="!state.isValid" @click="confirmEdit()">
                <i class="fas fa-fw fa-save"></i> {{ t('global.update') }}
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

    import {
        getAttribute,
        getEntityTypeDependencies,
        translateConcept,
    } from '../../../helpers/helpers.js';

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
            const confirmEdit = _ => {
                state.show = false;
                context.emit('confirm', state.dependency);
            };
            const closeModal = _ => {
                state.show = false;
                context.emit('closing', false);
            };
            const dependantSelected = e => {
            };
            const operatorSelected = e => {
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
                show: false,
                dependency: {
                    attribute: null,
                    operator: null,
                    value: null,
                },
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
                        case 'string':
                        case 'stringf':
                        case 'string-sc':
                        case 'string-mc':
                        case 'geography':
                        case 'entity':
                        case 'iconclass':
                        case 'rism':
                        case 'epoch':
                        case 'timeperiod':
                        case 'dimension':
                        case 'list':
                        case 'table':
                        case 'sql':
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

                    switch(state.dependency.attribute.datatype) {
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
                        case 'entity':
                            return 'select';
                        case 'epoch':
                        case 'timeperiod':
                        case 'dimension':
                        case 'list':
                        case 'table':
                        case 'sql':
                            return 'unsupported';
                    }
                }),
                attribute: computed(_ => getAttribute(attributeId.value)),
                activeDependency: computed(_ => getEntityTypeDependencies(entityTypeId.value, attributeId.value)),
                selection: computed(_ => {
                    return attributeSelection.value.filter(a => {
                        return a.id != attributeId.value;
                    });
                }),
                attributeSelected: computed(_ => state.dependency.attribute && state.dependency.attribute.id),
                operatorSelected: computed(_ => state.dependency.operator && state.dependency.operator.id),
            });

            // ON MOUNTED
            onMounted(_ => {
                state.show = true;
                if(!!state.activeDependency) {
                    for(let k in state.activeDependency) {
                        const dep = state.activeDependency[k];
                        state.dependency.attribute = getAttribute(k);
                        state.dependency.operator = operators.find(o => o.label == dep.operator);
                        state.dependency.value = dep.value;
                    }
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                translateConcept,
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