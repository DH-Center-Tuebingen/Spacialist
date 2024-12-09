<template>
    <simple-search
        :endpoint="searchEntity"
        :filter-fn="prependSelectAllMatches"
        :key-text="'name'"
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
        props: {

        },
        emits: ['selected'],
        setup(props, context) {
            // FETCH

            // FUNCTIONS
            const entitySelected = data => {
                context.emit('selected', data);
            };

            // TODO: Why are we doing this? [SO]
            const prependSelectAllMatches = (paginatedResults, query) => {

                const results = paginatedResults.data.slice();

                if(results.length > 0) {
                    results.unshift({
                        id: -1,
                        glob: true,
                        query: query,
                        name: `Select all entities matching ${query}`,
                    });
                }
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
