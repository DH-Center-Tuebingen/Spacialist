<template>
    <div class="card-text text-secondary d-flex flex-column gap-2 h-100">
        <MarkdownText
            class="flex-fill"
            :value="value?.metadata?.description ?? ''"
        />
        <span class="opacity-50">
            <i class="fa-regular fa-circle-user"></i> {{ authors }}
        </span>
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
