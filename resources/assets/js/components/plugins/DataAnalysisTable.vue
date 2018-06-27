<template>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered table-sm table-cell-250">
            <thead class="thead-light sticky-top">
                <tr>
                    <th class="align-top" v-for="column in columns" :key="column.key" v-show="renderColumn(column)">
                        {{ column.label }}
                        <v-popover class="d-inline" popover-base-class="popover popover-filter">
                            <a href="#">
                                <i class="fas fa-fw fa-search"></i>
                            </a>
                            <da-filter
                                slot="popover"
                                :column="column"
                                :on-add="onAddFilter">
                            </da-filter>
                        </v-popover>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="row in data">
                    <td v-for="column in columns" v-show="renderColumn(column)">
                        <div v-if="isType(row, column, 'geometry')">
                            <span v-if="row[column.key].geom">
                                {{ row[column.key].geom.coordinates }} ({{ row[column.key].geom.type }})
                            </span>
                            <span v-else>
                                {{ row[column.key].coordinates }} ({{ row[column.key].type }})
                            </span>
                        </div>
                        <div v-else-if="isType(row, column, 'entity')">
                            {{ row[column.key].name }}
                        </div>
                        <div v-else-if="isType(row, column, 'thesaurus') || isType(row, column, 'entity_type')">
                            {{ $translateConcept(row[column.key].thesaurus_url) }}
                        </div>
                        <div v-else-if="isType(row, column, 'percentage')">
                            {{ row[column.key] }}%
                        </div>
                        <div v-else-if="isType(row, column, 'color')" class="badge" :style="{'background-color': row[column.key]}">
                            {{ row[column.key] }}
                        </div>
                        <div v-else-if="isType(row, column, 'list.entity')">
                            <ul>
                                <li v-for="el in row[column.key]">
                                    {{ el.name }}
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="isType(row, column, 'list.thesaurus')">
                            <ul>
                                <li v-for="el in row[column.key]">
                                    {{ $translateConcept(el.thesaurus_url) }}
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="isType(row, column, 'list.bibliography')">
                            <ul>
                                <li v-for="el in row[column.key]">
                                    {{ el.title }} - {{ el.author }}
                                </li>
                            </ul>
                        </div>
                        <div v-else>
                            {{ row[column.key] }}
                        </div>
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
            },
            isType(row, column, type) {
                return row[column.key] && column.type == type;
            }
        },
        mounted() {}
    }
</script>
