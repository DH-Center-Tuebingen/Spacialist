<template>
    <div class="input-group">
        <input
            v-model="v.val.value"
            type="number"
            class="form-control text-center"
            :disabled="disabled"
            step="0.01"
            @input="v.val.handleInput"
        >
        <button
            class="btn btn-outline-secondary dropdown-toggle"
            :disabled="disabled"
            type="button"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        >
            {{ displaySymbol(v.unit.value?.symbol) }}
        </button>
        <div class="dropdown-menu">
            <a
                v-for="(unit, i) in state.dimensionUnits"
                :key="i"
                class="dropdown-item d-flex flex-row justify-content-between gap-3"
                :class="{'active': unit.label == v.unit.value?.label}"
                href="#"
                @click.prevent="setUnit(unit)"
            >
                <span>
                    {{ displaySymbol(unit.symbol) }}
                </span>
                <span
                    :class="{'text-light text-opacity-75': unit.label == v.unit.value?.label, 'text-muted': unit.label != v.unit.value?.label}"
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

    import store from '@/bootstrap/store.js';

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

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
            disabled: {
                type: Boolean,
            },
        },
        emits: ['change'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                name,
                value,
                disabled,
            } = toRefs(props);

            // FUNCTIONS
            const resetFieldState = _ => {
                v.val.resetField({
                    value: value.value.value
                });
                v.unit.resetField({
                    value: value.value.unit,
                });
            };
            const undirtyField = _ => {
                v.val.resetField({
                    value: v.val.value,
                });
                v.unit.resetField({
                    value: v.unit.value,
                });
            };
            const setUnit = (unit) => {
                v.unit.handleChange(unit);
            };

            const printSymbol = symbol => {
                if(Array.isArray(symbol)) {
                    return symbol.join(' | ');
                } else {
                    return symbol;
                }
            };

            const displaySymbol = symbol => {
                if(Array.isArray(symbol)) {
                    return symbol[0];
                } else {
                    return symbol;
                }
            };

            // DATA
            const {
                handleInput: hib,
                value: vb,
                meta: mb,
                resetField: rfb,
            } = useField(`value_${name.value}`, yup.number().positive(), {
                initialValue: value.value.B,
            });
            const {
                handleInput: hiu,
                handleChange: hcu,
                value: vu,
                meta: mu,
                resetField: rfu,
            } = useField(`unit_${name.value}`, yup.string().matches(/(nm|µm|mm|cm|dm|m|km)/), {
                initialValue: value.value.unit,
            });
            const state = reactive({
                unitGrp: 'area',
                dimensionUnits: computed(_ => store.getters.datatypeDataOf('si-unit')[state.unitGrp].units),
            });
            const v = reactive({
                value: computed(_ => {
                    return {
                        value: v.val.value,
                        unit: v.unit.value.label,
                    }
                }),
                meta: computed(_ => {
                    return {
                        dirty: v.val.meta.dirty || v.unit.meta.dirty,
                        valid: ((v.val.meta.dirty && v.val.meta.valid) || !v.val.meta.dirty)
                    };
                }),
                val: {
                    value: vb,
                    meta: mb,
                    resetField: rfb,
                    handleInput: hib,
                },
                unit: {
                    value: vu,
                    meta: mu,
                    resetField: rfu,
                    handleInput: hiu,
                    handleChange: hcu,
                },
            });


            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
                });
            });

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                resetFieldState,
                undirtyField,
                setUnit,
                printSymbol,
                displaySymbol,
                // STATE
                state,
                v,
            }
        },
    }
</script>
