<template>
    <div class="row">
        <label class="col-md-2 form-label" />
        <div class="col-md-10">
            <div class="form-check form-switch">
                <input
                    v-model="state.enabled"
                    class="form-check-input"
                    type="checkbox"
                    :readonly="readonly"
                    :disabled="readonly"
                    @input="onChange"
                >
            </div>
        </div>
    </div>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        _debounce
    } from '@/helpers/helpers.js';
    
    export default {
        props: {
            data: {
                required: true,
                type: Boolean,
            },
            readonly: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        emits: ['changed'],
        setup(props, context) {
            const { t } = useI18n();
            const {
                readonly,
            } = toRefs(props);

            // FUNCTIONS
            const onChange = _debounce(e => {
                if(readonly.value) return;
                context.emit('changed',  e.target.checked);
            }, 250);

            // DATA
            const state = reactive({
                enabled: props.data,
            });

            // RETURN
            return {
                t,
                // LOCAL
                onChange,
                // STATE
                state,
            };
        }
    }
</script>