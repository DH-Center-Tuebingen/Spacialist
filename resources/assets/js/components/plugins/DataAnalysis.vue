<template>
    <div class="h-100 row of-hidden">
        <button type="button" class="btn btn-outline-secondary btn-toggle" @click="toggleShowFilterOptions()">
            <span v-show="showFilterOptions">
                <i class="fas fa-fw fa-arrow-left"></i>
            </span>
            <span v-show="!showFilterOptions">
                <i class="fas fa-fw fa-bars"></i>
            </span>
        </button>
        <div :class="showFilterOptions ? 'col-md-3' : 'd-none'">
            <h4>Query Options</h4>
            <div v-show="expertMode">
                TODO
                <p class="alert alert-danger">
                    Expert Mode is still WIP
                </p>
                <h5>Active Order Rules</h5>
                <ol v-if="orders.length">
                    <li v-for="order in orders">
                        <span>{{order.col}} {{order.dir}}</span>
                    </li>
                </ol>
                <p v-else>
                    No order rules
                </p>
            </div>
            <h5>Active Filters</h5>
            <ol v-if="filters.length">
                <li v-for="filter in filters">
                    <span>
                        {{filter.col}} {{filter.comp}} {{filter.comp_value}}
                        <span v-if="filter.func">
                            (Usig {{filter.func}} with {{filter.func_values}})
                        </span>
                    </span>
                </li>
            </ol>
            <p v-else>
                No filters active.
            </p>
            <h5>Further Query Options</h5>
            <form role="form" @submit.prevent="applyFilter()">
                <div class="form-check form-group offset-md-3">
                    <input type="checkbox" class="form-check-input" id="apply-changes-toggle" v-model="instantFilter" />
                    <label for="apply-changes-toggle" class="form-check-label">Apply Changes immediately</label>
                </div>
                <div class="form-group row">
                    <label for="table" class="col-md-3 col-form-label">Table</label>
                    <div class="col-md-9">
                        <multiselect
                            label="label"
                            track-by="name"
                            v-model="origin"
                            :allowEmpty="false"
                            :closeOnSelect="true"
                            :hideSelected="true"
                            :multiple="false"
                            :options="origins"
                            @input="updateOrigin">
                        </multiselect>
                    </div>
                </div>
                <div class="form-check form-group offset-md-3" v-show="!expertMode">
                    <input type="checkbox" class="form-check-input" id="metadata-columns-toggle" v-model="showAmbiguous" />
                    <label for="metadata-columns-toggle" class="form-check-label">Show Metadata Columns</label>
                </div>
                <div class="form-check form-group offset-md-3">
                    <input type="checkbox" class="form-check-input" id="expert-mode-toggle" v-model="expertMode" @change="onToggleExpertMode" />
                    <label for="expert-mode-toggle" class="form-check-label">Expert Mode</label>
                </div>
                <button type="submit" class="btn btn-default mt-2">
                    <i class="fas fa-fw fa-filter"></i> Filter
                </button>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label"></label>
                    <div class="col-md-9">
                        <textarea class="form-control" rows="6" v-model="query" :disabled="true" :readonly="true"></textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="h-100" :class="showFilterOptions ? 'col-md-9' : 'col-md-12'">
            <p class="alert alert-info" v-if="!hasResults">
                Query returned no results or you didn't run the query yet.
            </p>
            <div v-else class="h-100 d-flex flex-column">
                <h4>Results <span class="badge badge-primary">{{overallResultCount}}</span></h4>
                <p class="text-secondary">
                    {{page.from}}-{{page.to}} / {{page.total}}
                </p>
                <ul class="pagination" v-show="!expertMode">
                    <li class="page-item" :class="{'disabled': page.current_page == 1}">
                        <a href="#" class="page-link" aria-label="First Page" @click="applyFilter(page.first_page_url)">
                            <i class="fas fa-fw fa-angle-double-left" aria-hidden="true"></i>
                            <i class="sr-only">First Page</i>
                        </a>
                    </li>
                    <li class="page-item" :class="{'disabled': page.current_page == 1}">
                        <a href="#" class="page-link" @click="applyFilter(page.prev_page_url)">
                            <i class="fas fa-fw fa-arrow-left"></i> Previous {{previousResultCount}} results
                        </a>
                    </li>
                    <li class="page-item" :class="{'disabled': page.current_page == page.last_page}">
                        <a href="#" class="page-link" @click="applyFilter(page.next_page_url)">
                            Next {{nextResultCount}} results <i class="fas fa-fw fa-arrow-right"></i>
                        </a>
                    </li>
                        <li class="page-item" :class="{'disabled': page.current_page == page.last_page}">
                            <a href="#" class="page-link" aria-label="Last Page" @click="applyFilter(page.last_page_url)">
                                <i class="fas fa-fw fa-angle-double-right" aria-hidden="true"></i>
                                <i class="sr-only">Last Page</i>
                            </a>
                        </li>
                </ul>
                <ul class="nav nav-tabs">
                    <li class="nav-item" v-show="expertMode">
                        <a class="nav-link" href="#">
                            <i class="fas fa-fw fa-list-ul"></i> Raw
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-fw fa-puzzle-piece"></i> Query Builder
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-fw fa-chart-line"></i> Visualization
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-fw fa-download"></i> Export
                        </a>
                    </li>
                </ul>
                <component :is="origin.name"></component>
            </div>
        </div>
    </div>
