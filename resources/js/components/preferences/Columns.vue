<template>
    <div class="column-preferences input-group flex-no-wrap">
        <template
            v-for="{ id, label } in sections"
            :key="id"
        >
            <span
                id="addon-wrapping"
                class="input-group-text"
            >{{ t(label) }}</span>
            <input
                :id="id"
                v-model="data[id]"
                class="form-control"
                type="number"
                :disabled="readonly"
                min="0"
                style="min-width:3rem;"
                :readonly="readonly"
                @input="onChange"
            >
        </template>
    </div>
</template>

<script>
    import {
        toRefs,
    } from 'vue';

    import {useI18n} from 'vue-i18n';

    import {
        _debounce
    } from '@/helpers/helpers.js';

    export default {
        props: {
            data: {
                required: true,
                type: Object,
            },
            readonly: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        emits: ['changed'],
        setup(props, context) {
            const {t} = useI18n();
            const {
                data,
                readonly,
            } = toRefs(props);

            const sections = [
                {id: 'left', label: t('main.preference.key.columns.left')},
                {id: 'center', label: t('main.preference.key.columns.center')},
                {id: 'right', label: t('main.preference.key.columns.right')},
            ];

            // FUNCTIONS
            const onChange = _debounce(e => {
                if(readonly.value) return;
                context.emit('changed', data.value);
            }, 250);

            // RETURN
            return {
                t,
                // LOCAL
                onChange,
                // STATE
                sections,
            };
        }
    };
</script>