<template>
    <div class="row d-flex flex-row overflow-hidden h-100" v-dcan="'view_users'">
        <div class="col-md-12 h-100 d-flex flex-column">
            <h3>
                {{ $tc('main.activity.title_project', 2) }}
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
    import ActivityMixin from './ActivityMixin.vue';

    export default {
        extends: ActivityMixin,
    }
</script>
