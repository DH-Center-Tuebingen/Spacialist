<template>
    <div class="row d-flex flex-row overflow-hidden h-100" v-can="'view_users'">
        <div class="col-md-12 h-100 d-flex flex-column">
            <h3>
                {{ $tc('main.activity.title_project', 2) }}
            </h3>
            <div class="flex-grow-1 overflow-hidden">
                <activity-log
                    class="h-100 overflow-hidden"
                    action-icons="only"
                    :activity="filteredActivity"
                    :disable-fetching="fetchingDisabled"
                    :hide-user="false"
                    :show-filter="true"
                    @filter-updated="handleFilterChange"
                    @fetch-data="handleDataFetching">
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
                // TODO fetch supported models
            },
            handleFilterChange(e) {
                this.filter = e.filters;
                this.filterActivity(null, true);
            },
            handleDataFetching(e) {
                if(!this.fetchingDisabled)
                this.filterActivity(this.pagination.next_page_url);
            },
            filterActivity(pageUrl, refresh) {
                pageUrl = pageUrl ? pageUrl : 'activity';
                this.isFetching = true;
                $httpQueue.add(() => $http.post(pageUrl, this.filteredData).then(response => {
                    if(refresh) {
                        this.filteredActivity.length = 0;
                        this.filteredActivity = response.data.data;
                    } else {
                        response.data.data.forEach(d => this.filteredActivity.push(d));
                    }
                    this.pagination = response.data;
                    delete this.pagination.data;
                    this.isFetching = false;
                }));
            },
        },
        data() {
            return {
                filteredActivity: [],
                pagination: {},
                filter: {},
                isFetching: false
            }
        },
        computed: {
            filteredData() {
                let data = {};
                const f = this.filter;
                if(f.users.length > 0) {
                    data.users = f.users.map(u => u.id);
                }
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
            },
            fetchingDisabled() {
                return this.isFetching ||
                    (this.pagination.current_page && this.pagination.current_page == this.pagination.last_page)
                    || !this.pagination.next_page_url;
            }
        }
    }
</script>
