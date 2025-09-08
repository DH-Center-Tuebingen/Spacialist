<template>
    <div class="changelog">
        <alert
            v-if="isEmpty"
            :message="t('main.plugins.info.no_changelog')"
        />
        <md-viewer
            v-else
            :source="value?.changelog"
        />
    </div>
</template>

<script>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';

    export default {
        props: {
            value: {
                type: Object,
                required: true,
            },
        },
        setup(props) {

            const t = useI18n().t;

            const isEmpty = computed(() => {
                if(!props.value?.changelog) return true; // no change
                let changelog = props.value?.changelog.trim();
                return !changelog || changelog === '';
            });

            return {
                t,
                isEmpty
            };
        }
    };
</script>