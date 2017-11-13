spacialistApp.component('datamodel', {
    bindings: {
        attributes: '<',
        attributetypes: '<',
        concepts: '<',
        contextTypes: '<',
        geometryTypes: '<',
        userConfig: '<'
    },
    templateUrl: 'data-model.html',
    controller: 'dataEditorCtrl'
});

spacialistApp.component('contexttypeedit', {
    bindings: {
        contextType: '<',
        attributes: '<',
        concepts: '<',
        fields: '<'
    },
    templateUrl: 'templates/context-type.html',
    controller: 'dataEditorCtrl'
});

spacialistApp.component('layer', {
    bindings: {
        avLayers: '<',
        concepts: '<'
    },
    templateUrl: 'layer-editor.html',
    controller: 'layerEditorCtrl'
});

spacialistApp.component('layeredit', {
    bindings: {
        layer: '<',
        concepts: '<'
    },
    templateUrl: 'templates/layer-edit.html',
    controller: 'layerEditCtrl'
});

spacialistApp.component('gis', {
    bindings: {
        concepts: '<',
        contexts: '<',
        map: '<',
        layer: '<',
        geodata: '<'
    },
    templateUrl: 'templates/gis.html',
    controller: 'gisCtrl'
});

spacialistApp.component('gisanalysis', {
    bindings: {
        concepts: '<'
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

        vm.dirs = [
            'asc',
            'desc'
        ]

        vm.column = {};
        vm.order = {};

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

        vm.addOrder = function() {
            var o = vm.order;
            vm.orders.push({
                col: o.col,
                dir: o.dir
            });
        };

        vm.removeOrder = function(order) {
            var index = vm.orders.indexOf(order);
            if(index > -1) {
                vm.orders.splice(index, 1);
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

        vm.addFilter = function(and) {
            var filter = {
                col: vm.col,
                comp: vm.comp,
                comp_value: vm.comp_value,
                and: and
            };
            if(vm.func) {
                filter.func = vm.func;
                filter.func_values = angular.fromJson(vm.func_values);
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
