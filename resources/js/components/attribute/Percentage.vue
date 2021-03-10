<template>
    <div class="d-flex">
        <input
            class="form-range"
            type="range"
            step="1"
            min="0"
            max="100"
            :disabled="disabled"
            :id="name"
            :name="name"
            v-model="state.value" />
        <span class="ms-3">
            {{ state.value }}%
        </span>
    </div>
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
                type: Number,
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
                value: value.value,
            });

            // RETURN
            return {
                // HELPERS
                // LOCAL
                // PROPS
                name,
                disabled,
                value,
                // STATE
                state,
            }
        },
    }
</script>
