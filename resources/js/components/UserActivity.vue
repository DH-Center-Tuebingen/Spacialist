<template>
    <div class="row d-flex flex-row overflow-hidden h-100" v-dcan="'users_roles_read'">
        <div class="col-md-12 h-100 d-flex flex-column">
            <h3>
                {{ t('main.activity.title_user', 2) }}
                <span class="badge bg-secondary fs-50 fw-normal">
                    {{ t('global.list.nr_of_entries', {
                        cnt: state.pagination.total
                    }, state.pagination.total) }}
                </span>
            </h3>
            <div class="flex-grow-1 overflow-hidden">
                <activity-log
                    class="h-100 overflow-hidden"
                    action-icons="only"
                    :activity="state.userActivity"
                    :disable-fetching="state.fetchingDisabled"
                    :no-more-results="state.allFetched"
                    :hide-user="true"
                    :show-filter="true"
                    :on-filter="applyFilter"
                    :on-fetch="fetchData">
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

    import ActivityMixin from '@/components/ActivityMixin.js';

    import {
        userId
    } from '@/helpers/helpers.js';

    export default {
        setup(props) {
            const { t } = useI18n();
            const {
                init,
                filterActivity,
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
                userActivity: computed(_ => actState.filteredActivity),
                filter: {},
                filteredData: computed(_ => {
                    let data = {};
                    const f = state.filter;
                    data.users = [userId()];
                    if(f.from || f.to) {
                        data.timespan = {};
                        if(f.from) {
                            data.timespan.from = f.from;
                        }
                        if(f.to) {
                            // Add one day minus one millisecond
                            // so f.to is set to the end of the day
                            f.to.setDate(f.to.getDate() + 1);
                            f.to.setMilliseconds(f.to.getMilliseconds() - 1);
                            data.timespan.to = f.to;
                        }
                    }
                    if(f.data_text) {
                        data.text = f.data_text;
                    }
                    return data;
                })
            });

            // WATCHER

            // MOUNTED
            onMounted(_ => {
                init();
            });

            // RETURN
            return {
                t,
                applyFilter,
                fetchData,
                state,
            };
        },
    }
</script>
