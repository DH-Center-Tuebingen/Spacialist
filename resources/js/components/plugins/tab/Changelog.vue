<template>
    <div class="changelog">
        <div v-if="loading" class="m-5 text-center">
            <i class="fas fa-2x fa-fw fa-spinner fa-spin" />
        </div>
        <template v-else>
            <alert
                v-if="isEmpty"
                :message="t('main.plugins.info.no_changelog')"
            />
            <MarkdownText
                v-else
                :value="changelog"
            />
        </template>
    </div>
</template>

<script>
    import { computed, onMounted, ref } from 'vue';
    import { useI18n } from 'vue-i18n';
    import { getChangelog } from '@/api/plugin';
    
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

            const t = useI18n().t;

            const changelog = ref('');
            const loading = ref(false);

            const loadChangelog = async () => {
                try {
                    loading.value = true;
                    const fetchedChangelog = await getChangelog(props.value.id)
                    changelog.value = fetchedChangelog;
                    console.log('Fetched changelog:', fetchedChangelog);
                } catch(error) {
                    changelog.value = '';
                } finally {
                    loading.value = false;
                }
            };

            onMounted(() => {
                loadChangelog();
            });

            const isEmpty = computed(() => {
                if(!changelog.value) return true; // no change
                let trimmedChangelog = changelog.value.trim();
                return !trimmedChangelog || trimmedChangelog === '';
            });

            return {
                t,
                changelog,
                loading,
                isEmpty
            };
        }
    };
</script>