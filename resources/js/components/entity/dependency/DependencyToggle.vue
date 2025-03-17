<template>
    <button
        id="dependency-mode-ingroup-toggle-btn"
        class="btn btn-sm btn-outline-secondary"
        type="button"
        @click="toggleGroupState"
    >
        <span
            v-show="modelValue"
            :title="t('global.dependency.modes.or_desc')"
        >
            <i class="fas fa-fw fa-object-ungroup" />
            {{ t('global.dependency.modes.or') }}
        </span>
        <span
            v-show="!modelValue"
            :title="t('global.dependency.modes.and_desc')"
        >
            <i class="fas fa-fw fa-object-group" />
            {{ t('global.dependency.modes.and') }}
        </span>
    </button>
</template>

<script>
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            modelValue: {
                type: Object,
                required: true,
            },
        },
        emits: ['update:modelValue'],
        setup(props, { emit }) {
            const { t } = useI18n();
            const toggleGroupState = _ => {
                emit('update:modelValue', !props.modelValue);
            };

            return {
                t,
                toggleGroupState,
            };
        }
    };
</script>