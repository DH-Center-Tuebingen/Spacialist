<template>
    <div class="card-text my-4 text-secondary">
        <VueMarkdownIt :source="value.metadata.description" />
        <p>
            <strong>{{ t("main.plugins.author") }}:</strong> {{
                (value.metadata.authors ?? []).join(', ') }}
        </p>
    </div>
</template>

<script>
    import { useI18n } from 'vue-i18n';
    import { VueMarkdownIt } from '@f3ve/vue-markdown-it';

    export default {
        components: {
            VueMarkdownIt,
        },
        props: {
            value: {
                type: Object,
                required: true,
            },
        },
        setup() {
            const { t } = useI18n();
            return { t };
        }
    };
</script>

<style lang='scss' scoped></style>