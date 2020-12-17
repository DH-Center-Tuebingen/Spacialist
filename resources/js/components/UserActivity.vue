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
                    :activity="userActivity"
                    :hide-user="true"
                    :show-filter="true"
                    @filter-updated="handleFilterChange">
                </activity-log>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.init();
        },
        methods: {
            init() {
                this.filterActivity({});
            },
            handleFilterChange(e) {
                this.filterActivity(e.filters);
            },
            filterActivity(filter, pageUrl) {
                pageUrl = pageUrl ? pageUrl : 'activity';
                $http.post(pageUrl, this.getFilteredData(filter)).then(response => {
                    this.userActivity.length = 0;
                    this.userActivity = response.data.data;
                    this.pagination = response.data;
                    delete this.pagination.data;
                });
            },
            getFilteredData(f) {
                let data = {};
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
            }
        },
        data() {
            return {
                userActivity: [],
                pagination: {
                    total: 0,
                },
            }
        },
    }
</script>
