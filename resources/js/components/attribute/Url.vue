<template>
    <div class="input-group">
        <input
            :id="name"
            v-model="v.value"
            class="form-control input-anchor"
            type="text"
            :disabled="disabled"
            :name="name"
            @mousedown="preventFocusOnCtrlClick"
            @click="openOnCtrlClick"
        >
        <a
            class="input-group-text text-muted"
            target="_blank"
            :href="v.value"
            :title="v.value"
        >
            <i class="fas fa-fw fa-arrow-up-right-from-square" />
        </a>
    </div>
</template>

<script>
    import {
        reactive,
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
                type: String,
                required: true,
            },
        },
        emits: ['change'],
        setup(props, context) {
            // FUNCTIONS
            const resetFieldState = _ => {
                v.resetField({
                    value: props.value
                });
            };
            const undirtyField = _ => {
                v.resetField({
                    value: v.value,
                });
            };

            const preventFocusOnCtrlClick = e => {
                if(e.ctrlKey) {
                    e.preventDefault();
                }
            };

            const openOnCtrlClick = e => {
                if(e.ctrlKey) {
                    e.preventDefault();
                    window.open(v.value, '_blank', 'noreferrer');
                }
            };

            // DATA
            const {
                value: fieldValue,
                meta,
                resetField,
            } = useField(`url_${props.name}`, yup.string().url(), {
                initialValue: props.value,
            });
            const state = reactive({

            });
            const v = reactive({
                value: fieldValue,
                meta,
                resetField,
            });


            watch(_ => props.value, (newValue, oldValue) => {
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
                openOnCtrlClick,
                preventFocusOnCtrlClick,
                // STATE
                state,
                v,
            };
        },
    };
</script>
