<template>
    <div class="row d-flex flex-row overflow-hidden h-100" v-can="'view_users'">
        <div class="col-md-12 h-100 d-flex flex-column">
            <h3>Project Activity</h3>
            <form role="form" id="filter-activity-form" class="col px-0 scroll-y-auto" name="filter-activity-form" @submit.prevent="filterActivity(filters)">
                <div class="form-group">
                    <label class="col-form-label col-md-3" for="type">
                        {{ $t('global.users') }}:
                    </label>
                    <div class="col-md-9">
                        <multiselect
                            v-model="filters.users"
                            label="name"
                            track-by="id"
                            :allowEmpty="true"
                            :closeOnSelect="false"
                            :hideSelected="true"
                            :multiple="true"
                            :options="allUsers"
                            :placeholder="$t('global.select.placehoder')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-form-label col-md-3" for="type">
                        {{ $t('global.entity_type') }}:
                    </label>
                    <div class="col-md-7">
                        <multiselect
                            v-model="filters.model.type"
                            label="name"
                            track-by="id"
                            :allowEmpty="true"
                            :closeOnSelect="false"
                            :hideSelected="true"
                            :multiple="true"
                            :options="allEntityTypes"
                            :placeholder="$t('global.select.placehoder')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                    </div>
                    <div class="col-md-2">
                        <input type="number" min="1" step="1" class="form-control" v-model="filters.model.id" />
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-form-label col-md-3" for="type">
                        {{ $t('global.time_from') }}:
                    </label>
                    <div class="col-md-9">
                        <!-- datepicker -->
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-form-label col-md-3" for="type">
                        {{ $t('global.time_to') }}:
                    </label>
                    <div class="col-md-9">
                        <!-- datepicker -->
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-form-label col-md-3" for="type">
                        {{ $t('global.model_type') }}:
                    </label>
                    <div class="col-md-9">
                        <multiselect
                            v-model="filters.type"
                            label="name"
                            track-by="id"
                            :allowEmpty="true"
                            :closeOnSelect="false"
                            :hideSelected="true"
                            :multiple="true"
                            :options="allSupportedModels"
                            :placeholder="$t('global.select.placehoder')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    Fetch Activity
                </button>
            </form>
            <div class="">
                Viewing page {{ pagination.current_page }} of {{ pagination.last_page }} with item {{ pagination.from }} to {{ pagination.to }} and {{ pagination.per_page}} items per page with a total of {{ pagination.total }}
            </div>
            <div class="flex-grow-1 overflow-hidden">
                <activity-log
                    v-if="dataLoaded"
                    class="mt-3 h-100 scroll-y-auto"
                    :activity="filteredActivity"
                    :hide-user="false">
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
                this.dataLoaded = false;
            },
            filterActivity(filter) {
                this.dataLoaded = false;
                $http.post(`activity`).then(response => {
                    this.filteredActivity.length = 0;
                    this.filteredActivity = response.data.data;
                    console.log(response.data);
                    this.dataLoaded = true;
                    this.pagination = response.data;
                    delete this.pagination.data;
                });
            }
        },
        data() {
            return {
                types: ['created', 'updated', 'deleted'],
                dataLoaded: false,
                filteredActivity: [],
                pagination: {},
                filters: {
                    users: [],
                    model: {
                        id: null,
                        type: {}
                    },
                    from: null,
                    to: null,
                    type: {}
                }
            }
        },
    }
</script>
