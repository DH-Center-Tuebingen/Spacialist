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
            class="mb-0 overflow-hidden"
        >
            <MdViewer
                :classes="'milkdown-wrapper p-3 mt-1 h-100 overflow-scroll'"
                :source="state.maintainer.description"
            />
        </p>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        getPreference,
    } from '@/helpers/helpers.js';

    export default {
        setup(props) {
            const { t } = useI18n();

            // FETCH

            // DATA
            const state = reactive({
                showDescription: true,
                project: computed(_ => getPreference('prefs.project-name')),
                maintainer: computed(_ => getPreference('prefs.project-maintainer')),
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
                getPreference,
                // LOCAL
                toggleShowDescription,
                // PROPS
                // STATE
                state,
            };
        }
    };
</script>
