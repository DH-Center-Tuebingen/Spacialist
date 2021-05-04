<template>
    <div class="input-group">
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="v.B.handleInput" v-model="v.B.value"/>
            <span class="input-group-text">&times;</span>
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="v.H.handleInput" v-model="v.H.value"/>
            <span class="input-group-text">&times;</span>
        <input type="number" class="form-control text-center" :disabled="disabled" min="0" max="9999" step="0.01" @input="v.T.handleInput" v-model="v.T.value"/>
        <div>
            <button class="btn btn-outline-secondary  dropdown-toggle" :disabled="disabled" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ v.unit.value }}
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#" v-for="(unit, i) in dimensionUnits" @click.prevent="setUnit(unit)" :key="i">
                    {{ unit }}
                </a>
            </div>
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

    import { useField } from 'vee-validate';

    import * as yup from 'yup';

    export default {
        props: {
            name: String,
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
            const {
                name,
                value,
                disabled,
            } = toRefs(props);

            // FUNCTIONS
            const resetFieldState = _ => {
                v.B.resetField({
                    value: value.value.B
                });
                v.H.resetField({
                    value: value.value.H
                });
                v.T.resetField({
                    value: value.value.T
                });
                v.unit.value = value.value.unit;
                v.unit.initialValue = value.value.unit;
                v.unit.meta.dirty = false;
            };
            const undirtyField = _ => {
                v.B.resetField({
                    value: v.B.value,
                });
                v.H.resetField({
                    value: v.H.value,
                });
                v.T.resetField({
                    value: v.T.value,
                });
                v.unit.initialValue = v.unit.value;
                v.unit.meta.dirty = false;
            };
            const setUnit = (unit) => {
                v.unit.value = unit;
                v.unit.meta.dirty = true;
            };

            // DATA
            const dimensionUnits = ['nm', 'µm', 'mm', 'cm', 'dm', 'm', 'km'];
            const {
                handleInput: hib,
                value: vb,
                meta: mb,
                resetField: rfb,
            } = useField(`b_${name.value}`, yup.number().positive(), {
                initialValue: value.value,
            });
            const {
                handleInput: hih,
                value: vh,
                meta: mh,
                resetField: rfh,
            } = useField(`h_${name.value}`, yup.number().positive(), {
                initialValue: value.value,
            });
            const {
                handleInput: hit,
                value: vt,
                meta: mt,
                resetField: rft,
            } = useField(`t_${name.value}`, yup.number().positive(), {
                initialValue: value.value,
            });
            const state = reactive({
            });
            const v = reactive({
                value: computed(_ => {
                    return {
                        B: v.B.value,
                        H: v.H.value,
                        T: v.T.value,
                        unit: v.unit.value,
                    }
                }),
                meta: computed(_ => {
                    return {
                        dirty: v.B.meta.dirty || v.H.meta.dirty || v.T.meta.dirty || v.unit.meta.dirty,
                        valid: ((v.B.meta.dirty && v.B.meta.valid) || !v.B.meta.dirty) &&
                               ((v.H.meta.dirty && v.H.meta.valid) || !v.H.meta.dirty) &&
                               ((v.T.meta.dirty && v.T.meta.valid) || !v.T.meta.dirty),
                    }
                }),
                B: {
                    value: vb,
                    meta: mb,
                    resetField: rfb,
                    handleInput: hib,
                },
                H: {
                    value: vh,
                    meta: mh,
                    resetField: rfh,
                    handleInput: hih,
                },
                T: {
                    value: vt,
                    meta: mt,
                    resetField: rft,
                    handleInput: hit,
                },
                unit: {
                    value: value.value.unit,
                    initialValue: value.value.unit,
                    meta: {
                        dirty: false,
                    }
                },
            });

            watch(_ => v.meta, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                });
            });

            // RETURN
            return {
                // HELPERS
                // LOCAL
                dimensionUnits,
                resetFieldState,
                undirtyField,
                setUnit,
                // PROPS
                name,
                disabled,
                // STATE
                state,
                v,
            }
        },
    }
</script>
