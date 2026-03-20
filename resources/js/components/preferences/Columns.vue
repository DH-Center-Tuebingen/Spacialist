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
                :value="modelValue[id]"
                class="form-control"
                type="number"
                :disabled="readonly"
                min="0"
                style="min-width:3rem;"
                :readonly="readonly"
                @input="(event) => onChange(event, id)"
            >
        </template>
    </div>
</template>

<script>
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            /**
            * The column data object with keys 'left', 'center' and 'right'
            */
            modelValue: {
                required: true,
                type: Object,
            },
            readonly: {
                required: false,
                type: Boolean,
                default: false,
            },
        },
        emits: ['changed', 'update:modelValue'],
        setup(props, context) {
            const { t } = useI18n();

            const sections = [
                { id: 'left', label: t('main.preference.key.columns.left') },
                { id: 'center', label: t('main.preference.key.columns.center') },
                { id: 'right', label: t('main.preference.key.columns.right') },
            ];

            // FUNCTIONS
            const onChange = (event, column) => {
                if(props.readonly) return;
                let value = { ...props.modelValue }
                value[column] = parseInt(event.target.value) || 0;
                context.emit('update:modelValue', value);
                context.emit('changed', value);
            }

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