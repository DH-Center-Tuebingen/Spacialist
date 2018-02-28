<template>
    <div>
        <draggable
            v-model="localAttributes"
            :clone="clone"
            :move="move"
            :options="dragOpts"
            @add="added"
            @end="dropped"
            @start="dragged">
            <div class="form-group row" v-for="(attribute, i) in localAttributes" @mouseenter="onEnter(i)" @mouseleave="onLeave(i)">
            <label class="control-label col-md-3 d-flex flex-row justify-content-between" :for="'attribute-'+attribute.id">
                <!-- <div style="display: table; float: right;">
                    <span style="display: table-cell">
                        {{ concepts[attribute.thesaurus_url].label }}:
                    </span>
                    <sup ui-sref="root.spacialist.context.data.sources({aid: attribute.id})" style="display: table-cell" class="source-link">
                        <i class="material-icons md-18" aria-hidden="true" ng-if="!readonlyInput">stars</i><i ng-if="(attributeSources | filter:{attribute_id:attribute.id}).length > 0 || localValues[attribute.id+'_cert'] < 100 || localValues[attribute.id+'_desc'].length > 0" class="material-icons md-18 material-addon fa-limegreen">fiber_manual_record</i>
                    </sup>
                </div> -->
                <!-- <div v-if="hovered[attribute.id]" class="d-flex">
                    <div v-if="allowEdit" style="">
                        <a class="btn btn-outline-info btn-sm p-1" href="">
                            <i class="fas fa-fw fa-edit"></i>
                        </a>
                    </div>
                    <div v-if="allowDelete">
                        <a class="btn btn-outline-danger btn-sm p-1" href="">
                            <i class="fas fa-fw fa-trash"></i>
                        </a>
                    </div>
                    <div v-if="allowReorder">
                        <a class="btn btn-outline-secondary btn-sm p-1" href="" v-if="!$first">
                            <i class="fas fa-fw fa-sort-up"></i>
                        </a>
                        <a class="btn btn-outline-secondary btn-sm p-1" href="" v-if="!$last">
                            <i class="fas fa-fw fa-sort-down"></i>
                        </a>
                    </div>
                </div> -->
                <div v-show="hoverState[i]">
                    <a v-if="onReorder" href="#">
                        <i class="fas fa-fw fa-sort text-secondary"></i>
                    </a>
                    <button v-if="onEdit" class="btn btn-info btn-fab rounded-circle">
                        <i class="fas fa-fw fa-xs fa-edit" style="vertical-align: 0;"></i>
                    </button>
                    <button v-if="onRemove" class="btn btn-danger btn-fab rounded-circle" @click="onRemove(attribute)">
                        <i class="fas fa-fw fa-xs fa-times" style="vertical-align: 0;"></i>
                    </button>
                    <button v-if="onDelete" class="btn btn-danger btn-fab rounded-circle" @click="onDelete(attribute)">
                        <i class="fas fa-fw fa-xs fa-trash" style="vertical-align: 0;"></i>
                    </button>
                </div>
                <span class="text-right">
                    {{concepts[attribute.thesaurus_url].label}}:
                </span>
                <sup v-if="onMetadata">
                    <i class="fas fa-fw fa-lg fa-dot-circle"></i>
                </sup>
            </label>
            <div class="col-md-9">
                <input class="form-control" v-if="attribute.datatype == 'string'" type="text" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id]"/>
                <input class="form-control" v-else-if="attribute.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id]"/>
                <input class="form-control" v-else-if="attribute.datatype == 'integer'" type="number" step="1" placeholder="0" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id]"/>
                <input class="form-control" v-else-if="attribute.datatype == 'boolean'" type="checkbox" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id]"/>
                <textarea class="form-control" v-else-if="attribute.datatype == 'stringf'" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id]"></textarea>
                <div v-else-if="attribute.datatype == 'percentage'" class="d-flex">
                    <input class="form-control" type="range" step="1" min="0" max="100" value="0" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id]"/>
                    <span class="ml-3">{{ localValues[attribute.id] }}%</span>
                </div>
                <div v-else-if="attribute.datatype == 'geography'">
                    <input class="form-control" type="text" :id="'attribute-'+attribute.id" placeholder="Add WKT" v-model="localValues[attribute.id]" />
                    <button type="button" class="btn btn-outline-secondary" style="margin-top: 10px;" ng-click="$ctrl.openGeographyPlacer(attribute.id)">
                        <i class="fas fa-fw fa-map-marker-alt"></i> Open Map
                    </button>
                </div>
                <div v-else-if="attribute.datatype == 'context'">
                    <context-search></context-search>
                </div>
                <div class="input-group date" data-provide="datepicker" v-else-if="attribute.datatype == 'date'">
                    <input type="text" class="form-control" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id]"  ng-model-options="{timezone:'utc'}"/>
                    <div class="input-group-append input-group-addon">
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="fas fa-fw fa-calendar-alt"></i>
                        </button>
                    </div>
                </div>
                <div v-else-if="attribute.datatype == 'string-mc'">
                    <multiselect
                        label="name"
                        v-model="localValues[attribute.id]"
                        :allowEmpty="true"
                        :closeOnSelect="false"
                        :hideSelected="true"
                        :multiple="true"
                        :options="[]">
                    </multiselect>
                </div>
                <div v-else-if="attribute.datatype == 'string-sc'">
                    <multiselect
                        label="name"
                        v-model="localValues[attribute.id]"
                        :allowEmpty="true"
                        :closeOnSelect="true"
                        :hideSelected="true"
                        :multiple="false"
                        :options="[]">
                    </multiselect>
                </div>
                <div v-else-if="attribute.datatype == 'list'">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="btn btn-outline-secondary" v-on:click="toggleList(attribute.id)">
                                <div v-show="!expands[attribute.id]">
                                    <i class="fas fa-fw fa-caret-up"></i>
                                    <span v-if="localValues[attribute.id] && localValues[attribute.id].length">
                                        ({{localValues[attribute.id].length}})
                                    </span>
                                </div>
                                <div v-show="expands[attribute.id]">
                                    <i class="fas fa-fw fa-caret-down"></i>
                                </div>
                            </button>
                        </div>
                        <input type="text" class="form-control" v-model="inputs[attribute.id]" />
                        <div class="input-group-append">
                            <button type="button" class="btn btn-success" v-on:click="addListEntry(attribute.id)">
                                <i class="fas fa-fw fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <ol class="mt-2" v-if="expands[attribute.id] && localValues[attribute.id] && localValues[attribute.id].length">
                        <li v-for="(l, i) in localValues[attribute.id]">
                            {{ l }}
                            <a href="#" class="text-danger" v-on:click="removeListEntry(attribute.id, i)">
                                <i class="fas fa-fw fa-trash"></i>
                            </a>
                        </li>
                    </ol>
                </div>
                <div v-else-if="attribute.datatype == 'epoch' && localValues[attribute.id]">
                    epoch
                    <div class="input-group">
                        <div class="input-group-btn" uib-dropdown>
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ localValues[attribute.id].startLabel }}
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="">BC</a>
                                </li>
                                <li>
                                    <a href="">AD</a>
                                </li>
                            </ul>
                        </div>
                        <input type="number" step="1" pattern="[0-9]+" class="form-control centered" aria-label="" v-model="localValues[attribute.id].start">
                        <span class="input-group-addon">-</span>
                        <input type="number" step="1" pattern="[0-9]+" class="form-control centered" aria-label="" v-model="localValues[attribute.id].end">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ localValues[attribute.id].endLabel }}
                            </button>
                            <ul uib-dropdown-menu class="dropdown-menu">
                                <li><a href="">BC</a></li>
                                <li><a href="">AD</a></li>
                            </ul>
                        </div>
                    </div>
                    <multiselect v-model="localValues[attribute.id].epoch" :options="[]" label="name" :multiple="false" :hideSelected="true" :closeOnSelect="false"></multiselect>
                </div>
                <div v-else-if="attribute.datatype == 'dimension'">
                    <div class="input-group">
                        <input type="number" class="form-control text-center" min="0" max="9999" step="0.01" v-model="localValues[attribute.id]" />
                        <div class="input-group-append input-group-prepend">
                            <span class="input-group-text">&times;</span>
                        </div>
                        <input type="number" class="form-control text-center" min="0" max="9999" step="0.01" v-model="localValues[attribute.id]" />
                        <div class="input-group-append input-group-prepend">
                            <span class="input-group-text">&times;</span>
                        </div>
                        <input type="number" class="form-control text-center" min="0" max="9999" step="0.01" v-model="localValues[attribute.id]" />
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"     data-toggle="dropdown" aria-haspopup="true"     aria-expanded="false">m</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" v-for="unit in dimensionUnits">
                                    {{ unit }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="input-group">
                        <input type="number" class="form-control centered" aria-label="" placeholder="{{'field-types.dimension.width-placeholder'|translate}}" min="0" max="9999" step="0.01" ng-model="localValues[attribute.id].B">
                        <span class="input-group-addon">&times;</span>
                        <input type="number" class="form-control centered" aria-label="" placeholder="{{'field-types.dimension.height-placeholder'|translate}}" min="0" max="9999" step="0.01" ng-model="localValues[attribute.id].H">
                        <span class="input-group-addon">&times;</span>
                        <input type="number" class="form-control centered" aria-label="" placeholder="{{'field-types.dimension.depth-placeholder'|translate}}" min="0" max="9999" step="0.01" ng-model="localValues[attribute.id].T">
                        <div class="input-group-btn" uib-dropdown>
                            <button type="button" class="btn btn-default dropdown-toggle" uib-dropdown-toggle data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ng-disabled="readonlyInput">
                                {{ localValues[attribute.id].unit }} <span class="caret"></span>
                            </button>
                            <ul uib-dropdown-menu class="dropdown-menu">
                                <li ng-repeat="unit in dimensionUnits"><a href="" ng-click="localValues[attribute.id].unit = unit">{{ unit }}</a></li>
                            </ul>
                        </div>
                    </div> -->
                </div>
                <div v-else-if="attribute.datatype == 'table'">
                    table
                    <!-- <table class="table table-striped table-hovered" ng-init="tableCol = {}">
                        <tr>
                            <th ng-repeat="c in attribute.columns">
                                {{ concepts[c.thesaurus_url].label }}
                            </th>
                            <th>
                                Delete
                            </th>
                        </tr>
                        <tr ng-repeat="row in localValues[attribute.id] track by $index">
                            <td ng-repeat="col in row track by $index">
                                <ng-switch on="col.datatype">
                                    <input class="form-control" v-else-if="attribute.datatype == 'string'" type="text" ng-model="col.value"/>
                                    <input class="form-control" v-else-if="attribute.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" ng-model="col.value"/>
                                    <input class="form-control" v-else-if="attribute.datatype == 'integer'" type="number" step="1" placeholder="0" ng-model="col.value"/>
                                    <input class="form-control" v-else-if="attribute.datatype == 'boolean'" type="checkbox" ng-model="col.value"/>
                                    <div v-else-if="attribute.datatype == 'string-sc'">
                                        <ui-select ng-disabled="readonlyInput" ng-model="col.value">
                                            <ui-select-match allow-clear="true">
                                                {{ concepts[$select.selected.concept_url].label }}
                                            </ui-select-match>
                                            <ui-select-choices repeat="choice in menus[c.id] | filter: $select.search">
                                                <span ng-bind-html="concepts[choice.concept_url].label | highlight: $select.search"></span>
                                            </ui-select-choices>
                                        </ui-select>
                                    </div>
                                </ng-switch>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-xs" ng-click="deleteTableRow(attribute.id, $index, $ctrl.editContext.form)">
                                    <i class="material-icons">delete</i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td ng-repeat="c in attribute.columns">
                                <ng-switch on="c.datatype">
                                    <input class="form-control" v-else-if="attribute.datatype == 'string'" type="text" ng-model="tableCol[c.id]"/>
                                    <input class="form-control" v-else-if="attribute.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" ng-model="tableCol[c.id]"/>
                                    <input class="form-control" v-else-if="attribute.datatype == 'integer'" type="number" step="1" placeholder="0" ng-model="tableCol[c.id]"/>
                                    <input class="form-control" v-else-if="attribute.datatype == 'boolean'" type="checkbox" ng-model="tableCol[c.id]"/>
                                    <div v-else-if="attribute.datatype == 'string-sc'">
                                        <ui-select ng-disabled="readonlyInput" ng-model="tableCol[c.id]">
                                            <ui-select-match allow-clear="true">
                                                {{ concepts[$select.selected.concept_url].label }}
                                            </ui-select-match>
                                            <ui-select-choices repeat="choice in menus[c.id] | filter: $select.search">
                                                <span ng-bind-html="concepts[choice.concept_url].label | highlight: $select.search"></span>
                                            </ui-select-choices>
                                        </ui-select>
                                    </div>
                                </ng-switch>
                            </td>
                        </tr>
                    </table>
                    <button type="button" class="btn btn-success" ng-click="addTableRow(attribute.id, attribute.columns, tableCol, $ctrl.editContext.form)">
                        <i class="material-icons">add</i> Add Row
                    </button> -->
                </div>
                <input class="form-control" v-else type="text" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id]"/>
            </div>
        </div>
        </draggable>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';

    export default {
        props: {
            attributes: {
                required: true,
                type: Array
            },
            values: {
                required: true,
                type: Object
            },
            concepts: {
                required: true,
                type: Object
            },
            isSource: {
                required: false,
                type: Boolean,
                default: false
            },
            onAdd: {
                required: false,
                type: Function
            },
            onDelete: {
                required: false,
                type: Function
            },
            onEdit: {
                required: false,
                type: Function
            },
            onRemove: {
                required: false,
                type: Function
            },
            onReorder: {
                required: false,
                type: Function
            },
            group: { // required if onReorder is set // TODO
                required: false,
                type: String
            },
            showInfo: { // shows parent on hover
                required: false,
                type: Boolean
            },
            onMetadata: { // Sources modal
                required: false,
                type: Function
            },
            test: {
                required: false
            }
        },
        components: {
            draggable
        },
        mounted() {},
        methods: {
            onEnter(i) {
                Vue.set(this.hovered, i, true);
            },
            onLeave(i) {
                Vue.set(this.hovered, i, false);
            },
            addListEntry(id) {
                if(!this.localValues[id]) {
                    Vue.set(this.localValues, id, []);
                }
                this.localValues[id].push(this.inputs[id]);
            },
            removeListEntry(id, index) {
                this.localValues[id].splice(index, 1);
            },
            toggleList(id) {
                if(!this.expands[id] && this.expands !== false) {
                    Vue.set(this.expands, id, false);
                }
                this.expands[id] = !this.expands[id];
            },
            // Vue.Draggable methods
            clone(original) {
                return Object.assign({}, original);
            },
            dragged(event) {
                this.drag = true;
            },
            added(event) {
                let oldIndex = event.oldIndex;
                let newIndex = event.newIndex;
                this.onAdd(oldIndex, newIndex);
            },
            dropped(event) {
                this.drag = false;
                // return here if list is source only or source of drop
                if(this.isSource) return;
                let tgtList = event.to;
                let srcList = event.from;
                let oldIndex = event.oldIndex;
                let newIndex = event.newIndex;
                let isNew = tgtList != srcList;
                this.onReorder(oldIndex, newIndex);
            },
            move(event, originalEvent) {
                let src = event.draggedContext.element;
                let dst = event.relatedContext;
                let tgtList = event.to;
                let srcList = event.from;
                // Move is always allowed if not source or from other list
                if(!this.isSource && tgtList == srcList) {
                    return true;
                }
                let index = dst.list.findIndex(function(e) {
                    return src.id == e.id;
                });
                // move is only allowed, if dragged element is not part of the list
                return index == -1;
            }
        },
        data() {
            return {
                hovered: [],
                inputs: {},
                expands: {},
                dimensionUnits: ['nm', 'µm', 'mm', 'cm', 'dm', 'm', 'km'],
            }
        },
        created() {
            for(let i=0; i<this.localAttributes.length; i++) {
                this.hovered.push(false);
            }
        },
        computed: {
            importedAttributes: function() {
                return this.attributes.slice();
            },
            localAttributes: {
                get: function() {
                    return this.importedAttributes;
                },
                set: function(newValue) {
                    // return newValue;
                }
            },
            importedValues: function() {
                return Object.assign({}, this.values);
            },
            localValues: {
                get: function() {
                    return this.importedValues;
                },
                set: function(newValue) {
                    // return newValue;
                }
            },
            hoverState: function() {
                return this.hovered;
            },
            dragOpts: function() {
                let opts = {};
                if(this.group && !this.isSource) {
                    opts.group= {
                        name: this.group,
                        pull: false,
                        put: true
                    };
                } else if(this.group && this.isSource) {
                    opts.group = {
                        name: this.group,
                        pull: 'clone',
                        put: false
                    };
                    opts.sort = false;
                }
                return opts;
            }
        }
    }
</script>
