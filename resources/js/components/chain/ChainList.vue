<template>
    <Chain
        v-for="path in truncatedList"
        :key="path.join('-') ?? 'default'"
        :chain="path"
        class="text-secondary"
    />
    <div
        v-if="lists.length > maxLength"
        class="list-info opacity-50 text-secondary small fst-italic text-secondary"
    >
        {{ t("global.list.and_more") }}
    </div>
</template>

<script>
    import { computed } from 'vue';
    import { useI18n } from 'vue-i18n';
    import {
        sortAlphabetically,
        sortByLength,
    } from '@/helpers/helpers.js';

    import Chain from './Chain.vue';

    export default {
        components: {
            Chain,
        },
        props: {
            // Array of arrays.
            lists: {
                type: Array,
                required: false,
                default: () => [],
            },
            maxLength: {
                type: Number,
                required: false,
                default: 0,
            },
        },
        setup(props) {
            const { t } = useI18n();
            const truncatedList = computed(_ => {
                const sortedList = props.lists.toSorted(
                    sortByLength(sortAlphabetically())
                );

                if(props.maxLength) {
                    return sortedList.slice(0, props.maxLength);
                } else {
                    return sortedList;
                }
            });

            return {
                t,
                truncatedList,
            };
        },
    };
</script>