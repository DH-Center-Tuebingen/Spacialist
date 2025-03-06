<template>
    <div
        class="d-flex align-items-center gap-3"
        @wheel="onWheel"
        @mouseenter="setActive(true)"
        @mouseleave="setActive(false)"
    >
        <input
            :id="name"
            v-model="v.value"
            class="form-range"
            type="range"
            min="0"
            max="100"
            :step="state.stepSize"
            :disabled="disabled"
            :name="name"
        >
        <div
            class="input-group range-addon"
        >
            <input
                v-model="v.value"
                class="form-control text-end"
                type="text"
            >
            <span class="input-group-text">%</span>
        </div>
        <!-- <span class="ms-3">
            {{ v.value }}%
        </span> -->
    </div>
</template>

<script>
    import {
        computed,
        onMounted,
        onUnmounted,
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
            disabled: {
                type: Boolean,
                required: false,
                default: false,
            },
            value: {
                type: Number,
                required: true,
            },
        },
        emits: ['change'],
        setup(props, context) {
            const {
                name,
                disabled,
                value,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS
            const resetFieldState = _ => {
                v.resetField({
                    value: value.value
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };

            const setActive = active => {
                state.active = active;
            };

            const onWheel = event => {
                if(!state.active) return;
                event.preventDefault();
                let newValue = parseFloat(v.value);
                // increase/decrease value based on wheel direction
                const inc = event.deltaY < 0;
                if(inc) {
                    newValue += state.stepSize;
                    if(newValue > 100) newValue = 100;
                } else {
                    newValue -= state.stepSize;
                    if(newValue < 0) newValue = 0;
                }
                // if divider is active, we have a float value and need to round it to max decimal places
                // otherwise round to nearest integer
                if(state.divider) {
                    if(newValue < 100) {
                        v.value = newValue.toFixed(2);
                    } else {
                        v.value = newValue;
                    }
                } else {
                    v.value = inc ? Math.floor(newValue) : Math.ceil(newValue);
                }
            };

            const onKey = event => {
                const keyPressed = event.type === 'keydown';
                if(event.key == 'Control') {
                    state.multiplier = keyPressed;
                }
                if(event.key == 'Shift') {
                    state.divider = keyPressed;
                }
            };

            // DATA
            const {
                value: fieldValue,
                meta,
                resetField,
            } = useField(`perc_${name.value}`, yup.number(), {
                initialValue: value.value,
            });
            const state = reactive({
                active: false,
                divider: false,
                multiplier: false,
                stepSize: computed(_ => {
                    let step = 1;
                    if(state.divider) step /= 100;
                    if(state.multiplier) step *= 10;
                    return step;
                }),
            });
            const v = reactive({
                value: fieldValue,
                meta,
                resetField,
            });

            onMounted(_ => {
                window.addEventListener('keydown', onKey);
                window.addEventListener('keyup', onKey);
            });
            onUnmounted(_ => {
                window.removeEventListener('keydown', onKey);
                window.removeEventListener('keyup', onKey);
            });
            watch(_ => value, (newValue, oldValue) => {
                resetFieldState();
            });
            watch(_ => v.value, (newValue, oldValue) => {
                // only emit @change event if field is validated (required because Entity.vue components)
                // trigger this watcher several times even if another component is updated/validated
                if(!v.meta.validated) return;
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
                resetFieldState,
                undirtyField,
                setActive,
                onWheel,
                // STATE
                state,
                v,
            };
        },
    };
</script>
