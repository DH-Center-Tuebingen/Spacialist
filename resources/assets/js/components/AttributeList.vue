<template>
    <div>
        <div class="form-group row" v-for="attribute in attributes" @mouseenter="onEnter(attribute.id)" @mouseleave="onLeave(attribute.id)">
            <label class="control-label col-md-3 d-flex justify-content-end" :for="'attribute-'+attribute.id">
                <!-- <div style="display: table; float: right;">
                    <span style="display: table-cell">
                        {{ concepts[attribute.thesaurus_url].label }}:
                    </span>
                    <sup ui-sref="root.spacialist.context.data.sources({aid: attribute.id})" style="display: table-cell" class="source-link">
                        <i class="material-icons md-18" aria-hidden="true" ng-if="!readonlyInput">stars</i><i ng-if="(attributeSources | filter:{attribute_id:attribute.id}).length > 0 || values[attribute.id+'_cert'] < 100 || values[attribute.id+'_desc'].length > 0" class="material-icons md-18 material-addon fa-limegreen">fiber_manual_record</i>
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
                    <div v-if="allowOrder">
                        <a class="btn btn-outline-secondary btn-sm p-1" href="" v-if="!$first">
                            <i class="fas fa-fw fa-sort-up"></i>
                        </a>
                        <a class="btn btn-outline-secondary btn-sm p-1" href="" v-if="!$last">
                            <i class="fas fa-fw fa-sort-down"></i>
                        </a>
                    </div>
                </div> -->
                <span>
                    {{attribute.thesaurus_url}}:
                </span>
                <sup>
                    <i class="fas fa-fw fa-lg fa-dot-circle"></i>
                </sup>
            </label>
            <div class="col-md-9">
                <input class="form-control" v-if="attribute.datatype == 'string'" type="text" :id="'attribute-'+attribute.id" v-model="values[attribute.id]"/>
                <input class="form-control" v-else-if="attribute.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" :id="'attribute-'+attribute.id" v-model="values[attribute.id]"/>
                <input class="form-control" v-else-if="attribute.datatype == 'integer'" type="number" step="1" placeholder="0" :id="'attribute-'+attribute.id" v-model="values[attribute.id]"/>
                <input class="form-control" v-else-if="attribute.datatype == 'boolean'" type="checkbox" :id="'attribute-'+attribute.id" v-model="values[attribute.id]"/>
                <textarea class="form-control" v-else-if="attribute.datatype == 'stringf'" :id="'attribute-'+attribute.id" v-model="values[attribute.id]"></textarea>
                <div v-else-if="attribute.datatype == 'percentage'">
                    <input class="form-control slider" type="range" step="1" min="0" max="100" value="0" :id="'attribute-'+attribute.id" v-model="values[attribute.id]"/>
                    <span class="slider-text">{{ values[attribute.id] }}%</span>
                </div>
                <div v-else-if="attribute.datatype == 'geography'">
                    <input class="form-control" type="text" :id="'attribute-'+attribute.id" placeholder="Add WKT" v-model="values[attribute.id]" />
                    <button type="button" class="btn btn-default" style="margin-top: 10px;" ng-click="$ctrl.openGeographyPlacer(attribute.id)">
                        <i class="fas fa-fw fa-map-marker-alt"></i> Open Map
                    </button>
                </div>
                <div v-else-if="attribute.datatype == 'context'">
                    <!-- TODO typeahead -->
                    <input class="form-control" type="text" :id="'attribute-'+attribute.id" placeholder="Add Context" v-model="values[attribute.id]" />
                </div>
                <div class="input-group" v-else-if="attribute.datatype == 'date'">
                    <!-- TODO datepicker -->
                    <input type="text" class="form-control" :id="'attribute-'+attribute.id" v-model="values[attribute.id]"  ng-model-options="{timezone:'utc'}"/>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary">
                            <i class="fas fa-fw fa-calendar-alt"></i>
                        </button>
                    </div>
                </div>
                <div v-else-if="attribute.datatype == 'string-mc'">
                    <multiselect v-model="values[attribute.id]" :options="[]" label="name" :multiple="true" :hideSelected="true" :closeOnSelect="false"></multiselect>
                </div>
                <div v-else-if="attribute.datatype == 'string-sc'">
                    <multiselect v-model="values[attribute.id]" :options="[]" label="name" :multiple="false" :hideSelected="true" :allowEmpty="true" :closeOnSelect="false"></multiselect>
                </div>
                <div v-else-if="attribute.datatype == 'list'">
                    list
                    <!-- <div class="input-group">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" ng-click="hiddenLists[attribute.id] = !hiddenLists[attribute.id]" ng-init="hiddenLists[attribute.id] = true">
                                <i class="material-icons" ng-show="hiddenLists[attribute.id]">arrow_drop_down</i>
                                <i class="material-icons" ng-hide="hiddenLists[attribute.id]">arrow_drop_up</i>
                            </button>
                        </span>
                        <input class="form-control" type="text" id="{{ attribute.id }}" ng-model="listInput[attribute.id]" placeholder="{{'field-types.list.new-placeholder'|translate}}"/>
                        <span class="input-group-btn">
                            <button class="btn btn-success" type="button" ng-click="addListEntry(attribute.id, listInput)" ng-disabled="!listInput[attribute.id] || readonlyInput">
                                <i class="material-icons">add</i>
                            </button>
                        </span>
                    </div>
                    <div ng-hide="hiddenLists[attribute.id] || !values[attribute.id] ||  values[attribute.id].length == 0" class="col-md-12 hideable-list">
                        <ol class="list-group inline-list">
                            <li ng-repeat="li in values[attribute.id] track by $index" class="list-group-item animated-item" ng-mouseenter="hovered = true;" ng-mouseleave="hovered = false;">
                                <span class="delete-icon" ng-show="hovered && !editEntry[attribute.id][$index]" ng-click="deleteListItem(attribute.id, $index)">
                                    <i class="material-icons">delete</i>
                                </span>
                                <span ng-hide="editEntry[attribute.id][$index]" ng-click="editListEntry(attribute.id, attribute.oid, $index, li)">{{ li.name }}</span>
                                <div class="item-edit-tab" ng-show="editEntry[attribute.id][$index]">
                                    <input type="text" ng-model="values[attribute.id][$index].name" class="list-item-edit" />
                                    <button type="button" class="btn btn-success" ng-click="storeEditListEntry()"><i class="material-icons">check</i></button>
                                    <button type="button" class="btn btn-danger" ng-click="cancelEditListEntry()"><i class="material-icons">clear</i></button>
                                </div>
                            </li>
                        </ol>
                    </div> -->
                </div>
                <div v-else-if="attribute.datatype == 'epoch' && values[attribute.id]">
                    epoch
                    <div class="input-group">
                        <div class="input-group-btn" uib-dropdown>
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ values[attribute.id].startLabel }}
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
                        <input type="number" step="1" pattern="[0-9]+" class="form-control centered" aria-label="" v-model="values[attribute.id].start">
                        <span class="input-group-addon">-</span>
                        <input type="number" step="1" pattern="[0-9]+" class="form-control centered" aria-label="" v-model="values[attribute.id].end">
                        <div class="input-group-btn">
                            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ values[attribute.id].endLabel }}
                            </button>
                            <ul uib-dropdown-menu class="dropdown-menu">
                                <li><a href="">BC</a></li>
                                <li><a href="">AD</a></li>
                            </ul>
                        </div>
                    </div>
                    <multiselect v-model="values[attribute.id].epoch" :options="[]" label="name" :multiple="false" :hideSelected="true" :closeOnSelect="false"></multiselect>
                </div>
                <div v-else-if="attribute.datatype == 'dimension'">
                    dimension
                    <!-- <div class="input-group">
                        <input type="number" class="form-control centered" aria-label="" placeholder="{{'field-types.dimension.width-placeholder'|translate}}" min="0" max="9999" step="0.01" ng-model="values[attribute.id].B">
                        <span class="input-group-addon">&times;</span>
                        <input type="number" class="form-control centered" aria-label="" placeholder="{{'field-types.dimension.height-placeholder'|translate}}" min="0" max="9999" step="0.01" ng-model="values[attribute.id].H">
                        <span class="input-group-addon">&times;</span>
                        <input type="number" class="form-control centered" aria-label="" placeholder="{{'field-types.dimension.depth-placeholder'|translate}}" min="0" max="9999" step="0.01" ng-model="values[attribute.id].T">
                        <div class="input-group-btn" uib-dropdown>
                            <button type="button" class="btn btn-default dropdown-toggle" uib-dropdown-toggle data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" ng-disabled="readonlyInput">
                                {{ values[attribute.id].unit }} <span class="caret"></span>
                            </button>
                            <ul uib-dropdown-menu class="dropdown-menu">
                                <li ng-repeat="unit in dimensionUnits"><a href="" ng-click="values[attribute.id].unit = unit">{{ unit }}</a></li>
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
                        <tr ng-repeat="row in values[attribute.id] track by $index">
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
                <input class="form-control" v-else type="text" :id="'attribute-'+attribute.id" v-model="values[attribute.id]"/>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['attributes', 'values', 'allowEdit', 'showInfo', 'allowDelete', 'allowMetadata', 'allowOrder'],
        mounted() {},
        methods: {
            onEnter(id) {
                Vue.set(this.$data.hovered, id, true);
            },
            onLeave(id) {
                Vue.set(this.$data.hovered, id, true);
            }
        },
        data() {
            return {
                hovered: {}
            }
        },
        // computed: {
        //     hovered: function() {
        //         let hoverStates = {};
        //         for(var i=0; i<this.attributes.length; i++) {
        //             hoverStates[i] = true;
        //         }
        //         return hoverStates;
        //     }
        // }
    }
</script>
