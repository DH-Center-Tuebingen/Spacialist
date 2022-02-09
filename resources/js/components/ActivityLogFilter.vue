<template>
    <div class="row">
        <div class="col-md-4">
            <form role="form" id="filter-activity-form" v-show="!state.hideFilterPanel" name="filter-activity-form" @submit.prevent="filterActivity(state.filters)">
                <div class="row mb-3" v-if="!state.hideUser">
                    <label class="col-form-label col-md-3" for="filter-activity-user">
                        {{ t('global.users', 2) }}:
                    </label>
                    <div class="col-md-9">
                        <multiselect
                            v-model="state.filters.users"
                            id="filter-activity-user"
                            :mode="'tags'"
                            :options="state.allUsers"
                            :searchable="true"
                            :valueProp="'id'"
                            :trackBy="'name'"
                            :label="'name'"
                            :placeholder="t('global.select.placeholder')"
                            :hideSelected="true">
                        </multiselect>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-form-label col-md-3" for="filter-activity-timespan-from">
                        {{ t('global.timespan') }}:
                    </label>
                    <div class="col-md-9">
                        <div class="row align-items-center">
                            <date-picker
                                class="mx-datepicker-col-5"
                                id="filter-activity-timespan-from"
                                v-model:value="state.filters.from"
                                :disabled-date="(date) => date > (state.filters.to ? state.filters.to : new Date())"
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
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0" :disabled="!state.filters.from" @click="state.filters.to = state.filters.from">
                                    <i class="fas fa-fw fa-long-arrow-alt-right"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-0" :disabled="!state.filters.to" @click="state.filters.from = state.filters.to">
                                    <i class="fas fa-fw fa-long-arrow-alt-left"></i>
                                </button>
                            </div>
                            <date-picker
                                class="mx-datepicker-col-5"
                                id="filter-activity-timespan-to"
                                v-model:value="state.filters.to"
                                :disabled-date="(date) => date > new Date() || date < (state.filters.from ? state.filters.from : new Date(0))"
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
                <div class="row mb-3">
                    <label class="col-form-label col-md-3" for="filter-activity-rawdata">
                        {{ t('main.activity.search_in_raw_data') }}:
                    </label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" id="filter-activity-rawdata" v-model="state.filters.data_text" />
                    </div>
                </div>
                <button type="submit" class="btn btn-outline-primary">
                    {{ t('main.activity.apply_filter') }}
                </button>
            </form>
            <div :class="{'mt-3': !state.hideFilterPanel}">
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" id="hide-filter-panel-switch" v-model="state.hideFilterPanel">
                    <label class="form-check-label" for="hide-filter-panel-switch">
                        {{ t('main.activity.hide_filter_panel') }}
                    </label>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import {
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        getUsers
    } from '../helpers/helpers.js';

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
        emits: ['updated'],
        setup(props, context) {
            // FETCH
            const { t } = useI18n();
            const {
                hideUser,
                hiddenPanel,
            } = toRefs(props);

            // FUNCTIONS
            const filterActivity = filters => {
                context.emit('updated', {
                    filters: filters
                });
            };

            // DATA
            const state = reactive({
                hideUser: hideUser,
                hiddenFilterPanel: hiddenPanel,
                types: ['created', 'updated', 'deleted'],
                allUsers: hideUser.value ? [] : getUsers(),
                filters: {
                    users: [],
                    from: null,
                    to: null,
                    data_text: '',
                }
            });

            // RETURN
            return {
                t,
                filterActivity,
                state
            }
        },
    }
</script>
