<template>
    <div class="d-flex flex-column h-100">
        <nav class="navbar navbar-dark bg-dark navbar-expand-lg overlay-all">
            <div class="container">
                <!-- Branding Image -->
                <router-link
                    :to="{ name: 'landing' }"
                    class="navbar-brand"
                >
                    <img
                        src="favicon.png"
                        class="logo"
                        alt="spacialist logo"
                    >
                    {{ getPreference('prefs.project-name') }}
                </router-link>
                <div
                    id="navbarSupportedContent"
                    class="collapse navbar-collapse"
                >
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <router-link
                                :to="{ name: 'modules' }"
                                class="nav-link"
                            >
                                Modules
                            </router-link>
                        </li>
                    </ul>
                </div>
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

    import store from '@/bootstrap/store.js';

    import {
        getPreference,
    } from '@/helpers/helpers.js';

    import {
        fetchGlobals,
    } from '@/open_api.js';

    import { useI18n } from 'vue-i18n';

    export default {
        setup(props) {
            const { t } = useI18n();

            // FETCH
            fetchGlobals().then(data => {
                store.commit('setConcepts', data.concepts);
                store.commit('setPreferences', data.preferences);
                store.commit('setUsers', {
                    active: data.users,
                    deleted: data.deleted_users,
                });
            });

            // DATA
            const state = reactive({
            });

            // FUNCTIONS

            // WATCHER

            // RETURN
            return {
                t,
                // HELPERS
                getPreference,
                // LOCAL
                // PROPS
                // STATE
                state,
            };
        }
    };
</script>
