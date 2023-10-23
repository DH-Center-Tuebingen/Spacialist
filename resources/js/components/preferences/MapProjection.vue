<template>
    <div class="row">
        <label class="col-md-2 col-form-label text-end">
            {{ t('main.preference.key.map.epsg') }}:
        </label>
        <div class="col-md-10">
            <input
                :value="data.epsg"
                class="form-control"
                type="number"
                min="0"
                max="99999"
                step="1"
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

    import { useI18n } from 'vue-i18n';

    import {
        _debounce
    } from '@/helpers/helpers.js';

    export default {
        props: {
            data: {
                required: true,
                type: Object,
                validator: (value) => {
                    return typeof value.epsg === 'number';
                }
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
                data,
                readonly,
            } = toRefs(props);

            // FUNCTIONS
            const onChange = _debounce(e => {
                if(readonly.value) return;
                context.emit('changed', {epsg: e.target.value});
            }, 250);

            // RETURN
            return {
                t,
                // LOCAL
                onChange,
                // STATE
            };
        }
    }
</script>