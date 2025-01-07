<template>
    <simple-search
        :endpoint="searchEntity"
        :filter-fn="prependSelectAllMatches"
        :key-text="'name'"
        chain="chain"
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

    export default {
        emits: ['selected'],
        setup(props, context) {
            // FETCH

            // FUNCTIONS
            const entitySelected = data => {
                context.emit('selected', data);
            };

            const constructChain = option => {
                let chain = [];
                if(option.parentNames && option.parentNames.length > 0) {
                    chain = option.parentNames.reverse();
                    chain.pop();
                }

                option.chain = chain;
                return option;
            };

            const prependSelectAllMatches = (paginatedResults, query) => {
                const results = paginatedResults.data.map(constructChain);
                results.unshift({
                    id: -1,
                    chain: [],
                    glob: true,
                    query: query,
                    results: results.slice(),
                    name: `Select all entities matching ${query}`,
                });
                return results;
            };

            // DATA
            const state = reactive({

            });

            // RETURN
            return {
                // HELPERS
                searchEntity,
                // LOCAL
                entitySelected,
                prependSelectAllMatches,
                // PROPS
                // STATE
                state,
            };

        },
    };
</script>
