<template>
    <div class="d-flex flex-column">
        <activity-log-filter
            v-if="showFilter"
            :hide-user="hideUser"
            @filter-updated="onFilterUpdated">
        </activity-log-filter>
        <div class="table-responsive h-100 mt-3" v-if="sortedActivity.length > 0">
            <table class="table table-striped table-hover">
                <thead class="thead-light sticky-top">
                    <tr>
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
                            <a href="#" @click.prevent="sortBy('model')">
                                {{ $t('global.element_class') }}
                            </a>
                        </th>
                        <th>
                            <a href="#" @click.prevent="sortBy('time')">
                                {{ $t('global.timestamp') }}
                            </a>
                        </th>
                        <th>
                            {{ $t('main.activity.data') }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="act in sortedActivity" :key="act.id">
                        <td v-if="!hideUser">{{ act.causer.name }}</td>
                        <td :class="actionIcons == 'only' ? 'text-center align-middle' : ''">
                            <span v-if="actionIcons == 'only'" v-html="getIcon(act.description)" data-toggle="popover" :data-content="act.description" data-trigger="hover" data-placement="right">
                            </span>
                            <span v-else-if="actionIcons == 'both'" v-html="getIcon(act.description, true)">
                            </span>
                            <span v-else>
                                {{ act.description }}
                            </span>
                        </td>
                        <td v-html="getName(act)"></td>
                        <td>
                            {{ act.updated_at | date('DD.MM.YYYY HH:mm:ss', true, true) }}
                        </td>
                        <td>
                            {{ act.properties }}
                        </td>
                    </tr>
                    <tr>
                        <td :colspan="hideUser ? '4' : '5'" v-if="!disableFetching">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="fetchData()">
                                Load next items
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
                    $('[data-toggle="popover"]').popover()
                });
            },
            getName(a) {
                let name = '';
                switch(a.subject_type) {
                    case 'App\\Entity':
                        name =  `<i class="fas fa-fw fa-monument text-primary"></i>`;
                        break;
                    case 'App\\File':
                        name =  `<i class="fas fa-fw fa-file text-primary"></i>`;
                        break;
                    case 'App\\Geodata':
                        name =  `<i class="fas fa-fw fa-map-marker-alt text-primary"></i>`;
                        break;
                    default:
                        name = '';
                        break;
                }
                name += ' ';
                if(a.subject) {
                    switch(a.subject_type) {
                        case 'App\\Geodata':
                            name += `Geodata #${a.subject.id}`;
                            break;
                        default:
                            name +=  a.subject.name;
                            break;
                    }
                } else if(a.properties.attributes.name) {
                    name += a.properties.attributes.name;
                } else {
                    let type = a.subject_type;
                    type = type.substring(type.lastIndexOf('\\') + 1);
                    let id = a.subject_id;
                    name += `<span class="font-italic" data-toggle="popover" data-content="${type} #${id}" data-trigger="hover" data-placement="bottom">Deleted Element</span>`;
                }

                return name;
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
                isFetching: false
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
            }
        }
    }
</script>
