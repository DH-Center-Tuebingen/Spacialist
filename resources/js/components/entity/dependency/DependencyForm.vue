<template>
    <header class="d-flex flex-row justify-content-between mb-2">
        <div class="input-group input-group-sm">
            <button
                type="button"
                class="btn btn-sm btn-outline-success"
                @click="addItem"
            >
                <i class="fas fa-fw fa-plus" />
                {{ t('global.dependency.add_rule') }}
            </button>
            <DependencyToggle
                :model-value="modelValue.groups[activeGroup].is_and"
                @update:model-value="toggleGroupState"
            />
        </div>
        <DependencyGroupControl
            class="flex-nowrap"
            :is-and="modelValue.is_and"
            :groups="modelValue.groups"
            :active-group="activeGroup"
            @add="addGroup"
            @prev="gotoPrevGroup"
            @next="gotoNextGroup"
            @remove="removeGroup"
            @toggle-state="toggleGroupState"
        />
    </header>
    <div class="dependency-group overflow-y-auto overflow-x-hidden">
        <Alert
            v-if="
                modelValue.groups[activeGroup].rules.length == 0"
            class="mb-2"
            :message="t('global.dependency.no_rules_defined')"
            :type="'info'"
            :noicon="true"
        />
        <DependencyInput
            v-for="(rule, i) in modelValue.groups[activeGroup].rules"
            :key="`dependency-group-${activeGroup}-item-${i}`"
            :options="options"
            :model-value="rule"
            @update:model-value="value => updateRule(value, i)"
            @remove="() => removeItem(activeGroup, i)"
        />
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
    } from '@/helpers/dependencies.js';

    import {
        mod,
    } from '@/helpers/math.js';

    import DependencyGroupControl from './DependencyGroupControl.vue';
    import DependencyInput from './DependencyInput.vue';
    import DependencyToggle from './DependencyToggle.vue';

    import { useI18n } from 'vue-i18n';

    export default {
        components: {
            DependencyGroupControl,
            DependencyInput,
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

            const t = useI18n().t;
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
                if(groupsCount.value < 1 || idx >= groupsCount.value) return;
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
                modelValue.is_and = !modelValue.is_and;
                modelValue.groups.forEach(element => {
                    element.is_and = !modelValue.is_and;
                });
                updateModelValue(modelValue);
            };

            const updateRule = (value, i) => {
                const modelValue = props.modelValue;
                modelValue.groups[activeGroup.value].rules[i] = value;
                updateModelValue(modelValue);
            };

            const updateModelValue = value => {
                emit('update:modelValue', value);
            };

            return {
                t,
                activeGroup,
                addGroup,
                addItem,
                gotoNextGroup,
                gotoPrevGroup,
                removeGroup,
                removeItem,
                toggleGroupState,
                updateRule,
            };
        }
    };
</script>