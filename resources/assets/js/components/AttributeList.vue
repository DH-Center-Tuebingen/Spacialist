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
            <div class="form-group row" :class="{'disabled not-allowed-handle': attribute.isDisabled}" v-for="(attribute, i) in localAttributes" @mouseenter="onEnter(i)" @mouseleave="onLeave(i)" v-show="!hiddenByDependency[attribute.id]">
                <label class="col-form-label col-md-3 d-flex flex-row justify-content-between" :for="'attribute-'+attribute.id" :class="{'copy-handle': isSource&&!attribute.isDisabled, 'not-allowed-handle text-muted': attribute.isDisabled}">
                    <div v-show="hoverState[i]">
                        <a v-if="onReorder" href="#" class="reorder-handle">
                            <i class="fas fa-fw fa-sort text-secondary"></i>
                        </a>
                        <button v-if="onEdit" class="btn btn-info btn-fab rounded-circle" @click="onEdit(attribute)">
                            <i class="fas fa-fw fa-xs fa-edit" style="vertical-align: 0;"></i>
                        </button>
                        <button v-if="onRemove" class="btn btn-danger btn-fab rounded-circle" @click="onRemove(attribute)">
                            <i class="fas fa-fw fa-xs fa-times" style="vertical-align: 0;"></i>
                        </button>
                        <button v-if="onDelete" class="btn btn-danger btn-fab rounded-circle" @click="onDelete(attribute)">
                            <i class="fas fa-fw fa-xs fa-trash" style="vertical-align: 0;"></i>
                        </button>
                    </div>
                    <span class="text-right col">
                        {{concepts[attribute.thesaurus_url].label}}:
                    </span>
                    <sup class="clickable" v-if="onMetadata" @click="onMetadata(attribute)">
                        <span>
                            <i class="fas fa-fw fa-exclamation"
                            :class="{
                                'text-danger': localValues[attribute.id].possibility <= 25,
                                'text-warning': localValues[attribute.id].possibility <= 50,
                                'text-info': localValues[attribute.id].possibility <= 75,
                                'text-success': localValues[attribute.id].possibility > 75 || (!localValues[attribute.id].possibility && localValues[attribute.id].possibility !== 0)
                                }"></i>
                            </span>
                            <span v-if="localValues[attribute.id].possibility_description">
                                <i class="fas fa-fw fa-comment"></i>
                            </span>
                            <span v-if="metadataAddon(attribute.thesaurus_url)">
                                <i class="fas fa-fw fa-bookmark"></i>
                            </span>
                    </sup>
                </label>
                <div class="col-md-9">
                    <input class="form-control" :disabled="attribute.isDisabled" v-if="attribute.datatype == 'string'" type="text" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value" @blur="checkDependency(attribute.id)"/>
                    <input class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value"/>
                    <input class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'integer'" type="number" step="1" placeholder="0" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value"/>
                    <input class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'boolean'" type="checkbox" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value"/>
                    <textarea class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'stringf'" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value"></textarea>
                    <div v-else-if="attribute.datatype == 'percentage'" class="d-flex">
                        <input class="custom-range" :disabled="attribute.isDisabled" type="range" step="1" min="0" max="100" value="0" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value"/>
                        <span class="ml-3">{{ localValues[attribute.id].value }}%</span>
                    </div>
                    <div v-else-if="attribute.datatype == 'geography'">
                        <input class="form-control" :disabled="attribute.isDisabled" type="text" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" placeholder="Add WKT" v-model="localValues[attribute.id].value" />
                        <button type="button" class="btn btn-outline-secondary mt-2" :disabled="attribute.isDisabled" @click="openGeographyModal(attribute.id)">
                            <i class="fas fa-fw fa-map-marker-alt"></i> Open Map
                        </button>
                    </div>
                    <!-- TODO: dirty checking -->
                    <div v-else-if="attribute.datatype == 'context'">
                        <context-search></context-search>
                    </div>
                    <div class="input-group date" data-provide="datepicker" v-else-if="attribute.datatype == 'date'">
                        <input type="text" class="form-control" :disabled="attribute.isDisabled" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value"  ng-model-options="{timezone:'utc'}"/>
                        <div class="input-group-append input-group-addon">
                            <button type="button" class="btn btn-outline-secondary">
                                <i class="fas fa-fw fa-calendar-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div v-else-if="attribute.datatype == 'string-mc'">
                        <multiselect
                            label="concept_url"
                            track-by="id"
                            v-model="localValues[attribute.id].value"
                            :allowEmpty="true"
                            :closeOnSelect="false"
                            :customLabel="translateLabel"
                            :disabled="attribute.isDisabled"
                            :hideSelected="true"
                            :multiple="true"
                            :options="localSelections[attribute.id] || []"
                            :name="'attribute-'+attribute.id"
                            v-validate="">
                        </multiselect>
                    </div>
                    <div v-else-if="attribute.datatype == 'string-sc'">
                        <multiselect
                            label="concept_url"
                            track-by="id"
                            v-model="localValues[attribute.id].value"
                            :allowEmpty="true"
                            :closeOnSelect="true"
                            :customLabel="translateLabel"
                            :disabled="attribute.isDisabled"
                            :hideSelected="true"
                            :multiple="false"
                            :options="localSelections[attribute.id] || []"
                            :name="'attribute-'+attribute.id"
                            v-validate="">
                        </multiselect>
                    </div>
                    <!-- TODO: validation/dirty checking -->
                    <div v-else-if="attribute.datatype == 'list'">
                        <list :entries="localValues[attribute.id].value" :disabled="attribute.isDisabled" :on-change="value => onChange(null, value, attribute.id)" :name="'attribute-'+attribute.id" v-validate="" />
                    </div>
                    <div v-else-if="attribute.datatype == 'epoch'">
                        <epoch :name="'attribute-'+attribute.id" :on-change="(field, value) => onChange(field, value, attribute.id)" :value="localValues[attribute.id].value" :concepts="concepts" :epochs="localSelections[attribute.id]" :disabled="attribute.isDisabled" v-validate=""/>
                    </div>
                    <div v-else-if="attribute.datatype == 'dimension'">
                        <dimension :name="'attribute-'+attribute.id" :on-change="(field, value) => onChange(field, value, attribute.id)" :value="localValues[attribute.id].value" :disabled="attribute.isDisabled" v-validate=""/>
                    </div>
                    <!-- TODO: validation/dirty checking -->
                    <div v-else-if="attribute.datatype == 'table'">
                        <table class="table table-striped table-hovered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th v-for="columnNames in attribute.columns">
                                        {{ concepts[columnNames.thesaurus_url].label }}
                                    </th>
                                    <th>
                                        Delete
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, $index) in localValues[attribute.id].value">
                                    <td v-for="col in row">
                                        <input class="form-control form-control-sm" v-if="col.datatype == 'string'" type="text" v-model="col.value"/>
                                        <input class="form-control form-control-sm" v-else-if="col.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" v-model="col.value"/>
                                        <input class="form-control form-control-sm" v-else-if="col.datatype == 'integer'" type="number" step="1" placeholder="0" v-model="col.value"/>
                                        <input class="form-control form-control-sm" v-else-if="col.datatype == 'boolean'" type="checkbox" v-model="col.value"/>
                                        <div v-else-if="col.datatype == 'string-sc'">
                                            <multiselect
                                                class="multiselect-sm"
                                                label="concept_url"
                                                track-by="id"
                                                v-model="col.value"
                                                :allowEmpty="true"
                                                :closeOnSelect="true"
                                                :customLabel="translateLabel"
                                                :disabled="attribute.isDisabled"
                                                :hideSelected="true"
                                                :multiple="false"
                                                :options="localSelections[col.attribute_id] || []">
                                            </multiselect>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" @click="deleteTableRow(attribute.id, $index)">
                                            <i class="fas fa-fw fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td v-for="columnNames in attribute.columns">
                                        <input class="form-control form-control-sm" v-if="columnNames.datatype == 'string'" type="text" v-model="newTableCols[attribute.id][columnNames.id]"/>
                                        <input class="form-control form-control-sm" v-else-if="columnNames.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" v-model="newTableCols[attribute.id][columnNames.id]"/>
                                        <input class="form-control form-control-sm" v-else-if="columnNames.datatype == 'integer'" type="number" step="1" placeholder="0" v-model="newTableCols[attribute.id][columnNames.id]"/>
                                        <input class="form-control form-control-sm" v-else-if="columnNames.datatype == 'boolean'" type="checkbox" v-model="newTableCols[attribute.id][columnNames.id]"/>
                                        <div v-else-if="columnNames.datatype == 'string-sc'">
                                            <multiselect
                                                class="multiselect-sm"
                                                label="concept_url"
                                                track-by="id"
                                                v-model="newTableCols[attribute.id][columnNames.id]"
                                                :allowEmpty="true"
                                                :closeOnSelect="true"
                                                :customLabel="translateLabel"
                                                :disabled="attribute.isDisabled"
                                                :hideSelected="true"
                                                :multiple="false"
                                                :options="localSelections[columnNames.id] || []">
                                            </multiselect>
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" @click="addTableRow(attribute.id, newTableCols[attribute.id], attribute.columns)">
                                            <i class="fas fa-fw fa-plus"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else-if="attribute.datatype == 'sql'">
                        <div v-if="isArray(localValues[attribute.id].value)">
                            <div class="table-responsive">
                                <table class="table table-striped table-hovered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th v-for="(columnNames, index) in localValues[attribute.id].value[0]">
                                                {{ getConceptLabel(index) }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, $index) in localValues[attribute.id].value">
                                            <td v-for="column in row">
                                                {{ getConceptLabel(column) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div v-else>
                            {{ localValues[attribute.id].value }}
                        </div>
                    </div>
                    <input class="form-control" :disabled="attribute.isDisabled" v-else type="text" :id="'attribute-'+attribute.id" v-model="localValues[attribute.id].value"  :name="'attribute-'+attribute.id" v-validate="" @blur="checkDependency(attribute.id)"/>
                </div>
            </div>
        </draggable>
        <modal :name="'geography-place-modal-'+uniqueId" width="80%" height="80%">
            <div class="modal-content h-100">
                <div class="modal-header">
                    <h5 class="modal-title">Set Geolocation</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideGeographyModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body d-flex flex-column">
                    <ol-map class="h-100"
                        :init-wkt="initialGeoValues"
                        :on-deleteend="onGeoFeaturesDeleted"
                        :on-drawend="onGeoFeatureAdded"
                        :on-modifyend="onGeoFeaturesUpdated"
                        :reset="true">
                    </ol-map>
                    <div class="mt-2">
                        WKT: <pre class="m-0"><code>{{ newGeoValue }}</code></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-success"     @click="setGeography">
                        Set
                    </button>
                    <button type="button" class="btn btn-outline-secondary"     @click="hideGeographyModal">
                        Close
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    import draggable from 'vuedraggable';
    import { mapFields } from 'vee-validate';
    Vue.component('dimension', require('./Dimension.vue'));
    Vue.component('epoch', require('./Epoch.vue'));
    Vue.component('list', require('./List.vue'));

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
            dependencies: {
                required: false,
                type: Object,
                default: _ => new Object()
            },
            disableDrag: {
                required: false,
                type: Boolean,
                default: false
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
        inject: ['$validator'],
        mounted() {},
        methods: {
            onEnter(i) {
                Vue.set(this.hovered, i, this.hoverEnabled);
            },
            onLeave(i) {
                Vue.set(this.hovered, i, false);
            },
            onChange(field, value, aid) {
                if (this.localValues[aid].value){
                    if (!field) {
                        this.localValues[aid].value = [];
                        value.forEach(v => this.localValues[aid].value.push(v));
                    } else {
                        this.localValues[aid].value[field] = value;
                    }
                }
            },
            addTableRow(aid, row, columns) {
                if(!this.localValues[aid].value) {
                    this.localValues[aid].value = [];
                }
                let cpy = Object.assign({}, row);
                let addRow = {};
                columns.forEach((c, i) => {
                    let newColumn = {};
                    newColumn.datatype = c.datatype;
                    newColumn.attribute_id = c.id;
                    newColumn.value = cpy[c.id];
                    addRow[i] = newColumn;
                });
                this.localValues[aid].value.push(addRow);
                // Reset inputs
                for(let k in row) delete row[k];
                // TODO set form dirty
            },
            checkDependency(aid) {
                if(!this.dependencies) return;
                if(!this.dependencies[aid]) return;
                let hides = {};
                const deps = this.dependencies[aid];
                deps.forEach(d => {
                    // return = continue in forEach
                    if(hides[d.dependant]) return;
                    hides[d.dependant] = this.evalDependency(this.localValues[aid].value, d.operator, d.value);
                });
                for(let k in hides) {
                    this.hiddenByDependency[k] = hides[k];
                }
            },
            evalDependency(attrValue, operator, depValue) {
                switch(operator) {
                    case '<':
                        return attrValue < depValue;
                    case '>':
                        return attrValue > depValue;
                    case '=':
                        return attrValue == depValue;
                }
                return false;
            },
            deleteTableRow(aid, index) {
                this.localValues[aid].value.splice(index, 1);
                // TODO set form dirty
            },
            openGeographyModal(aid) {
                this.currentGeoValue = this.newGeoValue = this.localValues[aid].value;
                if(this.currentGeoValue) {
                    this.initialGeoValues = [
                        this.currentGeoValue
                    ];
                } else {
                    this.initialGeoValues = [];
                }
                this.selectedAttribute = aid;
                this.$modal.show('geography-place-modal-'+this.uniqueId);
            },
            hideGeographyModal() {
                this.$modal.hide('geography-place-modal-'+this.uniqueId);
            },
            onGeoFeaturesDeleted(features, wkt) {
                // We only have one possible feature.
                // Thus, if at least one gets deleted,
                // we reset newGeoValue
                if(wkt.length) {
                    this.newGeoValue = '';
                }
            },
            onGeoFeatureAdded(feature, wkt) {
                this.newGeoValue = wkt;
            },
            onGeoFeaturesUpdated(features, wkt) {
                // We only have one possible feature.
                // Thus, if at least one is modified,
                // it has to be our current and we update it
                if(wkt.length) {
                    this.newGeoValue = wkt[0];
                }
            },
            setGeography() {
                if(!!this.localValues[this.selectedAttribute]) {
                    this.localValues[this.selectedAttribute].value = this.newGeoValue;
                }
                this.hideGeographyModal();
                this.currentGeoValue = undefined;
                this.newGeoValue = undefined;
                this.selectedAttribute = -1;
            },
            translateLabel(element, prop) {
                const value = element[prop];
                if(!value) return element;
                return this.getConceptLabel(value);
            },
            getConceptLabel(url)  {
                if(!url) return url;
                const concept = this.concepts[url];
                if(!concept) return url;
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
            },
            isArray(arr) {
                return Array.isArray(arr);
            },
            isDirty(fieldname) {
                if(this.fields[fieldname]) {
                    return this.fields[fieldname].dirty;
                }
                return false;
            }
        },
        data() {
            return {
                hiddenByDependency: {},
                hovered: [],
                uniqueId: Math.random().toString(36),
                selectedAttribute: -1,
                initialGeoValues: [],
                currentGeoValue: '',
                newGeoValue: ''
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
            newTableCols: function() {
                let cols = {};
                this.localAttributes.forEach((attribute) => {
                    if(attribute.datatype == 'table') {
                        cols[attribute.id] = {};
                    }
                });
                return cols;
            },
            hoverState: function() {
                return this.hovered;
            },
            dragOpts: function() {
                let opts = {};
                if(this.disableDrag) {
                    opts.disabled = true;
                    return opts;
                }
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
            },
            hoverEnabled: function() {
                return this.onReorder || this.onEdit  || this.onRemove || this.onDelete;
            }
        }
    }
</script>
