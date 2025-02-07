<template>
    <Chain
        v-for="path in state.truncatedList(option)"
        :key="path.join('-') ?? 'default'"
        :chain="path"
        class="text-secondary"
    />
    <div
        v-if="lists.length > state.maxChildLength"
        class="list-info opacity-50 text-secondary small fst-italic text-secondary"
    >
        {{ t("global.list.and_more") }}
    </div>
</template>

<script>
    import { useI18n } from 'vue-i18n';
    import { reactive } from 'vue';
    import { sortAlphabetically, sortByLength } from '@/helpers/helpers';

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
            const state = reactive({
                maxChildLength: 3,
                truncatedList: () => {
                    const sortedList = props.lists.toSorted(
                        sortByLength(sortAlphabetically())
                    );

                    if(props.maxLength) {
                        return sortedList.slice(0, props.maxLength);
                    } else {
                        return sortedList;
                    }
                },
            });

            return {
                t: useI18n().t,
                state,
            };
        },
    };
</script>