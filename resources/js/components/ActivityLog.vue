<template>
    <div class="d-flex flex-column">
        <activity-log-filter
            v-if="state.showFilter"
            class="row"
            :hide-user="state.hideUser"
            :hidden-panel="true"
            @updated="onFilterUpdated"
        />
        <div
            v-if="state.sortedActivity.length > 0"
            v-infinite-scroll="fetchData"
            class="table-responsive h-100 mt-3"
            :infinite-scroll-disabled="state.noMoreResults"
            infinite-scroll-delay="200"
            infinite-scroll-offset="100"
        >
            <table class="table table-light table-striped table-hover">
                <thead class="sticky-top">
                    <tr>
                        <th>
                            #
                        </th>
                        <th v-if="!state.hideUser">
                            <a
                                href="#"
                                @click.prevent="sortBy('user')"
                            >
                                {{ t('global.users') }}
                            </a>
                        </th>
                        <th>
                            <a
                                href="#"
                                @click.prevent="sortBy('action')"
                            >
                                {{ t('global.action') }}
                            </a>
                        </th>
                        <th>
                            <a
                                href="#"
                                @click.prevent="sortBy('time')"
                            >
                                {{ t('global.timestamp') }}
                            </a>
                        </th>
                        <th>
                            {{ t('main.activity.rawdata') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(act, i) in state.sortedActivity"
                        :key="act.id"
                    >
                        <td class="fw-bold">
                            {{ i + 1 }}
                        </td>
                        <td v-if="!state.hideUser">
                            <a
                                href="#"
                                class="text-nowrap"
                                @click.prevent="showUserInfo(act.causer)"
                            >
                                <user-avatar
                                    class="align-middle"
                                    :user="act.causer"
                                    :size="20"
                                />
                                <span class="align-middle ms-2">{{ act.causer.name }}</span>
                            </a>
                        </td>
                        <td :class="state.actionIcons == 'only' ? 'text-center' : ''">
                            <span
                                v-if="state.actionIcons == 'only'"
                                data-bs-toggle="popover"
                                :data-content="act.description"
                                data-trigger="hover"
                                data-placement="right"
                            >
                                <i :class="getIconClass(act.description)" />
                            </span>
                            <span v-else-if="state.actionIcons == 'both'">
                                <i :class="getIconClass(act.description)" /> {{ act.description }}
                            </span>
                            <span v-else>
                                {{ act.description }}
                            </span>
                        </td>
                        <td>
                            {{ date(act.updated_at, 'DD.MM.YYYY HH:mm:ss', true, true) }}
                        </td>
                        <td style="width: 99%;">
                            <button
                                type="button"
                                class="btn btn-primary btn-sm"
                                @click="toggleDataShown(i)"
                            >
                                <i class="fas fa-fw fa-database" />
                                {{ t('main.activity.toggle_raw_data') }}
                            </button>
                            <button
                                v-show="state.dataShown[i]"
                                type="button"
                                class="btn btn-primary btn-sm ms-2"
                                @click="togglePrettyPrint(i, act.properties)"
                            >
                                <i class="fas fa-fw fa-indent" />
                                {{ t('main.activity.toggle_pretty_print') }}
                            </button>
                            <div
                                v-show="state.dataShown[i]"
                                class="mt-2"
                            >
                                <pre
                                    v-highlightjs="state.dataString[i]"
                                    class="mb-0 small rounded"
                                ><code class="fs-1r text-prewrap word-break-all javascript" /></pre>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div
            v-else
            class="alert alert-info mb-0 mt-3"
            role="alert"
        >
            {{ t('global.list.no_entries') }}
        </div>
    </div>
</template>

<script>
import ActivityLogFilter from '@/components/ActivityLogFilter.vue';

import {
    computed,
    reactive,
    toRefs,
} from 'vue';

import { useI18n } from 'vue-i18n';

import {
    _orderBy,
} from '@/helpers/helpers.js';
import {
    date,
} from '@/helpers/filters.js';
import {
    showUserInfo,
} from '@/helpers/modal.js';

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
            default: 'both'
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
        const getIconClass = (desc) => {
            switch(desc) {
                case 'created':
                    return `fas fa-fw fa-plus text-success`;
                case 'updated':
                    return `fas fa-fw fa-edit text-warning`;
                case 'deleted':
                    return `fas fa-fw fa-trash text-danger`;
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
                for(let i = 0; i < state.sortKeys.length; i++) {
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
            getIconClass,
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
