<template>
    <div class="row">
        <label class="col-md-2 form-label" />
        <div class="col-md-10">
            <input
                :value="data"
                class="form-control"
                type="text"
                :disabled="readonly"
                :readonly="readonly"
                @input="onChange"
            >
        </div>
    </div>
</template>

<script>
    import {
        toRefs,
    } from 'vue';

    import {
        _debounce
    } from '@/helpers/helpers.js';

    export default {
        props: {
            data: {
                required: true,
                type: String,
            },
            readonly: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        emits: ['changed'],
        setup(props, context) {
            const {
                data,
                readonly,
            } = toRefs(props);

            // FUNCTIONS
            const onChange = _debounce(e => {
                if(readonly.value) return;
                context.emit('changed',  e.target.value );
            }, 250);

            // DATA

            // RETURN
            return {
                // LOCAL
                onChange,
                // STATE
            };
        }
    }
</script>