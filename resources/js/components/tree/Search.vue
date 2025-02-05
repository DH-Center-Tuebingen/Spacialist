<template>
    <simple-search
        :endpoint="searchEntity"
        :filter-fn="prependSelectAllMatches"
        :key-text="'name'"
        :can-fetch-more="true"
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
            const prependSelectAllMatches = (results, query, existingItems) => {

                if(isPaginated(results)) {
                    results = results.data;
                }

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
