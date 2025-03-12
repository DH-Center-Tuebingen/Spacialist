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
            <SomeIcon class="me-1" />
            {{ t('global.dependency.modes.or') }}
        </span>
        <span
            v-show="!modelValue"
            :title="t('global.dependency.modes.and_desc')"
        >
            <EveryIcon class="me-1" />
            {{ t('global.dependency.modes.and') }}
        </span>
    </button>
</template>

<script>
    import { useI18n } from 'vue-i18n';
    import SomeIcon from '../../icons/SomeIcon.vue';
    import EveryIcon from '../../icons/EveryIcon.vue';

    export default {
        components: {
            SomeIcon,
            EveryIcon,
        },
        props: {
            modelValue: {
                type: Object,
                required: true,
            },
        },
        emits: ['update:modelValue'],
        setup(props, { emit }) {
            const toggleGroupState = () => {
                emit('update:modelValue', !props.modelValue);
            };

            return {
                t: useI18n().t,
                toggleGroupState,
            };
        }
    };
</script>