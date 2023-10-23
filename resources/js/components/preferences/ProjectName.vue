<template>
    <div class="row">
        <label class="col-md-2 form-label" />
        <div class="col-md-10">
            <input
                :value="modelValue"
                class="form-control"
                type="text"
                :readonly="readonly"
                @input="onInput"
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
            modelValue: {
                required: true,
                type: String,
            },
            readonly: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        emits: ['changed', 'update:modelValue'],
        setup(props, context) {
            const {
                data,
                readonly,
            } = toRefs(props);

            // FUNCTIONS
            const onChange = _debounce(e => {
                if(readonly.value) return;
                context.emit('changed',  e.target.value);

            }, 250);

            const onInput = (e)=> {
                if(readonly.value) return;
                context.emit('update:modelValue', e.target.value);
                onChange(e)
            }

            // RETURN
            return {
                // LOCAL
                onChange,
                onInput,
                // STATE
            };
        }
    }
</script>