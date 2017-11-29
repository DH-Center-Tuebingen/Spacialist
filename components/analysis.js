spacialistApp.component('analysis', {
    bindings: {
        concepts: '<',
        attributes: '<',
        attributetypes: '<',
        contextTypes: '<',
        tags: '<'
    },
    templateUrl: 'templates/analysis.html',
    controller: function(httpPostFactory) {
        var vm = this;

        vm.defaultVisLayout = {
            width: 500,
            height: 500,
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor: 'rgba(0,0,0,0)'
        };

        vm.resultCount = 0;
        vm.results = [];
        vm.visualizationResults = [];
        vm.combinedResults = [];
        vm.splitResults = {};
        vm.query = '';
        vm.vis = {
            type: '',
            pie: {
                labels: [],
                values: [],
                selectLabel: function() {
                    vm.vis.pie.labels.length = 0;
                    var c = vm.vis.labelCol.as || vm.vis.labelCol.col || vm.vis.labelCol;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.pie.labels.push(r[c]);
                    }
                },
                selectValues: function() {
                    vm.vis.pie.values.length = 0;
                    var c = vm.vis.valueCol.as || vm.vis.valueCol.col || vm.vis.valueCol;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.pie.values.push(r[c]);
                    }
                },
                validate: function() {
                    var t = vm.vis.pie;
                    return t.values.length > 0 && t.labels.length > 0;
                },
                createData: function() {
                    return [{
                        values: vm.vis.pie.values,
                        labels: vm.vis.pie.labels,
                        type: 'pie'
                    }];
                },
                createLayout: function() {
                    return vm.defaultVisLayout;
                }
            },
            bar: {
                x: [],
                y: [],
                selectX: function() {
                    vm.vis.bar.x.length = 0;
                    var c = vm.vis.x.as || vm.vis.x.col || vm.vis.x;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.bar.x.push(r[c]);
                    }
                },
                selectY: function() {
                    vm.vis.bar.y.length = 0;
                    var c = vm.vis.y.as || vm.vis.y.col || vm.vis.y;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.bar.y.push(r[c]);
                    }
                },
                validate: function() {
                    var t = vm.vis.bar;
                    return t.x.length > 0 && t.y.length > 0;
                },
                createData: function() {
                    return [{
                        x: vm.vis.bar.x,
                        y: vm.vis.bar.y,
                        type: 'bar'
                    }];
                },
                createLayout: function() {
                    return vm.defaultVisLayout;
                }
            },
            line: {
                x: [],
                y: [],
                name: '',
                selectX: function() {
                    vm.vis.line.x.length = 0;
                    var c = vm.vis.x.as || vm.vis.x.col || vm.vis.x;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.line.x.push(r[c]);
                    }
                },
                selectY: function() {
                    vm.vis.line.y.length = 0;
                    var c = vm.vis.y.as || vm.vis.y.col || vm.vis.y;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.line.y.push(r[c]);
                    }
                },
                validate: function() {
                    var t = vm.vis.line;
                    return t.x.length > 0 && t.y.length > 0 && t.name.length > 0;
                },
                createData: function() {
                    return [{
                        x: vm.vis.line.x,
                        y: vm.vis.line.y,
                        mode: 'lines',
                        type: 'scatter',
                        name: vm.vis.line.name
                    }];
                },
                createLayout: function() {
                    return vm.defaultVisLayout;
                }
            },
            scatter: {
                x: [],
                y: [],
                name: '',
                marker: {
                    size: 24
                },
                selectX: function() {
                    vm.vis.scatter.x.length = 0;
                    var c = vm.vis.x.as || vm.vis.x.col || vm.vis.x;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.scatter.x.push(r[c]);
                    }
                },
                selectY: function() {
                    vm.vis.scatter.y.length = 0;
                    var c = vm.vis.y.as || vm.vis.y.col || vm.vis.y;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.scatter.y.push(r[c]);
                    }
                },
                validate: function() {
                    var t = vm.vis.scatter;
                    return t.x.length > 0 && t.y.length > 0 && t.name.length > 0 && t.marker.size > 0;
                },
                createData: function() {
                    return [{
                        x: vm.vis.scatter.x,
                        y: vm.vis.scatter.y,
                        mode: 'markers',
                        type: 'scatter',
                        name: vm.vis.scatter.name,
                        marker: vm.vis.scatter.marker
                    }];
                },
                createLayout: function() {
                    return vm.defaultVisLayout;
                }
            },
            histogram: {
                x: [],
                types: [
                    'cumulative',
                    'normalized'
                ],
                horizontal: false,
                selectX: function() {
                    vm.vis.histogram.x.length = 0;
                    var c = vm.vis.x.as || vm.vis.x.col || vm.vis.x;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.histogram.x.push(r[c]);
                    }
                },
                validate: function() {
                    var t = vm.vis.histogram;
                    return t.x.length > 0 && (t.auto_bins || (!t.auto_bins && typeof t.start != 'undefined' && typeof t.end != 'undefined' && typeof t.size != 'undefined'));
                },
                createData: function() {
                    var t = vm.vis.histogram;
                    var trace = {
                        type: 'histogram'
                    };
                    if(t.horizontal) {
                        trace.y = t.x;
                    } else {
                        trace.x = t.x;
                    }
                    switch(vm.vis.subtype) {
                        case 'cumulative':
                            trace.cumulative = {
                                enabled: true
                            };
                            break;
                        case 'normalized':
                            trace.histnorm = 'probability';
                            break;
                    }
                    if(!t.auto_bins) {
                        var xbins = {
                            end: t.end,
                            size: t.size,
                            start: t.start
                        };
                        trace.xbins = xbins;
                    }
                    return [trace];
                },
                createLayout: function() {
                    return vm.defaultVisLayout;
                }
            },
            ternary: {
                x: [],
                y: [],
                z: [],
                text: '',
                titles: {},
                marker: {
                    size: 6
                },
                selectX: function() {
                    vm.vis.ternary.x.length = 0;
                    var c = vm.vis.x.as || vm.vis.x.col || vm.vis.x;
                    vm.vis.ternary.titles.x = c;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.ternary.x.push(r[c]);
                    }
                },
                selectY: function() {
                    vm.vis.ternary.y.length = 0;
                    var c = vm.vis.y.as || vm.vis.y.col || vm.vis.y;
                    vm.vis.ternary.titles.y = c;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.ternary.y.push(r[c]);
                    }
                },
                selectZ: function() {
                    vm.vis.ternary.z.length = 0;
                    var c = vm.vis.z.as || vm.vis.z.col || vm.vis.z;
                    vm.vis.ternary.titles.z = c;
                    for(var i=0; i<vm.visualizationResults.length; i++) {
                        var r = vm.visualizationResults[i];
                        vm.vis.ternary.z.push(r[c]);
                    }
                },
                validate: function() {
                    var t = vm.vis.ternary;
                    return t.x.length > 0 && t.y.length > 0 && t.z.length > 0;
                },
                createData: function() {
                    return [{
                        a: vm.vis.ternary.x,
                        b: vm.vis.ternary.y,
                        c: vm.vis.ternary.z,
                        type: 'scatterternary',
                        mode: 'markers',
                        marker: vm.vis.ternary.marker
                    }];
                },
                createLayout: function() {
                    var t = vm.vis.ternary;
                    var layout = vm.defaultVisLayout;
                    layout.ternary = {
                        sum: 100,
                        aaxis: {
                            title: t.titles.x,
                            showline: true,
                            showgrid: true
                        },
                        baxis: {
                            title: t.titles.y,
                            showline: true,
                            showgrid: true
                        },
                        caxis: {
                            title: t.titles.z,
                            showline: true,
                            showgrid: true
                        }
                    };
                    layout.annotations = [{
                        showarrow: false,
                        text: t.text,
                        x: 0.5,
                        y: 1.2,
                        font: {
                            size: 16
                        }
                    }];
                    return layout;
                }
            }
        };

        vm.origins = [
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
                label: 'analysis.query-options.table.literature',
                name: 'literature'
            }
        ];

        vm.comps = [
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
        ];

        vm.simpleComps = {
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
        };

        vm.functions = [
            'pg_distance',
            'pg_area',
            'count',
            'min',
            'max',
            'avg',
            'sum'
        ];

        vm.simpleFunctions = {
            geoDistance: {
                label: 'distance to (in m)',
                comp: 'pg_distance',
                needs_value: true
            },
            geoArea: {
                label: 'area (in qm)',
                comp: 'pg_area'
            }
        };

        vm.availableVisualizations = [
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
        ]

        vm.expertMode = false;
        vm.showFilterOptions = true;
        vm.showAmbiguous = true;
        vm.instantFilter = !vm.expertMode;
        vm.column = {};
        vm.relation = {};

        vm.visualizationColumns = [];
        vm.availableColumns = [];
        vm.filters = [];
        vm.origin = vm.origins[1];
        vm.filteredOrigin;
        vm.columns = [];
        vm.contextColumns = [];
        vm.attributeColumns = [];
        vm.actionColumns = [];
        vm.datatypeColumns = [];
        vm.orders = [];
        vm.groups = [];
        vm.splits = [];
        vm.limit = {
            from: 0,
            amount: 30
        };
        vm.distinct = true;

        vm.toggleExpertMode = function() {
            // switch to simple tab if we no longer in expert mode
            if(!vm.expertMode && vm.activeResultTab == 'raw') {
                vm.activeResultTab = 'simple';
            }
        }

        vm.toggleShowFilterOptions = function() {
            vm.showFilterOptions = !vm.showFilterOptions;
        }

        vm.addColumn = function() {
            var c = vm.column;
            vm.columns.push({
                col: c.col.col,
                as: c.as,
                func: c.func,
                func_values: angular.fromJson(c.func_values)
            });
            if(vm.instantFilter) {
                vm.filter();
            }
            resetObject(c);
        };

        vm.removeColumn = function(column) {
            var index = vm.columns.indexOf(column);
            if(index > -1) {
                vm.columns.splice(index, 1);
                if(vm.instantFilter) {
                    vm.filter();
                }
            }
        };

        vm.addOrder = function(col, dir) {
            var order = vm.orders.find(function(order) {
                return order.col == col;
            });
            // Order already part of orders array, return
            if(order) {
                order.dir = dir;
            } else {
                vm.orders.push({
                    col: col,
                    dir: dir
                });
            }
            if(vm.instantFilter) {
                vm.filter();
            }
        };

        vm.removeOrder = function(order) {
            var index = vm.orders.indexOf(order);
            if(index > -1) {
                vm.orders.splice(index, 1);
                if(vm.instantFilter) {
                    vm.filter();
                }
            }
        };

        vm.moveOrderUp = function(order) {
            var index = vm.orders.indexOf(order);
            // index has to be > -1 AND > 0
            if(index > 0) {
                swap(vm.orders, index, index-1);
                if(vm.instantFilter) {
                    vm.filter();
                }
            }
        };

        vm.moveOrderDown = function(order) {
            var index = vm.orders.indexOf(order);
            if(index > -1 && index < vm.filters.length - 1) {
                swap(vm.orders, index, index+1);
                if(vm.instantFilter) {
                    vm.filter();
                }
            }
        };

        vm.addGroup = function() {
            vm.groups.push(vm.group);
            if(vm.instantFilter) {
                vm.filter();
            }
        };

        vm.removeGroup = function(group) {
            var index = vm.groups.indexOf(group);
            if(index > -1) {
                vm.groups.splice(index, 1);
                if(vm.instantFilter) {
                    vm.filter();
                }
            }
        };

        vm.datatypeSupportsSplit = function(datatype) {
            switch(datatype) {
                case 'string':
                case 'stringf':
                case 'double':
                case 'date':
                case 'integer':
                case 'boolean':
                case 'percentage':
                    return true;
                default:
                    return false;
            }
        };

        vm.addSplit = function(relation, column, value, name) {
            // check if split on given parameters already exist
            var matchingSplit = vm.splits.find(function(s) {
                return s.relation == relation && s.column == column && s.value == value;
            });
            // don't add it, if it exists
            if(matchingSplit) return;
            vm.splits.push({
                relation: relation,
                column: column,
                value: value,
                name: name
            });
            if(vm.instantFilter) {
                vm.filter();
            }
        };

        vm.removeSplit = function(index) {
            vm.splits.splice(index, 1);
            vm.filter();
        };

        vm.adjustFilterValues = function(relation, col, comp, comp_value) {
            var needsAdjustment = false;
            if(relation) {
                switch(relation.name) {
                    case 'literatures':
                    case 'files':
                    case 'child_contexts':
                        needsAdjustment = col == 'entry count';
                        break;
                    case 'geodata':
                    case 'context':
                    case 'root_context':
                        needsAdjustment = comp == 'IS NULL' || comp == 'IS NOT NULL';
                        break;
                }
                if(needsAdjustment) {
                    relation.comp = comp;
                    relation.value = comp_value;
                }
            }
            return needsAdjustment;
        }

        vm.addFilter = function(col, comp, comp_value, func, func_values, and, relation) {
            if(typeof comp == 'object') {
                // if comparison is * between, convert object to array
                // because they are stored as object on JS side
                switch(comp.id) {
                    case 'between':
                    case 'not_between':
                        var newValues = [];
                        for(var k in comp_value) {
                            if(comp_value.hasOwnProperty(k)) {
                                newValues.push(comp_value[k]);
                            }
                        }
                        comp_value = newValues;
                        break;
                }
                comp = comp.comp;
            }
            var adjusted = vm.adjustFilterValues(relation, col, comp, comp_value);
            if(!adjusted) {
                col = vm.getOriginalColumnName(col);
            }
            // check if endsWith or beginsWith comp is used
            if(comp == '%ILIKE' || comp == '%NOT ILIKE') {
                // cut off % and add it to the value
                comp = comp.substring(1);
                comp_value = '%' + comp_value;
            } else if(comp == 'ILIKE%' || comp == 'NOT ILIKE%') {
                // cut off % and add it to the value
                comp = comp.substring(0, comp.length - 1);
                comp_value = comp_value + '%';
            } else if(comp == '%ILIKE%' || comp == '%NOT ILIKE%') {
                comp = comp.substring(1, comp.length - 1);
                comp_value = '%' + comp_value + '%';
            }
            var filter = {
                col: col,
                comp: comp,
                comp_value: comp_value,
                and: and
            };
            if(func) {
                filter.func = func;
                filter.func_values = angular.fromJson(func_values);
            }
            if(relation) {
                filter.relation = angular.copy(relation);
            }
            vm.filters.push(filter);
            if(vm.instantFilter) {
                vm.filter();
            }
        };

        vm.editFilter = function(filter) {

        };

        vm.removeFilter = function(filter) {
            var index = vm.filters.indexOf(filter);
            if(index > -1) {
                vm.filters.splice(index, 1);
                if(vm.instantFilter) {
                    vm.filter();
                }
            }
        };

        vm.moveFilterUp = function(filter) {
            var index = vm.filters.indexOf(filter);
            // index has to be > -1 AND > 0
            if(index > 0) {
                swap(vm.filters, index, index-1);
                if(vm.instantFilter) {
                    vm.filter();
                }
            }
        };

        vm.moveFilterDown = function(filter) {
            var index = vm.filters.indexOf(filter);
            if(index > -1 && index < vm.filters.length - 1) {
                swap(vm.filters, index, index+1);
                if(vm.instantFilter) {
                    vm.filter();
                }
            }
        };

        vm.filter = function() {
            // check if current origin is different from last one
            // and reset filters and columns
            if(vm.filteredOrigin != vm.origin) {
                vm.filters.length = 0;
                vm.columns.length = 0;
            }
            var formData = new FormData();
            formData.append('filters', angular.toJson(vm.filters));
            formData.append('origin', vm.origin.name);
            formData.append('columns', angular.toJson(vm.columns));
            formData.append('orders', angular.toJson(vm.orders));
            formData.append('limit', angular.toJson(vm.limit));
            formData.append('simple', !vm.expertMode);
            if(vm.expertMode) {
                formData.append('distinct', vm.distinct);
            } else {
                formData.append('splits', angular.toJson(vm.splits));
            }
            httpPostFactory('api/analysis/filter', formData, function(response) {
                console.log(response);
                vm.filteredOrigin = vm.origin;
                vm.resultCount = response.count;
                vm.query = response.query;
                vm.results.length = 0;
                vm.combinedResults.length = 0;
                resetObject(vm.splitResults);
                var res;
                if(response.rows.length > 0) {
                    vm.availableColumns.length = 0;
                    if(vm.expertMode) {
                        var row = response.rows[0];
                        // add all returned column names to selection array
                        for(var k in row) {
                            var o = vm.getOriginalColumnName(k);
                            var ac = {
                                col: o
                            };
                            // if names are different, AS property is set
                            if(k != o) ac.as = k;
                            vm.availableColumns.push(ac)
                        }
                        for(var i=0; i<response.rows.length; i++) {
                            vm.results.push(response.rows[i]);
                        }
                    } else {
                        for(var i=0; i<response.rows.length; i++) {
                            vm.combinedResults.push(response.rows[i]);
                        }
                        for(var k in response.splits) {
                            if(response.splits.hasOwnProperty(k)) {
                                vm.splitResults[k] = response.splits[k];
                            }
                        }
                    }
                    vm.activeResultTab = vm.expertMode ? 'raw' : 'simple';
                }
                vm.visualizationColumns.length = 0;
                vm.visualizationResults.length = 0;
                vm.visualizationColumns = vm.getAvailableColumns();
                vm.visualizationResults = vm.getResults();
            });
        };

        vm.getAvailableColumns = function() {
            if(vm.expertMode) {
                return vm.availableColumns;
            } else {
                var columns = [];
                // add predefined columns
                if(vm.combinedResults.length > 0) {
                    var header = vm.combinedResults[0];
                    for(var k in header) {
                        if(header.hasOwnProperty(k)) {
                            columns.push(k);
                        }
                    }
                }

                // add splits
                for(var k in vm.splitResults) {
                    if(vm.splitResults.hasOwnProperty(k)) {
                        var label = k;
                        if(vm.concepts[k]) {
                            label = vm.concepts[k].label;
                        }
                        columns.push(label);
                    }
                }
                return columns;
            }
        };

        vm.getResults = function() {
            if(vm.expertMode) {
                return vm.results;
            } else {
                var results = angular.copy(vm.combinedResults);
                for(var i=0; i<results.length; i++) {
                    var r = results[i];
                    for(var k in vm.splitResults) {
                        if(vm.splitResults.hasOwnProperty(k)) {
                            r[k] = vm.splitResults[k][i];
                        }
                    }
                }
                return results;
            }
        }

        vm.visualize = function(type) {
            if(!vm.vis[type].validate()) return;
            var data = vm.vis[type].createData();
            var layout = vm.vis[type].createLayout();
            Plotly.newPlot('plotly-visualization-container', data, layout);
        };

        vm.getOriginalColumnName = function(k) {
            for(var i=0; i<vm.columns.length; i++) {
                var col = vm.columns[i];
                if(k == col.as || k == col.col) return col.col;
            }
            return k;
        };

        vm.countResults = function() {
            if(vm.expertMode) {
                return vm.results.length;
            } else {
                return vm.combinedResults.length;
            }
        }

        vm.hasResults = function() {
            return (vm.expertMode && vm.results.length > 0) || (!vm.expertMode && vm.combinedResults.length > 0);
        };

        vm.functionSelected = function() {
            var type;
            switch(vm.selectedFunc.comp) {
                case 'pg_distance':
                case 'pg_area':
                    type = 'count';
                    vm.selectedComps = vm.getSupportedComps(type);
                    vm.selectedType = type;
                    break;
            }
        }

        vm.getSubPopover = function(item, relation) {
            var needsCompare = true;
            var type, subcolumn;
            switch(relation.name) {
                case 'attributes':
                    switch(item.datatype) {
                        case 'string':
                        case 'stringf':
                            type = 'string';
                            subcolumn = 'str_val';
                            break;
                        case 'double':
                            type = 'double';
                            subcolumn = 'dbl_val';
                            break;
                        case 'integer':
                            type = 'integer';
                            subcolumn = 'int_val';
                            break;
                        case 'percentage':
                            type = 'percentage';
                            subcolumn = 'int_val';
                            break;
                        case 'boolean':
                            type = 'boolean';
                            subcolumn = 'int_val';
                            break;
                        case 'date':
                            type = 'date';
                            subcolumn = 'int_val';
                            break;
                        // TODO
                        case 'string-sc':
                        case 'string-mc':
                        case 'epoch':
                        case 'dimension':
                        case 'list':
                        case 'geography':
                        case 'context':
                        case 'table':
                        default:
                            type = 'string';
                            subcolumn = 'str_val';
                    }
                    break;
                case 'geodata':
                    subcolumn = 'geom';
                    switch(item) {
                        case 'existance':
                            type = 'geodata';
                            break;
                        case 'type':
                            type = 'yesno';
                            vm.geoTypeColumns = [
                                'Point',
                                'LineString',
                                'Polygon'
                            ];
                            break;
                        case 'functions':
                            type = 'geo_functions';
                            vm.functionColumns = vm.getSupportedFuncs(type);
                            needsCompare = false;
                            break;
                    }
                    break;
                case 'literatures':
                    subcolumn = item;
                    switch(item) {
                        case 'year':
                        case 'entry count':
                            type = 'count';
                            break;
                        default:
                            type = 'string';
                            break;
                    }
                    break;
                case 'files':
                    subcolumn = item;
                    switch(item) {
                        case 'entry count':
                            type = 'count';
                            break;
                        default:
                            type = 'string';
                            break;
                    }
                    break;
                case 'child_contexts':
                    switch(item) {
                        case 'entry count':
                            subcolumn = 'entry count';
                            type = 'count';
                            break;
                        case 'type':
                            type = 'yesno';
                            subcolumn = 'context_type_id';
                            vm.contextColumns = angular.copy(vm.contextTypes);
                            break;
                    }
                    break;
                case 'attribute':
                    switch(item) {
                        case 'name':
                            type = 'yesno';
                            subcolumn = 'thesaurus_url';
                            vm.contextColumns = angular.copy(vm.attributes)
                            break;
                        case 'type':
                            type = 'yesno';
                            subcolumn = 'datatype';
                            vm.datatypeColumns = angular.copy(vm.attributetypes);
                            break;
                    }
                    break;
            }

            vm.selectedColumn = subcolumn;
            vm.selectedType = type;
            if(needsCompare) {
                vm.selectedComps = vm.getSupportedComps(type);
            }
            if(relation) {
                relation.id = item.id;
            }
        };

        vm.setContextTypeValue = function(item, name) {
            if(name == 'attribute') {
                vm.selectedComp_value = item.thesaurus_url;
            } else {
                vm.selectedComp_value = item.id;
            }
            delete vm.relation.id;
        };

        vm.setDataTypeValue = function(item, name) {
            vm.selectedComp_value = item.datatype;
            delete vm.relation.id;
        };

        vm.setGeoTypeValue = function(item, name) {
            vm.selectedComp_value = item;
            delete vm.relation.id;
        };

        vm.onOpenPopover = function(column, type) {
            vm.resetFilterOptions();
            if(type == 'ambiguous') {
                vm.relation.name = column;
                var insertedIds = {};
                switch(column) {
                    case 'attributes':
                        for(var i=0; i<vm.combinedResults.length; i++) {
                            var r = vm.combinedResults[i];
                            for(var j=0; j<r[column].length; j++) {
                                var attr = r[column][j];
                                if(!insertedIds[attr.id]) {
                                    insertedIds[attr.id] = 1;
                                    vm.attributeColumns.push(attr);
                                }
                            }
                        }
                        break;
                    case 'context_type':
                        vm.contextColumns = angular.copy(vm.contextTypes);
                        vm.selectedColumn = 'id';
                        vm.selectedComps = vm.getSupportedComps('context_type');
                        vm.selectedType = 'context_type';
                        break;
                    case 'geodata':
                        vm.actionColumns.push('existance');
                        vm.actionColumns.push('type');
                        vm.actionColumns.push('functions');
                        break;
                    case 'literatures':
                        for(var i=0; i<vm.combinedResults.length; i++) {
                            var lit = vm.combinedResults[i].literatures;
                            if(lit.length > 0) {
                                var row = lit[0];
                                for(var k in row) {
                                    if(row.hasOwnProperty(k)) {
                                        vm.actionColumns.push(k);
                                    }
                                }
                                vm.actionColumns.push('entry count');
                                break;
                            }
                        }
                        break;
                    case 'files':
                        vm.actionColumns.push('name');
                        vm.actionColumns.push('entry count');
                        break;
                    case 'child_contexts':
                        vm.actionColumns.push('type');
                        vm.actionColumns.push('entry count');
                        break;
                    case 'attribute':
                        vm.actionColumns.push('type');
                        vm.actionColumns.push('name');
                        break;
                    case 'context':
                    case 'root_context':
                        vm.contextColumns = angular.copy(vm.contextTypes);
                        vm.selectedColumn = 'context_type_id';
                        vm.selectedComps = vm.getSupportedComps('context');
                        vm.selectedType = column;
                        break;
                }
            } else {
                vm.selectedColumn = column;
                vm.selectedComps = vm.getSupportedComps(type);
                vm.selectedType = type;
            }
        };

        vm.closePopover = function(event) {
            // get anchor as angluar element
            var elem = angular.element(event.currentTarget);
            // get first ancestor with popover class
            var popover = elem.closest('.popover');
            // get popover src trigger element
            var src = popover.siblings('.filter-popover-trigger');
            // hide the popover
            src.click();
        };

        vm.resetFilterOptions = function() {
            if(vm.contextColumns) vm.contextColumns.length = 0;
            if(vm.attributeColumns) vm.attributeColumns.length = 0;
            if(vm.actionColumns) vm.actionColumns.length = 0;
            if(vm.datatypeColumns) vm.datatypeColumns.length = 0;
            if(vm.comps) vm.comps.length = 0;
            if(vm.selectedComps) vm.selectedComps.length = 0;
            if(vm.functionColumns) vm.functionColumns.length = 0;
            vm.comp = undefined;
            vm.selectedColumn = undefined;
            vm.selectedFunc = undefined;
            vm.selectedType = undefined;
            vm.selectedComp_value = undefined;
            vm.selectedFunc_value = undefined;
            resetObject(vm.selectedComp);
            resetObject(vm.relation);
        }

        vm.getSupportedComps = function(datatype) {
            var sc = angular.copy(vm.simpleComps);
            switch(datatype) {
                case 'date':
                    return [
                        sc.is,
                        sc.isNull,
                        sc.equals,
                        sc.notEquals,
                        sc.between,
                        sc.notBetween,
                        sc.in,
                        sc.notIn,
                        sc.comesBefore,
                        sc.comesAfter,
                    ];
                case 'double':
                case 'percentage':
                case 'integer':
                    return [
                        sc.is,
                        sc.isNull,
                        sc.lessThan,
                        sc.lessOrEqual,
                        sc.greaterThan,
                        sc.greaterOrEqual,
                        sc.equals,
                        sc.notEquals,
                        sc.between,
                        sc.notBetween,
                        sc.in,
                        sc.notIn,
                    ];
                case 'count':
                    return [
                        sc.lessThan,
                        sc.lessOrEqual,
                        sc.greaterThan,
                        sc.greaterOrEqual,
                        sc.equals,
                        sc.notEquals
                    ];
                case 'boolean':
                    return [
                        sc.is,
                        sc.isNull,
                        sc.equals,
                        sc.notEquals
                    ];
                case 'color':
                    return [
                        sc.is,
                        sc.isNull,
                        sc.equals,
                        sc.notEquals,
                        sc.in,
                        sc.notIn
                    ];
                case 'context_type':
                    return [
                        sc.equals,
                        sc.notEquals
                    ];
                case 'geodata':
                case 'child_contexts':
                    return [
                        sc.is,
                        sc.isNull
                    ];
                case 'context':
                    return [
                        sc.is,
                        sc.isNull,
                        sc.equals,
                        sc.notEquals
                    ];
                case 'yesno':
                    return [
                        sc.equals,
                        sc.notEquals
                    ];
                case 'string':
                default:
                    return [
                        sc.beginsWith,
                        sc.endsWith,
                        sc.doesntBeginWith,
                        sc.doesntEndWith,
                        sc.contains,
                        sc.doesntContain,
                        sc.is,
                        sc.isNull,
                        sc.equals,
                        sc.notEquals,
                        sc.in,
                        sc.notIn,
                        sc.comesBefore,
                        sc.comesAfter,
                    ];
            }
        };

        vm.getSupportedFuncs = function(datatype) {
            var sf = angular.copy(vm.simpleFunctions);
            switch(datatype) {
                case 'geo_functions':
                 return [
                     sf.geoDistance,
                     sf.geoArea
                 ];
            }
        };
    }
});
