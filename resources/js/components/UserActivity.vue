<template>
    <div class="row d-flex flex-row overflow-hidden h-100" v-can="'view_users'">
        <div class="col-md-12 h-100 d-flex flex-column">
            <h3>
                {{ $tc('main.activity.title_user', 2) }}
                <span class="badge badge-secondary">
                    {{ $tc('main.activity.nr_of_entries', pagination.total, {
                        cnt: pagination.total
                    }) }}
                </span>
            </h3>
            <div class="flex-grow-1 overflow-hidden">
                <activity-log
                    class="h-100 overflow-hidden"
                    action-icons="only"
                    :activity="filteredActivity"
                    :disable-fetching="fetchingDisabled"
                    :hide-user="true"
                    :show-filter="true"
                    @filter-updated="handleFilterChange"
                    @fetch-data="handleDataFetching">
                </activity-log>
            </div>
        </div>
    </div>
</template>

<script>
    import ActivityMixin from './ActivityMixin.vue';

    export default {
        extends: ActivityMixin,
        computed: {
            filteredData() {
                let data = {};
                const f = this.filter;
                data.users = [this.$auth.user().id];
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
            },
        }
    }
</script>
