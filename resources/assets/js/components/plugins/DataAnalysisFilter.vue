<template>
    <div class="d-flex flex-column">
        <div class="text-right">
            <button type="button" class="close" aria-label="Close" v-close-popover>
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <form role="form" class="mt-2" @submit.prevent="addFilter(filter)">
            <div class="form-group row" v-if="filter.comp.needs_value">
                <label class="col-form-label col-md-3 text-right">Value</label>
                <div class="col-md-9">
                    <span v-if="isType(column.type, 'thesaurus|entity_type|entity|attribute') && filter.comp.is_dropdown">
                        <multiselect
                            label="thesaurus_url"
                            track-by="id"
                            v-model="filter.tmp_value"
                            :allowEmpty="false"
                            :closeOnSelect="true"
                            :customLabel="translateLabel"
                            :hideSelected="false"
                            :multiple="false"
                            :options="selections"
                            @input="setSelectionValue">
                        </multiselect>
                    </span>
                    <v-date-picker
                        mode="single"
                        v-else-if="isType(column.type, 'date')"
                        v-model="filter.value"
                        :max-date="new Date()">
                        <div class="input-group date" slot-scope="{ inputValue, updateValue }">
                            <input type="text" class="form-control" :value="inputValue" @input="updateValue($event.target.value, { formatInput: false, hidePopover: false })" @change="updateValue($event.target.value, { formatInput: true, hidePopover: false }) "/>
                            <div class="input-group-append input-group-addon">
                                <button type="button" class="btn btn-outline-secondary">
                                    <i class="fas fa-fw fa-calendar-alt"></i>
                                </button>
                            </div>
                        </div>
                    </v-date-picker>
                    <span v-else-if="isType(column.type, 'list.bibliography|list.entity')"></span>
                    <input v-else-if="isType(column.type, 'integer|percentage')" class="form-control" type="number" v-model="filter.value" />
                    <input v-else-if="isType(column.type, 'color')" class="form-control" type="color" v-model="filter.value" />
                    <input v-else class="form-control" type="text" v-model="filter.value" />
                </div>
            </div>
            <div class="form-group row">
                <label class="col-form-label col-md-3 text-right">Comp</label>
                <div class="col-md-9">
                    <multiselect
                        label="label"
                        track-by="id"
                        v-model="filter.comp"
                        :allowEmpty="false"
                        :closeOnSelect="true"
                        :hideSelected="false"
                        :multiple="false"
                        :options="typeComparisons">
                    </multiselect>
                </div>
            </div>
            <div v-if="filter.comp.is_function && !filter.comp.silent">
                <div class="form-group row" v-if="filter.comp.needs_fvalue">
                    <label class="col-form-label col-md-3 text-right">Function Value</label>
                    <div class="col-md-9">
                        <input type="text" class="form-control" v-model="filter.fvalue" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-right">Comp</label>
                    <div class="col-md-9">
                        <multiselect
                            label="label"
                            track-by="id"
                            v-model="filter.fcomp"
                            :allowEmpty="false"
                            :closeOnSelect="true"
                            :hideSelected="false"
                            :multiple="false"
                            :options="functionComparisons">
                        </multiselect>
                    </div>
                </div>
                <div class="form-group row" v-if="filter.fcomp.needs_value">
                    <label class="col-form-label col-md-3 text-right">Value</label>
                    <div class="col-md-9">
                        <input class="form-control" type="text" v-model="filter.value" />
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-outline-success w-100">
                <span class="fa-stack d-inline">
                    <i class="fas fa-filter"></i>
                    <i class="fas fa-plus" data-fa-transform="shrink-5 left-10 down-5"></i>
                </span>
                <span style="margin-left: -0.5rem;">
                    Add Filter
                </span>
            </button>
        </form>
    </div>
</template>

