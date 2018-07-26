<template>
    <div class="h-100 d-flex flex-column">
        <div class="d-flex align-items-center justify-content-between">
            <h1>{{ entity.name }}</h1>
            <span>
                <button type="button" class="btn btn-success" :disabled="!isFormDirty || !$can('duplicate_edit_concepts')" @click="saveEntity(entity)">
                    <i class="fas fa-fw fa-save"></i> Save
                </button>
                <button type="button" class="btn btn-danger" :disabled="!$can('delete_move_concepts')" @click="requestDeleteEntity(entity)">
                    <i class="fas fa-fw fa-trash"></i> Delete
                </button>
            </span>
        </div>
        <attributes class="pt-2 col pl-0 pr-2 scroll-y-auto scroll-x-hidden" v-if="dataLoaded" v-can="'view_concept_props'"
            :attributes="entity.attributes"
            :dependencies="entity.dependencies"
            :disable-drag="true"
            :on-metadata="showMetadata"
            :metadata-addon="hasReferenceGroup"
            :selections="entity.selections"
            :values="entity.data">
        </attributes>

        <entity-reference-modal v-can="'view_concept_props'"></entity-reference-modal>
    </div>
</template>

<script>
    export default {
        beforeRouteEnter(to, from, next) {
            const entityId = to.params.id;
            $http.get(`context/${entityId}`).then(response => {
                next(vm => vm.init(response.data));
            }).catch(error => {
                $throwError(error);
            });
        },
        beforeRouteUpdate(to, from, next) {
            if(to.params.id == from.params.id && to.name == from.name) {
                next();
            } else {
                const entityId = to.params.id;
                $http.get(`context/${entityId}`).then(response => {
                    this.init(response.data);
                    next();
                }).catch(error => {
                    $throwError(error);
                });
            }
        },
        props: {
            bibliography: {
                required: false,
                type: Array,
                default: () => []
            }
        },
        mounted() {},
        methods: {
            init(entity) {
                this.dataLoaded = false;
                this.entity = {};
                this.entity = entity;
                this.getContextData(entity);
            },
            getContextData(entity) {
                const vm = this;
                if(!vm.$can('view_concept_props')) {
                    Vue.set(vm.entity, 'data', {});
                    Vue.set(vm.entity, 'attributes', []);
                    Vue.set(vm.entity, 'selections', {});
                    Vue.set(vm.entity, 'dependencies', []);
                    Vue.set(vm.entity, 'references', []);
                    Vue.set(vm, 'dataLoaded', true);
                    return;
                }
                const cid = entity.id;
                const ctid = entity.context_type_id;
                vm.$http.get(`/context/${cid}/data`).then(function(response) {
                    // if result is empty, php returns [] instead of {}
                    if(response.data instanceof Array) {
                        response.data = {};
                    }
                    Vue.set(vm.entity, 'data', response.data);
                    return vm.$http.get(`/editor/context_type/${ctid}/attribute`);
                }).then(function(response) {
                    vm.entity.attributes = [];
                    let data = response.data;
                    for(let i=0; i<data.attributes.length; i++) {
                        let aid = data.attributes[i].id;
                        if(!vm.entity.data[aid]) {
                            let val = {};
                            switch(data.attributes[i].datatype) {
                                case 'dimension':
                                case 'epoch':
                                    val.value = {};
                                    break;
                                case 'table':
                                case 'list':
                                    val.value = [];
                                    break;
                            }
                            Vue.set(vm.entity.data, aid, val);
                        } else {
                            const val = vm.entity.data[aid].value;
                            switch(data.attributes[i].datatype) {
                                case 'date':
                                    const dtVal = new Date(val);
                                    vm.entity.data[aid].value = dtVal;
                                    break;
                            }
                        }
                        vm.entity.attributes.push(data.attributes[i]);
                    }
                    // if result is empty, php returns [] instead of {}
                    if(data.selections instanceof Array) {
                        data.selections = {};
                    }
                    if(data.dependencies instanceof Array) {
                        data.dependencies = {};
                    }
                    Vue.set(vm.entity, 'selections', data.selections);
                    Vue.set(vm.entity, 'dependencies', data.dependencies);
                    return vm.$http.get(`/context/${cid}/reference`);
                }).then(function(response) {
                    let data = response.data;
                    if(data instanceof Array) {
                        data = {};
                    }
                    Vue.set(vm.entity, 'references', data);
                    Vue.set(vm, 'dataLoaded', true);
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            saveEntity(entity) {
                const vm = this;
                if(!vm.$can('duplicate_edit_concepts')) return;
                let cid = entity.id;
                var patches = [];
                for(let f in vm.fields) {
                    if(vm.fields.hasOwnProperty(f) && f.startsWith('attribute-')) {
                        if(this.fields[f].dirty) {
                            let aid = Number(f.replace(/^attribute-/, ''));
                            let data = entity.data[aid];
                            var patch = {};
                            patch.params = {};
                            patch.params.aid = aid;
                            patch.params.cid = cid;
                            if(data.id) {
                                // if data.id exists, there has been an entry in the database, therefore it is a replace/remove operation
                                patch.params.id = data.id;
                                if(data.value && data.value != '') {
                                    // value is set, therefore it is a replace
                                    patch.op = "replace";
                                    patch.value = data.value;
                                } else {
                                    // value is empty, therefore it is a remove
                                    patch.op = "remove";
                                }
                            } else {
                                // there has been no entry in the database before, therefore it is an add operation
                                if(data.value && data.value != '') {
                                    patch.op = "add";
                                    patch.value = data.value;
                                }
                            }
                            patches.push(patch);
                        }
                    }
                }
                vm.$http.patch('/context/'+cid+'/attributes', patches).then(function(response) {
                    vm.resetFlags();
                    vm.$showToast('Entity updated', `Data of ${entity.name} successfully updated.`, 'success');
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            showMetadata(attribute) {
                const refs = this.entity.references[attribute.thesaurus_url];
                this.$modal.show('entity-references-modal', {
                    attribute: attribute,
                    attributeValue: {...this.entity.data[attribute.id]},
                    bibliography: this.bibliography,
                    references: refs ? refs.slice() : [],
                    active: true,
                    updateCertainty: this.updateCertainty,
                    addReference: this.addReference,
                    deleteReference: this.deleteReference,
                    updateReference: this.updateReference
                });
            },
            updateCertainty(reference) {
                if(!vm.$can('duplicate_edit_concepts')) return;
                const vm = this;
                const cid = vm.entity.id;
                const aid = reference.attribute.id;
                const oldData = vm.entity.data[aid];
                const newData = reference.attributeValue;
                if(newData.possibility == oldData.possibility && newData.possibility_description == oldData.possibility_description) {
                    return;
                }
                const data = {
                    certainty: newData.possibility,
                    certainty_description: newData.possibility_description
                };
                return vm.$http.patch(`/context/${cid}/attribute/${aid}`, data).then(function(response) {
                    oldData.possibility = newData.possibility;
                    oldData.possibility_description = newData.possibility_description;
                    const attributeName = vm.$translateConcept(reference.attribute.thesaurus_url);
                    vm.$showToast('Certainty updated', `Certainty of ${attributeName} successfully set to ${newData.possibility}% (${newData.possibility_description}).`, 'success');
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            addReference(item, reference) {
                const vm = this;
                if(!vm.$can('add_remove_literature')) return;
                const cid = vm.entity.id;
                const aid = reference.attribute.id;
                const data = {
                    bibliography_id: item.bibliography.id,
                    description: item.description
                };
                return vm.$http.post(`/context/${cid}/reference/${aid}`, data).then(function(response) {
                    const refUrl = reference.attribute.thesaurus_url;
                    let refs = vm.entity.references[refUrl];
                    if(!refs) {
                        refs = vm.entity.references[refUrl] = [];
                    }
                    refs.push(response.data);
                    return response.data;
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            deleteReference(reference, referenceModal) {
                const vm = this;
                if(!vm.$can('add_remove_literature')) return;
                const id = reference.id;
                return vm.$http.delete(`/context/reference/${id}`).then(function(response) {
                    let refs = vm.entity.references[referenceModal.attribute.thesaurus_url];
                    const index = refs.findIndex(r => r.id == reference.id);
                    if(index > -1) {
                        refs.splice(index, 1);
                        return index;
                    }
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            updateReference(referenceClone, referenceModal) {
                const vm = this;
                if(!vm.$can('edit_literature')) return;
                const id = referenceClone.id;
                let refs = vm.entity.references[referenceModal.attribute.thesaurus_url];
                let ref = refs.find(r => r.id == referenceClone.id);
                if(ref.description == referenceClone.description) {
                    return;
                }
                const data = {
                    description: referenceClone.description
                };
                return vm.$http.patch(`/context/reference/${id}`, data).then(function(response) {
                    ref.description = referenceClone.description;
                    // Vue.set(referenceModal, 'references', refs.slice());
                }).catch(function(error) {
                    vm.$throwError(error);
                });
            },
            hasReferenceGroup: function(group) {
                if(!this.entity.references) return false;
                if(!Object.keys(this.entity.references).length) return false;
                if(!this.entity.references[group]) return false;
                let count = Object.keys(this.entity.references[group]).length > 0;
                return count > 0;
            },
            resetFlags() {
                this.$validator.fields.items.forEach(field => {
                    field.reset();
                });
            }
        },
        data() {
            return {
                entity: {},
                dataLoaded: false
            }
        },
        computed: {
            isFormDirty: function() {
                return Object.keys(this.fields).some(key => this.fields[key].dirty);
            }
        }
    }
</script>
