<template>
    <div class="d-flex flex-column h-100">
        <nav class="navbar navbar-dark bg-dark navbar-expand-lg overlay-all">
            <div class="container">
                <!-- Branding Image -->
                <a
                    href="/open"
                    class="navbar-brand"
                >
                    <img
                        src="favicon.png"
                        class="logo"
                        alt="spacialist logo"
                    >
                    {{ state.projectName }}
                </a>
            </div>
        </nav>
        <div class="container my-3 col overflow-hidden d-flex flex-column">
            <router-view />
        </div>
    </div>
</template>

<script>
    import {
        reactive,
    } from 'vue';

    import useSystemStore from '@/bootstrap/stores/system.js';

    import {
        fetchGlobals,
    } from '@/open_api.js';

    import { useI18n } from 'vue-i18n';

    export default {
        setup(props) {
            const { t } = useI18n();
            const systemStore = useSystemStore();

            // FETCH
            systemStore.initializeOpenAccess();

            // DATA
            const state = reactive({
                projectName: computed(_ => systemStore.getProjectName()),
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
