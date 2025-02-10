<template>
    <input
        :id="name"
        class="form-control-plaintext"
        type="text"
        readonly
        :name="name"
        :value="value"
    >
</template>

<script>
    import {
        reactive,
    } from 'vue';

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
        setup(props, context) {

            const v = reactive({
                value: props.value,
                meta: {
                    dirty: false,
                    valid: true,
                },
                resetField: _ => true,
            });
            
            const resetFieldState = _ => {};
            const undirtyField = _ => {};

            // RETURN
            return {
                // STATE
                v,
                resetFieldState,
                undirtyField,
            };
        },
    };
</script>
