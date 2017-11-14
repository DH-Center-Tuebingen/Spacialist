spacialistApp.component('analysis', {
    bindings: {
        concepts: '<',
        attributes: '<',
        attributetypes: '<',
        contextTypes: '<',
        tags: '<'
    },
    templateUrl: 'templates/gis-analysis.html',
    controller: function(httpPostFactory) {
        var vm = this;

        vm.results = [];

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
            'not like',
            'not ilike',
            'not between',
            'not in'
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

        vm.column = {};

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

        // vm.groups.push('context_type_id');
        // vm.columns.push({
        //     col: 'context_type_id'
        // });
        // vm.columns.push({
        //     col: '*',
        //     as: 'Anzahl',
        //     func: 'count',
        //     func_values: []
        // });

        vm.addColumn = function() {
            var c = vm.column;
            vm.columns.push({
                col: c.col,
                as: c.as,
                func: c.func,
                func_values: angular.fromJson(c.func_values)
            });
        };

        vm.removeColumn = function(column) {
            var index = vm.columns.indexOf(column);
            if(index > -1) {
                vm.columns.splice(index, 1);
            }
        };

        vm.addOrder = function(col, dir) {
            var order = vm.orders.find(function(order) {
                return order.col == col;
            });
            // Order already part of orders array, return
            if(order) {
                order.dir = dir;
                return;
            }
            vm.orders.push({
                col: col,
                dir: dir
            });
        };

        vm.removeOrder = function(order) {
            var index = vm.orders.indexOf(order);
            if(index > -1) {
                vm.orders.splice(index, 1);
            }
        };

        vm.moveOrderUp = function(order) {
            var index = vm.orders.indexOf(order);
            // index has to be > -1 AND > 0
            if(index > 0) {
                swap(vm.orders, index, index-1);
            }
        };

        vm.moveOrderDown = function(order) {
            var index = vm.orders.indexOf(order);
            if(index > -1 && index < vm.filters.length - 1) {
                swap(vm.orders, index, index+1);
            }
        };

        vm.addGroup = function() {
            vm.groups.push(vm.group);
        };

        vm.removeGroup = function(group) {
            var index = vm.groups.indexOf(group);
            if(index > -1) {
                vm.groups.splice(index, 1);
            }
        };

        vm.addFilter = function(col, comp, comp_value, func, comp_values, and) {
            // if vm.columns is set, col is an object
            if(col.col) col = col.col;
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
        };

        vm.editFilter = function(filter) {

        };

        vm.removeFilter = function(filter) {
            var index = vm.filters.indexOf(filter);
            if(index > -1) {
                vm.filters.splice(index, 1);
            }
        };

        vm.moveFilterUp = function(filter) {
            var index = vm.filters.indexOf(filter);
            // index has to be > -1 AND > 0
            if(index > 0) {
                swap(vm.filters, index, index-1);
            }
        };

        vm.moveFilterDown = function(filter) {
            var index = vm.filters.indexOf(filter);
            if(index > -1 && index < vm.filters.length - 1) {
                swap(vm.filters, index, index+1);
            }
        };

        vm.filter = function() {
            var formData = new FormData();
            formData.append('filters', angular.toJson(vm.filters));
            formData.append('origin', vm.origin);
            formData.append('columns', angular.toJson(vm.columns));
            formData.append('orders', angular.toJson(vm.orders));
            formData.append('groups', angular.toJson(vm.groups));
            formData.append('limit', angular.toJson(vm.limit));
            httpPostFactory('api/analysis/filter', formData, function(response) {
                console.log(response);
                vm.results.length = 0;
                for(var i=0; i<response.length; i++) {
                    var r = response[i];
                    for(var k in r) {
                        if(r.hasOwnProperty(k)) {
                            var lk = k.toLowerCase();
                            var tmp = r[k];
                            delete r[k];
                            r[lk] = tmp;
                        }
                    }
                    vm.results.push(response[i]);
                }
            });
        };
    }
});
