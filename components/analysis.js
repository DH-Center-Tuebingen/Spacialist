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

        vm.results = [];
        vm.combinedResults = [];
        vm.query = '';
        vm.vis = {
            type: '',
            pie: {
                labels: [],
                values: [],
                selectLabel: function() {
                    vm.vis.pie.labels.length = 0;
                    var c = vm.vis.labelCol.as || vm.vis.labelCol.col;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
                        vm.vis.pie.labels.push(r[c]);
                    }
                },
                selectValues: function() {
                    vm.vis.pie.values.length = 0;
                    var c = vm.vis.valueCol.as || vm.vis.valueCol.col;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
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
                    var c = vm.vis.x.as || vm.vis.x.col;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
                        vm.vis.bar.x.push(r[c]);
                    }
                },
                selectY: function() {
                    vm.vis.bar.y.length = 0;
                    var c = vm.vis.y.as || vm.vis.y.col;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
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
                    var c = vm.vis.x.as || vm.vis.x.col;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
                        vm.vis.line.x.push(r[c]);
                    }
                },
                selectY: function() {
                    vm.vis.line.y.length = 0;
                    var c = vm.vis.y.as || vm.vis.y.col;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
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
                    var c = vm.vis.x.as || vm.vis.x.col;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
                        vm.vis.scatter.x.push(r[c]);
                    }
                },
                selectY: function() {
                    vm.vis.scatter.y.length = 0;
                    var c = vm.vis.y.as || vm.vis.y.col;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
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
                    var c = vm.vis.x.as || vm.vis.x.col;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
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
                    var c = vm.vis.x.as || vm.vis.x.col;
                    vm.vis.ternary.titles.x = c;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
                        vm.vis.ternary.x.push(r[c]);
                    }
                },
                selectY: function() {
                    vm.vis.ternary.y.length = 0;
                    var c = vm.vis.y.as || vm.vis.y.col;
                    vm.vis.ternary.titles.y = c;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
                        vm.vis.ternary.y.push(r[c]);
                    }
                },
                selectZ: function() {
                    vm.vis.ternary.z.length = 0;
                    var c = vm.vis.z.as || vm.vis.z.col;
                    vm.vis.ternary.titles.z = c;
                    for(var i=0; i<vm.results.length; i++) {
                        var r = vm.results[i];
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
            'attribute_values',
            'contexts',
            'files',
            'geodata',
            'literature'
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
                label: 'begins with',
                comp: 'ILIKE%',
                needs_value: true
            },
            endsWith: {
                label: 'ends with',
                comp: '%ILIKE',
                needs_value: true
            },
            doesntBeginWith: {
                label: 'does not begin with',
                comp: 'NOT ILIKE%',
                needs_value: true
            },
            doesntEndWith: {
                label: 'does not end with',
                comp: '%NOT ILIKE',
                needs_value: true
            },
            contains: {
                label: 'contains',
                comp: '%ILIKE%',
                needs_value: true
            },
            doesntContain: {
                label: 'does not contain',
                comp: '%NOT ILIKE%',
                needs_value: true
            },
            is: {
                label: 'exists',
                comp: 'IS NOT NULL'
            },
            isNull: {
                label: 'does not exist',
                comp: 'IS NULL'
            },
            lessThan: {
                label: 'less than',
                comp: '<',
                needs_value: true
            },
            lessOrEqual: {
                label: 'less or equal to',
                comp: '<=',
                needs_value: true
            },
            greaterThan: {
                label: 'greater than',
                comp: '>',
                needs_value: true
            },
            greaterOrEqual: {
                label: 'greater or equal to',
                comp: '>=',
                needs_value: true
            },
            equals: {
                label: 'equals',
                comp: '=',
                needs_value: true
            },
            notEquals: {
                label: 'equals not',
                comp: '!=',
                needs_value: true
            },
            between: {
                label: 'between',
                comp: 'BETWEEN',
                needs_value: true
            },
            notBetween: {
                label: 'not between',
                comp: 'NOT BETWEEN',
                needs_value: true
            },
            in: {
                label: 'is in',
                comp: 'IN',
                needs_value: true
            },
            notIn: {
                label: 'is not in',
                comp: 'NOT IN',
                needs_value: true
            },
            comesBefore: {
                label: 'comes before',
                comp: '<',
                needs_value: true
            },
            comesAfter: {
                label: 'comes after',
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

        vm.availableVisualizations = [
            'scatter',
            'line',
            'bar',
            'pie',
            'histogram',
            'histogram2d',
            'ternary',
            'ternarycontour',
            'heatmap',
            'windrose',
            'radar'
        ]

        vm.expertMode = false;
        vm.showFilterOptions = true;
        vm.showAmbiguous = true;
        vm.instantFilter = false;
        vm.column = {};
        vm.relation = {};

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

        vm.resetColumn = function(col) {
            for(var k in col) {
                if(col.hasOwnProperty(k)) {
                    delete col[k];
                }
            }
        };

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
            vm.resetColumn(c);
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

        vm.adjustFilterValues = function(relation, col, comp, comp_value) {
            var needsAdjustment = false;
            if(relation) {
                switch(relation.name) {
                    case 'literatures':
                    case 'files':
                    case 'child_contexts':
                        needsAdjustment = col == 'entry count';
                        break;
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
            formData.append('origin', vm.origin);
            formData.append('columns', angular.toJson(vm.columns));
            formData.append('orders', angular.toJson(vm.orders));
            formData.append('limit', angular.toJson(vm.limit));
            formData.append('simple', !vm.expertMode);
            formData.append('distinct', vm.distinct);
            httpPostFactory('api/analysis/filter', formData, function(response) {
                vm.filteredOrigin = vm.origin;
                vm.query = response.query;
                vm.results.length = 0;
                vm.combinedResults.length = 0;
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
                    }
                    vm.activeResultTab = vm.expertMode ? 'raw' : 'simple';
                }
            });
        };

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

        vm.getSubPopover = function(item, relation) {
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
                    type = 'geodata';
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
                            console.log(vm.attributetypes);
                            vm.datatypeColumns = angular.copy(vm.attributetypes);
                            break;
                    }
                    break;
            }

            vm.selectedColumn = subcolumn;
            vm.selectedComps = vm.getSupportedComps(type);
            vm.selectedType = type;
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
                        for(var i=0; i<vm.combinedResults.length; i++) {
                            var r = vm.combinedResults[i];
                        }
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
            vm.comp = undefined;
            vm.selectedColumn = undefined;
            vm.selectedType = undefined;
            vm.selectedComp_value = undefined;
            for(var k in vm.selectedComp) {
                if(vm.selectedComp.hasOwnProperty(k)) {
                    delete vm.selectedComp[k];
                }
            }
            vm.unsetRelation();
        }

        vm.unsetRelation = function() {
            for(var k in vm.relation) {
                if(vm.relation.hasOwnProperty(k)) {
                    delete vm.relation[k];
                }
            }
        };

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
                        sc.notEquals,
                        sc.between,
                        sc.notBetween,
                        sc.in,
                        sc.notIn,
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
    }
});
