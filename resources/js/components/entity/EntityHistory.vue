<template>
    <ul
        v-infinite-scroll="requestMore"
        class="list-group pe-2 overflow-auto"
        :infinite-scroll-disabled="allFetched"
        infinite-scroll-delay="200"
        infinite-scroll-offset="100"
    >
        <li
            v-for="entry in history"
            :key="`entity-history-entry-${entry.id}`"
            class="list-group-item"
        >
            <div v-if="entry.subject_type === 'App\\Entity'">
                {{ t('main.history.type.App\\Entity') }}
            </div>
            <EntityHistoryRow
                v-else-if="entry.subject_type === 'attribute_values'"
                :entry="entry"
                :creator-id="creatorId"
            />
            <div v-else>
                {{ t('main.history.type.unknown') }}
            </div>
        </li>
    </ul>
</template>

<script>

    import { useI18n } from 'vue-i18n';
    import { useRoute } from 'vue-router';

    import EntityHistoryRow from '@/components/entity/EntityHistoryRow.vue';

    export default {
        components: {
            EntityHistoryRow
        },
        props: {
            history: {
                type: Array,
                required: true
            },
            allFetched: {
                type: Boolean,
                required: true
            },
            creatorId: {
                type: Number,
                required: true
            },
        },
        emits: ['more'],
        setup(props, context) {
            const route = useRoute();

            const requestMore = () => {
                context.emit('more');
            };

            return {
                requestMore,
                route,
                t: useI18n().t
            };
        },
    };

</script>