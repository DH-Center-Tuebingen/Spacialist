<template>
    <div>
        <draggable
            class="h-100"
            v-model="localAttributes"
            :class="{'drag-container': !disableDrag}"
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
                        {{ $translateConcept(attribute.thesaurus_url) }}:
                    </span>
                    <sup class="clickable" v-if="onMetadata" @click="onMetadata(attribute)">
                        <span>
                        <i class="fas fa-fw fa-exclamation"
                        :class="getPossibilityClass(localValues[attribute.id].possibility)"></i>
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
                    <input class="form-control" :disabled="attribute.isDisabled" v-if="attribute.datatype == 'string'" type="text" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value" @blur="checkDependency(attribute.id)" />
                    <input class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'double'" type="number" step="any" min="0" placeholder="0.0" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value" @blur="checkDependency(attribute.id)" />
                    <input class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'integer'" type="number" step="1" placeholder="0" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value" @blur="checkDependency(attribute.id)" />
                    <input class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'boolean'" type="checkbox" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value" @change="checkDependency(attribute.id)" />
                    <textarea class="form-control" :disabled="attribute.isDisabled" v-else-if="attribute.datatype == 'stringf'" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value" @blur="checkDependency(attribute.id)"></textarea>
                    <div v-else-if="attribute.datatype == 'percentage'" class="d-flex">
                        <input class="custom-range" :disabled="attribute.isDisabled" type="range" step="1" min="0" max="100" value="0" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" v-model="localValues[attribute.id].value" @mouseup="checkDependency(attribute.id)"/>
                        <span class="ml-3">{{ localValues[attribute.id].value }}%</span>
                    </div>
                    <div v-else-if="attribute.datatype == 'geography'">
                        <input class="form-control" :disabled="attribute.isDisabled" type="text" :id="'attribute-'+attribute.id" :name="'attribute-'+attribute.id" v-validate="" :placeholder="$t('main.entity.attributes.add-wkt')" v-model="localValues[attribute.id].value" @blur="checkDependency(attribute.id)" />
                        <button type="button" class="btn btn-outline-secondary mt-2" :disabled="attribute.isDisabled" @click="openGeographyModal(attribute.id)">
                            <i class="fas fa-fw fa-map-marker-alt"></i> {{ $t('main.entity.attributes.open-map') }}
                        </button>
                    </div>
                    <!-- TODO: dirty checking -->
                    <div v-else-if="attribute.datatype == 'context'">
                        <context-search
                            v-validate=""
                            :id="'attribute-'+attribute.id"
                            :name="'attribute-'+attribute.id"
                            :on-select="selection => setContextSearchResult(selection, attribute.id)"
                            :value="localValues[attribute.id].name">
                        </context-search>
                    </div>
                    <v-date-picker
                        mode="single"
                        v-else-if="attribute.datatype == 'date'"
                        v-model="localValues[attribute.id].value"
                        v-validate=""
                        :max-date="new Date()"
                        :name="'attribute-'+attribute.id"
                        @input="updateDatepicker(attribute.id, 'attribute-'+attribute.id)">
                        <div class="input-group date" slot-scope="{ inputValue, updateValue }">
                            <input type="text" class="form-control" :disabled="attribute.isDisabled" :id="'attribute-'+attribute.id" :value="inputValue" @input="updateValue($event.target.value, { formatInput: false, hidePopover: false })" @change="updateValue($event.target.value, { formatInput: true, hidePopover: false }) "/>
                            <div class="input-group-append input-group-addon">
                                <button type="button" class="btn btn-outline-secondary">
                                    <i class="fas fa-fw fa-calendar-alt"></i>
                                </button>
                            </div>
                        </div>
                    </v-date-picker>
                    <div v-else-if="attribute.datatype == 'string-mc'">
                        <multiselect
                            label="concept_url"
                            track-by="id"
                            v-model="localValues[attribute.id].value"
                            v-validate=""
                            :allowEmpty="true"
                            :closeOnSelect="false"
                            :customLabel="translateLabel"
                            :disabled="attribute.isDisabled"
                            :hideSelected="true"
                            :multiple="true"
                            :options="localSelections[attribute.id] || []"
                            :name="'attribute-'+attribute.id"
                            :placeholder="$t('global.select.select')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')"
                            @input="(value, id) => checkDependency(attribute.id)">
                        </multiselect>
                    </div>
                    <div v-else-if="attribute.datatype == 'string-sc'">
                        <multiselect
                            label="concept_url"
                            track-by="id"
                            v-model="localValues[attribute.id].value"
                            v-validate=""
                            :allowEmpty="true"
                            :closeOnSelect="true"
                            :customLabel="translateLabel"
                            :disabled="attribute.isDisabled"
                            :hideSelected="true"
                            :multiple="false"
                            :options="localSelections[attribute.id] || []"
                            :name="'attribute-'+attribute.id"
                            :placeholder="$t('global.select.select')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')"
                            @input="(value, id) => checkDependency(attribute.id)">
                        </multiselect>
                    </div>
                    <div v-else-if="attribute.datatype == 'list'">
                        <list :entries="localValues[attribute.id].value" :disabled="attribute.isDisabled" :on-change="value => onChange(null, value, attribute.id)" :name="'attribute-'+attribute.id" v-validate="" />
                    </div>
                    <div v-else-if="attribute.datatype == 'epoch'">
                        <epoch :name="'attribute-'+attribute.id" :on-change="(field, value) => onChange(field, value, attribute.id)" :value="localValues[attribute.id].value" :epochs="localSelections[attribute.id]" :disabled="attribute.isDisabled" v-validate=""/>
                    </div>
                    <div v-else-if="attribute.datatype == 'dimension'">
                        <dimension :name="'attribute-'+attribute.id" :on-change="(field, value) => onChange(field, value, attribute.id)" :value="localValues[attribute.id].value" :disabled="attribute.isDisabled" v-validate=""/>
                    </div>
                    <div v-else-if="attribute.datatype == 'table'">
                        <tabular :name="'attribute-'+attribute.id" :on-change="(field, value) => onChange(field, value, attribute.id)" :value="localValues[attribute.id].value" :selections="localSelections" :attribute="attribute" :disabled="attribute.isDisabled" v-validate=""/>
                    </div>
                    <div v-else-if="attribute.datatype == 'sql'">
                        <div v-if="isArray(localValues[attribute.id].value)">
                            <div class="table-responsive">
                                <table class="table table-striped table-hovered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th v-for="(columnNames, index) in localValues[attribute.id].value[0]">
                                                {{ $translateConcept(index) }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(row, $index) in localValues[attribute.id].value">
                                            <td v-for="column in row">
                                                {{ $translateConcept(column) }}
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
                    <h5 class="modal-title">{{ $t('main.entity.attributes.set-location') }}</h5>
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
                    <button type="button" class="btn btn-outline-success"     @click="setGeography('attribute-'+selectedAttribute)">
                        {{ $t('global.set') }}
                    </button>
                    <button type="button" class="btn btn-outline-secondary"     @click="hideGeographyModal">
                        {{ $t('global.close') }}
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
    Vue.component('tabular', require('./Tabular.vue'))

    export default {
        props: {
            attributes: {
                required: true,
                type: Array
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
                if(this.localValues[aid].value){
                    if(field == null) {
                        this.localValues[aid].value = [];
                        value.forEach(v => this.localValues[aid].value.push(v));
                    } else {
                        this.localValues[aid].value[field] = value;
                    }
                }
                this.checkDependency(aid);
            },
            updateDatepicker(aid, fieldname) {
                const vm = this;
                vm.correctTimezone(aid);
                vm.fields[fieldname].dirty = true;
                vm.checkDependency(aid);
            },
            correctTimezone(aid) {
                const vm = this;
                const dtVal = vm.localValues[aid].value;
                const offset = dtVal.getTimezoneOffset() * (-1);
                let ms = Date.parse(dtVal.toUTCString());
                ms += (offset * 60 * 1000);
                vm.localValues[aid].value = new Date(ms);
            },
            setContextSearchResult(result, aid) {
                if(result) {
                    this.localValues[aid].value = result.id;
                } else {
                    this.localValues[aid].value = undefined;
                }
                this.fields[`attribute-${aid}`].dirty = true;
                this.checkDependency(aid);
            },
            getPossibilityClass(certainty, aid) {
                let activeClasses = [];
                if(certainty <= 25) {
                    activeClasses.push('text-danger');
                } else if(certainty <= 50) {
                    activeClasses.push('text-warning');
                } else if(certainty <= 75) {
                    activeClasses.push('text-info');
                } else {
                    activeClasses.push('text-success');
                }
                return activeClasses;
            },
            checkDependency(aid) {
                if(!this.dependencies) return;
                if(!this.dependencies[aid]) return;
                let hides = {};
                const deps = this.dependencies[aid];
                deps.forEach(d => {
                    // return = continue in forEach
                    if(hides[d.dependant]) return;
                    hides[d.dependant] = this.evalDependency(this.localValues[aid], d.operator, d.value);
                });
                for(let k in hides) {
                    this.hiddenByDependency[k] = hides[k];
                }
            },
            evalDependency(attrValue, operator, depValue) {
                const attr = this.localAttributes.find(function(a) {
                    return a.id == attrValue.attribute_id;
                });
                if(!attr) return false;
                switch(attr.datatype) {
                    case 'string-mc':
                        for(let i=0; i<attrValue.value.length; i++) {
                            const v = attrValue.value[i];
                            if(this.evalEquation(v.concept_url, depValue, operator)) {
                                return true;
                            }
                        }
                        return false;
                        break;
                    case 'string-sc':
                        return this.evalEquation(attrValue.value.concept_url, depValue, operator);
                        break;
                    default:
                        return this.evalEquation(attrValue.value, depValue, operator);
                        break;
                }
            },
            evalEquation(a, b, op) {
                switch(op) {
                    case '<':
                        return a < b;
                    case '>':
                        return a > b;
                    case '=':
                        return a == b;
                }
                return false;
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
            setGeography(fieldname) {
                if(!!this.localValues[this.selectedAttribute]) {
                    this.localValues[this.selectedAttribute].value = this.newGeoValue;
                    this.fields[fieldname].dirty = true;
                }
                this.hideGeographyModal();
                this.currentGeoValue = undefined;
                this.newGeoValue = undefined;
                this.selectedAttribute = -1;
            },
            translateLabel(element, prop) {
                return this.$translateLabel(element, prop);
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
