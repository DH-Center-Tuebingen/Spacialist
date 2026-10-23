<template>
    <h4>
        Here will be a GIS in the future!
    </h4>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    export default {
        setup(props) {
            const { t } = useI18n();

            // FETCH

            // DATA
            const state = reactive({
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
    };
</script>