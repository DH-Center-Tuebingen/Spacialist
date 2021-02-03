import {
    computed,
    reactive,
} from 'vue';

export default function() {
    // FUNCTIONS
    const init = _ => {
        filterActivity();
    };
    const handleFilterChange = f => {
        actState.filter = f;
        filterActivity(null, true, actState.filteredData);
    };
    const handleDataFetching = _ => {
        if(actState.fetchingDisabled) return;
        filterActivity(actState.pagination.next_page_url, false, actState.filteredData);
    };
    const filterActivity = (pageUrl, refresh, payload) => {
        pageUrl = pageUrl ? pageUrl : 'activity';
        actState.isFetching = true;
        $httpQueue.add(() => $http.post(pageUrl, payload).then(response => {
            if(refresh) {
                actState.filteredActivity.length = 0;
                actState.filteredActivity = response.data.data;
            } else {
                // response.data.data.forEach(d => actState.filteredActivity.push(d));
                actState.filteredActivity.push(...response.data.data);
                // actState.filteredActivity.push.apply(actState.filteredActivity, response.data.data);
            }
            actState.pagination = response.data;
            delete actState.pagination.data;
            actState.isFetching = false;
        }));
    };

    // DATA
    const actState = reactive({
        filteredActivity: [],
        pagination: {
            total: 0,
        },
        filter: {},
        isFetching: false,
        filteredData: computed(_ => {
            let data = {};
            const f = actState.filter;
            if(f.users && f.users.length > 0) {
                data.users = f.users;
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
        }),
        fetchingDisabled: computed(_ => {
            return actState.isFetching || actState.allFetched;
        }),
        allFetched: computed(_ => {
            return (
                actState.pagination.current_page && actState.pagination.current_page == actState.pagination.last_page
            ) || !actState.pagination.next_page_url;
        })
    });

    // RETURN
    return {
        init,
        handleFilterChange,
        handleDataFetching,
        filterActivity,
        actState,
    };
}
