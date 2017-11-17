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

        vm.results = [];
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
            '<>',
            '!=',
            '>',
            '>=',
            '<',
            '<=',
            'like',
            'ilike',
            'between',
            'in',
            'is null',
            'not like',
            'not ilike',
            'not between',
            'not in',
            'is not null',
        ];

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

        vm.showFilterOptions = true;
        vm.instantFilter = false;
        vm.column = {};

        vm.availableColumns = [];
        vm.filters = [];
        vm.origin = vm.origins[0];
        vm.columns = [];
        vm.orders = [];
        vm.groups = [];
        vm.limit = {};

        vm.filters.push({
            col: 'geodata.geom',
            comp: '<',
            comp_value: 150000,
            func: 'pg_distance',
            func_values: [[48.52,9.05]],
            and: true
        });

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

        vm.addFilter = function(col, comp, comp_value, func, func_values, and) {
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
            var formData = new FormData();
            formData.append('filters', angular.toJson(vm.filters));
            formData.append('origin', vm.origin);
            formData.append('columns', angular.toJson(vm.columns));
            formData.append('orders', angular.toJson(vm.orders));
            formData.append('limit', angular.toJson(vm.limit));
            httpPostFactory('api/analysis/filter', formData, function(response) {
                vm.query = response.query;
                vm.results.length = 0;
                if(response.rows.length > 0) {
                    var row = response.rows[0];
                    vm.availableColumns.length = 0;
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
                }
            });
        };

        vm.visualize = function(type) {
            if(!vm.vis[type].validate()) return;
            var data = vm.vis[type].createData();
            console.log(data);
            var layout = {
                width: 500,
                height: 500
            }
            Plotly.newPlot('plotly-visualization-container', data, layout);
        };

        vm.getOriginalColumnName = function(k) {
            for(var i=0; i<vm.columns.length; i++) {
                var col = vm.columns[i];
                if(k == col.as || k == col.col) return col.col;
            }
            return k;
        }

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
    }
});
