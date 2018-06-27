<template>
    <div class="h-100 row of-hidden">
        <div :class="showFilterOptions ? 'col-md-3 h-100 scroll-y-auto' : 'd-none'">
            <h4>
                <button type="button" class="btn btn-outline-secondary" v-show="showFilterOptions" @click="toggleShowFilterOptions()">
                    <i class="fas fa-fw fa-arrow-left"></i>
                </button>
                Query Options
            </h4>
            <button type="submit" class="btn btn-outline-primary mt-2 col-md-12" form="filter-form">
                <i class="fas fa-fw fa-filter"></i> Filter
            </button>
            <form role="form" class="mt-2" id="filter-form" name="filter-form" @submit.prevent="applyFilter()">
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
                <div class="form-check form-group offset-md-3">
                    <input type="checkbox" class="form-check-input" id="expert-mode-toggle" v-model="expertMode" @change="onToggleExpertMode" />
                    <label for="expert-mode-toggle" class="form-check-label">Expert Mode</label>
                </div>
                <div class="form-group row" v-if="expertMode">
                    <label class="col-md-3 col-form-label">
                        Query
                    </label>
                    <div class="col-md-9">
                        <textarea class="form-control" rows="6" v-model="query" :disabled="true" :readonly="true"></textarea>
                    </div>
                </div>
            </form>
            <h5>
                Filters
                <span class="badge badge-primary" v-if="hiddenByFilter">
                    Hides {{ hiddenByFilter }} results
                </span>
            </h5>
            <div v-if="expertMode">
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
            <div v-else>
                <div class="col-md-12">
                    <div class="row">
                        <div
                            class="col-md-4 filter-group rounded border border-success p-2"
                            v-for="(group, i) in filters.active.groups">
                            <draggable
                                class="h-100 scroll-y-auto"
                                v-model="filters.active.groups[i]"
                                :key="i"
                                :options="{group: 'filters'}"
                                @add="filterMoved"
                                @end="drag=false"
                                @start="drag=true">
                                <div v-for="(filter, pos) in filters.active.groups[i]" :key="filter.id" class="filter-item grab-handle bg-info text-dark d-flex flex-row justify-content-between my-1 p-1 rounded">
                                    <span>{{ filter.name }}</span>
                                    <div>
                                        <a href="#" class="text-dark" @click="removeFilter(filters.active.groups[i], pos)">
                                            <i class="fas fa-fw fa-times fa-sm"></i>
                                        </a>
                                        <a href="#" class="text-dark" @click="deleteFilter(filters.active.groups[i], pos)">
                                            <i class="fas fa-fw fa-trash fa-sm"></i>
                                        </a>
                                    </div>
                                </div>
                            </draggable>
                        </div>
                        <div class="col-md-4 filter-group rounded border border-secondary d-flex flex-column justify-content-center align-items-center clickable" :disabled="newGroupDisabled" @click="addNewGroup">
                            <i class="fas fa-fw fa-plus fa-2x"></i>
                            Add new Group
                        </div>
                    </div>
                </div>
                <h6 class="mt-2 mb-1">Inactive</h6>
                <div
                    class="col-md-12 filter-group rounded border border-secondary p-2">
                    <draggable
                        class="h-100 scroll-y-auto"
                        v-model="filters.inactive"
                        :options="{group: 'filters'}"
                        @add="filterMoved"
                        @end="drag=false"
                        @start="drag=true">
                        <div v-for="(filter, pos) in filters.inactive" class="filter-item grab-handle bg-warning text-dark d-flex flex-row justify-content-between my-1 p-1 rounded">
                            <span>{{ filter.name }}</span>
                            <div>
                                <a href="#" class="text-dark" @click="deleteFilter(filters.inactive, pos)">
                                    <i class="fas fa-fw fa-trash fa-sm"></i>
                                </a>
                            </div>
                        </div>
                    </draggable>
                </div>
            </div>
        </div>
        <div class="h-100" :class="showFilterOptions ? 'col-md-9' : 'col-md-12'">
            <h4>
                <button type="button" class="btn btn-outline-secondary" v-show="!showFilterOptions" @click="toggleShowFilterOptions()">
                    <i class="fas fa-fw fa-bars"></i>
                </button>
                Results <span class="badge badge-primary">{{overallResultCount}}</span>
            </h4>
            <p class="alert alert-info" v-if="!hasResults">
                Query returned no results or you didn't run the query yet.
            </p>
            <div v-else class="h-100 d-flex flex-column">
                <p class="text-secondary">
                    {{page.from}}-{{page.to}} / {{page.total}}
                </p>
                <ul class="nav nav-tabs mb-2">
                    <li class="nav-item" v-show="expertMode">
                        <a class="nav-link" href="#" :class="{'active': activeResultTab == 'raw'}" @click="activeResultTab = 'raw'">
                            <i class="fas fa-fw fa-list-ul"></i> Raw
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" :class="{'active': activeResultTab == 'simple'}" @click="activeResultTab = 'simple'">
                            <i class="fas fa-fw fa-puzzle-piece"></i> Query Builder
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" :class="{'active': activeResultTab == 'visualization'}" @click="activeResultTab = 'visualization'">
                            <i class="fas fa-fw fa-chart-line"></i> Visualization
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" :class="{'active': activeResultTab == 'export'}" @click="activeResultTab = 'export'">
                            <i class="fas fa-fw fa-download"></i> Export
                        </a>
                    </li>
                </ul>
                <div v-show="activeResultTab == 'simple' && !expertMode" class="col px-0">
                    <div class="d-flex flex-column h-100">
                        <div class="d-flex flex-row justify-content-between">
                            <ul class="pagination mb-2" v-show="!expertMode">
                                <li class="page-item" :class="{'disabled': page.current_page == 1}">
                                    <a href="#" class="page-link" aria-label="First Page" @click="applyFilter(page.first_page_url)">
                                        <i class="fas fa-fw fa-angle-double-left" aria-hidden="true"></i>
                                        <i class="sr-only">First Page</i>
                                    </a>
                                </li>
                                <li class="page-item" :class="{'disabled': page.current_page == 1}">
                                    <a href="#" class="page-link" aria-label="Previous Page" @click="applyFilter(page.prev_page_url)">
                                        <i class="fas fa-fw fa-arrow-left"></i> Previous {{previousResultCount}} results
                                    </a>
                                </li>
                                <li class="page-item" :class="{'disabled': page.current_page == page.last_page}">
                                    <a href="#" class="page-link" aria-label="Next Page" @click="applyFilter(page.next_page_url)">
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
                            <div class="form-check form-group">
                                <input type="checkbox" class="form-check-input" id="metadata-columns-toggle" v-model="showAmbiguous" />
                                <label for="metadata-columns-toggle" class="form-check-label">Show Metadata Columns</label>
                            </div>
                        </div>
                        <analysis-table
                            :columns="resultColumns"
                            :data="combinedResults"
                            :show-hidden="showAmbiguous"
                            :on-add-filter="addFilter">
                        </analysis-table>
                    </div>
                </div>
                <div v-show="activeResultTab == 'raw' && expertMode">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered table-sm table-cell-250">
                            <thead class="thead-light sticky-top">
                                <tr>
                                    <th v-for="(k, col) in results[0]">
                                        {{ k }}
                                        <a href="">
                                            <i class="fas fa-fw fa-sort-up" @click="addOrder(k, 'desc')"></i>
                                        </a>
                                        <a href="">
                                            <i class="fas fa-fw fa-sort-down" @click="addOrder(k, 'asc')"></i>
                                        </a>
                                        <a href="">
                                            <i class="fas fa-fw fa-search"></i>
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="r in results">
                                    <td v-for="(k, col) in results[0]">
                                        {{ r[k] }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-show="activeResultTab == 'visualization'">
                    Visualization
                </div>
                <div v-show="activeResultTab == 'export'">
                    <p class="alert alert-info">
                        Supported export formats are
                        <ul class="mb-0">
                            <li>CSV</li>
                            <li>XLSX</li>
                            <li>JSON</li>
                            <li>PDF</li>
                        </ul>
                    </p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-default" @click="exportRows('csv', true)">
                            Export
                        </button>
                        <button type="button" class="btn btn-default dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" @click="exportRows('csv', true)">
                                As CSV (All: {{page.total}} entries)
                            </a>
                            <a class="dropdown-item" href="#" @click="exportRows('xlsx', true)">
                                As XLSX (All: {{page.total}} entries)
                            </a>
                            <a class="dropdown-item" href="#" @click="exportRows('json', true)">
                                As JSON (All: {{page.total}} entries)
                            </a>
                            <a class="dropdown-item" href="#" @click="exportRows('pdf', true)">
                                As PDF (All: {{page.total}} entries)
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#" @click="exportRows('csv', false)">
                                As CSV (Current Selection: {{page.from}}-{{page.to}})
                            </a>
                            <a class="dropdown-item" href="#" @click="exportRows('xlsx', false)">
                                As XLSX (Current Selection: {{page.from}}-{{page.to}})
                            </a>
                            <a class="dropdown-item" href="#" @click="exportRows('json', false)">
                                As JSON (Current Selection: {{page.from}}-{{page.to}})
                            </a>
                            <a class="dropdown-item" href="#" @click="exportRows('pdf', false)">
                                As PDF (Current Selection: {{page.from}}-{{page.to}})
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';

    Vue.component('analysis-table', require('./DataAnalysisTable.vue'));

    export default {
        components: {
            draggable
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
                this.applyOnlyInstantFilter();
            },
            addNewGroup() {
                const vm = this;
                if(vm.newGroupDisabled) return;
                vm.filters.active.groups.push([]);
            },
            filterMoved() {
                this.applyOnlyInstantFilter();
            },
            addFilter(filterObj) {
                let newFilter = {
                    id: this.getNextFilterId(),
                };
                let col;
                if(filterObj.relation) {
                    col = filterObj.relation.name;
                } else {
                    col = filterObj.column;
                }
                newFilter.name = `${col} ${filterObj.comp} ${filterObj.comp_value}`;
                newFilter = { ...newFilter, ...filterObj };
                this.filters.active.groups[0].push(newFilter);
                this.applyOnlyInstantFilter();
            },
            removeFilter(filterGroup, position) {
                const filters = filterGroup.splice(position, 1);
                if(filters.length) {
                    this.filters.inactive.push(filters[0]);
                }
                this.applyOnlyInstantFilter();
            },
            deleteFilter(filterGroup, position) {
                filterGroup.splice(position, 1);
                // Only apply filters, if filter was removed from
                // an active group
                if(filterGroup != this.filters.inactive) {
                    this.applyOnlyInstantFilter();
                }
            },
            getNextFilterId() {
                return this.filterId++;
            },
            applyOnlyInstantFilter(url) {
                if(this.instantFilter) {
                    this.applyFilter(url);
                }
            },
            applyFilter(url) {
                url = url || '/api/analysis/filter?page=1';
                const vm = this;
                // check if current origin is different from last one
                // and reset filters and columns
                if(vm.filteredOrigin != vm.origin) {
                    vm.filters.active.groups = [
                        []
                    ];
                    vm.filters.inactive = [];
                    vm.columns = [];
                }
                const data = vm.setupFormData();
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
                    vm.hiddenByFilter = response.data.hidden;
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
                            const splits = response.data.splits;
                            for(let k in splits) {
                                vm.splitResults[k] = splits[k].values;
                                vm.splitType[k] = splits[k].type;
                            }
                        }
                        vm.activeResultTab = vm.expertMode ? 'raw' : 'simple';
                    }
                    vm.visualizationColumns = [];
                    vm.visualizationResults = [];
                    vm.visualizationColumns = vm.getAvailableColumns();
                    vm.visualizationResults = vm.getResults();
                }).catch(function(error) {
                    vm.$throwError(error);
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
                this.applyOnlyInstantFilter();
            },
            removeSpilt(index) {
                this.splits.splice(index, 1);
                this.applyFilter();
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
                        if(vm.$hasConcept(k)) {
                            label = vm.$translateConcept(k);
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
            exportRows(type, all) {
                const vm = this;
                type = type || 'csv';
                // exporting all rows is default
                if(all !== false) {
                    all = true;
                }
                let data = vm.setupFormData();
                if(all) {
                    data.limit.from = 1;
                    data.limit.amount = vm.page.total;
                    data.page = 1;
                } else {
                    data.page = vm.page.current_page;
                }
                vm.$http.post(`/api/analysis/export/${type}`, data).then(function(response) {
                    const filename = `${vm.origin.label}.${type}`;
                    vm.$createDownloadLink(response.data, filename, true, response.headers['content-type']);
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            setupFormData() {
                const vm = this;
                let data = {
                    filters: vm.filters.active.groups,
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
                return data;
            },
            getConceptLabel(url)  {
                return this.$translateConcept(url);
            }
        },
        data() {
            return {
                activeResultTab: '',
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
                hiddenByFilter: 0,
                filterId: 1,
                filters: {
                    active: {
                        groups: [
                            []
                        ]
                    },
                    inactive: []
                },
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
            resultColumns: function() {
                if(!this.filteredOrigin) return [];
                switch(this.filteredOrigin.name) {
                    case 'attribute_values':
                        return [
                            {
                                label: 'Name',
                                key: 'attribute',
                                hidden: false,
                                type: 'thesaurus'
                            },
                            {
                                label: 'Corresponding Entity',
                                key: 'context',
                                hidden: false,
                                type: 'entity'
                            },
                            {
                                label: 'Value',
                                key: 'value',
                                hidden: false
                            },
                            {
                                label: 'Certainty',
                                key: 'possibility',
                                hidden: true,
                                type: 'percentage'
                            },
                            {
                                label: 'Created At',
                                key: 'created_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Updated At',
                                key: 'updated_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Last Editor',
                                key: 'lasteditor',
                                hidden: true
                            }
                        ];
                    case 'contexts':
                        return [
                            {
                                label: 'Name',
                                key: 'name',
                                hidden: false
                            },
                            {
                                label: 'Entity-Type',
                                key: 'context_type',
                                hidden: false,
                                type: 'entity_type',
                                isRelation: true
                            },
                            {
                                label: 'Attributes',
                                key: 'attributes',
                                hidden: false,
                                type: 'list.thesaurus',
                                isRelation: true
                            },
                            // TODO split results
                            {
                                label: 'Geodata',
                                key: 'geodata',
                                hidden: true,
                                type: 'geometry',
                                isRelation: true
                            },
                            {
                                label: 'Literature',
                                key: 'bibliography',
                                hidden: true,
                                type: 'list.bibliography',
                                isRelation: true
                            },
                            {
                                label: 'Files',
                                key: 'files',
                                hidden: true,
                                type: 'list.entity',
                                isRelation: true
                            },
                            {
                                label: 'Child Entities',
                                key: 'child_contexts',
                                hidden: true,
                                type: 'list.entity',
                                isRelation: true
                            },
                            {
                                label: 'Parent Entity',
                                key: 'root_context',
                                hidden: true,
                                type: 'entity',
                                isRelation: true
                            },
                            {
                                label: 'Created At',
                                key: 'created_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Updated At',
                                key: 'updated_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Last Editor',
                                key: 'lasteditor',
                                hidden: true
                            }
                        ];
                    case 'files':
                        return [
                            {
                                label: 'Name',
                                key: 'name',
                                hidden: false
                            },
                            {
                                label: 'Modified',
                                key: 'modified',
                                hidden: false,
                                type: 'date'
                            },
                            {
                                label: 'Created',
                                key: 'created',
                                hidden: false,
                                type: 'date'
                            },
                            {
                                label: 'Description',
                                key: 'description',
                                hidden: false
                            },
                            {
                                label: 'Copyright',
                                key: 'copyright',
                                hidden: false
                            },
                            {
                                label: 'Created At',
                                key: 'created_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Updated At',
                                key: 'updated_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Last Editor',
                                key: 'lasteditor',
                                hidden: true
                            }
                        ];
                    case 'geodata':
                        return [
                            {
                                label: 'Geometry',
                                key: 'geom',
                                hidden: false,
                                type: 'geometry'
                            },
                            {
                                label: 'Entity',
                                key: 'context',
                                hidden: true,
                                type: 'entity'
                            },
                            {
                                label: 'Color',
                                key: 'color',
                                hidden: true,
                                type: 'color'
                            },
                            {
                                label: 'Created At',
                                key: 'created_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Updated At',
                                key: 'updated_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Last Editor',
                                key: 'lasteditor',
                                hidden: true
                            }
                        ];
                    case 'bibliography':
                        return [
                            {
                                label: 'Authors',
                                key: 'author',
                                hidden: false
                            },
                            {
                                label: 'Title',
                                key: 'title',
                                hidden: false
                            },
                            {
                                label: 'Type',
                                key: 'type',
                                hidden: false
                            },
                            {
                                label: 'Year',
                                key: 'year',
                                hidden: false
                            },
                            {
                                label: 'Volume',
                                key: 'volume',
                                hidden: false
                            },
                            {
                                label: 'Series',
                                key: 'series',
                                hidden: false
                            },
                            {
                                label: 'School',
                                key: 'school',
                                hidden: false
                            },
                            {
                                label: 'Publisher',
                                key: 'publisher',
                                hidden: false
                            },
                            {
                                label: 'Pages',
                                key: 'pages',
                                hidden: false
                            },
                            {
                                label: 'Organization',
                                key: 'organization',
                                hidden: false
                            },
                            {
                                label: 'Issue',
                                key: 'issue',
                                hidden: false
                            },
                            {
                                label: 'Note',
                                key: 'note',
                                hidden: false
                            },
                            {
                                label: 'Month',
                                key: 'month',
                                hidden: false
                            },
                            {
                                label: 'Journal',
                                key: 'journal',
                                hidden: false
                            },
                            {
                                label: 'Institution',
                                key: 'institution',
                                hidden: false
                            },
                            {
                                label: 'Chapter',
                                key: 'chapter',
                                hidden: false
                            },
                            {
                                label: 'Created At',
                                key: 'created_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Updated At',
                                key: 'updated_at',
                                hidden: true,
                                type: 'date'
                            },
                            {
                                label: 'Last Editor',
                                key: 'lasteditor',
                                hidden: true
                            }
                        ];
                    default:
                        return [];
                }
            },
            newGroupDisabled: function() {
                const lastGrpIdx = this.filters.active.groups.length - 1;
                // Disable new groups, if last group is empty
                return !this.filters.active.groups[lastGrpIdx].length;
            },
            overallResultCount: function() {
                if(!this.page.to || !this.page.from) return 0;
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
