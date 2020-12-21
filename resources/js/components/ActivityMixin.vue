<template>
</template>

<script>
    export default {
        mounted() {
            this.init();
        },
        methods: {
            init() {
                this.filterActivity();
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
                pagination: {
                    total: 0,
                },
                filter: {},
                isFetching: false
            }
        },
        computed: {
            filteredData() {
                let data = {};
                const f = this.filter;
                if(f.users && f.users.length > 0) {
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
                if(f.data_text) {
                    data.text = f.data_text;
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
