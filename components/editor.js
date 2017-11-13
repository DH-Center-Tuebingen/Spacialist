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
        //     func: 'count',
        //     as: 'Anzahl'
        // });

        vm.addFilter = function(and) {
            var filter = {
                col: vm.col,
                comp: vm.comp,
                comp_value: vm.comp_value,
                and: and
            };
            if(vm.func) {
                filter.func = vm.func;
                filter.func_values = vm.func_values;
            }
            vm.filters.push(filter);
        }

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
                    vm.results.push(response[i]);
                }
            })
        }
    }
});
