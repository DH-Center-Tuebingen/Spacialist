<template>
    <span class="badge bg-primary">
        <i class="fas fa-fw fa-puzzle-piece" />
        <span class="fst-italic">{{ name }}</span>-{{ t('main.plugins.title') }}
    </span>
</template>

<script>
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            name: {
                required: true,
                type: String,
            },
        },
        setup() {
            const { t } = useI18n();

            // DATA

            // RETURN
            return {
                t,
                // STATE
            };
        },
    };
</script>