<template>
    <div class="d-flex mb-2 gap-2 g-2 align-items-center">
        <div class="col-4">
            <multiselect
                :value="modelValue.attribute"
                :classes="{
                    ...multiselectResetClasslist,
                    'dropdown': 'multiselect-dropdown multiselect-modal-dropdown'
                }"
                :append-to-body="true"
                :value-prop="'id'"
                :label="'thesaurus_url'"
                :track-by="'id'"
                :object="true"
                :mode="'single'"
                :hide-selected="true"
                :options="options"
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
        </div>
        <div class="col-3">
            <multiselect
                v-if="modelValue.attribute?.id"
                :value="modelValue.operator"
                :disabled="!modelValue.attribute"
                :classes="{
                    ...multiselectResetClasslist,
                    'dropdown': 'multiselect-dropdown multiselect-modal-dropdown'
                }"
                :append-to-body="true"
                :value-prop="'id'"
                :label="'label'"
                :track-by="'id'"
                :mode="'single'"
                :object="true"
                :hide-selected="true"
                :options="operatorsForDatatypeWithLabels(modelValue.attribute.datatype)"
                :placeholder="t('global.select.placeholder')"
                @change="operatorSelected"
            />
            <input
                v-else
                type="text"
                class="form-control"
                :disabled="true"
            >
        </div>
        <div class="col-4">
            <input
                v-if="!attributeData || datatype == 'unsupported' || modelValue?.operator?.no_parameter"
                class="form-control"
                :disabled="true"
            >
            <Attribute
                v-else
                :data="attributeData"
                :value-wrapper="attributeValueWrapper"
                @change="valueChanged"
            />
        </div>
        <button
            class="btn btn-outline-danger flex-fill"
            :title="t('global.dependency.remove_rule')"
            @click="removeItem"
        >
            <i class="fas fa-fw fa-trash" />
        </button>
    </div>
</template>

<script>
    import {
        computed,
        nextTick,
        onMounted,
        ref,
    } from 'vue';
    import { useI18n } from 'vue-i18n';

    import {
        multiselectResetClasslist,
        translateConcept,
    } from '@/helpers/helpers.js';

    import {
        getInputTypeClass,
        getOperatorsForDatatype,
    } from '@/helpers/dependencies.js';

    import useSystemStore from '@/bootstrap/stores/system.js';
    import useEntityStore from '@/bootstrap/stores/entity.js';

    import Attribute from '@/components/attribute/Attribute.vue';

    export default {
        components: {
            Attribute,
        },
        props: {
            modelValue: {
                type: Object,
                required: true,
            },
            options: {
                type: Array,
                required: true,
            }
        },
        emits: ['update:modelValue', 'remove'],
        setup(props, { emit }) {
            const { t } = useI18n();

            const getDependantOptions = (aid, datatype) => {
                if(getInputTypeClass(datatype) == 'select') {
                    return store.getters.attributeSelections[aid];
                } else {
                    return [];
                }
            };

            const operatorsForDatatypeWithLabels = datatype => {
                const operators = getOperatorsForDatatype(datatype);
                console.log(operators);
                return operators.map(op => {
                    op.label = t(`global.dependency.operators.${op.name}`);
                    return op;
                });
            };

            const dependantSelected = value => {
                const modelValue = { ...props.modelValue };
                modelValue.attribute = value;
                modelValue.operator = null;
                modelValue.value = null;
                emit('update:modelValue', modelValue);
                updateAttributeDataNextTick();
            };

            const operatorSelected = value => {
                const modelValue = { ...props.modelValue };
                modelValue.operator = value;
                modelValue.value = null;
                emit('update:modelValue', modelValue);
                updateAttributeDataNextTick();
            };

            const valueChanged = e => {
                let value = e.value;

                if(datatype.value == 'string-sc' || datatype.value == 'entity') {
                    value = value.id;
                } else if(datatype.value == 'string-mc' || datatype.value == 'entity-mc') {
                    value = value.map(v => v.id);
                } if(datatype.value == 'si-unit') {
                    value = value.normalized;
                }

                const modelValue = { ...props.modelValue };
                modelValue.value = value;
                emit('update:modelValue', modelValue);
                updateAttributeDataNextTick();
            };

            const removeItem = _ => {
                emit('remove');
            };

            const attribute = computed(_ => {
                console.log(props.modelValue);
                return props.modelValue.attribute;
            });

            const datatype = computed(_ => {
                if(!attribute.value) return 'unsupported';
                return attribute.value?.datatype || 'unsupported';
            });

            /**
             * This is a little bit ugly. But we have to deal with
             * async data here, when we need to fetch data for the entity
             * field. [SO]
             */
            const attributeData = ref(null);
            const attributeValueWrapper = ref(null);

            const updateAttributeDataNextTick = _ => {
                nextTick(_ => {
                    updateAttributeData();
                });
            };

            const updateAttributeData = async _ => {
                let value = props.modelValue.value;
                switch(datatype.value) {
                    case 'string-sc':
                        console.log(value);
                        if(!value) {
                            value = null;
                        } else {
                            console.log(value);
                            value = useSystemStore().getConceptById(value);
                        }
                        break;
                    case 'string-mc':
                        value = value ? value.map(v => useSystemStore().getConceptById(v)) : null;
                        break;
                    case 'entity':
                        const entity = await useEntityStore().getEntity(value);

                        value = {
                            id: entity.id,
                            name: entity.name,
                        };
                        break;
                    case 'entity-mc':
                        value = value ? { id: value } : null;
                        break;
                }

                attributeData.value = attribute.value;
                attributeValueWrapper.value = value;
            };

            const searchEntity = async query => {
                // TODO: We only show the first result.
                // This should be just done inside the Attribute component.
                // But we cannot set a value from outside at the moment.
                if(!query) return [];
                return useEntityStore().search(query);
            };

            const getPaginatedResults = results => {
                return results.data;
            };

            onMounted(async _ => {
                if(datatype.value == 'entity') {
                    await useEntityStore().fetchEntity(props.modelValue.value);
                }
                updateAttributeData();
            });

            return {
                t,
                attributeData,
                attributeValueWrapper,
                dependantSelected,
                datatype,
                getDependantOptions,
                getPaginatedResults,
                getInputTypeClass,
                multiselectResetClasslist,
                operatorSelected,
                operatorsForDatatypeWithLabels,
                removeItem,
                searchEntity,
                translateConcept,
                valueChanged,
            };
        },
    };
</script>