</template>

<script>
    Vue.component('attribute_values', require('./DataAnalysisAttributes.vue'));
    Vue.component('contexts', require('./DataAnalysisEntity.vue'));
    Vue.component('files', require('./DataAnalysisFiles.vue'));
    Vue.component('geodata', require('./DataAnalysisGeodata.vue'));
    Vue.component('bibliography', require('./DataAnalysisBibliography.vue'));

    export default {
        props: {
            concepts: {
                required: false,
                type: Object
            }
        },
        mounted() {
            $('[data-toggle="popover"]').popover();
            this.origin = this.origins[1]; // Use entity table as default
        },
        methods: {
            onToggleExpertMode() {
                // switch to simple tab if we no longer in expert mode
                if(!this.expertMode && this.activeResultTab == 'raw') {
                    this.activeResultTab = 'simple';
                }
            },
            toggleShowFilterOptions() {
                this.showFilterOptions = !this.showFilterOptions;
            },
            updateOrigin(value) {
                if(this.instantFilter) {
                    this.applyFilter();
                }
            },
            applyFilter(url) {
                url = url || '/api/analysis/filter?page=1';
                const vm = this;
                // check if current origin is different from last one
                // and reset filters and columns
                if(vm.filteredOrigin != vm.origin) {
                    vm.filters = [];
                    vm.columns = [];
                }
                let data = {
                    filters: vm.filters,
                    origin: vm.origin.name,
                    columns: vm.columns,
                    orders: vm.orders,
                    limit: vm.limit,
                    simple: !vm.expertMode
                };
                if(vm.expertMode) {
                    data.distinct = vm.distinct;
                } else {
                    data.splits = vm.splits;
                }
                vm.$http.post(url, data).then(function(response) {
                    const data = response.data.page.data.slice();
                    delete response.data.page.data;
                    vm.page = response.data.page;
                    vm.page.first_page_url = '/api' + vm.page.first_page_url;
                    vm.page.last_page_url = '/api' + vm.page.last_page_url;
                    if(vm.page.prev_page_url) {
                        vm.page.prev_page_url = '/api' + vm.page.prev_page_url;
                    }
                    if(vm.page.next_page_url) {
                        vm.page.next_page_url = '/api' + vm.page.next_page_url;
                    }
                    vm.page.path = '/api' + vm.page.path;
                    vm.results = [];
                    vm.combinedResults = [];
                    vm.filteredOrigin = vm.origin;
                    vm.query = data.query;
                    vm.splitResults = Object.assign({});
                    vm.splitType = Object.assign({});
                    // Reset splitResults
                    // Reset splitType
                    if(data.length) {
                        vm.availableColumns = [];
                        if(vm.expertMode) {
                            let row = data[0];
                            // add all returned column names to selection array
                            for(let k in row) {
                                let o = vm.getOriginalColumnName(k);
                                let ac = {
                                    col: o
                                };
                                // if names are different, AS property is set
                                if(k != o) ac.as = k;
                                vm.availableColumns.push(ac);
                            }
                            for(let i=0; i<data.length; i++) {
                                vm.results.push(data[i]);
                            }
                        } else {
                            for(let i=0; i<data.length; i++) {
                                vm.combinedResults.push(data[i]);
                            }
                            for(let k in vm.page.splits) {
                                vm.splitResults[k] = vm.page.splits[k].values;
                                vm.splitType[k] = vm.page.splits[k].type;
                            }
                        }
                        vm.activeResultTab = vm.expertMode ? 'raw' : 'simple';
                    }
                    vm.visualizationColumns = [];
                    vm.visualizationResults = [];
                    vm.visualizationColumns = vm.getAvailableColumns();
                    vm.visualizationResults = vm.getResults();
                });
            },
            addSplit(relation, column, value, name) {
                // check if split on given parameters already exist
                const matchingSplit = this.splits.find(function(s) {
                    return s.relation == relation && s.column == column && s.value == value;
                });
                // don't add it, if it exists
                if(matchingSplit) return;
                this.splits.push({
                    relation: relation,
                    column: column,
                    value: value,
                    name: name
                });
                if(this.instantFilter) {
                    this.applyFilter();
                }
            },
            datatypeSupportsSplit(datatype) {
                switch(datatype) {
                    case 'string':
                    case 'stringf':
                    case 'double':
                    case 'date':
                    case 'integer':
                    case 'boolean':
                    case 'percentage':
                    case 'geography':
                    case 'sql':
                        return true;
                    default:
                        return false;
                }
            },
            getAvailableColumns() {
                const vm = this;
                if(vm.expertMode) {
                    return vm.availableColumns;
                } else {
                    let columns = [];
                    // add predefined columns
                    if(vm.combinedResults.length) {
                        let header = vm.combinedResults[0];
                        for(let k in header) {
                            columns.push(k);
                        }
                    }

                    // add splits
                    for(let k in vm.splitResults) {
                        let label = k;
                        if(vm.concepts[k]) {
                            label = vm.concepts[k].label;
                        }
                        columns.push(label);
                    }
                    return columns;
                }
            },
            getResults() {
                const vm = this;
                if(vm.expertMode) {
                    return vm.results;
                } else {
                    let results = vm.combinedResults.slice();
                    for(let i=0; i<results.length; i++) {
                        let r = results[i];
                        for(let k in vm.splitResults) {
                            r[k] = vm.splitResults[k][i];
                        }
                    }
                    return results;
                }
            },
            getConceptLabel(url)  {
                if(!url) return url;
                const concept = this.concepts[url];
                if(!concept) return url;
                return concept.label;
            }
        },
        data() {
            return {
                results: [],
                combinedResults: [],
                splitResults: {},
                splitType: {},
                query: '',
                origins: [
                    {
                        label: 'analysis.query-options.table.attribute-values',
                        name: 'attribute_values'
                    },
                    {
                        label: 'analysis.query-options.table.contexts',
                        name: 'contexts'
                    },
                    {
                        label: 'analysis.query-options.table.files',
                        name: 'files'
                    },
                    {
                        label: 'analysis.query-options.table.geodata',
                        name: 'geodata'
                    },
                    {
                        label: 'analysis.query-options.table.bibliography',
                        name: 'bibliography'
                    }
                ],
                comps: [
                    '=',
                    '!=',
                    '>',
                    '>=',
                    '<',
                    '<=',
                    'IS NULL',
                    'IS NOT NULL',
                    'LIKE',
                    'NOT LIKE',
                    'ILIKE',
                    'NOT ILIKE',
                    'BETWEEN',
                    'NOT BETWEEN',
                    'IN',
                    'NOT IN'
                ],
                simpleComps: {
                    beginsWith: {
                        label: 'analysis.comparisons.label.begins-with',
                        id: 'begins_with',
                        comp: 'ILIKE%',
                        needs_value: true
                    },
                    endsWith: {
                        label: 'analysis.comparisons.label.ends-with',
                        id: 'ends_with',
                        comp: '%ILIKE',
                        needs_value: true
                    },
                    doesntBeginWith: {
                        label: 'analysis.comparisons.label.not-begins-with',
                        id: 'doesnt_begin_with',
                        comp: 'NOT ILIKE%',
                        needs_value: true
                    },
                    doesntEndWith: {
                        label: 'analysis.comparisons.label.not-ends-with',
                        id: 'doesnt_end_with',
                        comp: '%NOT ILIKE',
                        needs_value: true
                    },
                    contains: {
                        label: 'analysis.comparisons.label.contains',
                        id: 'containts',
                        comp: '%ILIKE%',
                        needs_value: true
                    },
                    doesntContain: {
                        label: 'analysis.comparisons.label.not-contains',
                        id: 'doesnt_contain',
                        comp: '%NOT ILIKE%',
                        needs_value: true
                    },
                    is: {
                        label: 'analysis.comparisons.label.exists',
                        id: 'is',
                        comp: 'IS NOT NULL'
                    },
                    isNull: {
                        label: 'analysis.comparisons.label.not-exists',
                        id: 'is_not',
                        comp: 'IS NULL'
                    },
                    lessThan: {
                        label: 'analysis.comparisons.label.less-than',
                        id: 'less_than',
                        comp: '<',
                        needs_value: true
                    },
                    lessOrEqual: {
                        label: 'analysis.comparisons.label.less-than-or-equal',
                        id: 'less_than_equal',
                        comp: '<=',
                        needs_value: true
                    },
                    greaterThan: {
                        label: 'analysis.comparisons.label.greater-than',
                        id: 'greater_than',
                        comp: '>',
                        needs_value: true
                    },
                    greaterOrEqual: {
                        label: 'analysis.comparisons.label.greater-than-or-equal',
                        id: 'greater_than_equal',
                        comp: '>=',
                        needs_value: true
                    },
                    equals: {
                        label: 'analysis.comparisons.label.equals',
                        id: 'equals',
                        comp: '=',
                        needs_value: true
                    },
                    notEquals: {
                        label: 'analysis.comparisons.label.not-equals',
                        id: 'doesnt_equal',
                        comp: '!=',
                        needs_value: true
                    },
                    between: {
                        label: 'analysis.comparisons.label.between',
                        id: 'between',
                        comp: 'BETWEEN',
                        needs_value: true
                    },
                    notBetween: {
                        label: 'analysis.comparisons.label.not-between',
                        id: 'not_between',
                        comp: 'NOT BETWEEN',
                        needs_value: true
                    },
                    in: {
                        label: 'analysis.comparisons.label.in',
                        id: 'in',
                        comp: 'IN',
                        needs_value: true
                    },
                    notIn: {
                        label: 'analysis.comparisons.label.not-in',
                        id: 'not_in',
                        comp: 'NOT IN',
                        needs_value: true
                    },
                    comesBefore: {
                        label: 'analysis.comparisons.label.comes-before',
                        id: 'comes_before',
                        comp: '<',
                        needs_value: true
                    },
                    comesAfter: {
                        label: 'analysis.comparisons.label.comes-after',
                        id: 'comes_after',
                        comp: '>',
                        needs_value: true
                    },
                },
                functions: [
                    'pg_distance',
                    'pg_area',
                    'count',
                    'min',
                    'max',
                    'avg',
                    'sum'
                ],
                simpleFunctions: {
                    geoDistance: {
                        label: 'distance to (in m)',
                        comp: 'pg_distance',
                        needs_value: true
                    },
                    geoArea: {
                        label: 'area (in qm)',
                        comp: 'pg_area'
                    }
                },
                availableVisualizations: [
                    {
                        label: 'analysis.results.tabs.visualization.type.scatter',
                        type: 'scatter'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.line',
                        type: 'line'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.bar',
                        type: 'bar'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.pie',
                        type: 'pie'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.histogram',
                        type: 'histogram'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.histogram2d',
                        type: 'histogram2d'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.ternary',
                        type: 'ternary'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.ternarycontour',
                        type: 'ternarycontour'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.heatmap',
                        type: 'heatmap'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.windrose',
                        type: 'windrose'
                    },
                    {
                        label: 'analysis.results.tabs.visualization.type.radar',
                        type: 'radar'
                    }
                ],
                expertMode: false,
                showFilterOptions: true,
                showAmbiguous: true,
                instantFilter: true,
                distinct: true,
                page: {},
                column: {},
                relation: {},
                visualizationColumns: [],
                availableColumns: [],
                filters: [],
                origin: {},
                filteredOrigin: {},
                columns: [],
                contextColumns: [],
                attributeColumns: [],
                actionColumns: [],
                datatypeColumns: [],
                orders: [],
                groups: [],
                splits: [],
                limit: {
                    from: 0,
                    amount: 30
                },
                plotLayout: {
                    width: 500,
                    height: 500,
                    paper_bgcolor: 'rgba(0,0,0,0)',
                    plot_bgcolor: 'rgba(0,0,0,0)'
                }
            }
        },
        computed: {
            overallResultCount: function() {
                return (this.page.to - this.page.from) + 1;
            },
            previousResultCount: function() {
                if(this.page.current_page == 1) return 0;
                return this.page.per_page;
            },
            nextResultCount: function() {
                const maxTo = this.page.to + this.page.per_page;
                const max = Math.min(maxTo, this.page.total);
                const min = this.page.to + 1;
                return (max - min) + 1;
            },
            hasResults: function() {
                return (this.expertMode && this.results.length > 0) || (!this.expertMode && this.combinedResults.length > 0);
            }
        }
    }
</script>
