<template>
    <div class="form-check form-switch h-100 d-flex align-items-center">
        <input
            class="form-check-input"
            type="checkbox"
            :disabled="disabled"
            :id="name"
            :name="name"
            v-model="value" />
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
