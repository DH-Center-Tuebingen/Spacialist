<template>
    <div class="h-100 d-flex flex-column">
        <h4>{{ $t('main.datamodel.detail.properties.title') }}</h4>
        <div v-if="entityType.id" class="col d-flex flex-column">
            <form role="form" v-on:submit.prevent="updateContextType">
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-right">{{ $t('main.datamodel.detail.properties.top-level') }}</label>
                    <div class="col-md-9">
                        <input type="checkbox" v-model="entityType.is_root" />
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3 text-right">{{ $t('main.datamodel.detail.properties.sub-types') }}</label>
                    <div class="col-md-9">
                        <multiselect
                            label="thesaurus_url"
                            track-by="id"
                            v-model="entityType.sub_context_types"
                            :allowEmpty="true"
                            :closeOnSelect="false"
                            :customLabel="translateLabel"
                            :hideSelected="true"
                            :multiple="true"
                            :options="minimalContextTypes"
                            :placeholder="$t('global.select.select')"
                            :select-label="$t('global.select.select')"
                            :deselect-label="$t('global.select.deselect')">
                        </multiselect>
                        <div class="pt-2">
                            <button type="button" class="btn btn-outline-success mr-2" @click="addAllContextTypes">
                                <i class="fas fa-fw fa-tasks"></i> {{ $t('global.select-all') }}
                            </button>
                            <button type="button" class="btn btn-outline-danger" @click="removeAllContextTypes">
                                <i class="fas fa-fw fa-times"></i> {{ $t('global.select-none') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-form-label col-md-3"></label>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-fw fa-save"></i> {{ $t('global.save') }}
                        </button>
                    </div>
                </div>
                <hr />
            </form>
            <h4>{{ $t('main.datamodel.detail.attribute.title') }}</h4>
            <attributes
                class="col scroll-y-auto"
                group="attributes"
                :attributes="entityAttributes"
                :values="entityValues"
                :selections="entitySelections"
                :on-add="addAttributeToContextType"
                :on-edit="onEditContextAttribute"
                :on-remove="onRemoveAttributeFromContextType"
                :on-reorder="reorderContextAttribute"
                :show-info="true">
            </attributes>
        </div>

        <modal name="edit-context-attribute-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="openedModal == 'edit-context-attribute-modal'">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('global.edit-name.title', {name: $translateConcept(modalSelectedAttribute.thesaurus_url)}) }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideEditContextAttributeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="editContextAttributeForm" name="editContextAttributeForm" role="form" v-on:submit.prevent="editContextAttribute(modalSelectedAttribute, selectedDependency)">
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                {{ $t('global.label') }}:
                            </label>
                            <div class="col-md-9">
                                <input type="text" class="form-control-plaintext" :value="$translateConcept(modalSelectedAttribute.thesaurus_url)" readonly />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                {{ $t('global.type') }}:
                            </label>
                            <div class="col-md-9">
                                <input type="text" class="form-control-plaintext" :value="modalSelectedAttribute.datatype" readonly />
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">
                                {{ $t('global.depends-on') }}:
                            </label>
                            <div class="col-md-9">
                                <multiselect
                                    class="mb-2"
                                    label="thesaurus_url"
                                    track-by="id"
                                    v-model="selectedDependency.attribute"
                                    :allowEmpty="true"
                                    :closeOnSelect="true"
                                    :customLabel="translateLabel"
                                    :hideSelected="false"
                                    :multiple="false"
                                    :options="depends.attributes"
                                    :placeholder="$t('global.select.select')"
                                    :select-label="$t('global.select.select')"
                                    :deselect-label="$t('global.select.deselect')"
                                    @input="dependencyAttributeSelected">
                                </multiselect>
                                <multiselect
                                    class="mb-2"
                                    label="id"
                                    track-by="id"
                                    v-if="selectedDependency.attribute && selectedDependency.attribute.id"
                                    v-model="selectedDependency.operator"
                                    :allowEmpty="true"
                                    :closeOnSelect="true"
                                    :hideSelected="false"
                                    :multiple="false"
                                    :options="dependencyOperators"
                                    :placeholder="$t('global.select.select')"
                                    :select-label="$t('global.select.select')"
                                    :deselect-label="$t('global.select.deselect')">
                                </multiselect>
                                <div v-if="selectedDependency.attribute && selectedDependency.attribute.id">
                                    <input type="checkbox" class="form-check-input" v-if="dependencyType == 'boolean'" v-model="selectedDependency.value" />
                                    <input type="number" class="form-control" step="1" v-else-if="dependencyType == 'integer'" v-model="selectedDependency.value" />
                                    <input type="number" class="form-control" step="0.01" v-else-if="dependencyType == 'double'" v-model="selectedDependency.value" />
                                    <multiselect
                                        label="concept_url"
                                        track-by="id"
                                        v-else-if="dependencyType == 'select'"
                                        v-model="selectedDependency.value"
                                        :allowEmpty="true"
                                        :closeOnSelect="true"
                                        :customLabel="translateLabel"
                                        :hideSelected="false"
                                        :multiple="false"
                                        :options="depends.values"
                                        :placeholder="$t('global.select.select')"
                                        :select-label="$t('global.select.select')"
                                        :deselect-label="$t('global.select.deselect')">
                                    </multiselect>
                                    <input type="text" class="form-control" v-else v-model="selectedDependency.value" />
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="editContextAttributeForm" class="btn btn-success" :disabled="editContextAttributeDisabled">
                        <i class="fas fa-fw fa-save"></i> {{ $t('global.update') }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideEditContextAttributeModal">
                        <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>

        <modal name="remove-attribute-from-ct-modal" height="auto" :scrollable="true">
            <div class="modal-content" v-if="openedModal == 'remove-attribute-from-ct-modal'">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $t('global.remove-name.title', {name: $translateConcept(modalSelectedAttribute.thesaurus_url)}) }}</h5>
                    <button type="button" class="close" aria-label="Close" @click="hideRemoveAttributeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        {{ $t('global.remove-name.desc', {name: $translateConcept(modalSelectedAttribute.thesaurus_url)}) }}
                    </p>
                    <p class="alert alert-danger">
                        {{
                            $tc('main.datamodel.detail.attribute.alert', attributeValueCount, {
                                name: $translateConcept(modalSelectedAttribute.thesaurus_url),
                                cnt: attributeValueCount,
                                refname: $translateConcept(modalSelectedEntityType.thesaurus_url)
                            })
                        }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" @click="removeAttributeFromContextType(modalSelectedAttribute)">
                        <i class="fas fa-fw fa-check"></i> {{ $t('global.delete') }}
                    </button>
                    <button type="button" class="btn btn-secondary" @click="hideRemoveAttributeModal">
                        <i class="fas fa-fw fa-times"></i> {{ $t('global.cancel') }}
                    </button>
                </div>
            </div>
        </modal>
    </div>
</template>

<script>
    export default {
        beforeRouteEnter(to, from, next) {
            next(vm => vm.init(to.params.id));
        },
        beforeRouteUpdate(to, from, next) {
            this.init(to.params.id);
            next();
        },
        props: {
            attributes: {
                required: true,
                type: Array
            }
        },
        methods: {
            init(id) {
                this.entityAttributes = [];
                this.entityType = this.$getEntityTypes()[id];
                $http.get(`/editor/context_type/${id}/attribute`)
                    .then(response => {
                        let data = response.data;
                        // if result is empty, php returns [] instead of {}
                        if(data.selections instanceof Array) {
                            data.selections = {};
                        }
                        this.entitySelections = data.selections;
                        this.entityDependencies = data.dependencies;
                        for(let i=0; i<data.attributes.length; i++) {
                            this.entityAttributes.push(data.attributes[i]);
                            // Set values for all context attributes to '', so values in <attributes> are existant
                            Vue.set(this.entityValues, data.attributes[i].id, '');
                        }
                        for(let i=0; i<this.attributes.length; i++) {
                            let id = this.attributes[i].id;
                            let index = this.entityAttributes.findIndex(a => a.id == id);
                            this.attributes[i].isDisabled = index > -1;
                        }
                    });
            },
            updateContextType() {
                if(!this.entityType.id) return;
                const id = this.entityType.id;
                const data = {
                    'is_root': this.entityType.is_root,
                    'sub_context_types': this.entityType.sub_context_types.map(t => t.id)
                };
                $http.post(`/editor/dm/${id}/relation`, data).then(response => {
                    const name = this.$translateConcept(this.entityType.thesaurus_url);
                    this.$showToast(
                        this.$t('main.datamodel.toasts.updated-type.title'),
                        this.$t('main.datamodel.toasts.updated-type.msg', {
                            name: name
                        }),
                        'success'
                    );
                });
            },
            addAttributeToContextType(oldIndex, index) {
                const ctid = this.entityType.id;
                const attribute = this.attributes[oldIndex];
                let attributes = this.entityAttributes;
                let data = {};
                data.attribute_id = attribute.id;
                data.position = index + 1;
                $http.post(`/editor/dm/context_type/${ctid}/attribute`, data).then(response => {
                    // Add element to attribute list
                    attributes.splice(index, 0, response.data);
                    attribute.isDisabled = true;
                    Vue.set(this.entityValues, response.data.id, '');
                    // Update position attribute of successors
                    for(let i=index+1; i<attributes.length; i++) {
                        attributes[i].position++;
                    }
                    const attrName = this.$translateConcept(response.data.thesaurus_url);
                    const etName = this.$translateConcept(this.entityType.thesaurus_url);
                    this.$showToast(
                        this.$t('main.datamodel.toasts.added-attribute.title'),
                        this.$t('main.datamodel.toasts.added-attribute.msg', {
                            name: attrName,
                            etName: etName
                        }),
                        'success'
                    );
                });

            },
            editContextAttribute(attribute, options) {
                const vm = this;
                if(vm.editContextAttributeDisabled) return;
                const aid = attribute.id;
                const ctid = attribute.context_type_id;
                let data = {
                    d_attribute: options.attribute.id,
                    d_operator: options.operator.id
                };
                data.d_value = vm.getDependencyValue(options.value, options.attribute.datatype);
                vm.$http.patch(`/editor/dm/context_type/${ctid}/attribute/${aid}/dependency`, data).then(function(response) {
                    vm.hideEditContextAttributeModal();
                });
            },
            onEditContextAttribute(attribute) {
                const ctid = this.entityType.id;
                this.depends.attributes = this.entityAttributes.filter(function(a) {
                    return a.id != attribute.id;
                });
                let attrDependency = {};
                for(let k in this.entityDependencies) {
                    const attrDeps = this.entityDependencies[k];
                    const dep = attrDeps.find(function(ad) {
                        return ad.dependant == attribute.id;
                    });
                    if(dep) {
                        attrDependency[k] = dep;
                    }
                }
                this.setModalSelectedAttribute(attribute);
                if(Object.keys(attrDependency).length) {
                    this.setSelectedDependency(attrDependency);
                }
                this.openedModal = 'edit-context-attribute-modal';
                this.$modal.show('edit-context-attribute-modal');
            },
            hideEditContextAttributeModal() {
                this.$modal.hide('edit-context-attribute-modal');
                this.openedModal = '';
                this.selectedDependency.attribute = {};
                this.selectedDependency.operator = undefined;
                this.selectedDependency.value = undefined;
            },
            removeAttributeFromContextType(attribute) {
                const ctid = this.entityType.id;
                const aid = attribute.id;
                this.$http.delete('/editor/dm/context_type/'+ctid+'/attribute/'+aid).then(response => {
                    const index = this.entityAttributes.findIndex(function(a) {
                        return a.id == attribute.id;
                    });
                    if(index > -1) {
                        // Remove element from attribute list
                        this.entityAttributes.splice(index, 1);
                        // Update position attribute of successors
                        for(let i=index; i<this.entityAttributes.length; i++) {
                            this.entityAttributes[i].position--;
                        }
                    }
                    this.hideRemoveAttributeModal();
                });
            },
            reorderContextAttribute(oldIndex, index) {
                let attribute = this.entityAttributes[oldIndex];
                const ctid = this.entityType.id;
                let aid = attribute.id;
                let position = index + 1;
                // same index, nothing to do
                if(oldIndex == index) {
                    return;
                }
                let data = {};
                data.position = position;
                $http.patch(`/editor/dm/context_type/${ctid}/attribute/${aid}/position`, data).then(response => {
                    attribute.position = position;
                    this.entityAttributes.splice(oldIndex, 1);
                    this.entityAttributes.splice(index, 0, attribute);
                    if(oldIndex < index) {
                        for(let i=oldIndex; i<index; i++) {
                            this.entityAttributes[i].position--;
                        }
                    } else { // oldIndex > index
                        for(let i=index+1; i<=oldIndex; i++) {
                            this.entityAttributes[i].position++;
                        }
                    }
                });
            },
            dependencyAttributeSelected(attribute) {
                const vm = this;
                if(!attribute) {
                    vm.depends.values = [];
                    return;
                }
                const id = attribute.id;
                switch(attribute.datatype) {
                    case 'string-sc':
                    case 'string-mc':
                        vm.$http.get(`/editor/attribute/${id}/selection`).then(function(response) {
                            vm.depends.values = [];
                            const selections = response.data;
                            if(selections) {
                                for(let i=0; i<selections.length; i++) {
                                    vm.depends.values.push(selections[i]);
                                }
                            }
                        });
                        break;
                    default:
                        vm.depends.values = [];
                        break;
                }
            },
            getDependencyValue(valObject, type) {
                switch(type) {
                    case 'string-sc':
                    case 'string-mc':
                        return valObject.concept_url;
                    default:
                        return valObject;
                }
            },
            // Modal Methods
            onRemoveAttributeFromContextType(attribute) {
                const aid = attribute.id;
                const ctid = this.entityType.id;
                $http.get(`/editor/dm/attribute/occurrence_count/${aid}/${ctid}`).then(response => {
                    this.setModalSelectedAttribute(attribute);
                    this.setModalSelectedContextType(this.entityType);
                    this.setAttributeValueCount(response.data);
                    this.openedModal = 'remove-attribute-from-ct-modal';
                    this.$modal.show('remove-attribute-from-ct-modal');
                });
            },
            hideRemoveAttributeModal() {
                this.$modal.hide('remove-attribute-from-ct-modal');
                this.openedModal = '';
            },
            addAllContextTypes() {
                this.entityType.sub_context_types = [];
                this.entityType.sub_context_types = this.minimalContextTypes.slice();
            },
            removeAllContextTypes() {
                this.entityType.sub_context_types = [];
            },
            setSelectedDependency(values) {
                if(!values) return;
                let aid;
                // We have an object with only one key
                // Hacky way to get that key
                for(let k in values) {
                    aid = k;
                    break;
                }
                this.selectedDependency.attribute = this.entityAttributes.find(function(a) {
                    return a.id == aid;
                });
                this.selectedDependency.operator = {id: values[aid].operator};
                if(this.selectedDependency.attribute) {
                    switch(this.selectedDependency.attribute.datatype) {
                        case 'string-sc':
                        case 'string-mc':
                            this.selectedDependency.value = {
                                concept_url: values[aid].value
                            };
                            break;
                        default:
                            this.selectedDependency.value = values[aid].value;
                            break;
                    }
                }
            },
            setAttributeValueCount(cnt) {
                this.attributeValueCount = cnt;
            },
            setModalSelectedAttribute(attribute) {
                this.modalSelectedAttribute = Object.assign({}, attribute);
            },
            setModalSelectedContextType(contextType) {
                this.modalSelectedEntityType = Object.assign({}, contextType);
            },
            translateLabel(element, prop) {
                return this.$translateLabel(element, prop);
            }
        },
        data() {
            return {
                entityType: {},
                entityAttributes: [],
                entitySelections: {},
                entityDependencies: [],
                entityValues: {},
                selectedDependency: {
                    attribute: {},
                    operator: undefined,
                    value: undefined
                },
                depends: {
                    attributes: [],
                    values: []
                },
                minimalContextTypes: Object.values(this.$getEntityTypes()).map(ct => ({
                        id: ct.id,
                        thesaurus_url: ct.thesaurus_url
                    })),
                openedModal: '',
                modalSelectedAttribute: {},
                modalSelectedEntityType: {},
                attributeValueCount: 0
            }
        },
        computed: {
            dependencyOperators: function() {
                if(!this.selectedDependency.attribute) return [];
                switch(this.selectedDependency.attribute.datatype) {
                    case 'boolean':
                        return [
                            {id: '='}
                        ];
                    case 'double':
                    case 'integer':
                    case 'date':
                    case 'percentage':
                        return [
                            {id: '<'},
                            {id: '>'},
                            {id: '='},
                        ];
                    default:
                        return [
                            {id: '='}
                        ];
                }
            },
            dependencyType: function() {
                if(!this.selectedDependency.attribute) return '';
                switch(this.selectedDependency.attribute.datatype) {
                    case 'boolean':
                        return 'boolean';
                    case 'double':
                        return 'double';
                    case 'integer':
                    case 'date':
                    case 'percentage':
                        return 'integer';
                    case 'string-sc':
                    case 'string-mc':
                        return 'select';
                    default:
                        return 'string';
                }
            },
            editContextAttributeDisabled: function() {
                return !this.modalSelectedAttribute ||
                    // Either all or none of the deps must be set to be valid
                       !(
                           (
                               this.selectedDependency.attribute &&
                               this.selectedDependency.attribute.id &&
                               this.selectedDependency.operator &&
                               this.selectedDependency.operator.id &&
                               this.selectedDependency.value
                            )
                            ||
                            (
                                (
                                    !this.selectedDependency.attribute ||
                                    !this.selectedDependency.attribute.id
                                ) &&
                               (
                                   !this.selectedDependency.operator ||
                                   !this.selectedDependency.operator.id
                               ) &&
                               !this.selectedDependency.value
                            )
                        )
                        ;
            },
        }
    }
</script>
