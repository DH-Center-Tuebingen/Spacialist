<template>
    <div class="badge bg-primary d-flex flex-row align-items-center justify-content-center gap-1">
        <i class="fas fa-fw fa-puzzle-piece" />
        <div>
            <span class="fst-italic">{{ name }}</span>-{{ t('main.plugins.title') }}
        </div>
    </div>
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