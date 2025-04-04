<template>
    <div class="d-flex flex-column h-100 px-3 overflow-y-auto overflow-x-hidden">
        <h2>Spacialist Open Access - Panel</h2>
        <p
            v-if="state.prefLoaded"
            class="lead bg-primary bg-opacity-10 p-4 rounded-4 mb-3"
        >
            This page allows open access to the Spacialist Project <span class="fst-italic">{{ state.project }}</span> by <a :href="`mailto:${state.maintainer.email}`" class="fst-italic">{{ state.maintainer.name }}</a>.
        </p>

        <h4>
            Access Modules
        </h4>
        <div class="row mb-3">
            <div class="col-sm-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-medium">
                                Free Search
                            </h5>
                            <p class="card-text">
                                Let's you search through the complete data with any filter combination of entity types and attributes.
                            </p>
                        </div>
                        <div class="mt-2">
                            <router-link
                                class="btn btn-primary"
                                :to="{name: 'freesearch'}"
                            >
                                Start Free Search
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-medium">
                                Single Search
                            </h5>
                            <p class="card-text">
                                Let's you search through all entities of a specific entity type.
                            </p>
                        </div>
                        <div class="mt-2">
                            <router-link
                                class="btn btn-primary"
                                :to="{ name: 'singlesearch'}"
                            >
                                Start Single Search
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h4>
            Project Description
            <small class="small">
                <a
                    v-show="state.showDescription"
                    href="#"
                    class="text-reset"
                    @click.prevent="toggleShowDescription()"
                >
                    <i class="fas fa-fw fa-eye" />
                </a>
                <a
                    v-show="!state.showDescription"
                    href="#"
                    class="text-reset"
                    @click.prevent="toggleShowDescription()"
                >
                    <i class="fas fa-fw fa-eye-slash" />
                </a>
            </small>
        </h4>
        <p
            v-if="state.prefLoaded"
            v-show="state.showDescription"
            class="p-4 bg-primary bg-opacity-10 rounded-4 text-start rendered-markdown"
        >
            <md-viewer :source="state.maintainer.description" />
        </p>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import useSystemStore from '@/bootstrap/stores/system.js';

    export default {
        setup(props) {
            const { t } = useI18n();
            const systemStore = useSystemStore();

            // FETCH

            // DATA
            const state = reactive({
                showDescription: false,
                project: computed(_ => systemStore.getProjectName()),
                maintainer: computed(_ => systemStore.getPreference('prefs.project-maintainer')),
                prefLoaded: computed(_ => !!state.maintainer),
            });

            // FUNCTIONS
            const toggleShowDescription = _ => {
                state.showDescription = !state.showDescription;
            };

            // WATCHER

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                toggleShowDescription,
                // PROPS
                // STATE
                state,
            };
        }
    };
</script>
