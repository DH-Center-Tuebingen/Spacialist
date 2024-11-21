<template>
    <simple-search
        :endpoint="searchEntity"
        :filter-fn="prependSelectAllMatches"
        :key-text="'name'"
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
        props: {

        },
        emits: ['selected'],
        setup(props, context) {
            // FETCH

            // FUNCTIONS
            const entitySelected = data => {
                context.emit('selected', data);
            };
            const prependSelectAllMatches = (results, query) => {
                results.unshift({
                    id: -1,
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
