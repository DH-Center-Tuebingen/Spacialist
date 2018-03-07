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
            <div class="form-group row" :class="{'disabled not-allowed-handle': attribute.isDisabled}" v-for="(attribute, i) in localAttributes" @mouseenter="onEnter(i)" @mouseleave="onLeave(i)">
            <label class="control-label col-md-3 d-flex flex-row justify-content-between" :for="'attribute-'+attribute.id" :class="{'copy-handle': isSource&&!attribute.isDisabled, 'not-allowed-handle text-muted': attribute.isDisabled}">
                <div v-show="hoverState[i]">
                    <a v-if="onReorder" href="#" class="reorder-handle">
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
                    <span class="fa-layers fa-fw">
                        <i class="fas fa-lg fa-dot-circle"></i>
                        <i v-if="metadataAddon(attribute.thesaurus_url)" class="fa-inverse fas fa-circle text-success" data-fa-transform="shrink-5 down-6 right-6"></i>
                    </span>
                    <!-- <div style="display: table; float: right;">
                        <span style="display: table-cell">
                            {{ concepts[attribute.thesaurus_url].label }}:
                        </span>
                        <sup ui-sref="root.spacialist.context.data.sources({aid: attribute.id})" style="display: table-cell" class="source-link">
                            <i class="material-icons md-18" aria-hidden="true" ng-if="!readonlyInput">stars</i><i ng-if="(attributeSources | filter:{attribute_id:attribute.id}).length > 0 || localValues[attribute.id+'_cert'] < 100 || localValues[attribute.id+'_desc'].length > 0" class="material-icons md-18 material-addon fa-limegreen">fiber_manual_record</i>
                        </sup>
                    </div> -->
                </sup>
            </label>
            <div class="col-md-9">
                <input class="form-control" :disabled="attribute.isDisabled" v-if="attribute.datatype == 'string'" type="text" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id].value"/>
                <input class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id].value"/>
                <input class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'integer'" type="number" step="1" placeholder="0" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id].value"/>
                <input class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'boolean'" type="checkbox" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id].value"/>
                <textarea class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'stringf'" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id].value"></textarea>
                <div v-else-if="attribute.datatype == 'percentage'" class="d-flex">
                    <input class="form-control" :disabled="attribute.isDisabled" type="range" step="1" min="0" max="100" value="0" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id].value"/>
                    <span class="ml-3">{{ localValues[attribute.id].value }}%</span>
                </div>
                <div v-else-if="attribute.datatype == 'geography'">
                    <input class="form-control" :disabled="attribute.isDisabled" type="text" :id="'attribute-'+attribute.id" placeholder="Add WKT" v-model="localValues[attribute.id].value" />
                    <button type="button" class="btn btn-outline-secondary" :disabled="attribute.isDisabled" style="margin-top: 10px;" ng-click="$ctrl.openGeographyPlacer(attribute.id)">
                        <i class="fas fa-fw fa-map-marker-alt"></i> Open Map
                    </button>
                </div>
                <div v-else-if="attribute.datatype == 'context'">
                    <context-search></context-search>
                </div>
                <div class="input-group date" data-provide="datepicker" v-else-if="attribute.datatype == 'date'">
                    <input type="text" class="form-control" :disabled="attribute.isDisabled" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id].value"  ng-model-options="{timezone:'utc'}"/>
                    <div class="input-group-append input-group-addon">
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="fas fa-fw fa-calendar-alt"></i>
                        </button>
                    </div>
                </div>
                <div v-else-if="attribute.datatype == 'string-mc'">
                    <multiselect
                        :customLabel="translateLabel"
                        label="concept_url"
                        track-by="id"
                        v-model="localValues[attribute.id].value"
                        :allowEmpty="true"
                        :closeOnSelect="false"
                        :disabled="attribute.isDisabled"
                        :hideSelected="true"
                        :multiple="true"
                        :options="localSelections[attribute.id]">
                    </multiselect>
                </div>
                <div v-else-if="attribute.datatype == 'string-sc'">
                    <multiselect
                        :customLabel="translateLabel"
                        label="concept_url"
                        track-by="id"
                        v-model="localValues[attribute.id].value"
                        :allowEmpty="true"
                        :closeOnSelect="true"
                        :disabled="attribute.isDisabled"
                        :hideSelected="true"
                        :multiple="false"
                        :options="localSelections[attribute.id]">
                    </multiselect>
                </div>
                <div v-else-if="attribute.datatype == 'list'">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="btn btn-outline-secondary" :disabled="attribute.isDisabled" v-on:click="toggleList(attribute.id)">
                                <div v-show="!expands[attribute.id]">
                                    <i class="fas fa-fw fa-caret-up"></i>
                                    <span v-if="localValues[attribute.id].value && localValues[attribute.id].value.length">
                                        ({{localValues[attribute.id].value.length}})
                                    </span>
                                </div>
                                <div v-show="expands[attribute.id]">
                                    <i class="fas fa-fw fa-caret-down"></i>
                                </div>
                            </button>
                        </div>
                        <input type="text" class="form-control" :disabled="attribute.isDisabled" v-model="inputs[attribute.id]" />
                        <div class="input-group-append">
                            <button type="button" class="btn btn-success" v-on:click="addListEntry(attribute.id)">
                                <i class="fas fa-fw fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <ol class="mt-2" v-if="expands[attribute.id] && localValues[attribute.id].value && localValues[attribute.id].value.length">
                        <li v-for="(l, i) in localValues[attribute.id].value">
                            {{ l }}
                            <a href="#" class="text-danger" v-on:click="removeListEntry(attribute.id, i)">
                                <i class="fas fa-fw fa-trash"></i>
                            </a>
                        </li>
                    </ol>
                </div>
                <div v-else-if="attribute.datatype == 'epoch' && localValues[attribute.id].value">
                    <div class="input-group">
                        <div class="input-group-prepend" uib-dropdown>
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="attribute.isDisabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ localValues[attribute.id].value.startLabel }}
                            </button>
                            <ul class="dropdown-menu">
                                <a class="dropdown-item" href="">BC</a>
                                <a class="dropdown-item" href="">AD</a>
                            </ul>
                        </div>
                        <input type="number" step="1" pattern="[0-9]+" class="form-control text-center" :disabled="attribute.isDisabled" aria-label="" v-model="localValues[attribute.id].value.start">
                        <div class="input-group-prepend input-group-append">
                            <span class="input-group-text">-</span>
                        </div>
                        <input type="number" step="1" pattern="[0-9]+" class="form-control text-center" :disabled="attribute.isDisabled" aria-label="" v-model="localValues[attribute.id].value.end">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary dropdown-toggle" :disabled="attribute.isDisabled" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ localValues[attribute.id].value.endLabel }}
                            </button>
                            <ul uib-dropdown-menu class="dropdown-menu">
                                <a class="dropdown-item" href="">BC</a>
                                <a class="dropdown-item" href="">AD</a>
                            </ul>
                        </div>
                    </div>
                    <multiselect class="pt-2"
                        label="name"
                        track-by="id"
                        v-model="localValues[attribute.id].value.epoch"
                        :closeOnSelect="false"
                        :disabled="attribute.isDisabled"
                        :hideSelected="true"
                        :multiple="false"
                        :options="localSelections[attribute.id]">
                    </multiselect>
                </div>
                <div v-else-if="attribute.datatype == 'dimension'">
                    <div class="input-group">
                        <input type="number" class="form-control text-center" :disabled="attribute.isDisabled" min="0" max="9999" step="0.01" v-model="localValues[attribute.id].value" />
                        <div class="input-group-append input-group-prepend">
                            <span class="input-group-text">&times;</span>
                        </div>
                        <input type="number" class="form-control text-center" :disabled="attribute.isDisabled" min="0" max="9999" step="0.01" v-model="localValues[attribute.id].value" />
                        <div class="input-group-append input-group-prepend">
                            <span class="input-group-text">&times;</span>
                        </div>
                        <input type="number" class="form-control text-center" :disabled="attribute.isDisabled" min="0" max="9999" step="0.01" v-model="localValues[attribute.id].value" />
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary dropdown-toggle" :disabled="attribute.isDisabled" type="button"     data-toggle="dropdown" aria-haspopup="true"     aria-expanded="false">m</button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" v-for="unit in dimensionUnits">
                                    {{ unit }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="input-group">
                        <input type="number" class="form-control text-center" aria-label="" placeholder="{{'field-types.dimension.width-placeholder'|translate}}" min="0" max="9999" step="0.01" ng-model="localValues[attribute.id].value.B">
                        <span class="input-group-addon">&times;</span>
                        <input type="number" class="form-control text-center" aria-label="" placeholder="{{'field-types.dimension.height-placeholder'|translate}}" min="0" max="9999" step="0.01" ng-model="localValues[attribute.id].value.H">
                        <span class="input-group-addon">&times;</span>
                        <input type="number" class="form-control text-center" aria-label="" placeholder="{{'field-types.dimension.depth-placeholder'|translate}}" min="0" max="9999" step="0.01" ng-model="localValues[attribute.id].value.T">
                        <div class="input-group-btn" uib-dropdown>
                            <button type="button" class="btn btn-default dropdown-toggle" uib-dropdown-toggle data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ng-disabled="readonlyInput">
                                {{ localValues[attribute.id].value.unit }} <span class="caret"></span>
                            </button>
                            <ul uib-dropdown-menu class="dropdown-menu">
                                <li ng-repeat="unit in dimensionUnits"><a href="" ng-click="localValues[attribute.id].value.unit = unit">{{ unit }}</a></li>
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
                        <tr ng-repeat="row in localValues[attribute.id].value track by $index">
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
                <input class="form-control" :disabled="attribute.isDisabled" v-else type="text" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id].value"/>
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
            concepts: {
                required: true,
                type: Object
            },
            group: { // required if onReorder is set // TODO
                required: false,
                type: String
            },
            isSource: {
                required: false,
                type: Boolean,
                default: false
            },
            metadataAddon: {
                required: false,
                type: Function,
                default: () => false
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
            onMetadata: { // Sources modal
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
            selections: {
                required: true,
                type: Object
            },
            showInfo: { // shows parent on hover
                required: false,
                type: Boolean
            },
            values: {
                required: true,
                type: Object
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
            translateLabel(element, label) {
                let value = element[label];
                if(!value) return element;
                let concept = this.concepts[element[label]];
                if(!concept) return element;
                return concept.label;
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
            importedSelections: function() {
                return Object.assign({}, this.selections);
            },
            localSelections: {
                get: function() {
                    return this.importedSelections;
                },
                set function(newValue) {
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
                    opts.filter = '.disabled';
                }
                if(this.onReorder) {
                    opts.handle = '.reorder-handle';
                }
                return opts;
            }
        }
    }
</script>
