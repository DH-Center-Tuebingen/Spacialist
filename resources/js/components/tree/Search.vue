<template>
    <simple-search
        :endpoint="searchWrapper"
        :filter-fn="prependSelectAllMatches"
        :key-text="'name'"
        :chain="'ancestors'"
        :can-fetch-more="state.hasNextPage"
        :infinite="true"
        @selected="e => entitySelected(e)"
    />
</template>

<script>
    import {
        reactive,
    } from 'vue';

    import {
        searchEntity,
    } from '@/api.js';

    import {
        isPaginated,
        hasNextPage,
    } from '@/helpers/pagination.js';

    export default {
        props: {

        },
        emits: ['selected'],
        setup(props, context) {
            // FETCH

            // FUNCTIONS
            const entitySelected = data => {
                context.emit('selected', data);
            };
            const searchWrapper = async query => {
                if(!query) {
                    state.page = 0;
                    return Promise.resolve([]);
                }
                if(state.lastQueryString.toLowerCase() != query.toLowerCase()) {
                    state.page = 0;
                }
                state.lastQueryString = query;
                state.page++;
                const pagination = await searchEntity(query, state.page);
                state.hasNextPage = hasNextPage(pagination);

                return pagination.data;
            };
            const prependSelectAllMatches = (results, query, existingItems) => {
                // We append the 'Select All' option if there are results and no existing items
                // otherwise we would add it at the start of any 'page' on a paginated list.
                if(results.length !== 0 && existingItems.length === 0) {
                    results.unshift({
                        id: -1,
                        glob: true,
                        query: query,
                        results: results.slice(),
                        name: `Select all entities matching ${query}`,
                    });
                }
                return results;
            };

            // DATA
            const state = reactive({
                lastQueryString: '',
                page: 0,
                hasNextPage: false,
            });

            // RETURN
            return {
                // HELPERS
                searchEntity,
                // LOCAL
                entitySelected,
                searchWrapper,
                prependSelectAllMatches,
                // PROPS
                // STATE
                state,
            };

        },
    };
</script>
