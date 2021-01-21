<template>
    <div class="d-flex flex-column">
        <activity-log-filter
            class="row"
            v-if="showFilter"
            :hide-user="hideUser"
            :hidden-panel="true"
            @filter-updated="onFilterUpdated">
        </activity-log-filter>
        <div class="table-responsive h-100 mt-3" v-if="sortedActivity.length > 0">
            <table class="table table-striped table-hover">
                <thead class="thead-light sticky-top">
                    <tr>
                        <th>
                            #
                        </th>
                        <th v-if="!hideUser">
                            <a href="#" @click.prevent="sortBy('user')">
                                {{ $tc('global.users') }}
                            </a>
                        </th>
                        <th>
                            <a href="#" @click.prevent="sortBy('action')">
                                {{ $t('global.action') }}
                            </a>
                        </th>
                        <th>
                            <a href="#" @click.prevent="sortBy('time')">
                                {{ $t('global.timestamp') }}
                            </a>
                        </th>
                        <th>
                            {{ $t('main.activity.rawdata') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(act, i) in sortedActivity" :key="act.id">
                        <td class="font-weight-bold">
                            {{ i + 1 }}
                            </td>
                        <td v-if="!hideUser">
                            <a href="#" @click.prevent="$showUserInfo(act.causer)" class="text-nowrap">
                                <user-avatar :user="act.causer" :size="20"></user-avatar>
                                <span class="align-middle">{{ act.causer.name }}</span>
                            </a>
                        </td>
                        <td :class="actionIcons == 'only' ? 'text-center' : ''">
                            <span v-if="actionIcons == 'only'" v-html="getIcon(act.description)" data-bs-toggle="popover" :data-content="act.description" data-trigger="hover" data-placement="right">
                            </span>
                            <span v-else-if="actionIcons == 'both'" v-html="getIcon(act.description, true)">
                            </span>
                            <span v-else>
                                {{ act.description }}
                            </span>
                        </td>
                        <td>
                            {{ act.updated_at | date('DD.MM.YYYY HH:mm:ss', true, true) }}
                        </td>
                        <td style="width: 99%;">
                            <button type="button" class="btn btn-primary btn-sm" @click="toggleDataShown(i)">
                                <i class="fas fa-fw fa-database"></i>
                                {{ $t('main.activity.toggle_raw_data') }}
                            </button>
                            <button type="button" class="btn btn-primary btn-sm ms-2" v-show="dataShown[i]" @click="togglePrettyPrint(i, act.properties)">
                                <i class="fas fa-fw fa-indent"></i>
                                {{ $t('main.activity.toggle_pretty_print') }}
                            </button>
                            <div v-show="dataShown[i]" class="mt-2">
                                <pre class="mb-0 small rounded" v-highlightjs="dataStrings[i]"><code class="text-prewrap word-break-all"></code></pre>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td :colspan="hideUser ? '4' : '5'" v-if="!disableFetching">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="fetchData()">
                                {{ $t('main.activity.fetch_next_entries') }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <infinite-loading @infinite="fetchData">
                <span slot="spinner"></span>
                <span slot="no-more"></span>
                <span slot="no-results"></span>
            </infinite-loading>
        </div>
        <p v-else class="alert alert-info mb-0 mt-3">
            {{ $t('main.activity.no_results') }}
        </p>
    </div>
</template>

<script>
    export default {
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
            hideUser: {
                required: false,
                type: Boolean,
                default: false
            },
            actionIcons: {
                required: false,
                type: String,
                default: "both"
            }
        },
        mounted() {
        },
        methods: {
            sortBy(col) {
                if(this.sortOn == col) {
                    this.desc = !this.desc;
                } else {
                    this.desc = false;
                    this.sortOn = col;
                }
            },
            fetchData($state) {
                if(this.disableFetching) return;
                this.$emit('fetch-data');
                this.$nextTick(_ => {
                    $('[data-bs-toggle="popover"]').popover()
                });
            },
            toggleDataShown(i) {
                Vue.set(this.dataShown, i, !this.dataShown[i]);
                Vue.set(this.dataIsPretty, i, false);
                this.setDataString(i, this.sortedActivity[i].properties);
            },
            togglePrettyPrint(i, data) {
                Vue.set(this.dataIsPretty, i, !this.dataIsPretty[i]);
                this.setDataString(i, data);
            },
            setDataString(i, data) {
                let str;
                if(this.dataIsPretty[i]) {
                    str = JSON.stringify(data, null, 4);
                } else {
                    str = JSON.stringify(data);
                }
                Vue.set(this.dataStrings, i, str);
            },
            getIcon(desc, withText) {
                const addon = !!withText ? ` ${desc}` : '';
                switch(desc) {
                    case 'created':
                        return `<i class="fas fa-fw fa-plus text-success"></i>${addon}`;
                    case 'updated':
                        return `<i class="fas fa-fw fa-edit text-warning"></i>${addon}`;
                    case 'deleted':
                        return `<i class="fas fa-fw fa-trash text-danger"></i>${addon}`;
                }
            },
            getStyle(a) {
                let style = '';
                switch(a.description) {
                    case 'created':
                        style += 'border-left: 5px solid green;';
                        break;
                    case 'updated':
                        style += 'border-left: 5px solid orange;';
                        break;
                    case 'deleted':
                        style += 'border-left: 5px solid red;';
                        break;
                }
                return style;
            },
            onFilterUpdated(e) {
                this.$emit('filter-updated', e);
            }
        },
        data() {
            return {
                sortOn: 'time',
                desc: false,
                isFetching: false,
                dataShown: [],
                dataIsPretty: [],
                dataStrings: [],
            }
        },
        computed: {
            sortedActivity() {
                let sortOrder = [];
                for(let i=0; i<this.sortKeys.length; i++) {
                    sortOrder.push(this.desc ? 'desc' : 'asc');
                }
                return _orderBy(this.activity, this.sortKeys, sortOrder);
            },
            sortKeys() {
                switch(this.sortOn) {
                    case 'user':
                        return ['causer.name', 'id'];
                    case 'action':
                        return ['description', 'id'];
                    case 'model':
                        return ['subject_type', 'subject_id', 'id'];
                    case 'time':
                        return ['properties.attributes.updated_at', 'id'];
                    default:
                        return [this.sortOn, 'id'];
                }
            },
        }
    }
</script>
