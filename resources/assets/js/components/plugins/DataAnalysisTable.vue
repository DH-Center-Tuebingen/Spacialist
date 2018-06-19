<template>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered table-sm table-cell-250">
            <thead class="thead-light sticky-top">
                <tr>
                    <th class="align-top" v-for="column in columns" v-show="renderColumn(column)">
                        {{ column.label }}
                        <v-popover class="d-inline">
                            <a href="#">
                                <i class="fas fa-fw fa-search"></i>
                            </a>
                            <da-filter
                                slot="popover"
                                :concepts="concepts"
                                :on-add="onAddFilter">
                            </da-filter>
                        </v-popover>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in data">
                    <td v-for="column in columns" v-show="renderColumn(column)">
                        {{ row[column.key] }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    Vue.component('da-filter', require('./DataAnalysisFilter.vue'));

    export default {
        props: {
            concepts: {
                required: true,
                validator: Vue.$validateObject
            },
            columns: {
                required: true,
                type: Array
            },
            data: {
                required: true,
                type: Array
            },
            showHidden: {
                required: false,
                type: Boolean
            },
            onAddFilter: {
                required: false,
                type: Function,
                default: _ => {}
            }
        },
        methods: {
            renderColumn(column) {
                return !column.hidden || (column.hidden && this.showHidden);
            }
        },
        mounted() {}
    }
</script>
