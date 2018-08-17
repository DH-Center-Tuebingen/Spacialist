<template>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-bordered table-sm table-cell-250">
            <thead class="thead-light sticky-top">
                <tr>
                    <th class="align-top" v-for="column in columns" :key="column.key" v-show="renderColumn(column)">
                        <div class="d-flex flex-row justify-content-between">
                            <div class="d-flex flex-row">
                                {{ column.label }}
                                <div v-if="onOrderBy">
                                    <a href="#" @click="onOrderBy(column, 'desc')">
                                        <i class="fas fa-fw fa-sort-up"></i>
                                    </a>
                                    <a href="#" @click="onOrderBy(column, 'asc')">
                                        <i class="fas fa-fw fa-sort-down"></i>
                                    </a>
                                </div>
                            </div>
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
                        </div>
                    </th>
                    <th class="align-top" v-for="(splitColumn, k) in splitData">
                        {{ $translateConcept(k) }}
                        <v-popover class="d-inline" popover-base-class="popover popover-filter">
                            <a href="#">
                                <i class="fas fa-fw fa-search"></i>
                            </a>
                            <da-filter
                                slot="popover"
                                :column="{type: splitColumn.type}"
                                :on-add="onAddFilter">
                            </da-filter>
                        </v-popover>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, i) in data">
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
                        <div v-else-if="isType(row, column, 'attribute')">
                            {{ $translateConcept(row[column.key].thesaurus_url) }}
                        </div>
                        <div v-else-if="isType(row, column, 'percentage')">
                            {{ row[column.key] }}%
                        </div>
                        <div v-else-if="isType(row, column, 'color')" class="badge" :style="{'background-color': row[column.key]}">
                            {{ row[column.key] }}
                        </div>
                        <div v-else-if="isType(row, column, 'list.entity')">
                            <ul class="mb-0">
                                <li v-for="el in row[column.key]">
                                    {{ el.name }}
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="isType(row, column, 'list.thesaurus')">
                            <ul class="mb-0">
                                <li v-for="el in row[column.key]">
                                    {{ $translateConcept(el.thesaurus_url) }}
                                </li>
                            </ul>
                        </div>
                        <attributes v-else-if="isType(row, column, 'list.attribute')"
                            :attributes="getAttributes(row[column.key])"
                            :disable-drag="true"
                            :on-extract="getSplitFunction(column)"
                            :selections="{}"
                            :values="getValues(row[column.key])">
                        </attributes>
                        <attributes v-else-if="isType(row, column, 'value')"
                            :attributes="attributeToAttributeArray(row.attribute)"
                            :disable-drag="true"
                            :selections="{}"
                            :values="attributeToValueList(row.attribute, row[column.key])">
                        </attributes>
                        <div v-else-if="isType(row, column, 'list.bibliography')">
                            <ul class="mb-0">
                                <li v-for="el in row[column.key]">
                                    {{ el.title }} - {{ el.author }}
                                </li>
                            </ul>
                        </div>
                        <div v-else>
                            {{ row[column.key] }}
                        </div>
                    </td>
                    <td v-for="(splitColumn, k) in splitData">
                        <attributes v-if="splitColumn.values[i]"
                            :attributes="getAttributes([splitColumn.values[i]])"
                            :disable-drag="true"
                            :selections="{}"
                            :values="getValues([splitColumn.values[i]])">
                        </attributes>
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
            splitData: {
                required: false,
                type: Object,
                default: _ => new Object()
            },
            showHidden: {
                required: false,
                type: Boolean
            },
            onAddFilter: {
                required: false,
                type: Function,
                default: _ => {}
            },
            onAddSplit: {
                required: false,
                type: Function,
                default: _ => {}
            },
            onOrderBy: {
                required: false,
                type: Function
            }
        },
        methods: {
            getAttributes(column) {
                return column.map(c => {
                    c.isDisabled = true;
                    return c;
                });
            },
            getValues(column) {
                let values = {};
                column.forEach(c => {
                    values[c.id] = c.pivot;
                });
                return values;
            },
            getSplitFunction(column) {
                if(column.split_prop) {
                    return attribute => this.onAddSplit(column.key, column.split_prop, attribute.id, attribute.thesaurus_url);
                } else {
                    return undefined;
                }
            },
            attributeToAttributeArray(attribute) {
                return [
                    {
                        ...attribute,
                        isDisabled: true
                    }
                ];
            },
            attributeToValueList(attribute, value) {
                let valueList = {};
                valueList[attribute.id] = {
                    value: value
                };
                return valueList;
            },
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
