<template>
    <div class="row d-flex flex-row overflow-hidden h-100" v-dcan="'view_users'">
        <div class="col-md-12 h-100 d-flex flex-column">
            <h3>
                {{ t('main.activity.title_project', 2) }}
                <span class="badge bg-secondary">
                    {{ t('global.list.nr_of_entries', {
                        cnt: state.pagination.total
                    }, state.pagination.total) }}
                </span>
            </h3>
            <div class="flex-grow-1 overflow-hidden">
                <activity-log
                    class="h-100 overflow-hidden"
                    action-icons="only"
                    :activity="state.filteredActivity"
                    :disable-fetching="state.fetchingDisabled"
                    :no-more-results="state.allFetched"
                    :hide-user="false"
                    :show-filter="true"
                    :on-filter="handleFilterChange"
                    :on-fetch="handleDataFetching">
                </activity-log>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        computed,
        reactive,
        onMounted,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import ActivityMixin from './ActivityMixin.js';

    export default {
        setup(props) {
            const { t } = useI18n();
            const {
                init,
                handleFilterChange,
                handleDataFetching,
                actState,
            } = ActivityMixin();
            // FETCH

            // FUNCTIONS
            const applyFilter = f => {
                state.filter = f;
                filterActivity(null, true, state.filteredData);
            };
            const fetchData = e => {
                if(state.fetchingDisabled) return;
                filterActivity(state.pagination.next_page_url, false, state.filteredData);
            };

            // DATA
            const state = reactive({
                pagination: computed(_ => actState.pagination),
                isFetching: computed(_ => actState.isFetching),
                fetchingDisabled: computed(_ => actState.fetchingDisabled),
                allFetched: computed(_ => actState.allFetched),
                filteredActivity: computed(_ => actState.filteredActivity),
            });

            // WATCHER

            // MOUNTED
            onMounted(_ => {
                init();
            });

            // RETURN
            return {
                t,
                handleFilterChange,
                handleDataFetching,
                state,
            };
        },
    }
</script>
