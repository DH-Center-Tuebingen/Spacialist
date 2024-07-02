<template>
    <div
        v-if="state.loaded"
    >
        <h5 class="fw-bold">
            {{ state.entity.name }}
        </h5>
        Created at: {{ state.entity.created_at }}
        Last updated: {{ state.entity.updated_at }}

        {{ state.entity.metadata }}
    </div>
</template>

<script>
    import {
        reactive,
    } from 'vue';

    import {
        useRoute,
    } from 'vue-router';

    import {
        getEntity,
    } from '@/open_api.js';

    import { useI18n } from 'vue-i18n';

    export default {
        setup(props) {
            const { t } = useI18n();
            const route = useRoute();

            // FETCH
            console.log(route.params.id);
            getEntity(route.params.id).then(data => {
                state.entity = data;
                state.loaded = true;
            });

            // DATA
            const state = reactive({
                loaded: false,
                entity: null,
            });

            // FUNCTIONS

            // WATCHER

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                // PROPS
                // STATE
                state,
            };
        }
    }
</script>