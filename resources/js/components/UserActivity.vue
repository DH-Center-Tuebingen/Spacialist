<template>
    <div>
        <h3>
            {{ $tc('main.activity.title_user', 2) }}
        </h3>
        <activity-log
            :activity="userActivity"
            :hide-user="true"
            :show-filter="true"
            @filter-updated="handleFilterChange">
        </activity-log>
    </div>
</template>

<script>
    export default {
        mounted() {
            this.init();
        },
        methods: {
            init() {
                $http.post(`activity`, this.getFilteredData({})).then(response => {
                    this.userActivity.length = 0;
                    this.userActivity = response.data.data;
                    this.pagination = response.data;
                    delete this.pagination.data;
                });
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
                if(f.model) {
                    data.model = f.model.id;
                }
                return data;
            }
        },
        data() {
            return {
                userActivity: [],
            }
        },
    }
</script>
