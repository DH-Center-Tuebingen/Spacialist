<template>
    <div class="card-text my-4 text-secondary">
        <MarkdownText :value="value?.metadata?.description ?? ''" />
        <p>
            <strong>{{ t("main.plugins.author") }}:</strong> {{ authors }}
        </p>
    </div>
</template>

<script>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';
    import MarkdownText from '@/components/mde/MarkdownText.vue';

    export default {
        components: {
            MarkdownText
        },
        props: {
            value: {
                type: Object,
                required: true,
            },
        },
        setup(props) {
            const { t } = useI18n();

            const description = computed(() => {
                return props.value?.metadata?.description ?? '';
            });

            const authors = computed(() => {
                const authors = props.value?.metadata?.authors ?? [];
                if(authors.length === 0) {
                    return '–';
                }
                return authors.join(', ');
            });

            return {
                t,
                description,
                authors
            };
        }
    };
</script>
