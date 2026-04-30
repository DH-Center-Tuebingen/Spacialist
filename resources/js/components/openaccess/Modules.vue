<template>
    <h4>
        Available Site Modules
    </h4>
    <h5>Map Modules</h5>
    <div class="row mb-3">
        <div class="col-sm-6">
            <router-link
                :to="{name: 'webgis'}"
                custom
                v-slot="{ navigate }"
            >
                <div
                    class="card h-100 clickable"
                    @click="navigate"
                >
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-medium">
                                <i class="fas fa-fw fa-map-marked-alt" />
                                Web GIS
                            </h5>
                            <p class="card-text">
                                Let's you search through the complete data on a map with different filters and combinations.
                            </p>
                        </div>
                </div>
                </div>
            </router-link>
        </div>
    </div>
    <h5>Search Modules</h5>
    <div class="row mb-3">
        <div class="col-sm-6">
            <router-link
                :to="{name: 'freesearch'}"
                custom
                v-slot="{ navigate }"
            >
                <div
                    class="card h-100 clickable"
                    @click="navigate"
                >
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-medium">
                                Free Search
                            </h5>
                            <p class="card-text">
                                Let's you search through the complete data with any filter combination of entity types and attributes.
                            </p>
                        </div>
                    </div>
                </div>
            </router-link>
        </div>
        <div class="col-sm-6">
            <router-link
                :to="{name: 'entity-type-search'}"
                custom
                v-slot="{ navigate }"
            >
                <div
                    class="card h-100 clickable"
                    @click="navigate"
                >
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title fw-medium">
                                Single Search
                            </h5>
                            <p class="card-text">
                                Let's you search through all entities of a specific entity type.
                            </p>
                        </div>
                    </div>
                </div>
            </router-link>
        </div>
    </div>
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