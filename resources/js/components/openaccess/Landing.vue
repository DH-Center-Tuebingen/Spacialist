<template>
    <div class="d-flex flex-column h-100 px-3 overflow-y-auto overflow-x-hidden">
        <h2>Spacialist Open Access - Panel</h2>
        <p
            v-if="state.prefLoaded"
            class="lead bg-secondary bg-opacity-10 p-4 rounded-4 mb-3"
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
            <Markdown
                :data="markdownContent"
                :readonly="true"
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

    import useSystemStore from '@/bootstrap/stores/system.js';

    import { Markdown } from 'dhc-components';

    export default {
        components:{
            Markdown,
        },
        setup(props) {
            const { t } = useI18n();
            const systemStore = useSystemStore();

            // FETCH

            // DATA
            const state = reactive({
                showDescription: true,
                project: computed(_ => systemStore.getProjectName()),
                maintainer: computed(_ => systemStore.getPreference('prefs.project-maintainer')),
                prefLoaded: computed(_ => !!state.maintainer),
            });

            // FUNCTIONS
            const toggleShowDescription = _ => {
                state.showDescription = !state.showDescription;
            };

            const markdownContent = computed(_ => {
                return state.maintainer?.description || '_No description available_';
            });

            // WATCHER

            // RETURN
            return {
                t,
                // HELPERS
                // LOCAL
                toggleShowDescription,
                markdownContent,
                // PROPS
                // STATE
                state,
            };
        }
    };
</script>
