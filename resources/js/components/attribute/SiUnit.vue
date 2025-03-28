<template>
    <div class="input-group">
        <input
            v-model="valueFieldValue"
            type="number"
            class="form-control text-center"
            :disabled="disabled"
            step="0.01"
            @keydown="onKeydown"
        >
        <button
            class="btn btn-outline-secondary dropdown-toggle"
            :disabled="disabled"
            type="button"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        >
            {{ siSymbolToStr(unitValue?.symbol) }}
        </button>
        <div class="dropdown-menu">
            <a
                v-for="(unit, i) in state.groupUnits "
                :key="i"
                class="dropdown-item d-flex flex-row justify-content-between gap-3"
                :class="{ 'active': unit.label == unitValue?.label }"
                href="#"
                @click.prevent="handleUnitChange(unit)"
            >
                <span>
                    {{ siSymbolToStr(unit.symbol) }}
                </span>
                <span
                    :class="{ 'text-light text-opacity-75': unit.label == unitValue?.label, 'text-muted': unit.label != unitValue?.label }"
                >
                    {{ t(`global.attributes.si_units.${state.unitGrp}.units.${unit.label}`) }}
                </span>
            </a>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        toRefs,
        watch,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import useSystemStore from '@/bootstrap/stores/system.js';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    import {
        siSymbolToStr,
    } from '@/helpers/helpers.js';

    import { useNumberInputHotkeys } from '@/composables/number-input-hotkeys.js';

    export default {
        props: {
            name: {
                type: String,
                required: true,
            },
            value: {
                type: Object,
                default: _ => new Object(),
            },
            metadata: {
                type: Object,
                required: true,
            },
            disabled: {
                type: Boolean,
            },
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();
            const systemStore = useSystemStore();

            // We need the toRefs for the vee-validate fields
            const {
                value: valueProp,
                metadata: metadataProp,
            } = toRefs(props);

            // FUNCTIONS
            const getUnitFromLabel = lbl => {
                if(!lbl) return null;

                return state.groupUnits.find(u => u.label == lbl);
            };


            // DATA
            const state = reactive({
                unitGrp: metadataProp.value?.si_baseunit,
                groupUnits: computed(_ => {
                    if(!state.unitGrp) return [];
                    let groupName = state.unitGrp;

                    const allGroups = systemStore.getDatatypeDataOf('si-unit');
                    if(!allGroups) return [];

                    let group = allGroups[groupName];
                    if(!group || !group.units) return [];

                    return group.units;
                }),
            });

            const {
                handleInput: handleValueInput,
                value: valueFieldValue,
                meta: valueFieldMeta,
                resetField: resetValueField,
            } = useField(`value_${props.name}`, yup.number(), {
                initialValue: valueProp.value.value,
            });

            const {
                handleChange: handleUnitChange,
                value: unitValue,
                meta: unitMeta,
                resetField: resetUnitField,
            } = useField(`unit_${props.name}`, yup.string(), {
                initialValue: getUnitFromLabel(valueProp.value.unit || metadataProp.value?.si_default),
            });

            const v = reactive({
                value: computed(_ => {
                    const group = state.groupUnits.find(u => u.label == unitValue.value?.label);
                    const conversion = group?.conversion ?? 1;
                    return {
                        value: valueFieldValue.value,
                        unit: unitValue.value?.label || '',
                        normalized: valueFieldValue.value * conversion,
                        baseunit: state.unitGrp,
                    };
                }),
                meta: computed(_ => {
                    return {
                        dirty: valueFieldMeta.dirty || unitMeta.dirty,
                        valid: ((valueFieldMeta.dirty && valueFieldMeta.valid) || !valueFieldMeta.dirty)
                    };
                }),
            });

            const {
                onKeydown,
            } = useNumberInputHotkeys(valueFieldValue, true);

            const resetFieldState = _ => {
                resetValueField({
                    value: valueProp.value.value,
                });
                resetUnitField({
                    value: valueProp.value.unit,
                });
            };

            const undirtyField = _ => {
                resetValueField({
                    value: valueFieldValue.value,
                });

                resetUnitField({
                    value: unitValue.value,
                });
            };

            watch(_ => valueProp, (newValue, oldValue) => {
                resetFieldState();
            });

            watch(_ => v, (newValue, oldValue) => {
                // TODO: The SiUnit is not updating accurately. as the new and old value is always the same (new) value.
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            }, {
                deep: true,
            });

            // only needed for preview, otherwise unit can not change
            // this is not yet working properly.
            // watch(_ => metadataProp.value, (newValue, oldValue) => {
            //     state.unitGrp = newValue;
            //     console.log('unitGrp', state.unitGrp);
            // }, {
            //     immediate: true,

            // });

            watch(_ => valueProp.value.default, (newValue, oldValue) => {
                if(!newValue) {
                    handleUnitChange(null);
                } else {
                    const def = state.groupUnits.find(u => u.label == newValue);
                    handleUnitChange(def);
                }
            });

            // RETURN
            return {
                t,
                // HELPERS
                siSymbolToStr,
                // LOCAL
                resetFieldState,
                undirtyField,
                handleUnitChange,
                handleValueInput,
                // STATE
                state,
                v,
                valueFieldValue,
                unitValue,
                onKeydown,
            };
        },
    };
</script>
