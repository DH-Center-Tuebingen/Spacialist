<template>
    <div class="">
        <form role="form" id="filter-activity-form" class="col-md-4" v-show="!hideFilterPanel" name="filter-activity-form" @submit.prevent="filterActivity(filters)">
            <div class="form-group row" v-if="!hideUser">
                <label class="col-form-label col-md-3" for="filter-activity-user">
                    {{ $tc('global.users', 2) }}:
                </label>
                <div class="col-md-9">
                    <multiselect
                        v-model="filters.users"
                        id="filter-activity-user"
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
            <div class="form-group row">
                <label class="col-form-label col-md-3" for="filter-activity-timespan-from">
                    {{ $t('global.timespan') }}:
                </label>
                <div class="col-md-9">
                    <div class="row align-items-center">
                        <date-picker
                            class="col-5"
                            id="filter-activity-timespan-from"
                            v-model="filters.from"
                            :disabled-date="(date) => date > (filters.to ? filters.to : new Date())"
                            :input-class="'form-control'"
                            :show-week-number="true"
                            :value-type="'date'">
                            <template v-slot:icon-calendar>
                                <i class="fas fa-fw fa-calendar-alt"></i>
                            </template>
                            <template v-slot:icon-clear>
                                <i class="fas fa-fw fa-times"></i>
                            </template>
                        </date-picker>
                        <div class="col-2 d-flex flex-row align-items-center justify-content-between">
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" :disabled="!filters.from" @click="filters.to = filters.from">
                                <i class="fas fa-fw fa-long-arrow-alt-right"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-0" :disabled="!filters.to" @click="filters.from = filters.to">
                                <i class="fas fa-fw fa-long-arrow-alt-left"></i>
                            </button>
                        </div>
                        <date-picker
                            class="col-5"
                            id="filter-activity-timespan-to"
                            v-model="filters.to"
                            :disabled-date="(date) => date > new Date() || date < (filters.from ? filters.from : new Date(0))"
                            :input-class="'form-control'"
                            :show-week-number="true"
                            :value-type="'date'">
                            <template v-slot:icon-calendar>
                                <i class="fas fa-fw fa-calendar-alt"></i>
                            </template>
                            <template v-slot:icon-clear>
                                <i class="fas fa-fw fa-times"></i>
                            </template>
                        </date-picker>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-md-3" for="filter-activity-rawdata">
                    {{ $t('main.activity.search_in_raw_data') }}:
                </label>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="filter-activity-rawdata" v-model="filters.data_text" />
                </div>
            </div>
            <button type="submit" class="btn btn-outline-primary">
                {{ $t('main.activity.apply_filter') }}
            </button>
        </form>
        <div class="col-md-4" :class="{'mt-3': !hideFilterPanel}">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="hide-filter-panel-switch" v-model="hideFilterPanel">
                <label class="custom-control-label" for="hide-filter-panel-switch">
                    {{ $t('main.activity.hide_filter_panel') }}
                </label>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            hideUser: {
                required: false,
                type: Boolean,
                default: false
            },
            hiddenPanel: {
                required: false,
                type: Boolean,
                default: false
            },
        },
        mounted() {
        },
        methods: {
            filterActivity(filters) {
                this.$emit('filter-updated', {
                    filters: filters
                });
            },
        },
        data() {
            return {
                hideFilterPanel: this.hiddenPanel,
                types: ['created', 'updated', 'deleted'],
                allUsers: this.hideUser ? [] : this.$getUsers(),
                filters: {
                    users: [],
                    from: null,
                    to: null,
                    data_text: '',
                }
            }
        },
    }
</script>
