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
        toRefs,
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
            const {
                name,
                disabled,
                value,
            } = toRefs(props);
            // FETCH

            // FUNCTIONS

            // DATA
            const state = reactive({

            });

            // RETURN
            return {
                // STATE
                state,
            }
        },
    }
</script>
