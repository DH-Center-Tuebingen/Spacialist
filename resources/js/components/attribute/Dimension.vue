<template>
    <div class="input-group">
        <input
            v-model="v.B.value"
            type="number"
            class="form-control text-center"
            :disabled="disabled"
            min="0"
            max="9999"
            step="0.01"
            @input="v.B.handleInput"
        >
        <span class="input-group-text">&times;</span>
        <input
            v-model="v.H.value"
            type="number"
            class="form-control text-center"
            :disabled="disabled"
            min="0"
            max="9999"
            step="0.01"
            @input="v.H.handleInput"
        >
        <span class="input-group-text">&times;</span>
        <input
            v-model="v.T.value"
            type="number"
            class="form-control text-center"
            :disabled="disabled"
            min="0"
            max="9999"
            step="0.01"
            @input="v.T.handleInput"
        >
        <button
            class="btn btn-outline-secondary  dropdown-toggle"
            :disabled="disabled"
            type="button"
            data-bs-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false"
        >
            {{ v.unit.value }}
        </button>
        <div class="dropdown-menu">
            <a
                v-for="(unit, i) in dimensionUnits"
                :key="i"
                class="dropdown-item"
                href="#"
                @click.prevent="setUnit(unit)"
            >
                {{ unit }}
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
                v.unit.resetField({
                    value: value.value.unit,
                });
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
                v.unit.resetField({
                    value: v.unit.value,
                });
            };
            const setUnit = (unit) => {
                v.unit.handleChange(unit);
            };

            // DATA
            const dimensionUnits = ['nm', 'µm', 'mm', 'cm', 'dm', 'm', 'km'];
            const {
                handleInput: hib,
                value: vb,
                meta: mb,
                resetField: rfb,
            } = useField(`b_${name.value}`, yup.number().positive(), {
                initialValue: value.value.B,
            });
            const {
                handleInput: hih,
                value: vh,
                meta: mh,
                resetField: rfh,
            } = useField(`h_${name.value}`, yup.number().positive(), {
                initialValue: value.value.H,
            });
            const {
                handleInput: hit,
                value: vt,
                meta: mt,
                resetField: rft,
            } = useField(`t_${name.value}`, yup.number().positive(), {
                initialValue: value.value.T,
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
            });
            const v = reactive({
                value: computed(_ => {
                    return {
                        B: v.B.value,
                        H: v.H.value,
                        T: v.T.value,
                        unit: v.unit.value,
                    };
                }),
                meta: computed(_ => {
                    return {
                        dirty: v.B.meta.dirty || v.H.meta.dirty || v.T.meta.dirty || v.unit.meta.dirty,
                        valid: ((v.B.meta.dirty && v.B.meta.valid) || !v.B.meta.dirty) &&
                               ((v.H.meta.dirty && v.H.meta.valid) || !v.H.meta.dirty) &&
                               ((v.T.meta.dirty && v.T.meta.valid) || !v.T.meta.dirty),
                    };
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
            watch(_ => v.value, (newValue, oldValue) => {
                context.emit('change', {
                    dirty: v.meta.dirty,
                    valid: v.meta.valid,
                    value: v.value,
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
                // STATE
                state,
                v,
            };
        },
    };
</script>