<script>
    export default {
        props: {
            column: {
                required: true,
                type: Object
            },
            onAdd: {
                required: false,
                type: Function,
                default: _ => {}
            }
        },
        mounted() {
            this.init();
        },
        methods: {
            init() {
                const vm = this;
                const c = vm.comparisons;
                const f = vm.functions;
                let newTypes;
                vm.selections = [];
                switch(vm.column.type) {
                    case 'entity_type':
                        vm.filter.comp = c.equals_dd;
                        newTypes = [
                            c.equals_dd,
                            c.notEqual_dd
                        ];
                        vm.selections = Object.values(vm.$getEntityTypes());
                        break;
                    case 'entity':
                    case 'context':
                        vm.filter.comp = c.equals;
                        newTypes = [
                            c.is,
                            c.isNull,
                            c.equals,
                            c.notEqual,
                            c.equals_dd,
                            c.notEqual_dd
                        ];
                        vm.selections = Object.values(vm.$getEntityTypes());
                        break;
                    case 'geometry':
                        vm.filter.comp = c.is;
                        newTypes = [
                            c.is,
                            c.isNull,
                            f.geoDistance,
                            f.geoArea,
                            f.isPoint,
                            f.isLine,
                            f.isPolygon
                        ];
                        break;
                    case 'thesaurus':
                        vm.filter.comp = c.equals_dd;
                        newTypes = [
                            c.equals_dd,
                            c.notEqual_dd
                        ];
                        vm.selections = [];
                        break;
                    case 'attribute':
                        vm.filter.comp = c.equals_dd;
                        newTypes = [
                            c.equals_dd,
                            c.notEqual_dd
                        ];
                        vm.$http.get('/editor/dm/attribute').then(function(response) {
                            vm.selections = response.data;
                        });
                        break;
                    case 'date':
                        vm.filter.comp = c.lessThan;
                        newTypes = [
                            c.lessThan,
                            c.greaterThan
                        ];
                        break;
                    case 'integer':
                    case 'percentage':
                        vm.filter.comp = c.equals;
                        newTypes = [
                            c.lessThan,
                            c.lessOrEqual,
                            c.greaterThan,
                            c.greaterOrEqual,
                            c.equals,
                            c.notEqual
                        ];
                        break;
                    case 'double':
                        vm.filter.comp = c.equals;
                        newTypes = [
                            c.lessThan,
                            c.greaterThan,
                            c.equals,
                            c.notEqual
                        ];
                        break;
                    case 'color':
                        vm.filter.comp = c.equals;
                        newTypes = [
                            c.is,
                            c.isNull,
                            c.equals,
                            c.notEqual
                        ];
                        break;
                    case 'string-sc':
                    case 'string-mc':
                    case 'epoch':
                    case 'dimension':
                    case 'list':
                    case 'geography':
                    case 'boolean':
                    case 'table':
                        vm.filter.comp = null;
                        newTypes = [];
                        break;
                    case 'list.bibliography':
                    case 'list.entity':
                        vm.filter.comp = c.is;
                        newTypes = [
                            c.is,
                            c.isNull
                        ];
                        break;
                    case 'string':
                    case 'stringf':
                    default: // strings
                        vm.filter.comp = c.equals;
                        newTypes = [
                            c.beginsWith,
                            c.endsWith,
                            c.doesntBeginWith,
                            c.doesntEndWith,
                            c.contains,
                            c.doesntContain,
                            c.equals,
                            c.notEqual
                        ];
                        break;
                }
                if(vm.column.supports_aggregate) {
                    const a = vm.aggregates;
                    for(let k in a) {
                        newTypes.push(a[k]);
                    }
                }
                vm.typeComparisons = [];
                newTypes.forEach(t => vm.typeComparisons.push(t));
            },
            setSelectionValue(value, id) {
                if(value) {
                    this.filter.value = value.id;
                } else {
                    this.filter.value = '';
                }
            },
            addFilter(filter) {
                const parsedFilter = this.parseFilter(filter);
                this.onAdd(parsedFilter.filter, this.column, parsedFilter.comps);
            },
            parseFilter(filter) {
                // Copy filter, because we have to modify it
                let f = {...filter};
                let comps = {};
                comps.comp1 = {
                    comp: f.comp,
                    value: f.value
                };
                if(f.comp.is_function) {
                    // Swap comparisons, because we have to apply the
                    // non-function comparison first
                    const tmp = {...f.comp};
                    f.comp = {...f.fcomp};
                    f.fcomp = tmp;
                    comps.comp1 = {
                        comp: f.fcomp,
                        value: f.fvalue
                    };
                    comps.comp2 = {
                        comp: f.comp,
                        value: f.value
                    };
                    // Automatically set comparison for is_... geometry type
                    // functions/filter
                    // Setting f.comp here is "magic", so we delete this
                    // comparison from the comps
                    if(f.fcomp.id == 'is_point') {
                        f.comp = this.comparisons.endsWith;
                        f.value = 'POINT';
                        delete comps.comp2;
                    } else if(f.fcomp.id == 'is_line') {
                        f.comp = this.comparisons.endsWith;
                        f.value = 'LINESTRING';
                        delete comps.comp2;
                    } else if(f.fcomp.id == 'is_polygon') {
                        f.comp = this.comparisons.endsWith;
                        f.value = 'POLYGON';
                        delete comps.comp2;
                    }
                }
                let value;
                if(f.comp.returnValue) {
                    value = f.comp.returnValue(f.value);
                } else {
                    value = f.value;
                }
                let obj = {
                    comp_value: value,
                    comp: f.comp.comp,
                    column: this.column.key
                };
                if(this.column.is_relation) {
                    obj.relation = {
                        name: this.column.key,
                    };
                    if(this.isType(this.column.type, 'geometry')) {
                        obj.column = 'geom';
                    }
                    if(f.comp.on_relation) {
                        if(this.isType(this.column.type, 'entity_type')) {
                            obj.column = 'id';
                        } else if(this.isType(this.column.type, 'entity')) {
                            obj.column = 'context_type_id';
                        } else if(this.isType(this.column.type, 'attribute')) {
                            obj.column = 'attribute_id';
                        } else {
                            obj.relation.comp = f.comp.comp;
                            obj.relation.value = f.value;
                        }
                    } else {
                        switch(this.column.type) {
                            case 'entity':
                                obj.column = 'name';
                                break;
                        }
                    }
                }
                if(f.fcomp.is_function) {
                    obj.func = f.fcomp.comp;
                    obj.func_values = f.fvalue;
                }
                // Returns the parsed filter, but also the applied
                // comparisons, so they can be displayed human-readable
                return {
                    filter: obj,
                    comps: comps
                };
            },
            isType(columnType, types) {
                const typeArray = types.split('|');
                for(let i=0; i<typeArray.length; i++) {
                    if(typeArray[i] == columnType) {
                        return true;
                    }
                }
                return false;
            },
            translateLabel(element, prop) {
                return this.$translateLabel(element, prop);
            }
        },
        data() {
            return {
                filter: {
                    comp: {},
                    fcomp: {},
                    value: '',
                    fvalue: '',
                    tmp_value: ''
                },
                selections: [],
                typeComparisons: [],
                comparisons: {
                    beginsWith: {
                        label: 'begins-with',
                        id: 'begins_with',
                        comp: 'ILIKE',
                        returnValue: function(value) {
                            return `${value}%`;
                        },
                        needs_value: true
                    },
                    endsWith: {
                        label: 'ends-with',
                        id: 'ends_with',
                        comp: 'ILIKE',
                        returnValue: function(value) {
                            return `%${value}`;
                        },
                        needs_value: true
                    },
                    doesntBeginWith: {
                        label: 'not-begins-with',
                        id: 'doesnt_begin_with',
                        comp: 'NOT ILIKE',
                        returnValue: function(value) {
                            return `${value}%`;
                        },
                        needs_value: true
                    },
                    doesntEndWith: {
                        label: 'not-ends-with',
                        id: 'doesnt_end_with',
                        comp: 'NOT ILIKE',
                        returnValue: function(value) {
                            return `%${value}`;
                        },
                        needs_value: true
                    },
                    contains: {
                        label: 'contains',
                        id: 'containts',
                        comp: 'ILIKE',
                        returnValue: function(value) {
                            return `%${value}%`;
                        },
                        needs_value: true
                    },
                    doesntContain: {
                        label: 'not-contains',
                        id: 'doesnt_contain',
                        comp: 'NOT ILIKE',
                        returnValue: function(value) {
                            return `%${value}%`;
                        },
                        needs_value: true
                    },
                    is: {
                        label: 'exists',
                        id: 'is',
                        comp: 'IS NOT NULL',
                        on_relation: true
                    },
                    isNull: {
                        label: 'not-exists',
                        id: 'is_not',
                        comp: 'IS NULL',
                        on_relation: true
                    },
                    lessThan: {
                        label: 'less-than',
                        id: 'less_than',
                        comp: '<',
                        needs_value: true
                    },
                    lessOrEqual: {
                        label: 'less-than-or-equal',
                        id: 'less_than_equal',
                        comp: '<=',
                        needs_value: true
                    },
                    greaterThan: {
                        label: 'greater-than',
                        id: 'greater_than',
                        comp: '>',
                        needs_value: true
                    },
                    greaterOrEqual: {
                        label: 'greater-than-or-equal',
                        id: 'greater_than_equal',
                        comp: '>=',
                        needs_value: true
                    },
                    equals: {
                        label: 'equals',
                        id: 'equals',
                        comp: '=',
                        needs_value: true
                    },
                    notEqual: {
                        label: 'not-equals',
                        id: 'doesnt_equal',
                        comp: '!=',
                        needs_value: true
                    },
                    equals_dd: {
                        label: 'dd_equals',
                        id: 'dd_equals',
                        comp: '=',
                        needs_value: true,
                        is_dropdown: true,
                        on_relation: true
                    },
                    notEqual_dd: {
                        label: 'dd_not-equals',
                        id: 'dd_doesnt_equal',
                        comp: '!=',
                        needs_value: true,
                        is_dropdown: true,
                        on_relation: true
                    }
                },
                aggregates: {
                    unique: {
                        label: 'Unique Values',
                        id: 'unique',
                        comp: 'GROUP BY',
                        is_aggregate: true
                    },
                    count: {
                        label: 'Count Uniques',
                        id: 'count',
                        comp: 'COUNT',
                        is_aggregate: true
                    },
                },
                functions: {
                    geoDistance: {
                        label: 'distance to (in m)',
                        id: 'distance_to_m',
                        comp: 'pg_distance',
                        needs_fvalue: true,
                        is_function: true
                    },
                    geoArea: {
                        label: 'area (in qm)',
                        id: 'area_qm',
                        comp: 'pg_area',
                        is_function: true
                    },
                    isPoint: {
                        label: 'is-point',
                        id: 'is_point',
                        comp: 'GeometryType',
                        is_function: true,
                        silent: true
                    },
                    isLine: {
                        label: 'is-line',
                        id: 'is_line',
                        comp: 'GeometryType',
                        is_function: true,
                        silent: true
                    },
                    isPolygon: {
                        label: 'is-polygon',
                        id: 'is_polygon',
                        comp: 'GeometryType',
                        is_function: true,
                        silent: true
                    }
                }
            }
        },
        computed: {
            functionComparisons: function() {
                if(!this.filter.comp || !this.filter.comp.is_function) {
                    return [];
                }
                return [
                    this.comparisons.lessThan,
                    this.comparisons.greaterThan
                ];
            }
        }
    }
</script>
