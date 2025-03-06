<template>
    <DependencyGroupControl
        :is-and="modelValue.groups[activeGroup].union"
        :groups="modelValue.groups"
        @add="addGroup"
        @prev="gotoPrevGroup"
        @next="gotoNextGroup"
        @remove="removeGroup"
        @toggle-state="toggleState"
    />
    <Alert
        v-if="modelValue.groups[activeGroup].rules.length == 0"
        class="mb-2"
        :message="t('global.dependency.no_rules_defined')"
        :type="'info'"
        :noicon="true"
    />
    <div class="overflow-y-auto overflow-x-hidden">
        <div
            v-for="(itm, i) in modelValue.groups[activeGroup].rules"
            :key="`dependency-group-${activeGroup}-item-${i}`"
            class="mb-2 row g-2 align-items-center"
        >
            <div class="col-5">
                <multiselect
                    v-model="itm.attribute"
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
            <div class="col-2">
                <multiselect
                    v-if="itm.attribute?.id"
                    v-model="itm.operator"
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
                    :options="getOperatorsForDatatype(itm.attribute.datatype)"
                    :placeholder="t('global.select.placeholder')"
                    @change="operatorSelected"
                />
            </div>
            <div class="col-4">
                <div v-if="itm.attribute?.id && itm.operator?.id && !itm.operator.no_parameter">
                    <div
                        v-if="getInputTypeClass(itm.attribute.datatype) == 'boolean'"
                        class="form-check form-switch"
                    >
                        <input
                            id="dependency-boolean-value"
                            v-model="itm.value"
                            type="checkbox"
                            class="form-check-input"
                        >
                    </div>
                    <input
                        v-else-if="getInputTypeClass(itm.attribute.datatype) == 'number'"
                        v-model.number="itm.value"
                        type="number"
                        class="form-control"
                        :step="itm.attribute.datatype == 'double' ? 0.01 : 1"
                    >
                    <multiselect
                        v-else-if="getInputTypeClass(itm.attribute.datatype) == 'select'"
                        v-model="itm.value"
                        :classes="{
                            ...multiselectResetClasslist,
                            'dropdown': 'multiselect-dropdown multiselect-modal-dropdown'
                        }"
                        :append-to-body="true"
                        :value-prop="'id'"
                        :label="'concept_url'"
                        :track-by="'id'"
                        :hide-selected="true"
                        :mode="'single'"
                        :options="getDependantOptions(itm.attribute.id, itm.attribute.datatype)"
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
                        v-model="itm.value"
                        type="text"
                        class="form-control"
                    >
                </div>
            </div>
            <div
                class="col-1 d-flex flex-row justify-content-center align-items-center"
                :title="t('global.dependency.remove_rule')"
            >
                <a
                    href="#"
                    class="text-danger text-decoration-none"
                    @click.prevent="removeItem(activeGroup, i)"
                >
                    <i class="fas fa-fw fa-trash" />
                </a>
            </div>
        </div>
    </div>
    <div class="input-group input-group-sm">
        <button
            type="button"
            class="btn btn-sm btn-outline-success"
            @click="addItem"
        >
            <i class="fas fa-fw fa-plus" />
            {{ t('global.dependency.add_rule') }}
        </button>
        <DependencyToggle />
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        ref,
    } from 'vue';

    import {
        getEmptyGroup,
        getInputTypeClass,
        getOperatorsForDatatype,
    } from '@/helpers/dependencies.js';
    
    import {
        translateConcept
    } from '@/helpers/helpers.js';

    import {
        mod,
    } from '@/helpers/math.js';

    import DependencyGroupControl from './DependencyGroupControl.vue';
    import DependencyToggle from './DependencyToggle.vue';
    
    import { useI18n } from 'vue-i18n';

    export default {
        components: {
            DependencyGroupControl,
            DependencyToggle,
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
        emits: ['update:modelValue'],
        setup(props, { emit }) {

            const activeGroup = ref(0);
            const groupsCount = computed(_ => props.modelValue.groups.length);
            const lastGroupEmpty = computed(_ => props.modelValue.groups[groupsCount.value - 1].rules.length == 0);

            const gotoGroup = id => {
                if(id >= groupsCount.value) return;
                activeGroup.value = id;
            };

            const gotoPrevGroup = _ => {
                const id = mod(activeGroup.value - 1, groupsCount.value);
                gotoGroup(id);
            };

            const gotoNextGroup = _ => {
                const id = mod(activeGroup.value + 1, groupsCount.value);
                gotoGroup(id);
            };

            const addGroup = _ => {
                if(lastGroupEmpty.value) return;
                const modelValue = props.modelValue;
                modelValue.groups.push(getEmptyGroup());
                updateModelValue(modelValue);
                gotoGroup(groupsCount.value - 1);
            };

            const removeGroup = idx => {
                if(idx >= groupsCount.value) return;
                const modelValue = props.modelValue;
                modelValue.groups.splice(idx, 1);
                updateModelValue(modelValue);

                if(activeGroup.value == idx) {
                    gotoPrevGroup();
                }
            };
            const addItem = _ => {
                const modelValue = props.modelValue;
                modelValue.groups[activeGroup.value].rules.push({});
                updateModelValue(modelValue);
            };

            const removeItem = (grpIdx, idx) => {
                if(grpIdx >= groupsCount.value) return;
                if(idx >= props.modelValue.groups[grpIdx].rules.length) return;

                const modelValue = props.modelValue;
                modelValue.groups[grpIdx].rules.splice(idx, 1);
                updateModelValue(modelValue);
            };

            const toggleGroupState = _ => {
                const modelValue = props.modelValue;
                modelValue.groups[activeGroup.value].union = !modelValue.groups[activeGroup.value].union;
                // TODO?  Don't we need to update the child states here? 
                updateModelValue(modelValue);
            };

            const getDependantOptions = (aid, datatype) => {
                if(getInputTypeClass(datatype) == 'select') {
                    return store.getters.attributeSelections[aid];
                } else {
                    return [];
                }
            };

            const updateModelValue = value => {
                emit('update:modelValue', value);
            };

            return {
                t: useI18n().t,
                activeGroup,
                addGroup,
                getInputTypeClass,
                addItem,
                getDependantOptions,
                getOperatorsForDatatype,
                gotoNextGroup,
                gotoPrevGroup,
                removeGroup,
                removeItem,
                toggleGroupState,
                translateConcept,
            };
        }
    };
</script>