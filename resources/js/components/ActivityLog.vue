<template>
    <div class="d-flex flex-column">
        <activity-log-filter
            class="row"
            v-if="state.showFilter"
            :hide-user="state.hideUser"
            :hidden-panel="true"
            @updated="onFilterUpdated">
        </activity-log-filter>
        <div class="table-responsive h-100 mt-3" v-if="state.sortedActivity.length > 0">
            <table class="table table-light table-striped table-hover">
                <thead class="sticky-top">
                    <tr>
                        <th>
                            #
                        </th>
                        <th v-if="!state.hideUser">
                            <a href="#" @click.prevent="sortBy('user')">
                                {{ t('global.users') }}
                            </a>
                        </th>
                        <th>
                            <a href="#" @click.prevent="sortBy('action')">
                                {{ t('global.action') }}
                            </a>
                        </th>
                        <th>
                            <a href="#" @click.prevent="sortBy('time')">
                                {{ t('global.timestamp') }}
                            </a>
                        </th>
                        <th>
                            {{ t('main.activity.rawdata') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(act, i) in state.sortedActivity" :key="act.id">
                        <td class="fw-bold">
                            {{ i + 1 }}
                            </td>
                        <td v-if="!state.hideUser">
                            <a href="#" @click.prevent="showUserInfo(act.causer)" class="text-nowrap">
                                <user-avatar class="align-middle" :user="act.causer" :size="20"></user-avatar>
                                <span class="align-middle ms-2">{{ act.causer.name }}</span>
                            </a>
                        </td>
                        <td :class="state.actionIcons == 'only' ? 'text-center' : ''">
                            <span v-if="state.actionIcons == 'only'" v-html="getIcon(act.description)" data-bs-toggle="popover" :data-content="act.description" data-trigger="hover" data-placement="right">
                            </span>
                            <span v-else-if="state.actionIcons == 'both'" v-html="getIcon(act.description, true)">
                            </span>
                            <span v-else>
                                {{ act.description }}
                            </span>
                        </td>
                        <td>
                            {{ date(act.updated_at, 'DD.MM.YYYY HH:mm:ss', true, true) }}
                        </td>
                        <td style="width: 99%;">
                            <button type="button" class="btn btn-primary btn-sm" @click="toggleDataShown(i)">
                                <i class="fas fa-fw fa-database"></i>
                                {{ t('main.activity.toggle_raw_data') }}
                            </button>
                            <button type="button" class="btn btn-primary btn-sm ms-2" v-show="state.dataShown[i]" @click="togglePrettyPrint(i, act.properties)">
                                <i class="fas fa-fw fa-indent"></i>
                                {{ t('main.activity.toggle_pretty_print') }}
                            </button>
                            <div v-show="state.dataShown[i]" class="mt-2">
                                <pre class="mb-0 small rounded" v-highlightjs="state.dataString[i]"><code class="text-prewrap word-break-all javascript"></code></pre>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td :colspan="hideUser ? '4' : '5'">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="fetchData" :disabled="state.disableFetching">
                                <span v-if="!state.noMoreResults">
                                    <i class="fas fa-fw fa-sync"></i>
                                    {{ t('global.list.fetch_next_entries') }}
                                </span>
                                <span v-else>
                                    <i class="fas fa-fw fa-ban"></i>
                                    {{ t('global.list.no_more_entries') }}
                                </span>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- <infinite-loading @infinite="fetchData">
                <span slot="spinner"></span>
                <span slot="no-more"></span>
                <span slot="no-results"></span>
            </infinite-loading> -->
        </div>
        <div v-else class="alert alert-info mb-0 mt-3" role="alert">
            {{ t('global.list.no_entries') }}
        </div>
    </div>
</template>

<script>
    import ActivityLogFilter from './ActivityLogFilter.vue';

    import {
        computed,
        reactive,
        toRefs,
    } from 'vue';

    import { useI18n } from 'vue-i18n';

    import {
        _orderBy,
    } from '../helpers/helpers.js';
    import {
        date,
    } from '../helpers/filters.js';
    import {
        showUserInfo,
    } from '../helpers/modal.js';

    export default {
        components: {
            'activity-log-filter': ActivityLogFilter,
        },
        props: {
            activity: {
                required: true,
                type: Array
            },
            showFilter: {
                required: false,
                type: Boolean,
                default: false
            },
            disableFetching: {
                required: false,
                type: Boolean,
                default: false
            },
            noMoreResults: {
                required: false,
                type: Boolean,
                default: false,
            },
            hideUser: {
                required: false,
                type: Boolean,
                default: false
            },
            actionIcons: {
                required: false,
                type: String,
                default: "both"
            },
            onFilter: {
                required: true,
                type: Function,
            },
            onFetch: {
                required: true,
                type: Function,
            },
        },
        setup(props) {
            const { t } = useI18n();
            const {
                activity,
                showFilter,
                disableFetching,
                noMoreResults,
                hideUser,
                actionIcons,
            } = toRefs(props);

            // FUNCTIONS
            const sortBy = col => {
                if(state.sortOn == col) {
                    state.desc = !state.desc;
                } else {
                    state.desc = false;
                    state.sortOn = col;
                }
            };
            const getIcon = (desc, withText) => {
                const addon = !!withText ? ` ${desc}` : '';
                switch(desc) {
                    case 'created':
                        return `<i class="fas fa-fw fa-plus text-success"></i>${addon}`;
                    case 'updated':
                        return `<i class="fas fa-fw fa-edit text-warning"></i>${addon}`;
                    case 'deleted':
                        return `<i class="fas fa-fw fa-trash text-danger"></i>${addon}`;
                }
            };
            const setDataString = (i, data) => {
                let str;
                if(state.dataIsPretty[i]) {
                    str = JSON.stringify(data, null, 4);
                } else {
                    str = JSON.stringify(data);
                }
                state.dataString[i] = str;
            };
            const toggleDataShown = i => {
                state.dataShown[i] = !state.dataShown[i];
                state.dataIsPretty[i] = false;
                setDataString(i, state.sortedActivity[i].properties);
            };
            const togglePrettyPrint = (i, data) => {
                state.dataIsPretty[i] = !state.dataIsPretty[i];
                setDataString(i, data);
            };
            const fetchData = _ => {
                if(state.disableFetching) return;
                props.onFetch();
            };
            const onFilterUpdated = e => {
                props.onFilter(e.filters);
            };

            // DATA
            const state = reactive({
                sortOn: 'time',
                desc: false,
                isFetching: false,
                showFilter: showFilter,
                disableFetching: disableFetching,
                noMoreResults: noMoreResults,
                hideUser: hideUser,
                actionIcons: actionIcons,
                dataShown: [],
                dataIsPretty: [],
                dataString: [],
                sortedActivity: computed(_ => {
                    let sortOrder = [];
                    for(let i=0; i<state.sortKeys.length; i++) {
                        sortOrder.push(state.desc ? 'desc' : 'asc');
                    }
                    return _orderBy(activity.value, state.sortKeys, sortOrder);
                }),
                sortKeys: computed(_ => {
                    switch(state.sortOn) {
                        case 'user':
                            return ['causer.name', 'id'];
                        case 'action':
                            return ['description', 'id'];
                        case 'model':
                            return ['subject_type', 'subject_id', 'id'];
                        case 'time':
                            return ['properties.attributes.updated_at', 'id'];
                        default:
                            return [state.sortOn, 'id'];
                    }
                }),
            });

            // RETURN
            return {
                t,
                // HELPERS
                date,
                showUserInfo,
                // LOCAL
                sortBy,
                getIcon,
                toggleDataShown,
                togglePrettyPrint,
                fetchData,
                onFilterUpdated,
                // PROPS
                // STATAE
                state,
            };
        },
    }
</script>
